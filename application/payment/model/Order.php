<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/5
 * Time: 16:46
 * @introduce
 */

namespace app\payment\model;

use app\common\model\BaseModel;

class Order extends BaseModel
{
    protected $table = 'payment_order';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-6
     *
     * @description 订单的添加(再用)
     * @param array $params
     * @return mixed
     */
    public function orderAdd(array $params)
    {
        $orderModel=new Order($params);
        $orderModel->allowField(true)->save();
        return $orderModel->id;
    }
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 订单的列表
     * @param int $page
     * @param int $size
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function orderList(int $page, int $size,array $where)
    {
        return $this->where($where)->order('created_at desc')->page($page, $size)->select();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     * @description 订单的总数
     * @param array $where
     * @return int|string
     */
    public function orderCount(array $where)
    {
        return $this->where($where)->order('created_at desc')->count();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 订单修改(在用)
     * @param array $params
     * @return false|int
     */
    public function orderEdit(array $params)
    {
        return Order::allowField(['finish_at','is_close'])
            ->save($params, ['order_sn' => $params['order_sn']]);
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 订单详情(在用)
     * @param string $orderSn
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function orderDetail(string $orderSn)
    {
       return $this->where('order_sn',$orderSn)->field('uid,money,bonus_id,wx_money,type,finish_at')->find();
    }

    /**
     * @Author liyongchuan  panhao
     * @DateTime 2018-01-11 2018-01-27
     *
     * @description 根据bonus_id查询order
     * @param int $bonusId
     * @param int $type
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function orderDetailByBonusId(int $bonusId, int $type = 1)
    {
        return $this->where(['bonus_id'=>$bonusId,'type'=>$type])->field('uid,finish_at,is_close,order_sn,wx_money')->find();
    }
}