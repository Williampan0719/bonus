<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2017/11/20
 * Time: 下午4:48
 */

namespace app\cms\model;

use traits\model\SoftDelete;
use app\common\model\BaseModel;

class Circles extends BaseModel
{
    use SoftDelete;

    protected $table = 'cms_circles';
    protected $pk = 'user_uuid';

    protected $autoWriteTimestamp = 'timestamp';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    /**
     * @Author panhao
     * @DateTime 2017-12-04
     *
     * @description 获取全部数据
     * @param $param
     * @return array
     */
    public function getAll(array $param) {
        $a = $this->field('id,user_uuid,type,content,img,live_url,created_at,updated_at,liked,uuid')->page($param['page'],$param['size'])->select();
        return $a;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-27
     *
     * @description 条件搜索列表
     * @param array $where
     * @param int $page
     * @param int $size
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function searchRowsByUid(array $where, int $page, int $size) {
        $a = $this->field('id,user_uuid,type,content,img,live_url,created_at,updated_at,liked,uuid')->where($where)->page($page,$size)->select();
        return $a;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-12
     *
     * @description 获取前台热门列表/我的动态列表
     * @param $param
     * @return array
     */
    public function getCommunityList(array $param) {
        if (!empty($param['concern_uuid'])) {
            $a = $this->field('id,user_uuid,type,content,img,live_url,created_at,updated_at,liked,uuid')
                ->order('updated_at', 'desc')
                ->where(['user_uuid' => ['in', $param['concern_uuid']]])
                ->page($param['page'], $param['size'])
                ->select();
        }else {
            $a = $this->field('id,user_uuid,type,content,img,live_url,created_at,updated_at,liked,uuid')
                ->order('updated_at', 'desc')
                ->page($param['page'], $param['size'])
                ->select();
        }

        return $a;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-27
     *
     * @description 获取个人动态
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getMyCircles(array $param) {
        $a = $this->field('id,user_uuid,type,content,img,live_url,created_at,updated_at,liked,uuid')
            ->order('updated_at', 'desc')
            ->where(['user_uuid' => $param['user_uuid']])
            ->page($param['page'], $param['size'])
            ->select();

        return $a;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-21
     *
     * @description 根据circles_uuid获取详情
     * @param $param
     * @return array
     */
    public function getCirclesDetail(array $param) {
        $b = $this->field('id,user_uuid,type,content,img,live_url,created_at,updated_at,liked,uuid')
            ->where(['uuid' => $param['circles_uuid']])
            ->find();
        return $b;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-21
     *
     * @description 新增数据
     * @param $param
     * @return mixed
     */
    public function addCircles(array $param) {
        return $this->allowField(['user_uuid','type','content','img','live_url','uuid'])->save($param);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-11
     *
     * @description
     * @param $param
     * @return int|true
     */
    public function setLike(array $param) {
        return $this->where($param)->setInc('liked');
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-11
     *
     * @description
     * @param $param
     * @return int|true
     */
    public function unsetLike(array $param) {
        return $this->where($param)->setDec('liked');
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-19
     *
     * @description
     * @param int $id
     * @return int
     */
    public function delCircles(array $param) {
        return Circles::destroy($param);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-19
     *
     * @description 获取个人动态数量
     * @param int $id
     * @return int
     */
    public function getCountByUuid(string $uuid) {
        return $this->where(['user_uuid' => $uuid])->count();
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-28
     *
     * @description 获取动态发布者uuid
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function getUserUuid(array $where, string $field) {
        return $this->where($where)->value($field);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-28
     *
     * @description 批量获取动态信息
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function getUserInfo(array $where, string $field) {
        return $this->where($where)->field($field)->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-2
     *
     * @description 获取某状态个数
     * @param array $where
     * @return int|string
     */
    public function getCount(array $where)
    {
        return $this->where($where)->count();
    }

}