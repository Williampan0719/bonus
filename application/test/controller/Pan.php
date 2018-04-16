<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/10
 * Time: 下午5:07
 * @introduce
 */
namespace app\test\controller;

use app\cron\logic\UserLogic;
use app\payment\logic\AbonusLogic;
use app\payment\logic\OrderLogic;
use app\payment\model\Bonus;
use extend\service\RedisService;
use think\Hook;
use think\Loader;
use think\Request;

Loader::import('thirdpart.wxpay.WxPayPubHelper.WxPayPubHelper');
Loader::import('thirdpart.wxpay.lib.WxPay');

class Pan
{
    protected $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    public function test()
    {
        $wx['out_trade_no'] = 'UTSEOR15235175981956951957';
        $wx['out_refund_no'] = 'UTSEOR15235175981956951957';
        $wx['total_fee'] = 0.01*100;
        $wx['refund_fee'] = 0.01*100;
        $wx['op_user_id'] = 2;
        $response = \WxPayApi::refund($wx);
        return $response;
    }

    public function testFormId()
    {
        $redis = new RedisService();
        return $redis->lpop('ocJN_4jN7hgUaIrIkVC-fDhUX_SU');
    }

    public function testCloseOrder()
    {
        $wx = new \WxPayOrderQuery();
        $param = $this->request->param();
        $wx->SetOut_trade_no($param['order_sn']);
        $result = \WxPayApi::orderQuery($wx);
        dump($result);exit;
    }

}