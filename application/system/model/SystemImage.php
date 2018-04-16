<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2017/11/22
 * Time: 下午4:48
 */

namespace app\system\model;

use think\Db;
use traits\model\SoftDelete;
use app\common\model\BaseModel;

class SystemImage extends BaseModel
{
    use SoftDelete;

    protected $table = 'system_image';


    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = 'deleted_at';
    protected $pk = 'uuid';

    public function getRowsByIds($ids)
    {
        $b = $this->field('*')->order('id asc')->where(['id' => ['in', $ids]])->select();
        return $b;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-25
     *
     * @description 新增数据
     * @param $param
     * @return mixed
     */
    public function addImage($param)
    {
        $img = new SystemImage($param);
        $img->allowField(true)->save();
        return $img->uuid;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-26
     *
     * @description 修改产品
     * @param $param
     * @param $id
     * @return false|int
     */
    public function editImage($param, $id)
    {
        return $this->allowField(true)->save($param,['link_uuid'=> $id]);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-26
     *
     * @description 图片删除
     * @param array $param
     * @return int
     */
    public function delImage(array $param)
    {
        return SystemImage::destroy($param);
    }


    /**
     * @Author zhanglei
     * @DateTime 2017-12-14
     *
     * @description 个人照片添加
     * @param array $params
     * @return int|string
     */
    public function addAllImage(array $params)
    {

        $result = SystemImage::allowField(true)->insertAll($params);

        return $result;
    }


    /**
     * @Author zhanglei
     * @DateTime 2017-12-14
     *
     * @description 个人照片修改
     * @param array $params
     * @return array|false
     */
    public function editAllImage(array $params)
    {
        $result = SystemImage::saveAll($params);

        return $result;
    }


    /**
     * @Author zhanglei
     * @DateTime 2017-12-19
     *
     * @description 图片列表
     * @param $params
     * @return mixed
     */
    public function imageList(array $params)
    {

        $field = $params['field'] ?? '';
        $page = $params['page'] ?? '';
        $size = $params['size'] ?? '';
        $where = $params['where'] ?? '';

        return SystemImage::field($field)->where($where)->page($page, $size)->select();

    }


}