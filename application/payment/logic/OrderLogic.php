<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/5
 * Time: 16:45
 * @introduce
 */

namespace app\payment\logic;

use app\common\logic\BaseLogic;
use app\game\model\GameCoinLog;
use app\game\model\GameOrder;
use app\payment\model\Abonus;
use app\payment\model\AbonusSend;
use app\payment\model\BillInfo;
use app\payment\model\BillLog;
use app\payment\model\Bonus;
use app\payment\model\BonusDetail;
use app\payment\model\Enterprise;
use app\payment\model\Order;
use app\payment\model\Wallet;
use app\user\model\User;
use extend\helper\Files;
use extend\helper\Utils;
use extend\service\RedisService;
use extend\service\WechatService;
use think\Db;
use think\Exception;
use think\File;

class OrderLogic extends BaseLogic
{
    protected $orderModel;
    protected $recharge = '赶紧说-语音口令支付';//充值
    protected $withdrawals = '赶紧说-余额提现';//提现
    protected $ask = '赶紧说-赏红包';
    protected $redisService;
    protected $weService;
    protected $abonus;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->redisService = new RedisService();
        $this->weService = new WechatService();
        $this->abonus = new Abonus();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 订单的生成(在用)
     * @param array $params
     * @return bool|string
     */

    public function orderAdd(array $params)
    {
        try {
            $params['bonus_id'] = $params['bonus_id'] ?? 0;
            $orderParams = [];
            if (!empty($params['type']) && $params['type'] == 2) {
                $orderParams['order_detail'] = $this->ask;
                $orderParams['bonus_id'] = $params['bonus_id'];
                $orderParams['type'] = 2;
                $orderParams['order_sn'] = Utils::genUUID('AO');
            }elseif ($params['bonus_id'] == 0) {
                $orderParams['order_detail'] = $this->withdrawals;
                $orderParams['order_sn'] = Utils::genUUID('O');
                $orderParams['check_name'] = $params['check_name'] ?? '';
            } else {
                $orderParams['order_detail'] = $this->recharge;
                $orderParams['bonus_id'] = $params['bonus_id'];
                $orderParams['order_sn'] = Utils::genUUID('O');
            }
            $orderParams['uid'] = $params['uid'];
            $orderParams['money'] = $params['money'];
            $orderParams['wx_money'] = $params['wx_money'];
            if (isset($params['finish_at'])) {
                $orderParams['finish_at'] = $params['finish_at'];
            }
            $orderId = $this->orderModel->orderAdd($orderParams);
            if ($orderId > 0) {
                $result = [
                    'order_sn' => $orderParams['order_sn'],
                    'order_detail' => $orderParams['order_detail'],
                    'money' => $orderParams['money'],
                    'wx_money' => $orderParams['wx_money'],

                ];
            } else {
                $result = false;
            }
        } catch (Exception $exception) {
            $result = false;
        }
        return $result;
    }

    /** 游戏充值订单生成
     * auth smallzz
     * @param array $param
     * @return bool
     */
    public function gameOrderAdd(array $param){
        try{
            $gameOrder = new GameOrder();
            $order['uid'] = $param['uid'];
            $order['money'] = $param['money'];
            $order['wx_money'] = $param['wx_money'];
            $order['order_sn'] = Utils::genUUID('GM');
            $order['order_detail'] = '蒲公英虚拟币充值';
            $order['type'] = 3;
            $result = $gameOrder->orderAdd($order);
        }catch (Exception $exception){
            return false;
        }
        $orders['wx_money'] = $order['wx_money'];
        $orders['order_sn'] = $order['order_sn'];
        $orders['order_detail'] = $order['order_detail'];
        $orders['order_id'] = $result;
        return $orders;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-31
     *
     * @description 多种红包统一回调
     * @param array $params
     * @return bool
     */
    public function orderCallback(array $params)
    {
        $orderInfo = $this->orderModel->orderDetail($params['order_sn']);
        if($orderInfo['type'] == 2){
            return $this->_orderAskCallback($params);
        }else{
            return $this->_orderCallback($params);
        }
    }

    /** 游戏充值回调实现
     * auth smallzz
     * @param array $params
     * @return bool
     */
    public function orderCallbackGame(array $params){
        Files::CreateLog('paytest.txt',var_export($params));
        $gameOrder = new GameOrder();
        $walletModel = new Wallet();
        $coinlog = new GameCoinLog();
        $virtual = Utils::getVirtualConfig();
        try{
            Db::startTrans();
            #查询一下订单
            $orderinfo = $gameOrder->orderInfo($params['order_sn']);
            Files::CreateLog('xmla.txt',var_export($orderinfo,true));
            #获取到对应的金币
            $vcoin = $virtual[intval($orderinfo['money'])];
            #修改订单状态
            if(!empty($orderinfo['finish_at']) || empty($orderinfo)){
                return true;
            }
            $order_res = $gameOrder->save(['finish_at'=>date('Y-m-d H:i:s')],['order_sn'=>$params['order_sn']]);

            $walletInfo = $walletModel->walletDetail($orderinfo['uid']);
            #增加金币
            $vicoin_res = $walletModel->where(['uid'=>$orderinfo['uid']])->setInc('virtual',$vcoin);
            #减金额
            $balance_res = $walletModel->where(['uid'=>$orderinfo['uid']])->setDec('balance',$orderinfo['money'] - $orderinfo['wx_money']);
            Files::CreateLog('wallet.txt',$balance_res.'-'.time());
            //资金流水记录
            $billLogID = (new BillLog())->billLogAdd([
                'uid' => $orderinfo['uid'],
                'type' => 8,
                'affect_money' => '-' . $orderinfo['wx_money'],
                'balance_money' => 0,
                'money_source' => 1,
            ]);
            if($orderinfo['money'] != $orderinfo['wx_money']){   #判断是否是混合支付
                $billLogID = (new BillLog())->billLogAdd([
                    'uid' => $orderinfo['uid'],
                    'type' => 8,
                    'affect_money' => '-' . ($orderinfo['money'] - $orderinfo['wx_money']),
                    'balance_money' => $walletInfo['balance'] - ($orderinfo['money'] - $orderinfo['wx_money']),
                    'money_source' => 2,
                ]);
            }
            (new BillInfo())->billInfoAdd([
                'trade_no'=>$params['trade_no'],
                'pay_type'=>$params['pay_type'],
                'uid'=>$orderinfo['uid'],
                'money'=>$params['money'] ?? $orderinfo['money'],
                'order_sn'=>$params['order_sn'],
                'type'=>3,  #充值
            ]);
            //日志记录
            $coinlog->addLog($orderinfo['uid'],7,$vcoin,1,$walletInfo['virtual']+$vcoin);     #虚拟币日志

        }catch (Exception $exception){
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }
    /**
     * @Author panhao
     * @DateTime
     *
     * @description 微信支付回调(赏红包)
     * @param array $params
     * @return bool
     */
    private function _orderAskCallback(array $params)
    {
        try {
            Db::startTrans();
            $orderInfo = $this->orderModel->orderDetail($params['order_sn']);
            $abonus_send = new AbonusSend();
            $abonus_id = $abonus_send->getOne(['id'=>$orderInfo['bonus_id']]);
            $orderInfo['bonus_id'] = $abonus_id['abonus_id'];

            if (empty($orderInfo) || $orderInfo['finish_at'] != null) {
                return false;
            }
            //修改订单状态
            $orderResult = $this->_orderUpdate($params);
            //交易明细的新增
            $params['billInfo_type'] = 1;
            $billInfoResult = $this->_billInfoAdd($params, $orderInfo);
            //修改赏红包的状态
            $bonusResult = $this->_abonusUpdate($orderInfo);

            $walletModel = new Wallet();
            $walletInfo = $walletModel->walletDetail($orderInfo['uid']);
            //收入红包资金流水
            $receive_uid = $this->abonus->getDetail($orderInfo['bonus_id']);
            //微信支付资金流水(赏)
            $billLogWxResult = $this->_billLogAdd([
                'uid' => $orderInfo['uid'],
                'type' => 6,
                'affect_money' => '-' . $orderInfo['wx_money'],
                'balance_money' => $walletInfo['balance'],
                'money_source' => 1,
                'from_uid' => $receive_uid['uid'], // 不是资金来源
            ]);
            $walletInfo2 = $walletModel->walletDetail($receive_uid['uid']);
            $money = $abonus_send->getOne(['uid'=>$orderInfo['uid'],'abonus_id'=>$orderInfo['bonus_id']]);
            $service = $money['money']*config('RECEIVE_RATE');
            $m = $money['money'] - $service; // 实际收取金额
            //修改讨红包表
            $this->abonus->editAbonus(['receive_money'=>['exp','receive_money+'.$m],'num'=>['exp','num+1'],'service_money'=>['exp','service_money+'.$service]],$orderInfo['bonus_id']);

            $billLogWxResult2 = $this->_billLogAdd([
                'uid' => $receive_uid['uid'],
                'type' => 7,
                'affect_money' => $money['money'] - $service,
                'balance_money' => $walletInfo2['balance'] + $money['money'] - $service,
                'money_source' => 3,
                'from_uid' => $orderInfo['uid'],
            ]);
            $walletResult2 = $walletModel->walletEdit([
                'uid' => $receive_uid['uid'],
                'balance' => $walletInfo2['balance'] + $money['money'] - $service,
            ]);

            if ($orderInfo['money'] > $orderInfo['wx_money']) {
                //混合支付余额资金流水
                $billLogBalanceResult = $this->_billLogAdd([
                    'uid' => $orderInfo['uid'],
                    'type' => 6,
                    'affect_money' => '-' . ($orderInfo['money'] - $orderInfo['wx_money']),
                    'balance_money' => $walletInfo['balance'] - $orderInfo['money'] + $orderInfo['wx_money'],
                    'money_source' => 2,
                    'from_uid' => $receive_uid['uid'],
                ]);
                //混合支付扣余额
                $walletResult = $walletModel->walletEdit([
                    'uid' => $orderInfo['uid'],
                    'balance' => $walletInfo['balance'] - $orderInfo['money'] + $orderInfo['wx_money'],
                ]);
            } else {
                $billLogBalanceResult = true;
                $walletResult = 1;
            }
            if ($orderResult && $billInfoResult && $bonusResult && $billLogWxResult && $billLogWxResult2 && $walletResult2 && $billLogBalanceResult && $walletResult > 0) {
                Db::commit();
                //收益模板推送
                $user = new User();
                $redis = new RedisService();
                $nick = $user->userDetail($orderInfo['uid']);
                $tpl = [
                    'type' => 'rewardMoney',
                    'page' => 'pages/beg_details/beg_details?beg_id='.$orderInfo['bonus_id'],
                    'form_id' => $redis->lpop($receive_uid['uid']),
                    'openid' => $receive_uid['uid'],
                    'key1' => $nick['nickname'],
                    'key2' => $money['money'] - $service,
                    'key3' => '当前你发出的讨红包共收到'.$receive_uid['receive_money'].'元',
                ];
                $this->weService->tplSend($tpl);
                $result = $this->ajaxSuccess(100,['list'=>$tpl]);
            } else {
                Db::rollback();
                $result = false;
            }

        } catch (Exception $exception) {
            $result = false;
        }
        return $result;
    }


    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 微信支付回调(发红包)
     * @param array $params
     * @return bool
     */
    private function _orderCallback(array $params)
    {
        try {
            Db::startTrans();
            $orderInfo = $this->orderModel->orderDetail($params['order_sn']);
            if (empty($orderInfo) || $orderInfo['finish_at'] != null) {
                return false;
            }
            //修改订单状态
            $orderResult = $this->_orderUpdate($params);
            //交易明细的新增
            $params['billInfo_type'] = 1;
            $billInfoResult = $this->_billInfoAdd($params, $orderInfo);
            //修改红包的状态
            $bonusResult = $this->_bonusUpdate($orderInfo);
            $walletModel = new Wallet();
            $walletInfo = $walletModel->walletDetail($orderInfo['uid']);
            //微信支付资金流水
            $billLogWxResult = $this->_billLogAdd([
                'uid' => $orderInfo['uid'],
                'type' => 1,
                'affect_money' => '-' . $params['money'],
                'balance_money' => $walletInfo['balance'],
                'money_source' => 1,
            ]);
            if ($orderInfo['money'] > $orderInfo['wx_money']) {
                //混合支付余额资金流水
                $billLogBalanceResult = $this->_billLogAdd([
                    'uid' => $orderInfo['uid'],
                    'type' => 1,
                    'affect_money' => '-' . ($orderInfo['money'] - $orderInfo['wx_money']),
                    'balance_money' => $walletInfo['balance'] - $orderInfo['money'] + $orderInfo['wx_money'],
                    'money_source' => 2,
                ]);
                //混合支付扣余额
                $walletResult = $walletModel->walletEdit([
                    'uid' => $orderInfo['uid'],
                    'balance' => $walletInfo['balance'] - $orderInfo['money'] + $orderInfo['wx_money'],
                ]);
            } else {
                $billLogBalanceResult = true;
                $walletResult = 1;
            }
            if ($orderResult && $billInfoResult && $bonusResult && $billLogWxResult && $billLogBalanceResult && $walletResult > 0) {
                Db::commit();
                $bonusModel=new Bonus();
                $bonusInfo = $bonusModel->bonusDerail($orderInfo['bonus_id']);
                if($bonusInfo['class']==1){
                    $page='pages/packet/packet?bonus_id=' . $orderInfo['bonus_id'];;
                }else{
                    $page='pages/play/play?bonus_id=' . $orderInfo['bonus_id'];
                }
                $tpl = [
                    'type' => 'send',
                    'page' => $page,
                    'form_id' => $bonusInfo['prepay_id'],
                    'openid' => $bonusInfo['uid'],
                    'key1' => empty($bonusInfo['bonus_password'])?'语音红包':$bonusInfo['bonus_password'],
                    'key2' => date('Y-m-d'),
                ];
                $this->weService->tplSend($tpl);
                //存到redis
                $this->_bonusDetailAddRedis($orderInfo['bonus_id']);
                $result = true;
            } else {
                Db::rollback();
                $result = false;
            }

        } catch (Exception $exception) {
            $result = false;
        }
        return $result;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-22
     *
     * @description 提现支付回调
     * @param array $params
     * @return bool
     */
    public function orderCallbackWithdrawals(array $params)
    {
        try {
            Db::startTrans();
            $orderInfo = $this->orderModel->orderDetail($params['order_sn']);

            //修改订单状态
            $orderResult = $this->_orderUpdate($params);

            //交易明细的新增
            $params['billInfo_type'] = 2;
            $billInfoResult = $this->_billInfoAdd($params, $orderInfo);

            $walletModel = new Wallet();
            $walletInfo = $walletModel->walletDetail($orderInfo['uid']);
            //资金流水
            $billLogParams = [
                'uid' => $orderInfo['uid'],
                'type' => 3,//提现
                'affect_money' => '-' . $orderInfo['wx_money'],
            ];
            if ($params['enterprise_type'] != 3) {
                $billLogParams['balance_money'] = $walletInfo['balance'] - $orderInfo['wx_money'];
                $billLogParams['money_source'] = 2;
            } else {
                $billLogParams['balance_money'] = $walletInfo['balance'];
                $billLogParams['money_source'] = 5;
            }
            $billLogWxResult = $this->_billLogAdd($billLogParams);

            if ($params['enterprise_type'] != 3) {
                //提现 扣余额
                $walletNum = $walletModel->walletEdit(['uid' => $orderInfo['uid'], 'balance' => $billLogParams['balance_money']]);
            } else {
                $walletNum = 1;
            }

            //提现类型记录表
            $enterpriseResult = $this->_enterpriseLogAdd($orderInfo['uid'],$params['enterprise_type'],$orderInfo['wx_money']);
            if ($enterpriseResult && $billLogWxResult && $billInfoResult && $orderResult && $walletNum > 0) {
                Db::commit();
                $result = true;
            } else {
                Db::rollback();
                $result = false;
            }
        } catch (Exception $exception) {
            $result = false;
        }
        return $result;
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 修改订单状态
     * @param array $params
     * @return bool
     */
    private function _orderUpdate(array $params)
    {
        $orderParams['order_sn'] = $params['order_sn'];
        $orderParams['finish_at'] = date('Y-m-d H:i:s');
        $num = $this->orderModel->orderEdit($orderParams);
        return $num > 0 ? true : false;
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 修改红包状态
     * @param $orderInfo
     * @return bool
     */
    private function _bonusUpdate($orderInfo)
    {
        $bonusModel = new Bonus();
        $bonusNum = $bonusModel->bonusEdit([
            'is_pay' => 1,
            'bonus_id' => $orderInfo['bonus_id']
        ]);
        return $bonusNum > 0 ? true : false;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-27
     *
     * @description 修改红包状态
     * @param $orderInfo
     * @return bool
     */
    private function _abonusUpdate($orderInfo)
    {
        $bonusModel = new AbonusSend();
        $bonusNum = $bonusModel->editWxPay(['is_pay' => 1,'abonus_id'=>$orderInfo['bonus_id'],'uid'=>$orderInfo['uid']]);
        return $bonusNum > 0 ? true : false;
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 交易明细的新增
     * @param array $params
     * @param  $orderInfo
     * @return bool
     */
    private function _billInfoAdd(array $params,$orderInfo)
    {
        $billInfoParams['trade_no'] = $params['trade_no'];
        $billInfoParams['pay_type'] = $params['pay_type'];
        $billInfoParams['uid'] = $orderInfo['uid'];
        $billInfoParams['money'] = $params['money'] ?? $orderInfo['wx_money'];
        $billInfoParams['order_sn'] = $params['order_sn'];
        $billInfoParams['type'] = $params['billInfo_type'];
        $billInfoModel = new BillInfo();
        $id = $billInfoModel->billInfoAdd($billInfoParams);
        return $id > 0 ? true : false;
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 设置红包详情到redis
     * @param int $bonusId
     */
    private function _bonusDetailAddRedis(int $bonusId)
    {
        $bonusDetailModel = new BonusDetail();
        $returnlist = $bonusDetailModel->getAddData($bonusId);
        foreach ($returnlist as $k => $v) {
            if (!empty($v['receive_money'])) {
                $this->redisService->lpush('bonus_' . $bonusId, $v['id'] . '-' . $v['receive_money']);
            }
        }
        $this->redisService->expire('bonus_' . $bonusId, config('payment.wx_notify')['bonus_effective_time']); #设置红包的生命周期
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 资金流水的添加
     * @param array $params
     * @return bool
     */
    private function _billLogAdd(array $params)
    {
        $billLogModel = new BillLog();
        $billLog = $billLogModel->billLogAdd($params);
        return $billLog > 0 ? true : false;
    }
    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 提现记录表添加
     * @param string $uid
     * @param int $type
     * @param $money
     * @return bool
     */
    private function _enterpriseLogAdd(string $uid,int $type,$money, string $name = '')
    {
        $enterpriseModel = new Enterprise();
        $enterpriseInfo = $enterpriseModel->enterpriseLast();
        $enterpriseParams = [
            'type' => $type,
            'money' => $money,
            'uid' => $uid,
            'enterprise_balance' => $enterpriseInfo['enterprise_balance'] - $money,
        ];
        $enterprise = $enterpriseModel->enterpriseAdd($enterpriseParams);
        return $enterprise > 0 ? true : false;
    }
}