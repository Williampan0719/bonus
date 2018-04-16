<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2017/12/11
 * Time: 上午11:12
 * @introduce 点赞
 */
namespace app\cms\model;

use app\common\model\BaseModel;

class CirclesLike extends BaseModel
{

    protected $table = 'cms_circles_like';

    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    /**
     * @Author panhao
     * @DateTime 2017-12-11
     *
     * @description 新增数据
     * @param $param
     * @return mixed
     */
    public function addCirclesLike(array $param) {
        return $this->allowField(['user_uuid','circles_uuid'])->save($param);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-25
     *
     * @description 判断用户是否已点赞
     * @param string $user_uuid
     * @param string $circles_uuid
     * @return int|string
     */
    public function isLiked(string $user_uuid, string $circles_uuid) {
        return $this->where(['user_uuid' => $user_uuid, 'circles_uuid' => $circles_uuid])->count();
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-25
     *
     * @description 取消点赞
     * @param array $where
     * @return int
     */
    public function delCirclesLike($where) {

        return $this->where($where)->delete();
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-18
     *
     * @description  获取点赞列表
     * @param int $id
     * @return int
     */
    public function getRowsByCids(array $circles_uuids, string $uuid) {
        $b = $this->field('*')->where(['user_uuid' => $uuid])->where(['circles_uuid' => ['in',$circles_uuids]])->select();
        return $b;
    }
}
