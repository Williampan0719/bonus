<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/6
 * Time: 08:50
 * @introduce
 */

namespace app\payment\model;

use app\common\model\BaseModel;
use think\Exception;

class Bonus extends BaseModel
{
    protected $table = 'payment_bonus';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    protected $autoWriteTimestamp ='timestamp';
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 红包的添加(在用)
     * @param array $params
     * @return mixed
     */
    public function bonusAdd(array $params)
    {
        $bonusModel = new Bonus($params);
        $bonusModel->allowField(true)->save();
        return $bonusModel->id;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 查询条件
     * @param $query
     * @param $keyword
     */
    protected function scopeBonusWhere($query, $keyword)
    {
        $query->where('admin_name', 'like', '%' . $keyword . '%');
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 红包的列表
     * @param int $page
     * @param int $size
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function bonusList(int $page, int $size)
    {
        return $this->field('id,uid,bonus_money,bonus_num,bonus_password,created_at,finish_at,service_money,refund_service_money,refund_money,is_pay,receive_bonus_num,is_done')
            ->order('created_at desc')->page($page, $size)->select();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 红包的总数
     * @return int|string
     */
    public function bonusCount()
    {
        return $this->count();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-10
     *
     * @description 获取红包口令
     * @param $bonus_id
     * @return mixed
     */
    public function getBonusTit($bonus_id)
    {
        return $this->where(['id' => $bonus_id])->value('bonus_password');
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-07
     *
     * @description 根据uid查询红包(在用)
     * @param string $uid
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function bonusFindByUid(string $uid)
    {
        return $this->where('uid', $uid)->where('is_pay',1)->
        field('uid,bonus_money,bonus_num,bonus_password,created_at,id,finish_at,class,voice_path')->order('created_at desc')->select();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-07
     *
     * @description 红包修改(在用)
     * @param array $params
     * @return false|int
     */
    public function bonusEdit(array $params)
    {
        return $this->allowField('is_pay,service_money,refund_service_money,finish_at,prepay_id,receive_bonus_num')->save($params,['id'=>$params['bonus_id']]);
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-08
     *
     * @description 红包的详情(在用)
     * @param int $bonusId
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function bonusDerail(int $bonusId)
    {
        return $this->where('id',$bonusId)->find();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-08
     *
     * @description 获取发红包人的详情(在用)
     * @param int $bonus_id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getBonusUser(int $bonus_id)
    {
        return $this->alias('b')
            ->join('wx_user u', 'u.openid = b.uid', 'inner')
            ->where(['b.id' => $bonus_id])->field('u.nickname,u.avatarulr,u.gender')->find();
    }

    /**
     * auth smallzz
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getInfo(int $bonus_id){
        return $this->where(['id'=>$bonus_id])->field('uid,created_at,form_id')->find();
    }

    /** 修改口令
     * auth smallzz
     * @param int $bonus_id
     * @param string $str
     * @return false|int
     */
    public function example(int $bonus_id,string $str){
        return $this->save(['bonus_password'=>$str],['id'=>$bonus_id]);
    }

    /** 获取发送最多的口令
     * auth smallzz
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getHot(){
        return $this->where('class = 0')->group('bonus_password')
            ->order('count(id) desc')
            ->field('bonus_password')
            ->limit(5)
            ->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-05
     *
     * @description 广告大厅
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAdvHall(array $param, int $page, int $size)
    {
        return $this->where($param)->field('bonus_num,adv_name,adv_logo,id,uid,bonus_password')->page($page,$size)->order('created_at desc')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-06
     *
     * @description 用户红包数
     * @param array $param
     * @return int|string
     */
    public function getUserCount(array $param)
    {
        return $this->where($param)->order('created_at desc')->count();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-09
     *
     * @description 红包搜索
     * @param array $param
     * @param int $page
     * @param int $size
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function searchBonusList(array $param,int $page,int $size, $field = '*')
    {
        return $this->field($field)->where($param)->page($page,$size)->order('created_at desc')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-12
     *
     * @description 获取领取金额
     * @param int $bonus_id
     * @return mixed
     */
    public function getSendUserOne(int $bonus_id)
    {
        return $this->where(['id'=>$bonus_id])->value('uid');
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-12
     *
     * @description 获取各项条件总计
     * @param array $param
     * @param array $where2
     * @param string $field
     * @return float|int
     */
    public function serviceMoneyStats(array $param, array $where2, string $field)
    {
        return $this->where($param)->where($where2)->sum($field);
    }


}