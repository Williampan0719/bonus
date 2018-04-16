<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2017/12/14
 * Time: 上午11:07
 * @introduce
 */

namespace app\system\logic;


use app\common\logic\BaseLogic;

use app\system\model\SystemImage;
use extend\helper\Rsa;
use extend\helper\Utils;
use think\Exception;

class SystemImageLogic extends BaseLogic
{


    protected $imageModel;

    public function __construct()
    {
        $this->imageModel = new SystemImage();
    }

    /**
     * @Author zhanglei
     * @DateTime  2017-12-18
     *
     * @description  修改照片
     * @param string $uuid
     * @param array $params
     * @return array
     */
    public function photoEdit(string $uuid, array $params)
    {

        try{

            $where['link_uuid'] = $uuid;
            $where['type'] = 3;
            $data['where'] = $where;
            $data['field'] = "uuid as image_uuid, link_uuid as user_uuid, img_path";
            $oldPhotos = $this->imageModel->imageList($data);
            $oldIds = $newIds = [];
            foreach ($oldPhotos as $val) {
                $oldIds[] = $val['image_uuid'];
            }
            $photoList = $params['photo_list'];

            $photoList = htmlspecialchars_decode($photoList);
            $photoList = json_decode($photoList, true);
            $addAll = $editAll = [];
            foreach ($photoList as $val) {
                if (!empty($val['image_uuid'])) {
                    $newIds[] = $val['image_uuid'];
                    $editAll[] = ['uuid'=>$val['image_uuid'], 'link_uuid'=>$uuid, 'img_path'=>$val['img_path']];
                }else{
                    $pUuid = Utils::genUUID('P');
                    $addAll[] = ['link_uuid'=>$uuid, 'uuid'=>$pUuid,'img_path'=>$val['img_path'], 'type'=>3];
                }
            }
            $ids = array_diff($oldIds, $newIds);
            $delResult = $addResult = $editResult = true;
            if(!empty($ids)) {
                $delResult = $this->imageModel->delImage($ids);

            }
            if(!empty($editResult)) {
                $editResult = $this->imageModel->editAllImage($editAll);
            }
            if(!empty($addAll)){
                $addResult = $this->imageModel->addAllImage($addAll);
            }


            if($delResult!==false && $addResult!==false && $editResult!==false) {
                $list = $this->imageModel->imageList($data);
                $result = $this->ajaxSuccess(200, ['photo_list' => $list], '修改成功');
            }else{
                $result = $this->ajaxError(201, [], '修改失败');
            }
        }catch (Exception $exception){
            echo $exception;exit;
            $result = $this->ajaxError(201, [], '修改失败');
        }


        return $result;
    }


    /**
     * @Author zhanglei
     * @DateTime 2017-12-14
     *
     * @description 获取用户照片
     * @param string $uuid
     * @return array
     */
    public function photoList(string $uuid)
    {
        try {

            $where['link_uuid'] = $uuid;
            $where['type'] = 3;
            $data['where'] = $where;
            $data['field'] = "uuid as image_uuid, link_uuid as user_uuid, img_path";

            $list = $this->imageModel->imageList($data);
            $result = $this->ajaxSuccess(200, ['photo_list' => $list], '获取成功');
        } catch (Exception $exception) {
            $result = $this->ajaxError(201, [], '获取失败');
        }

        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-26
     *
     * @description 上传图片
     * @param array $param
     * @return array
     */
    public function uploadImg(array $param)
    {
        try {
            $img = Utils::ossUpload64($param['img'],$param['type']);
            $result = $this->ajaxSuccess(200,['list' => $img],'上传成功');
        } catch (Exception $exception) {
            $result = $this->ajaxError(206, [], '上传失败');
        }
        return $result;
    }

    /**
     * @Author zhanglei
     * @DateTime 2018-02-05
     *
     * @description 版本控制
     * @return array
     */
    public function versionConfig()
    {
        try{
            $openAudit = config('open_audit');
            $result = $this->ajaxSuccess(200, ['open_audit' => $openAudit], '获取成功');
        }catch (Exception $exception){
            $result = $this->ajaxError(201, [], '系统异常');
        }

        return $result;
    }

    /**
     * @Author zhanglei
     * @DateTime 2018-02-05
     *
     * @description 版本信息
     * @param array $params
     * @return array
     */
    public function versionInfo(array $params)
    {
        try{
            if(empty($params)) {
                return $this->ajaxError(201, [], '版本信息不能为空');
            }
            unset($params['token']);
            $info = json_encode($params);
            $rsa = new Rsa();
            $versionHash = $rsa->pubencrypt($info);
            $result = $this->ajaxSuccess(200, ['version_hash' => $versionHash], '获取成功');
        }catch (Exception $exception){

            $result = $this->ajaxError(201, [], '系统异常');
        }

        return $result;
    }

    /**
     * @Author zhanglei
     * @DateTime 2018-02-05
     *
     * @description 版本信息解密
     * @param array $params
     * @return array
     */
    public function deVersionInfo(array $params)
    {
        try{
            $rsa = new Rsa();
            $versionHash = $rsa->pridecrypt($params['hash']);
            $result = ['version_hash' => json_decode($versionHash,true)];
        }catch (Exception $exception){

            $result = $this->ajaxError(201, [], '系统异常');
        }

        return $result;
    }
}