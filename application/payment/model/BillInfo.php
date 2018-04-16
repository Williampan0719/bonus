<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/6
 * Time: 13:19
 * @introduce
 */
namespace app\payment\model;

use app\common\model\BaseModel;

class BillInfo extends BaseModel
{
    protected $table = 'payment_bill_info';

    protected $createTime = "created_at";

    protected $updateTime = false;

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-02
     *
     * @description 支付信息添加(再用)
     * @param array $params
     * @return mixed
     */
    public function billInfoAdd(array $params)
    {
        $billInfoModel=new BillInfo($params);
        $billInfoModel->save();
        return $billInfoModel->id;
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 支付详情
     * @param string $orderSn
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function billInfoDetail(string $orderSn)
    {
        return $this->where('order_sn',$orderSn)->find();
    }
}