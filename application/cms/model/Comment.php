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

class Comment extends BaseModel
{
    use SoftDelete;

    protected $table = 'cms_comment';

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
        $a = $this->field('id,from_uuid,content,circles_uuid,created_at,updated_at,liked,uuid')->page($param['page'],$param['size'])->select();
        return $a;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-25
     *
     * @description 搜索评论列表
     * @param [array] $where
     * @param [int] $page
     * @param [int] size
     * @return array
     */
    public function searchRowsByUid(array $where, int $page, int $size) {
        $a = $this->field('id,from_uuid,to_uuid,type,content,circles_uuid,created_at,updated_at,liked,uuid')->where($where)->page($page,$size)->select();
        return $a;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-24
     *
     * @description 根据circles_id获取列表
     * @param $id
     * @return array
     */
    public function getRowsByCid(string $id) {
        $b = $this->field('id,from_uuid,to_uuid,type,content,circles_uuid,created_at,updated_at,liked,uuid')->where(['circles_uuid' => $id])->select();
        return $b;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-17
     *
     * @description 根据多个circles_id获取列表
     * @param $circles_ids
     * @return array
     */
    public function getRowsByCids(array $circles_ids) {
        $b = $this->field('id,from_uuid,to_uuid,type,content,circles_uuid,created_at,updated_at,liked,uuid')->order('id desc')->where(['circles_uuid' => ['in',$circles_ids]])->select();
        return $b;
    }

    /**
     * @Author panhao
     * @DateTime
     *
     * @description 根据多个circles_id获取评论个数
     * @param $param
     * @return array
     */
    public function getCountByCids(array $circles_uuids) {
        $b = $this->field('count(*),circles_uuid')->group('circles_uuid')->where(['circles_uuid' => ['in',$circles_uuids]])->select();
        return $b;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-17
     *
     * @description 新增数据
     * @param $param
     * @return mixed
     */
    public function addComment(array $param) {

        return $this->allowField(['from_uuid','to_uuid','type','content','circles_uuid','uuid'])->save($param);
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-18
     *
     * @description
     * @param int $id
     * @return int
     */
    public function delComment(array $param) {
        return Comment::destroy($param);
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
     * @DateTime 2017-12-28
     *
     * @description 获取评论发布者uuid
     * @param array $where
     * @param string $field
     * @return array
     */
    public function getUserUuid(array $where,string $field) {
        return $this->field($field)->where($where)->find();
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