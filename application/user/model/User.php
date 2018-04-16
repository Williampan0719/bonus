<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/5
 * Time: 下午2:23
 */

namespace app\user\model;


use app\common\model\BaseModel;

class User extends BaseModel
{
    protected $table = 'user';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    public function userAdd(array $params){

        $this->save($params);

    }


    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 获取userID详情(在用)
     * @param string $uid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function userDetail(string $uid)
    {
        return  $this->where('openid',$uid)->field('nickname,avatarulr,gender')->find();
    }

    /** 获取用户详情 指定字段
     * auth smallzz
     * @param string $uid
     * @param $field
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function userDetailAll(string $uid,string $field){
        return $this->where(['openid'=>$uid])->field($field)->find();
    }

    /** 检查openid的信息是否存在
     * auth smallzz
     * @param string $openid
     * @return int|string
     */
    public function checkOpenid(string $openid){
        return $this->where(['openid'=>$openid])->count();
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-10
     *
     * @description 批量获取昵称
     * @param array $open_ids
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getNameList(array $open_ids)
    {
        return $this->where(['openid'=>['in',$open_ids]])->field('nickname,openid,avatarulr,gender')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-13
     *
     * @description 根据id获取openid
     * @param string $id
     * @return mixed
     */
    public function getOpenidById(string $id)
    {
        return $this->where(['id'=>$id])->value('openid');
    }

    /**
     * @Author zhanglei
     * @DateTime 2017-12-25
     *
     * @description 获取用户openid
     * @param array $where
     * @return mixed
     */
    public function getOpenid(array $where)
    {
        return $this->where($where)->field('openid')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-11
     *
     * @description 获取单条
     * @param string $openid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function userPanel(string $openid)
    {
        return $this->where(['openid'=>$openid])->field('nickname,avatarulr,created_at,distribute_time,status,truename,mobile,ip')->find();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-13
     *
     * @description 冻结/解封
     * @param array $param
     * @return false|int
     */
    public function forbidUser(array $param)
    {
        return $this->allowField('status,ip')->save($param,['openid'=>$param['openid']]);
    }
}