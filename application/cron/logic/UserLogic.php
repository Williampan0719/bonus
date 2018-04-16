<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/15
 * Time: 09:28
 * @introduce
 */

namespace app\cron\logic;

use app\common\logic\BaseLogic;
use app\game\model\GameCoinLog;
use app\game\model\GameSsc;
use app\payment\logic\DistributeLogic;
use app\payment\logic\PayLogic;
use app\payment\model\Order;
use app\payment\model\Wallet;
use extend\helper\Files;
use extend\service\RedisService;
use extend\service\WechatService;
use think\Cache;
use think\Db;
use think\Exception;

class UserLogic extends BaseLogic
{
    private $config;
    private $redisService;

    public function __construct()
    {
        $this->config = config('payment.automatic_refund');
        $this->redisService = new RedisService();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-10
     *
     * @description 24小时自动退款(除讨红包外,讨红包无退款)
     * @return bool
     */
    public function automaticRefund()
    {
        $wxService = new WechatService();
        $payLogic = new PayLogic();
        $time = date('Y-m-d H:i:s', time() - $this->config['automatic_time']);
        //未领完的红包
        $refundBonus = Db::table('wx_payment_bonus')->where('class','<', 2)->where('is_pay', 1)->
        where('finish_at', null)->where('created_at', '<', $time)->select();

        //广告红包48小时
        $time2 = date('Y-m-d H:i:s', time() - $this->config['adv_automatic_time']);
        //未领完的红包
        $refundBonus2 = Db::table('wx_payment_bonus')->where('class', 2)->where('is_pay', 1)->
        where('finish_at', null)->where('created_at', '<', $time2)->select();

        $refundBonus = array_merge($refundBonus,$refundBonus2);

        foreach ($refundBonus as $key => $vo) {
            if ($vo['bonus_num'] == 1) {
                $rateArray = $this->_bonusOne($vo['id']);//退款服务费,退款金额,领取服务费(单个红包)
            } else {
                $rateArray = $this->_bonusDouble($vo['id']);//退款服务费,退款金额,领取服务费(多个红包)
            }
            Db::startTrans();
            //红包修改
            $bonusUpdate = [
                'service_money' => $rateArray['receiveServiceMoney'],
                'refund_service_money' => $rateArray['refundServiceMoney'],
                'refund_money' => $rateArray['refundMoney'],
                'finish_at' => date('Y-m-d H:i:s'),
                'is_done' => 1,
            ];
            //退款金额大于0,退款到余额
            if ($rateArray['refundMoney'] > 0) {
                //提成记录
                $distributeLogic = new DistributeLogic();
                $dis = $distributeLogic->addLog(2, $vo['id'], $vo['uid']);
                //90次
                $enterpriseNum = Db::table('wx_payment_enterprise')->where('type', 3)->count();
                $enterpriseMoney = Db::table('wx_payment_enterprise')->order('id desc')->value('enterprise_balance');
                if ($enterpriseMoney < $this->config['enterprise_balance']) {
                    //判断发送次数和间隔时间
                    $sendEmail=Cache::get('send_email','0-0');
                    $sendEmailArr = explode('-', $sendEmail);
                    if($sendEmailArr<4 &&  $sendEmailArr[1] + $this->config['send_time'] < time()){
                        $this->_noticeBalanceEmail($sendEmailArr,'send_email');
                    }
                    //判断发送次数和间隔时间
                    $sendDingTalk=Cache::get('send_dingtalk','0-0');
                    $sendDingTalkArr = explode('-', $sendDingTalk);
                    if($sendEmailArr<4 &&  $sendEmailArr[1] + $this->config['send_time'] < time()){
                        $this->_noticeBalanceDingTalk( $sendDingTalkArr,'send_dingtalk');
                    }
                }
                //退款金额大于1元,且企业提现小于限制次数
                if ($rateArray['refundMoney'] >= 1 && $enterpriseNum <= $this->config['enterprise_withdraw_count']) {
                    $order = new Order();
                    $order_sn = $order->orderDetailByBonusId($vo['id']);
                    //退款至微信钱包
                    $enterpriseResult = $payLogic->EnterprisePay([
                        'uid' => $vo['uid'],
                        'money' => $rateArray['refundMoney'], // 所有未领取的钱
                        'enterprise_type' => 3,//退款提现
                        'order_sn' => $order_sn['order_sn'],
                        'total_fee'=> $order_sn['wx_money'],
                        'refund_fee'=> ($order_sn['wx_money'] > $rateArray['refundMoney']) ? $rateArray['refundMoney'] : $order_sn['wx_money'],
                    ]);
                    if ($rateArray['refundMoney'] > $order_sn['wx_money']) {
                        $wallet = new Wallet();
                        $wallet->setBalance($vo['uid'],($rateArray['refundMoney'] - $order_sn['wx_money']));
                    }
                    $refund = ($enterpriseResult['status'] == 1 && $dis > 0) ? 1 : 0;
                } else {
                    //退款余额
                    $refundResult = $this->_refundToBalance($vo['uid'], $rateArray['refundMoney']);
                    $refund = ($refundResult && $dis > 0) ? 1 : 0;
                }
            } else {
                $refund = 1;
            }
            $bonusUpd = Db::table('wx_payment_bonus')->where('id', $vo['id'])->update($bonusUpdate);
            if ($bonusUpd != false && $refund == 1) {
                Db::commit();
                $num = 1;
                if($vo['class']==1){
                    $page='pages/packet/packet?bonus_id=' . $vo['id'];
                }elseif($vo['class'] == 2){
                    $page='pages/adv_play/adv_play?bonus_id=' . $vo['id'];
                }else{
                    $page='pages/play/play?bonus_id=' . $vo['id'];
                }
                while ($num != 0) {
                    $tpl = [
                        'type' => 'refund',
                        'page' => $page,
                        'form_id' => $this->redisService->lpop($vo['uid']),
                        'openid' => $vo['uid'],
                        'key1' => $rateArray['refundMoney'],
                        'key2' => $vo['bonus_password'],
                    ];
                    $curl = $wxService->tplSend($tpl);
                    if ($curl['errcode'] == 0) {
                        break;
                    }
                    $num = $this->redisService->llen($vo['uid']);
                }
            } else {
                Db::rollback();
            }
        }
        $result = true;
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 小游戏自动过期退金币
     *
     * @description
     */
    public function automaticGameRefund()
    {
        $wxService = new WechatService();
        $coin_log = new GameCoinLog();
        $wallet = new Wallet();
        $ssc = new GameSsc();
        $time = date('Y-m-d H:i:s', time() - $this->config['automatic_time']);
        //未应战的小游戏
        $refundSsc = Db::table('wx_game_ssc')->where('result',0)->where('created_at', '<', $time)->select();
        foreach ($refundSsc as $key => $value) {
            $virtual = $wallet->getVirtual($value['uid']);
            //小游戏退款记录
            $coin_log->addLog($value['uid'],9,$value['coin'],1,$virtual+$value['coin'],$value['id']);
            //虚拟币增加
            $wallet->setVirtual($value['uid'],$value['coin']);
            //游戏状态修改
            $ssc->editInfo(['id'=>$value['id'],'result'=>4]);
            $tpl = [
                'type' => 'gameRefund',
                'page' => 'pages/pk/pk?ssid='.$value['id'],
                'form_id' => $this->redisService->lpop($value['uid']),
                'openid' => $value['uid'],
                'key1' => $value['coin'].'金币',
            ];
            $wxService->tplSend($tpl);
        }
        //押注者
        $ssids = array_column($refundSsc,'id');
        $where = ['ssid'=>['in',$ssids],'coin'=>['gt',0]];
        $refundSscto = Db::table('wx_game_ssc_to')->where($where)->select();
        foreach ($refundSscto as $k => $v) {
            $virtual = $wallet->getVirtual($v['uid']);
            //小游戏退款记录
            $coin_log->addLog($v['uid'],9,$v['coin'],1,$virtual+$v['coin'],$v['ssid']);
            //虚拟币增加
            $wallet->setVirtual($v['uid'],$v['coin']);
            $tpl = [
                'type' => 'gameRefund',
                'page' => 'pages/pk/pk?ssid='.$v['ssid'],
                'form_id' => $this->redisService->lpop($v['uid']),
                'openid' => $v['uid'],
                'key1' => $v['coin'].'金币',
            ];
            $wxService->tplSend($tpl);
        }
        return true;
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 单个红包服务费计算
     * @param int $bonusId
     * @return array
     */
    private function _bonusOne(int $bonusId)
    {
        $bonusRefundArray = Db::table('wx_payment_bonus_detail')->where('bonus_id', $bonusId)->
        field('receive_money,receive_service_money,is_use,payable_money')->select();
        $payableRefundMoney = 0;//应退金额
        $payableServiceMoney = 0;//应收服务费
        foreach ($bonusRefundArray as $k => $v) {
            $payableRefundMoney += $v['payable_money'];
            $payableServiceMoney += $v['receive_service_money'];
        }
        return [
            'refundServiceMoney' => round($payableRefundMoney * 0.01, 2),//退款服务费
            'refundMoney' => $payableRefundMoney + round($payableServiceMoney, 2) -
                round($payableRefundMoney * 0.01, 2),//退款金额
            'receiveServiceMoney' => 0,//领取服务费
        ];
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 多个红包的费率计算
     * @param int $bonusId
     * @return array
     */
    private function _bonusDouble(int $bonusId)
    {
        $bonusRefundArray = Db::table('wx_payment_bonus_detail')->where('bonus_id', $bonusId)->
        field('receive_money,receive_service_money,is_use,payable_money')->select();
        $payableRefundMoney = 0;//应退金额
        $payableServiceMoney = 0;//应收服务费
        foreach ($bonusRefundArray as $k => $v) {
            if ($v['is_use'] == 1) {
                //以领取
                $payableServiceMoney += $v['receive_service_money'];
            } else {
                //未领取
                $payableRefundMoney += $v['payable_money'];
            }
        }
        return [
            'refundServiceMoney' => round($payableRefundMoney * 0.01, 2),//退款服务费
            'refundMoney' => $payableRefundMoney - round($payableRefundMoney * 0.01, 2),//退款金额
            'receiveServiceMoney' => round($payableServiceMoney, 2),//领取服务费
        ];
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 退款至余额
     * @param string $uid
     * @param $refundMoney
     * @return bool
     */
    private function _refundToBalance(string $uid, $refundMoney)
    {
        //钱包新增
        $walletInfo = Db::table('wx_payment_wallet')->where('uid', $uid)->find();
        $walletUpdate = [
            'balance' => $walletInfo['balance'] + $refundMoney,
        ];
        $enterpriseMoney = Db::table('wx_payment_enterprise')->order('id desc')->value('enterprise_balance');
        $waupd = Db::table('wx_payment_wallet')->where('uid', $uid)->update($walletUpdate);
        //流水
        $data = [
            'uid' => $uid,
            'type' => 4,
            'affect_money' => $refundMoney,
            'balance_money' => $walletInfo['balance'] + $refundMoney,
            'money_source' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $billLogID = Db::table('wx_payment_bill_log')->insert($data);
        //第一次大于1元,自动提现到微信钱包
        $payLogic = new PayLogic();
        if (($walletInfo['balance'] + $refundMoney) > 1 && ($walletInfo['is_first'] == 0) && $enterpriseMoney > ($walletInfo['balance'] + $refundMoney)) {
            $payLogic->EnterprisePay([
                'uid' => $uid,
                'money' => $walletInfo['balance'] + $refundMoney,
                'enterprise_type' => 1,//退款提现
            ]);
        }
        if (!($waupd != false && $billLogID > 0)) {
            return false;
        }
        return true;
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 发送企业账户余额提醒email
     * @param $sendEmailArr
     * @param $redisKey
     */
    private function _noticeBalanceEmail($sendEmailArr,$redisKey)
    {
        //存入队列,发送邮件
        $json = json_encode($this->config['email']);
        $this->redisService->rpush('send_message', $json);
        Cache::set($redisKey,($sendEmailArr[0] + 1) . '-' . time(),new \DateTime($this->config['send_time_check']));
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 发送企业账户余额提醒dingTalk
     * @param $sendDingTalkArr
     * @param $redisKey
     */
    private function _noticeBalanceDingTalk($sendDingTalkArr,$redisKey)
    {
        $json = json_encode($this->config['ding_talk']);
        $this->redisService->rpush('send_message', $json);
        Cache::set($redisKey,($sendDingTalkArr[0] + 1) . '-' . time(),new \DateTime($this->config['send_time_check']));
    }
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-15
     *
     * @description 每天11点半,今天没登入过的都推送
     * @return bool
     */
    public function remindUser()
    {
        try {
            $wxService = new WechatService();
            $time = date('Y-m-d');
            $user = Db::table('wx_user_power')->where('login_time', '<', $time)->select();
            $everyday = Db::table('wx_system_power')->where('title', '=', 'everyday')->value('num');
            foreach ($user as $key => $vo) {
                $tpl = [
                    'type' => 'give',
                    'page' => 'pages/hall/hall',
                    'form_id' => $this->redisService->lpop($vo['uid']),
                    'openid' => $vo['uid'],
                    'key2' => $everyday,
                ];
                $wxService->tplSend($tpl);
            }
            $result = true;
        } catch (Exception $exception) {
            $result = false;
        }
        return $result;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01
     *
     * @description 当日提现金额超过90W,提醒
     * @return bool
     */
    public function generalPresentation()
    {
        $startTime = date('Y-m-d') . ' 00:00:00';
        $endTime = date('Y-m-d') . ' 23:59:59';
        $sumMoney = Db::table('wx_payment_enterprise')->
        where('created_at', 'between time', [$startTime, $endTime])->
        where('type', '>', 0)->sum('money');
        if ($sumMoney != false && $sumMoney >= 90 * 10000) {
            //判断发送次数和间隔时间
            $sendEmail=Cache::get('send_email_90','0-0');
            $sendEmailArr = explode('-', $sendEmail);
            if($sendEmailArr<4 &&  $sendEmailArr[1] + $this->config['send_time'] < time()){
                $this->_noticeBalanceEmail($sendEmailArr,'send_email_90');
            }
            //判断发送次数和间隔时间
            $sendDingTalk=Cache::get('send_dingtalk_90','0-0');
            $sendDingTalkArr = explode('-', $sendDingTalk);
            if($sendEmailArr<4 &&  $sendEmailArr[1] + $this->config['send_time'] < time()){
                $this->_noticeBalanceDingTalk( $sendDingTalkArr,'send_dingtalk_90');
            }
        }
        $result = true;
        return $result;
    }
}