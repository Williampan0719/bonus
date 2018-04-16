<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/12/22
 * Time: 下午2:55
 */

namespace extend\service\payment;


use extend\helper\Files;
use extend\service\payment\contracts\Payment;
use think\Exception;
use think\File;
use think\Log;

class WeChatPayService implements Payment

{
    private $config;

    /**
     * WeChatPayService constructor.
     */
    public function __construct()
    {
        $this->config = config('wechat');
    }
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-10
     *
     * @description 微信支付生成参数(在用)
     * @param array $data
     * @param null $callback
     * @return array
     * @throws Exception
     */
    public function payInfo(array $data = [], $callback = null,$type = 0)
    {
        $jsApi = new \JsApi_pub();
        $input = new \UnifiedOrder_pub();
        new \WxPayConf_pub($this->config);
        if($type == 1){   #区分回调
            $notifyUrl = url('api/pay/wx-notify_game', '', false, true);
        }else{
            $notifyUrl = url('api/pay/wx-notify', '', false, true);
        }
        $total_fee = $data['wx_money']*100;
        if ((1 > $total_fee) && (0 < $total_fee)) {
            $total_fee = 1;
        }
        $input->setParameter("body", $data['order_detail']);//商品描述
        $input->setParameter('openid',$data['uid']);
        $input->setParameter("out_trade_no", $data['order_sn']);//商户订单号
        $input->setParameter("total_fee", $total_fee);//总金额
        $input->setParameter("notify_url", $notifyUrl);//通知地址
        $input->setParameter("trade_type", "JSAPI");//交易类型

        try {
            $prepay_id = $input->getPrepayId();
        } catch (Exception $e) {
            echo $e;
            exit;
        }
        if ($prepay_id !== null) {
            $jsApi->setPrepayId($prepay_id);
            $response = [
                'response'=>$jsApi->getParameters(),
                'prepay_id'=>$prepay_id
                ];
            return $response;
        } else {
            throw new Exception("错误：获取prepayid失败");
        }
    }

    /**
     * @Author zhanglei
     * @DateTime 2018-01-03
     *
     * @description 微信支付回调函数
     * @param array $data
     * @param null $class(用)
     * @param null $callback
     * @return mixed
     */
    public function notifyCallback(array $data = [], $callback = null)
    {
        Files::CreateLog(date('Y-m-d').'.txt','liyongchuan');
        //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/video/log.txt','liyongchuan'."\r\n",FILE_APPEND);
        //使用通用通知接口
        $notify = new \Notify_pub();
        new \WxPayConf_pub($this->config);
        //存储微信的回调
        $xml = file_get_contents("php://input");
        #file_put_contents($_SERVER['DOCUMENT_ROOT'].'/video/log.txt',$xml."\r\n",FILE_APPEND);
        Files::CreateLog(date('Y-m-d').'.txt',$xml);
        $notify->saveData($xml);
        if ($notify->checkSign() == FALSE) {
            $notify->setReturnParameter("return_code", "fail");//返回状态码
            $notify->setReturnParameter("return_msg", "签名失败");//返回信息
        } else {
            $notify->setReturnParameter("return_code", "success");//设置返回码
        }
        if ($notify->checkSign() == TRUE || $notify->checkSign() == 1) {
            if ($notify->data["return_code"] == "fail") {
                Log::record("【微信支付】=======通信失败");
                return false;
            } else if ($notify->data["result_code"] == "fail") {
                Log::record("【微信支付】=======交易失败=====错误代码{$notify->data["err_code"]}=======错误代码描述{$notify->data["err_code_des"]}");
                return false;
            } else {
                Files::CreateLog('zzhh.txt','succ');
                return $notify->data;
            }
        }

    }
    public function notifyCallbacks(array $data = [], $callback = null)
    {
        //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/video/log.txt','liyongchuan'."\r\n",FILE_APPEND);
        //使用通用通知接口
        $notify = new \Notify_pub();
        new \WxPayConf_pub($this->config);
        //存储微信的回调
        $xml = file_get_contents("php://input");
        Files::CreateLog(date('Y-m-d').'.txt',$xml);
        #将xml转数组
        $data = $this->xmlToArray($xml);
        $notify->saveData($xml);
        if ($notify->checkSign() == FALSE) {
            $notify->setReturnParameter("return_code", "fail");//返回状态码
            $notify->setReturnParameter("return_msg", "签名失败");//返回信息
        } else {

            $notify->setReturnParameter("return_code", "success");//设置返回码
        }
        if ($notify->checkSign() == TRUE || $notify->checkSign() == 1) {
            if ($data["return_code"] == "fail") {
                Log::record("【微信支付】=======通信失败");
                return false;
            } else if ($data["result_code"] == "fail") {
                Log::record("【微信支付】=======交易失败=====错误代码{$notify->data["err_code"]}=======错误代码描述{$notify->data["err_code_des"]}");
                return false;
            } else {
                #Files::CreateLog('zzhh1.txt',var_export($data,true));
                #return $notify->data;
                return $data;
            }
        }

    }
    public function xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }
}