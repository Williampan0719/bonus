<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/6
 * Time: 10:38
 * @introduce
 */

namespace app\payment\model;

use app\common\model\BaseModel;
use think\Exception;

class BonusReceive extends BaseModel
{
    protected $table = 'payment_bonus_receive';

    protected $createTime = 'created_at';
    protected $updateTime = false;

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 领取者详情
     * @param int $bonusID
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function bonusReceiveList(int $bonusID)
    {
        return  $this->where('bonus_id',$bonusID)->select();
    }
    /** 添加
     * auth smallzz
     * @param array $param
     * @return string
     */
    public function addRece(array $param){
        $Model = new BonusReceive($param);
        $Model->allowField(true)->save();
        return $Model->getLastInsID();
    }
    //----------------xin----------
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-07
     *
     * @description 根据uid查询领取红包(在用)
     * @param string $receiveUid
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function bonusReceive(string $receiveUid)
    {
       return $this->where('receive_uid',$receiveUid)->where('detail_id','>',0)
           ->field('bonus_id,detail_id,created_at')->order('created_at desc')->select();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-07
     *
     * @description 根据bonus_id查询领取红包(在用)
     * @param int $bonusID
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function bonusReceiveByBonusID(int $bonusID)
    {
        return $this->where('bonus_id',$bonusID)->where('detail_id','>',0)
            ->field('bonus_id,detail_id,created_at,receive_uid,receive_voice,time_length,identify_num')->select();
    }

    /** 查询是否领取过此红包（不为空说明领取过）
     * auth smallzz
     * @param $opeind
     * @param $bonus_id
     * @return int|string
     */
    public function checkReceStatus($opeind,$bonus_id){
        return $this->where(['bonus_id'=>$bonus_id,'receive_uid'=>$opeind])->count();
    }

    /** 获取指定人领取数
     * auth smallzz
     * @param $openid
     * @return int|string
     */
    public function getMeTotal($openid){
        return $this->where(['receive_uid'=>$openid])->count();
    }

    /** 获取今日访问数
     * auth smallzz
     * @return int|string
     */
    public function getDayRenshu(){
        $stime = date('Y-m-d 00:00:00');
        $etime = date('Y-m-d 23:59:59');
        return $this->where('created_at >= "'.$stime.'" and created_at <= "'.$etime.'"')->count();
    }

    /** 获取limit100
     * auth smallzz
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getReceLimit(){
        return $this->alias('br')
            ->join('wx_payment_bonus_detail bd','bd.id = br.detail_id','inner')
            ->join('wx_user u','u.openid = br.receive_uid','inner')
            ->field('u.nickname,u.avatarulr,bd.receive_money')
            ->limit(100)
            ->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-09
     *
     * @description 红包领取搜索
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
     * @description 获取个数
     * @param array $param
     * @return int|string
     */
    public function getMeGroupCount(array $param)
    {
        return $this->where($param)->count();
    }

}