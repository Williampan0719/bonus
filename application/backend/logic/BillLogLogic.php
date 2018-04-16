<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/7
 * Time: 09:01
 * @introduce
 */
namespace app\backend\logic;

use app\common\logic\BaseLogic;
use app\payment\model\AbonusSend;
use app\payment\model\BillLog;
use app\payment\model\Bonus;
use app\payment\model\BonusDetail;
use app\payment\model\Wallet;
use think\Exception;

class BillLogLogic extends BaseLogic
{
    protected $type;
    protected $money_source;
    protected $billLogModel;

    public function __construct()
    {
        $this->type = [
            '1' => '发红包',
            '2' => '收红包',
            '3' => '提现',
            '4' => '退款',
            '5' => '提成',
            '6' => '赏红包',
            '7' => '讨红包',
            '8' => '充值金币',
        ];
        $this->money_source = [
            '1' => '微信',
            '2' => '余额',
            '3' => '红包收入',
            '4' => '提成收入',
            '5' => '微信企业收入',
        ];
        $this->billLogModel=new BillLog();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-07
     *
     * @description 资金流水列表(充值)
     * @param array $params
     * @return array
     */
    public function billLogList(array $params)
    {
        try{
            $page = $params['page'] ?? config('paginate.default_page');
            $size = $params['size'] ?? config('paginate.default_size');
            $keyword=$params['keyword']??'';
            $start_time=!empty($params['start_time'])? $params['start_time'] :'1970-01-01';
            $end_time=!empty($params['end_time'])? $params['end_time'] :date('Y-m-d H:i:s',time());
            $total = $this->billLogModel->billLogCount($keyword,$start_time,$end_time);
            if ($total > 0) {
                $bonusList = $this->billLogModel->billLogList($page, $size,$keyword,$start_time,$end_time);
                if ($bonusList != false) {
                    foreach ($bonusList as $k => $v) {
                        $bonusList[$k]['type'] = $this->type[$v['type']];
                        $bonusList[$k]['money_source'] = $this->money_source[$v['money_source']];
                    }
                    $result = $this->ajaxSuccess(202, ['list' => $bonusList, 'total' => $total]);
                } else {
                    $result = $this->ajaxSuccess(202, ['list' => [], 'total' => $total]);
                }
            } else {
                $result = $this->ajaxSuccess(202, ['list' => [], 'total' => $total]);
            }

        }catch (Exception $exception){
            $result=$this->ajaxError(205);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-13
     *
     * @description 收支对账
     * @param array $param
     * @return array
     */
    public function billLogStats(array $param)
    {
        try {
            $where = [];
            if (!empty($param['start_time']) && !empty($param['end_time'])) {
                $where['updated_at'] = ['between', [$param['start_time'],$param['end_time']]];
            }elseif (!empty($param['start_time'])) {
                $where['updated_at'] = ['gt',$param['start_time']];
            }elseif (!empty($param['end_time'])) {
                $where['updated_at'] = ['lt',$param['end_time']];
            }
            //红包充值
            $wx_recharge_where = ['type'=>1,'money_source'=>1];
            $wx_recharge = $this->billLogModel->billLogStats($where,$wx_recharge_where,'affect_money');

            //金币充值
            $coin_recharge_where = ['type'=>8,'money_source'=>1];
            $coin_recharge = $this->billLogModel->billLogStats($where,$coin_recharge_where,'affect_money');

            //提现
            $withdraw_where = ['type' => 3];
            $withdraw = $this->billLogModel->billLogStats($where,$withdraw_where,'affect_money');

            //总余额
            $wallet = new Wallet();
            $wallet_where = [];
            $all_wallet = $wallet->walletStats($where,$wallet_where,'balance');

            //红包毛利
            $bonus_detail = new BonusDetail();
            $bonus = new Bonus();
            $abonus_send = new AbonusSend();
            $receive_service_money_where = ['is_use'=>1];
            $receive_service_money = $bonus_detail->serviceMoneyStats($where,$receive_service_money_where,'receive_service_money');
            $refund_service_money_where = ['finish_at'=>['<>',''],'is_done'=>0];
            $refund_service_money = $bonus->serviceMoneyStats($where,$refund_service_money_where,'refund_service_money');
            $abonus_send_where = $where;
            $abonus_send_where['is_pay'] = 1;
            $abonus_send_where['is_send'] = 1;
            $abonus_service_money = $abonus_send->getSum($abonus_send_where);
            $abonus_service_money = $abonus_service_money*0.06;


            //退款
            $refund_where = ['type' => 4];
            $refund = $this->billLogModel->billLogStats($where,$refund_where,'affect_money');

            $list = [
                'wx_recharge' => (0-$wx_recharge).'元',
                'coin_recharge' => (0-$coin_recharge).'元',
                'all_recharge'=> (0-$wx_recharge-$coin_recharge).'元',
                'withdraw' => (0-$withdraw).'元',
                'change_balance' => '0.00元',
                'all_balance' => $all_wallet.'元',
                'bonus_fees' => ($receive_service_money+$refund_service_money+$abonus_service_money).'元',
                'refund' => $refund.'元',
                'profit' => ($receive_service_money+$refund_service_money+$abonus_service_money).'元',
                'wx_fees' => '0.00元',
            ];
            $result = $this->ajaxSuccess(202, ['list' => $list]);
        }catch (Exception $exception){
            $result=$this->ajaxError(205);
        }
        return $result;
    }
}