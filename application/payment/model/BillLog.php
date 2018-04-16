<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/6
 * Time: 14:16
 * @introduce
 */
namespace app\payment\model;

use app\common\model\BaseModel;

class BillLog extends BaseModel
{
    protected $table = 'payment_bill_log';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /**
     * @Author liyongchuan
     * @DateTime 2017-01-06
     *
     * @description 资金流水添加(在用)
     * @param array $params
     * @return mixed
     */
    public function billLogAdd(array $params)
    {
        $billLogModel=new BillLog($params);
        $billLogModel->allowField(true)->save();
        return $billLogModel->id;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-27
     *
     * @description 批量新增流水记录
     * @param array $param
     * @return array|false
     */
    public function billLogAddAll(array $param)
    {
        return $this->allowField(true)->saveAll($param);
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-07
     *
     * @description 资金流水总数
     * @param string $keyword
     * @param string $start_time
     * @param string $end_time
     * @return int|string
     */
    public function billLogCount(string $keyword,string $start_time,string $end_time)
    {
        $where['u.nickname']=['like','%'.$keyword.'%'];
        $where['b.type']=['NEQ',3];
        if($start_time!='0' && $end_time!='0') {
            $where['b.created_at'] = ['between', [$start_time, $end_time]];
        }
        return $this->alias('b')->join('user u','u.openid=b.uid')->
        where($where)->count();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-07
     *
     * @description 资金流水分页
     * @param string $page
     * @param string $size
     * @param string $keyword
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function billLogList(string $page,string $size,string $keyword,string $start_time,string $end_time)
    {
        $where['u.nickname']=['like','%'.$keyword.'%'];
        $where['b.type']=['NEQ',3];
        if($start_time!='0' && $end_time!='0') {
            $where['b.created_at'] = ['between', [$start_time, $end_time]];
        }
        return $this->alias('b')->join('user u','u.openid=b.uid')->
        where($where)->
        field('u.nickname,b.type,b.affect_money,b.balance_money,b.created_at,b.money_source')->page($page,$size)->select();
    }
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-11
     *
     * @description 个人资金明细列表
     * @param string $uid
     * @param string $page
     * @param string $size
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function billLogListApi(string $uid,string $page,string $size)
    {
        return $this->where('uid',$uid)->field('type,affect_money,created_at,from_uid')->order('created_at desc')->page($page,$size)->select();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-11
     *
     * @description 个人资金明细数量
     * @param string $uid
     * @return int|string
     */
    public function billLogCountApi(string $uid)
    {
        return $this->where('uid',$uid)->count();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-09
     *
     * @description 账单搜索
     * @param array $param
     * @param int $page
     * @param int $size
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function searchBillList(array $param,int $page,int $size, $field = '*')
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
    public function billLogStats(array $param, array $where2, string $field)
    {
        return $this->where($param)->where($where2)->sum($field);
    }
}