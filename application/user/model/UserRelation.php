<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/10
 * Time: 下午1:43
 * @introduce
 */
namespace app\user\model;


use app\common\model\BaseModel;

class UserRelation extends BaseModel
{
    protected $table = 'user_relation';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    /**
     * @Author panhao
     * @DateTime 2018-1-10
     *
     * @description 绑定用户
     * @param array $param
     * @return false|int
     */
    public function bindingUser(array $param)
    {
        return $this->allowField(true)->save($param);
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-12
     *
     * @description 批量绑定
     * @param array $param
     * @return array|false
     */
    public function bindingUserAll(array $param)
    {
        return $this->allowField(true)->saveAll($param);
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-10
     *
     * @description 获得单条信息
     * @param string $uid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getOne(string $uid)
    {
        return $this->field('id,uid,pid,depth,path,created_at')->where(['uid'=>$uid])->find();
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-10
     *
     * @description 判断用户是否已存在绑定
     * @param array $param
     * @return int|string
     */
    public function getExist(array $param)
    {
        return $this->where($param)->count();
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-11
     *
     * @description 获取邀请列表
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getUidList(array $param)
    {
        return $this->field('id,uid,pid,depth,path,created_at')->order('created_at desc')->where($param)->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-23
     *
     * @description 获取分组统计
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getGroupCount(array $param)
    {
        return $this->group('depth')->field('count(*)')->where($param)->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-10
     *
     * @description 条件搜索列表
     * @param array $where
     * @param int $page
     * @param int $size
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function searchRowsByUid(array $where, int $page, int $size) {
        return $this->field('id,uid,pid,depth,path,created_at')->order('created_at desc')->where($where)->page($page,$size)->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-23
     *
     * @description 条件搜索列表
     * @param array $where
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function searchRowsCount(array $where) {
        return $this->field('id,uid,pid,depth,path,created_at')->order('created_at desc')->where($where)->count();
    }
}