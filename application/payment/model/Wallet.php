<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/6
 * Time: 14:01
 * @introduce
 */
namespace app\payment\model;

use app\common\model\BaseModel;

class Wallet extends BaseModel
{
    protected $table = 'payment_wallet';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 钱包详情(在用)
     * @param string $uid
     * @param string $field
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function walletDetail(string $uid, string $field = '*')
    {
        return $this->where('uid',$uid)->field($field)->find();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-07
     *
     * @description 余额修改(在用)
     * @param array $params
     * @return false|int
     */
    public function walletEdit(array $params)
    {
        return $this->allowField('balance,frozen_money')->save($params,['uid'=>$params['uid']]);
    }

    /** 获取余额
     * auth smallzz
     * @param string $openid
     */
    public function getYuE(string $openid){
        return $this->where(['uid'=>$openid])->value('balance');
    }

    /** 获取虚拟币余额
     * auth smallzz
     * @param string $openid
     * @return mixed
     */
    public function getVirtual(string $openid){
        return $this->where(['uid'=>$openid])->value('virtual');
    }

    /** 设置虚拟币
     * auth smallzz
     * @param string $openid
     * @return mixed
     */
    /*public function setVirtual(string $openid,int $virtual){
        return $this->save(['virtual'=>$virtual],['uid'=>$openid]);
    }*/
    public function setVirtual(string $openid,int $virtual){
        return $this->where(['uid'=>$openid])->setInc('virtual',$virtual);
    }

    /** 减虚拟币
     * auth smallzz
     * @param string $openid
     * @param int $virtual
     * @return int|true
     */
    public function delVirtual(string $openid,int $virtual){
        return $this->where(['uid'=>$openid])->setDec('virtual',$virtual);
    }

    /** 增加余额
     * auth smallzz
     * @param string $openid
     * @param int $balance
     * @return int|true
     */
    public function setBalance(string $openid,$balance){
        #var_dump($balance);exit;
        return $this->where(['uid'=>$openid])->setInc('balance',$balance);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-07
     *
     * @description 减少余额
     * @param string $openid
     * @param $balance
     * @return int|true
     */
    public function delBalance(string $openid, $balance) {
        return $this->where(['uid'=>$openid])->setDec('balance',$balance);
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
    public function walletStats(array $param, array $where2, string $field)
    {
        return $this->where($param)->where($where2)->sum($field);
    }
}