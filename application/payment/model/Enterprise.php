<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/19
 * Time: 17:43
 * @introduce
 */
namespace app\payment\model;

use app\common\model\BaseModel;

class Enterprise extends BaseModel
{
    protected $table = 'payment_enterprise';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-19
     *
     * @description 企业支付(提现)记录添加
     * @param array $params
     * @return mixed
     */
    public function enterpriseAdd(array $params)
    {
        $enterpriseModel=new Enterprise($params);
        $enterpriseModel->allowField(true)->save();
        return $enterpriseModel->id;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-19
     *
     * @description 查询每种类型的企业支付(提现)次数
     * @param string $uid
     * @param int $type
     * @return int|string
     */
    public function enterpriseLimit(string $uid,int $type)
    {
        $startTime=date('Y-m-d',time()).' 00:00:00';
        $endTime=date('Y-m-d',time()).' 23:59:59';
        return $this->where('uid',$uid)->where('type',$type)->
        where('created_at', 'between time', [$startTime, $endTime])->count();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-22
     *
     * @description 查询最后一条数据
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function enterpriseLast()
    {
        return $this->order('id desc')->find();
    }
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-22
     *
     * @description 企业账户充值列表
     * @param int $page
     * @param int $size
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function enterpriseRecharge(int $page,int $size)
    {
       return $this->where('type',0)->
       find('money,created_at')->order('created_at desc')->
       page($page,$size)->select();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-22
     *
     * @description 企业账户充值列表总条数
     * @return int|string
     */
    public function enterpriseRechargeCount()
    {
        return $this->where('type',0)->count();
    }
}