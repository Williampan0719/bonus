<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/8
 * Time: 上午9:40
 */

namespace app\payment\model;

use app\common\model\BaseModel;
use think\db\Query;

class BonusDetail extends BaseModel
{

    protected $table = 'payment_bonus_detail';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $bonus = null;
    function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->bonus = new Bonus();
    }
    /**
     * auth smallzz
     * @param array $param
     * @return false|int
     */
    public function editReceBonus(array $param,array $where)
    {

        return $this->save($param, ['id' => $where['recedetail_id']]);

    }
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-08
     *
     * @description 红包详情的添加单个(在用)
     * @param array $params
     * @return mixed
     */
    public function detailAdd(array $params)
    {
        $bonusDetail = new BonusDetail($params);
        $bonusDetail->allowField(true)->save();
        return $bonusDetail->id;
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 红包详情的添加多个(在用)
     * @param array $params
     * @return array|false
     */
    public function detailAddAll(array $params)
    {
        return $this->saveAll($params);
    }
    public function detailAdd1(array $params)
    {
        foreach ($params as $key=>$vo){
            $bonusDetail = new BonusDetail($vo);
            $bonusDetail->allowField(true)->save();
        }
        return 1;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-08
     *
     * @description 根据bonusID查询领取个数(在用)
     * @param int $bonusID
     * @return int|string
     */
    public function bonusReceiveCountByBonusSn(int $bonusID)
    {
        return $this->where('bonus_id',$bonusID)->where('is_use',1)->count();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-08
     *
     * @description 领取红包的详情(在用)
     * @param int $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function bonusReceiveDetail(int $id)
    {
       return $this->where('id',$id)->where('is_use',1)->find();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-08
     *
     * @description 领取红包
     * @param int $bonusID
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function bonusRefundDetail(int $bonusID)
    {
        return $this->where('bonus_id',$bonusID)->
        field('receive_money,receive_service_money,is_use')->select();
    }

    /** 检测红包是否被领取完毕
     * auth smallzz
     * @param $bonus_id
     * @return int|string
     */
    public function checkReceDone(int $bonus_id){
        return $this->where(['bonus_id'=>$bonus_id,'is_use'=>0])->count();
    }

    /** 获取新增的数据
     * auth smallzz
     */
    public function getAddData(int $bonus_id){
        return $this->where(['bonus_id'=>$bonus_id,'is_use'=>0])->field('id,receive_money')->select();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-10
     *
     * @description 统计领取金额
     * @param int $bonus_id
     * @return float|int
     */
    public function getReceiveMoney(int $bonus_id)
    {
        return $this->where(['bonus_id'=>$bonus_id,'is_use'=>1])->sum('receive_money');
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-10
     *
     * @description 统计应领取金额
     * @param int $bonus_id
     * @return float|int
     */
    public function getPayableMoney(int $bonus_id)
    {
        return $this->where(['bonus_id'=>$bonus_id,'is_use'=>1])->sum('payable_money');
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-12
     *
     * @description 获取领取金额
     * @param int $bonus_id
     * @return mixed
     */
    public function getReceiveMoneyOne(int $bonus_id)
    {
        return $this->where(['id'=>$bonus_id,'is_use'=>1])->value('receive_money');
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