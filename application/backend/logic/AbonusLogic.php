<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/28
 * Time: 下午4:13
 * @introduce 后台红包管理逻辑
 */
namespace app\backend\logic;

use app\common\logic\BaseLogic;
use app\payment\model\Abonus;
use app\payment\model\AbonusSend;
use app\system\model\SystemAbonusTemplate;
use app\user\model\User;
use extend\helper\Utils;
use think\Exception;

class AbonusLogic extends BaseLogic
{
    protected $abonusModel;
    protected $abonusSend;
    protected $user;

    public function __construct()
    {
        $this->abonusModel = new Abonus();
        $this->abonusSend = new AbonusSend();
        $this->user = new User();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-29
     *
     * @description 后台搜索
     * @param array $param
     * @return array
     */
    public function searchRows(array $param)
    {
        try {
            $page = $param['page'] ?? 1;
            $size = $param['size'] ?? 10;
            $where = [];
            //发包人
            if (!empty($param['name'])) {
                $ids = $this->user->getOpenid(['nickname'=>$param['name']]);
                $where['uid'] = ['in',array_column($ids,'openid')];
            }
            if (!empty($param['openid'])) {
                $where['uid'] = $param['openid'];
            }

            if (!empty($param['start_time']) && !empty($param['end_time'])) {
                $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
            }elseif (!empty($param['start_time'])) {
                $where['created_at'] = ['gt',$param['start_time']];
            }elseif (!empty($param['end_time'])) {
                $where['created_at'] = ['lt',$param['end_time']];
            }

            $data = $this->abonusModel->searchRows($where,$page,$size);

            //获取昵称
            if (!empty($data)) {
                $name_list = array_column($data,'uid');
                $name = $this->user->getNameList($name_list);
                $a = [];
                foreach ($name as $k => $v) {
                    $a[$v['openid']]['nickname'] = $v['nickname'];
                    $a[$v['openid']]['avatarulr'] = $v['avatarulr'];
                }
                foreach ($data as $k => $v) {
                    $data[$k]['name'] = $a[$v['uid']]['nickname'] ?? '';
                    $data[$k]['avatarulr'] = $a[$v['uid']]['avatarulr'] ?? '';
                    $data[$k]['receive_money'] = sprintf("%01.2f",$v['receive_money']);
                }
            }
            $result = $this->ajaxSuccess(102,['list'=>$data]);
        }catch (Exception $exception){
            $result=$this->ajaxError(105);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-01
     *
     * @description 后台讨红包详情
     * @param array $param
     * @return array
     */
    public function searchDetailRows(array $param)
    {
        try {
            $page = $param['page'] ?? 1;
            $size = $param['size'] ?? 10;

            $one = $this->abonusModel->getDetail($param['id']);

            $data = $this->abonusSend->searchRowById(['abonus_id'=>$param['id'],'is_pay'=>1],$page,$size);

            $uid = [$one['uid']];
            if (!empty($data)) {
                $uid = array_merge($uid,array_column($data,'uid'));
            }
            //昵称头像
            $name = $this->user->getNameList($uid);
            $names = [];
            foreach ($name as $k => $v) {
                $names[$v['openid']] = $v;
            }
            foreach ($data as $key => $value) {
                $data[$key]['nickname'] = $names[$value['uid']]['nickname'] ?? '';
                $data[$key]['avatarulr'] = $names[$value['uid']]['avatarulr'] ?? '';
                $data[$key]['gender'] = $names[$value['uid']]['gender'] ?? 0;
            }
            $one['nickname'] = $names[$one['uid']]['nickname'] ?? '';
            $one['avatarulr'] = $names[$one['uid']]['avatarulr'] ?? '';
            $one['list'] = $data;
            $total = $this->abonusSend->getCount(['abonus_id'=>$param['id'],'is_pay'=>1]);


            $result = $this->ajaxSuccess(102,['total'=>$total,'list'=>$one]);
        }catch (Exception $exception){
            $result=$this->ajaxError(105);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-29
     *
     * @description 添加模板
     * @param array $param
     * @return array
     */
    public function addTemplate(array $param)
    {
        $data = Utils::ossUpload64([$param['img']],'template');
        if (empty($data)) {
            return $this->ajaxError(105);
        }
        $where = [
            'url' => $data[0][0],
            'word'=> $param['word'],
            'class'=>$param['class'],
            'scenes'=>$param['scenes'],
        ];
        $template = new SystemAbonusTemplate();
        $a = $template->addTemplate($where);
        if ($a) {
            return $this->ajaxSuccess(100,['list'=>$where]);
        }
        return $this->ajaxError(105);
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-29
     *
     * @description 模板列表
     * @return array
     */
    public function getTemplateList()
    {
        try {
            $template = new SystemAbonusTemplate();
            $list = $template->getAll();
            if (!empty($list)) {
                $result = $this->ajaxSuccess(102,['list'=>$list]);
            } else {
                $result = $this->ajaxError(105);
            }

        } catch (Exception $exception) {
            $result = $this->ajaxError(105);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-30
     *
     * @description 模板编辑
     * @param array $param
     * @return array
     */
    public function editTemplate(array $param)
    {
        try {
            $where = ['id'=>$param['id']];
            if (!empty($param['img'])) {
                $data = Utils::ossUpload64([$param['img']],'template');
                if (empty($data)) {
                    return $this->ajaxError(105);
                }
                $where['url'] = $data[0][0];
            }
            if (!empty($param['word'])) {
                $where['word'] = $param['word'];
            }
            $where['status'] = $param['status'] ?? 1;

            $template = new SystemAbonusTemplate();
            $a = $template->editTemplate($where);
            if (!empty($a)) {
                $result = $this->ajaxSuccess(201);
            } else {
                $result = $this->ajaxError(207);
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(207);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-11
     *
     * @description 删除模板
     * @param int $id
     * @return array
     */
    public function delTemplate(int $id)
    {
        $template = new SystemAbonusTemplate();
        $a = $template->delTemplate($id);
        if (!empty($a)) {
            return $this->ajaxSuccess(203);
        } else {
            return $this->ajaxError(204);
        }
    }
}