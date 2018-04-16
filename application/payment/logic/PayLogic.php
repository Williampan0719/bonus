<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/6
 * Time: 09:24
 * @introduce
 */

namespace app\payment\logic;

use app\common\logic\BaseLogic;
use app\game\model\GameCoinLog;
use app\game\model\GameOrder;
use app\game\model\GameSsc;
use app\message\logic\DingTalkLogic;
use app\message\logic\EmailLogic;
use app\payment\model\BillLog;
use app\payment\model\Bonus;
use app\payment\model\BonusDetail;
use app\payment\model\BonusRemark;
use app\payment\model\Enterprise;
use app\payment\model\Order;
use app\payment\model\Wallet;
use app\payment\model\WithdrawReview;
use app\user\model\User;
use app\user\model\UserLog;
use extend\helper\Curl;
use extend\helper\Files;
use extend\helper\Utils;
use extend\service\AudioService;
use extend\service\payment\WeChatEnterprisePayService;
use extend\service\payment\WeChatPayService;
use extend\service\RedisService;
use extend\service\WechatService;
use think\Cache;
use think\Db;
use think\Exception;
use think\Loader;
use think\log\driver\File;

Loader::import('thirdpart.wxpay.WxPayPubHelper.WxPayPubHelper');
Loader::import('thirdpart.wxpay.lib.WxPay');

class PayLogic extends BaseLogic
{
    protected $bonusLogic;
    protected $walletModel;
    protected $bonusModel;
    protected $orderLogic;
    protected $billLogModel;
    protected $weService;

    public function __construct()
    {
        $this->bonusLogic = new BonusLogic();
        $this->walletModel = new Wallet();
        $this->bonusModel = new Bonus();
        $this->orderLogic = new OrderLogic();
        $this->billLogModel = new BillLog();
        $this->weService = new WechatService();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-10
     *
     * @description 支付统一调用接口
     * @param array $params
     * @return array
     */
    public function pay(array $params, $file)
    {
        try {
            #增加浏览
            $userlog = new UserLog();
            $userlog->uLogAdd($params['uid']);
            //语音红包录音上传
            if (!empty($file)) {
                $mp3 = $this->uploadMp3($file);
                if (!empty($mp3)) {
                    $params['voice_path'] = $mp3;
                }else {
                    return $this->ajaxError(106);
                }
            }
            $walletInfo = $this->walletModel->walletDetail($params['uid']);
            $money = $params['bonus_num'] == 1 ? $params['bonus_money'] + $params['service_money'] : $params['bonus_money'];
            if ($money <= $walletInfo->balance) {
                $result = $this->_balancePay($params);
            } elseif ($walletInfo->balance >= 0.01) {
                $result = $this->_mixedPay($params);
            } else {
                $result = $this->_wxpay($params);
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(106);
        }
        return $result;
    }

    /** 游戏充值支付模拟
     * auth smallzz
     * @param array $params
     */
    public function gpay(array $params){
        try{
            #增加浏览
            $userlog = new UserLog();
            $userlog->uLogAdd($params['uid']);
            #检查使用什么支付
            $walletInfo = $this->walletModel->walletDetail($params['uid']);

            if(intval($params['money']) <= $walletInfo->balance){
                #余额支付
                $result = $this->_gameYuEPay($params);
            }elseif($walletInfo->balance >= 0.01){
                #混合支付
                $result = $this->_gameHybridPay($params);
            }else{
                #纯微信支付
                $result = $this->_gamePay($params);
            }

        }catch (Exception $exception){
            return $exception->getMessage();
        }
        return $result;
    }
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-10
     *
     * @description 混合支付
     * @param array $params
     * @return array
     */
    private function _mixedPay(array $params)
    {
        try {
            Db::startTrans();
            //生成红包
            $bonusId = $this->bonusLogic->bonusAdd($params);
            if ($bonusId != false) {
                //广告红包
                if (isset($params['class']) && ($params['class'] == 2)) {
                    $remark = new BonusRemark();
                    $where = ['text'=>$params['adv_remark'] ?? '','bonus_id'=>$bonusId];
                    $a = $remark->addRemarkByBonus($where);
                    if (!$a) {
                        return $this->ajaxError(106, [], '广告详情保存失败');
                    }
                }
                //生成红包详情
                $service_money = $params['service_money'] ?? 0;
                $bonusDetail = $this->bonusLogic->bonusDistribution($bonusId, $service_money);
                if ($bonusDetail != false) {
                    $money = $params['bonus_num'] == 1 ? $params['bonus_money'] + $params['service_money'] : $params['bonus_money'];
                    $walletInfo = $this->walletModel->walletDetail($params['uid']);
                    //生成订单
                    $orderParams['bonus_id'] = $bonusId;
                    $orderParams['uid'] = $params['uid'];
                    $orderParams['money'] = $money;
                    $orderParams['wx_money'] = $money - sprintf("%01.2f",intval($walletInfo['balance']*100)/100);
                    $orderInfo = $this->orderLogic->orderAdd($orderParams);
                    if ($orderInfo != false) {
                        //生成微信支付
                        $wcService = new WeChatPayService();
                        $orderInfo['uid'] = $params['uid'];
                        $response = $wcService->payInfo($orderInfo);
                        Db::commit();
                        $this->bonusModel->bonusEdit([
                            'bonus_id' => $bonusId,
                            'prepay_id' => $response['prepay_id'],
                        ]);
                        $result = $this->ajaxSuccess(1306, ['sign' => $response['response'], 'bonus_id' => $bonusId], '订单支付成功');

                    } else {
                        Db::rollback();
                        $result = $this->ajaxError(106, [], '生成失败');
                    }
                } else {
                    Db::rollback();
                    $result = $this->ajaxError(106, [], '生成失败');
                }
            } else {
                Db::rollback();
                $result = $this->ajaxError(106, [], '生成失败');
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(106, [], '生成失败');
        }
        return $result;

    }

    /** 微信支付
     * auth smallzz
     * @return array
     */
    public function _gamePay(array $params){
        $virtual = Utils::getVirtualConfig();
        Db::startTrans();
        try{
            $money = intval($params['money']);
            $vcoin = $virtual[$money];
            if(empty($vcoin)){
                $result = $this->ajaxError(1812, [], '金额错误');
                return $result;
            }
            //生成订单
            $orderParams['uid'] = $params['uid'];
            $orderParams['money'] = $money;
            $orderParams['wx_money'] = $money;
            $orderInfo = $this->orderLogic->gameOrderAdd($orderParams);
            if ($orderInfo != false) {
                //生成微信支付
                $wcService = new WeChatPayService();
                $orderInfo['uid'] = $params['uid'];
                $response = $wcService->payInfo($orderInfo ,null,1);

                $result = $this->ajaxSuccess(1306, ['sign' => $response['response'],'order_sn'=>$orderInfo['$orderInfo']], '订单支付成功');

            } else {
                Db::rollback();
                $result = $this->ajaxError(106, [], '生成失败');
            }
        }catch (Exception $exception){
            Db::rollback();
            $result = $this->ajaxError(106, [], '生成失败');
        }
        Db::commit();
        return $result;
    }

    /** 混合支付
     * auth smallzz
     */
    public function _gameHybridPay(array $params){
        Db::startTrans();
        $virtual = Utils::getVirtualConfig();
        try{
            $walletInfo = $this->walletModel->walletDetail($params['uid']);

            if(empty($walletInfo)){
                return $this->ajaxError(1814, [], '用户信息错误');
            }
            $money = intval($params['money']);
            $vcoin = $virtual[$money];
            if(empty($vcoin)){
                return $this->ajaxError(1812, [], '金额错误');
            }

            //生成订单
            $orderParams['uid'] = $params['uid'];
            $orderParams['money'] = $money;
            $orderParams['wx_money'] = $money - sprintf("%01.2f",intval($walletInfo['balance']*100)/100);

            $orderInfo = $this->orderLogic->gameOrderAdd($orderParams);
            if ($orderInfo != false) {
                //生成微信支付
                $wcService = new WeChatPayService();
                $orderInfo['uid'] = $params['uid'];
                $response = $wcService->payInfo($orderInfo ,null,1);
                $result = $this->ajaxSuccess(1306, ['sign' => $response['response'],'order_sn'=>$orderInfo['order_sn']], '订单支付成功');
                Db::commit();

            } else {
                Db::rollback();
                $result = $this->ajaxError(106, [], '生成失败1');
            }
        }catch (Exception $exception){
            Db::rollback();
            $result = $this->ajaxError(106, [], '生成失败2');
        }
        return $result;
    }
    /** 余额支付
     * auth smallzz
     */
    public function _gameYuEPay(array $params){
        $coinlog = new GameCoinLog();
        $gameOrder = new GameOrder();
        $virtual = Utils::getVirtualConfig();
        Db::startTrans();
        try{
            $walletInfo = $this->walletModel->walletDetail($params['uid']);
            if(empty($walletInfo)){
                return $this->ajaxError(1814, [], '用户信息错误');

            }
            $money = intval($params['money']);
            if(($walletInfo->balance - $money) < 0){
                return $this->ajaxError(1813, [], '余额不足');

            }
            $vcoin = $virtual[$money];
            if(empty($vcoin)){
                return $this->ajaxError(1812, [], '金额错误');

            }
            //生成订单
            $orderParams['uid'] = $params['uid'];
            $orderParams['money'] = $money;
            $orderParams['wx_money'] = 0;
            $orderInfo = $this->orderLogic->gameOrderAdd($orderParams);
            if ($orderInfo != false) {
                #减金额
                $balance_res = $this->walletModel->where(['uid'=>$params['uid']])->setDec('balance',$money);
                #+虚拟币
                $vicoin_res = $this->walletModel->where(['uid'=>$params['uid']])->setInc('virtual',$vcoin);
                if($balance_res && $vicoin_res){
                    //资金流水记录
                    $billLogID = $this->billLogModel->billLogAdd([
                        'uid' => $params['uid'],
                        'type' => 8,
                        'affect_money' => '-' . $money,
                        'balance_money' => $walletInfo->balance - $money,
                        'money_source' => 2,
                    ]);
                    //日志记录
                    $coinlog->addLog($params['uid'],7,$vcoin,1,$walletInfo['virtual']+$vcoin);     #虚拟币日志
                    //订单完成
                    #修改订单状态
                    $order_res = $gameOrder->save(['finish_at'=>date('Y-m-d H:i:s')],['order_sn'=>$orderInfo['order_sn']]);

                    Db::commit();
                    $result = $this->ajaxSuccess(1307, ['order_sn' => $orderInfo['order_sn']], '订单支付成功');
                }else{
                    Db::rollback();
                    $result = $this->ajaxError(106, [], '支付失败');
                }
            } else {
                Db::rollback();
                $result = $this->ajaxError(106, [], '生成失败');
            }
        }catch (Exception $exception){
            Db::rollback();
            $result = $this->ajaxError(106, [], '生成失败');
        }
        return $result;
    }
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-08
     *
     * @description 余额支付
     * @param array $params
     * @return array
     */
    private function _balancePay(array $params)
    {
        try {
            Db::startTrans();
            //生成红包
            $bonusId = $this->bonusLogic->bonusAdd($params);
            if ($bonusId != false) {
                //生成红包详情
                $service_money = $params['service_money'] ?? 0;
                //广告红包
                if (isset($params['class']) && ($params['class'] == 2)) {
                    $remark = new BonusRemark();
                    $where = ['text'=>$params['adv_remark'] ?? '','bonus_id'=>$bonusId];
                    $a = $remark->addRemarkByBonus($where);
                    if (!$a) {
                        return $this->ajaxError(106, [], '广告详情保存失败');
                    }
                }
                $bonusDetail = $this->bonusLogic->bonusDistribution($bonusId, $service_money);
                if ($bonusDetail) {
                    $money = $params['bonus_num'] == 1 ? $params['bonus_money'] + $params['service_money'] : $params['bonus_money'];
                    //红包支付成功
                    $bonusNum = $this->bonusModel->bonusEdit([
                        'is_pay' => 1,
                        'bonus_id' => $bonusId,
                    ]);

                    //生成订单
                    $orderParams['bonus_id'] = $bonusId;
                    $orderParams['uid'] = $params['uid'];
                    $orderParams['money'] = $money;
                    $orderParams['finish_at'] = date('Y-m-d H:i:s');
                    $orderParams['wx_money'] = 0;
                    $orderInfo = $this->orderLogic->orderAdd($orderParams);
                    $walletInfo = $this->walletModel->walletDetail($params['uid']);
                    $wanum = $this->walletModel->walletEdit([
                        'uid' => $params['uid'],
                        'balance' => $walletInfo->balance - $money,
                    ]);
                    $billLogID = $this->billLogModel->billLogAdd([
                        'uid' => $params['uid'],
                        'type' => 1,
                        'affect_money' => '-' . $money,
                        'balance_money' => $walletInfo->balance - $money,
                        'money_source' => 2,
                    ]);
                    //存在redis里面
                    #查询新增的数据
                    $bonusDetailModel=new BonusDetail();
                    $redis=new RedisService();
                    $returnlist = $bonusDetailModel->getAddData($bonusId);
                    foreach ($returnlist as $k=>$v){
                        if(!empty($v['receive_money'])){
                            $redis->lpush('bonus_'.$bonusId,$v['id'].'-'.$v['receive_money']);
                        }
                    }
                    $redis->expire('bonus_'.$bonusId,config('payment.wx_notify')['bonus_effective_time']); #设置红包的生命周期
                    if ($billLogID > 0 && $wanum != false && $bonusNum != false && $orderInfo != false) {
                        Db::commit();
                        if($params['class']==1){
                            $page='pages/packet/packet?bonus_id=' . $bonusId; //语音
                        } elseif ($params['class'] == 2) {
                            $page = 'pages/adv_play/adv_play?bonus_id=' . $bonusId; //广告
                        } else {
                            $page='pages/play/play?bonus_id=' . $bonusId; //口令
                        }
                        $tpl = [
                            'type' => 'send',
                            'page' => $page,
                            'form_id' => $params['form_id'] ?? '',
                            'openid' => $params['uid'],
                            'key1' => empty($params['bonus_password'])?'语音红包':$params['bonus_password'],
                            'key2' => date('Y-m-d'),
                        ];
                        $this->weService->tplSend($tpl);
                        $result = $this->ajaxSuccess(1307, ['bonus_id' => $bonusId, 'tpl'=>$tpl]);
                    } else {
                        Db::rollback();
                        $result = $this->ajaxError(106, [], '生成失败');
                    }
                } else {
                    Db::rollback();
                    $result = $this->ajaxError(106, [], '生成失败');
                }
            } else {
                Db::rollback();
                $result = $this->ajaxError(106, [], '生成失败');
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(106, [], '生成失败');
        }
        return $result;
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 微信支付(在用)
     * @param array $params
     * @return array
     */
    private function _wxpay(array $params)
    {
        try {
            Db::startTrans();
            //生成红包
            $bonusId = $this->bonusLogic->bonusAdd($params);
            if ($bonusId != false) {
                //生成红包详情
                $service_money = $params['service_money'] ?? 0;
                $bonusDetail = $this->bonusLogic->bonusDistribution($bonusId, $service_money);
                if ($bonusDetail) {
                    //广告红包
                    if (isset($params['class']) && ($params['class'] == 2)) {
                        $remark = new BonusRemark();
                        $where = ['text'=>$params['adv_remark'] ?? '','bonus_id'=>$bonusId];
                        $a = $remark->addRemarkByBonus($where);
                        if (!$a) {
                            return $this->ajaxError(106, [], '广告详情保存失败');
                        }
                    }
                    //生成订单
                    $money = $params['bonus_num'] == 1 ? $params['bonus_money'] + $params['service_money'] : $params['bonus_money'];
                    $orderParams['bonus_id'] = $bonusId;
                    $orderParams['uid'] = $params['uid'];
                    $orderParams['money'] = $money;
                    $orderParams['wx_money'] = $money;
                    $orderInfo = $this->orderLogic->orderAdd($orderParams);
                    if ($orderInfo != false) {
                        $wcService = new WeChatPayService();
                        $orderInfo['uid'] = $params['uid'];
                        $response = $wcService->payInfo($orderInfo);
                        Files::CreateLog('apitest.txt',$response);
                        Db::commit();
                        $this->bonusModel->bonusEdit([
                            'bonus_id' => $bonusId,
                            'prepay_id' => $response['prepay_id'],
                        ]);
                        $result = $this->ajaxSuccess(1306, ['sign' => $response['response'], 'bonus_id' => $bonusId], '订单支付成功');
                    } else {
                        Db::rollback();
                        $result = $this->ajaxError(106, [], '生成失败');
                    }
                } else {
                    Db::rollback();
                    $result = $this->ajaxError(106, [], '生成失败');
                }
            } else {
                Db::rollback();
                $result = $this->ajaxError(106, [], '生成失败');
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(106, [], '生成失败');
        }
        return $result;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 微信支付回调(在用)
     */
    public function wxNotify()
    {
        $wcService = new WeChatPayService();
        $result = $wcService->notifyCallback();
        if ($result !== false) {
            $orderSN = $result["out_trade_no"];//商户订单号
            $tradeNo = $result["transaction_id"];//微信支付订单号
            $money = $result['total_fee'] / 100;//支付金额
            $params['pay_type'] = 1;
            $params['trade_no'] = $tradeNo;
            $params['order_sn'] = $orderSN;
            $params['money'] = $money;
            $flag = $this->orderLogic->orderCallback($params);
            if ($flag) {
                echo 'success';
            } else {
                echo 'error';
            }
        }
    }

    /** 微信游戏充值支付回调
     * auth smallzz
     */
    public function wxNotifyGame()
    {
        $wcService = new WeChatPayService();
        $result = $wcService->notifyCallbacks();
        if ($result !== false) {
            $orderSN = $result["out_trade_no"];//商户订单号
            $tradeNo = $result["transaction_id"];//微信支付订单号
            $money = $result['total_fee'] / 100;//支付金额
            $params['pay_type'] = 1;
            $params['trade_no'] = $tradeNo;
            $params['order_sn'] = $orderSN;
            $params['money'] = $money;
            $flag = $this->orderLogic->orderCallbackGame($params);
            if ($flag) {
                echo 'success';
            } else {
                echo 'error';
            }
        }
    }
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-07
     *
     * @description 用户提现(在用)
     * @param array $params
     * @return array
     */
    public function EnterprisePay(array $params)
    {
        $enterpriseModel=new Enterprise();
        //主动提现情况单独判断
        if ($params['enterprise_type'] == 2) {
            if((strtotime(date('H:i:s')) < strtotime(config('wallet.withdraw_peroid')[0])) ||
                (strtotime(date('H:i:s')) > strtotime(config('wallet.withdraw_peroid')[1])))
            {
                return $this->ajaxError(105,[],'提现时间为早上9点到晚上9点');
            }
            $balance = $this->walletModel->getYuE($params['uid']);
            if ($balance < $params['money'] || ($params['money'] < 0)) {
                return $this->ajaxError(105,[],'余额不足');
            }
            if ($params['money'] >= config('wallet.withdraw_max')) {
                $review = new WithdrawReview();
                $a = $review->initOne(['uid'=>$params['uid'],'money'=>$params['money']]);
                if (!empty($a)) {
                    $wallet = new Wallet();
                    $wallet->delBalance($params['uid'],$params['money']);
                }
                return $this->ajaxSuccess(102,[],'您的提现请求已发送，我们将在1-5个工作日内处理。');
            }
            $num=$enterpriseModel->enterpriseLimit($params['uid'],2);
            if ($num >= 2) {
                return $this->ajaxError(105, [], '已超出每日提现次数(限制为2次)');
            }
        }

        $enterpriseInfo=$enterpriseModel->enterpriseLast();
        if($enterpriseInfo['enterprise_balance']-$params['money']<config('payment.automatic_refund')['enterprise_balance']){
            $redisService=new RedisService();
            $sendEmail=Cache::get('send_email','0-0');
            $sendEmailArr=explode('-',$sendEmail);
            if($sendEmailArr[0]<4 && $sendEmailArr[1]+config('payment.automatic_refund')['send_time']<time()){
                //邮箱 放队列去
                $json = json_encode(config('payment.automatic_refund')['email']);
                $redisService->rpush('send_message', $json);
                Cache::set('send_email',($sendEmailArr[0] + 1) . '-' . time(),new \DateTime(config('payment.automatic_refund')['send_time_check']));
            }
            $sendDingtalk=Cache::get('send_dingtalk','0-0');
            $sendDingtalkArr=explode('-',$sendDingtalk);
            if($sendDingtalkArr[0]<4 && $sendDingtalkArr[1]+config('payment.automatic_refund')['send_time']<time()){
                //推送钉钉
                $json = json_encode(config('payment.automatic_refund')['ding_talk']);
                $redisService->rpush('send_message', $json);
                Cache::set('send_dingtalk',($sendDingtalkArr[0] + 1) . '-' . time(),new \DateTime(config('payment.automatic_refund')['send_time_check']));
            }
        }
        //提现订单生成
        $orderParams['uid'] = $params['uid'];
        $orderParams['money'] = $params['money'];
        $orderParams['wx_money'] = $params['refund_fee'] ?? $params['money'];
        $orderParams['check_name'] = $params['check_name'] ?? '';
        $orderInfo = $this->orderLogic->orderAdd($orderParams);
        $orderInfo['uid'] = $params['uid'];
        //走订单退款
        if ($params['enterprise_type'] == 3) {
            $wx['out_trade_no'] = $params['order_sn'];
            $wx['out_refund_no'] = $params['order_sn'];
            $wx['total_fee'] = $params['total_fee']*100;
            $wx['refund_fee'] = $params['refund_fee']*100;
            $wx['op_user_id'] = $params['uid'];
            $response = \WxPayApi::refund($wx);
            if ($response['return_code'] == 'SUCCESS' && $response['result_code'] == 'SUCCESS') {
                return $this->ajaxSuccess(102, [], '退款成功');
            }
        }else{
            //微信支付
            $weep = new WeChatEnterprisePayService();
            $url = config('wechat.url');
            $data = $weep->EnteropriseParams($orderInfo);
            $xml = Curl::postXmlSSLCurl($data, $url, config('wechat.sslcert_path'), config('wechat.sslkey_path'));
            $response = Utils::parseMsgData($xml);
        }
        if ($response) {
            if ($response['return_code'] == 'SUCCESS') {
                if ($response['result_code'] == 'SUCCESS') {
                    //成功
                    $orderSN = $response["partner_trade_no"];//商户订单号
                    $tradeNo = $response["payment_no"];//微信支付订单号
                    $wxparams['pay_type'] = 1;
                    $wxparams['trade_no'] = $tradeNo;
                    $wxparams['order_sn'] = $orderSN;
                    $wxparams['enterprise_type']=$params['enterprise_type'];
                    $wxparams['check_name'] = $params['check_name'] ?? '';
                    $flag = $this->orderLogic->orderCallbackWithdrawals($wxparams);
                    if ($flag) {
                        $redisService = new RedisService();
                        $tpl = [
                            'type' => 'withdrawals',
                            'page' => 'pages/hall/hall',
                            'form_id' => $redisService->lpop($params['uid']),
                            'openid' => $params['uid'],
                            'key1' =>  $params['money']. '元',
                            'key3' => date('Y-m-d H:i:s'),
                        ];
                        $curl = $this->weService->tplSend($tpl);
                        $result = $this->ajaxSuccess(102, [], '提现成功');
                    } else {
                        //失败,数据表更新新错误
                        $result = $this->ajaxError(105, [], '提现失败,请联系客服');
                    }
                } else {
                    //失败,$response['err_code']错误信息
                    $result = $this->ajaxError(105, [], '提现失败,请联系客服');
                }
            } else {
                //失败,验签失败
                $result = $this->ajaxError(105, [], '提现失败,请联系客服');
            }
        } else {
            //失败,回调不是xml
            $result = $this->ajaxError(105, [], '提现失败,请联系客服');
        }
        return $result;
    }


    /**
     * @Author liyongchuan
     * @DateTime 2018-01-11
     *
     * @description 订单关闭
     * @param array $params
     * @return array
     */
    public function closeOrder(array $params)
    {
        try {
            $orderModel = new Order();
            $orderInfo = $orderModel->orderDetailByBonusId($params['bonus_id'],$params['type'] ?? 1);
            //关闭微信订单
            $wx = new \WxPayCloseOrder();
            $wx->SetOut_trade_no($orderInfo['order_sn']);
            $result = \WxPayApi::closeOrder($wx);
            if (($result['result_code'] != 'SUCCESS') || ($result['return_code'] != 'SUCCESS')) {
                return $this->ajaxError(104, [], '微信订单关闭失败');
            }

            if ($orderInfo['is_close'] == 0) {
                $edit = $orderModel->orderEdit([
                    'order_sn' => $orderInfo['order_sn'],
                    'is_close' => 1,
                    'finish_at' => date('Y-m-d H:i:s'),
                ]);
                if ($edit != false) {
                    $result = $this->ajaxError(101, [], '订单关闭成功');
                } else {
                    $result = $this->ajaxError(104, [], '订单关闭失败');
                }
            } else {
                $result = $this->ajaxError(101, [], '订单关闭成功');
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(104, [], '订单关闭失败');
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-09
     *
     * @description 关闭游戏充值订单
     * @param array $params
     * @return array
     */
    public function closeGameOrder(array $params)
    {
        try {
            $orderModel = new GameOrder();
            $orderInfo = $orderModel->orderInfo($params['order_sn']);
            //关闭微信订单
            $wx = new \WxPayCloseOrder();
            $wx->SetOut_trade_no($orderInfo['order_sn']);
            $result = \WxPayApi::closeOrder($wx);
            if (($result['result_code'] != 'SUCCESS') || ($result['return_code'] != 'SUCCESS')) {
                return $this->ajaxError(104, [], '微信订单关闭失败');
            }

            if ($orderInfo['is_close'] == 0) {
                $edit = $orderModel->orderEdit([
                    'order_sn' => $orderInfo['order_sn'],
                    'is_close' => 1,
                    'finish_at' => date('Y-m-d H:i:s'),
                ]);
                if ($edit != false) {
                    $result = $this->ajaxError(101, [], '订单关闭成功');
                } else {
                    $result = $this->ajaxError(104, [], '订单关闭失败');
                }
            } else {
                $result = $this->ajaxError(101, [], '订单关闭成功');
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(104, [], '订单关闭失败');
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-05
     *
     * @description 广告红包详情
     * @param array $param
     * @return array
     */
    public function advBonusDetail(array $param)
    {
        $remark = new BonusRemark();
        $data = $this->bonusModel->bonusDerail($param['bonus_id']);
        if (!empty($data)) {
            $re = $remark->getOne(['bonus_id'=>$param['bonus_id']]);
            $list['adv_name'] = $data['adv_name'];
            $list['adv_logo'] = $data['adv_logo'];
            $list['adv_remark'] = $re['text'] ?? '';
            $list['view_num'] = $re['view_num'] ?? 0;
            $list['password'] = $data['bonus_password'];
            $user = new User();
            $nick = $user->userDetail($data['uid']);
            $list['nickname'] = $nick['nickname'] ?? '';
            $list['avatarulr'] = $nick['avatarulr'] ?? '';
            return $this->ajaxSuccess(102,['list'=>$list]);
        }
        return $this->ajaxError(105);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-05
     *
     * @description 广告红包用户信息
     * @param array $param
     * @return array
     */
    public function getAdvUserInfo(array $param)
    {
        $user = new User();
        $wallet = new Wallet();
        $user = $user->userDetail($param['uid']);
        $wallet = $wallet->walletDetail($param['uid']);
        if (!empty($user) && !empty($wallet)) {
            $list['nickname'] = $user['nickname'] ?? '';
            $list['avatarulr'] = $user['avatarulr'] ?? '';
            $list['balance'] = !empty($wallet['balance']) ? sprintf("%01.2f",intval($wallet['balance']*100)/100): 0;
            return $this->ajaxSuccess(102,['list'=>$list]);
        }
        return $this->ajaxError(105);
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-24
     *
     * @description 上传录音文件(内部调用)
     * @param $file
     * @return array|bool
     */
    public function uploadMp3($file)
    {
        $filetime = Files::createFileName();
        $audio = new AudioService();
        $newfile = config('audio')['mp3_dir'] . 'tape' . $filetime . '.mp3';
        $res = $audio->moveFile($file['file']['tmp_name'], $newfile);
        if (!empty($res)) {
            return $audio->audioOssUp('tape' . $filetime . '.mp3', $newfile);
        }
        return [];
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-05
     *
     * @description 上传图片
     * @param $file
     * @return array
     */
    public function uploadPic($file)
    {
        $data = Utils::uploadPic($file,'pic');
        if (empty($data)) {
            return $this->ajaxError(105);
        }
        return $this->ajaxSuccess(100,['list'=>$data]);
    }
}