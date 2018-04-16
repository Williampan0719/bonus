<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/7
 * Time: 10:42
 * @introduce
 */

namespace extend\service\payment;

use extend\helper\Utils;

class WeChatEnterprisePayService
{
    protected $mch_appid;
    protected $mchid;
    protected $key;
    public function __construct()
    {
        $this->mch_appid = config('wechat.appid');
        $this->mchid = config('wechat.mchid');
        $this->key = config('wechat.key');
    }

    /**
     *    作用：生成签名
     */
    public function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->key;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }

    /**
     *    作用：格式化参数，签名过程需要使用
     */
    public function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    /**
     *    作用：array转xml
     */
    public function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";

            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-07
     *
     * @description 企业支付参数整理
     * @param array $data
     * @return mixed
     */
    public function EnteropriseParams(array $data)
    {
        $params["mch_appid"] = $this->mch_appid;//商户账号appid
        $params['mchid'] = $this->mchid;//mchid
        $params['nonce_str'] = Utils::randomString(32);
        $params['openid'] = $data['uid'];
        $params['check_name'] = 'NO_CHECK';
        $params['amount'] = $data['wx_money']*100;
        $params['desc'] = $data['order_detail'];
        $params['partner_trade_no']=$data['order_sn'];
        $params['spbill_create_ip'] = $_SERVER['REMOTE_ADDR']??config('wechat.service_ip');//终端ip
        $s = $this->getSign($params);
        $params["sign"] = $s;
        $xml=$this->arrayToXml($params);
        return $xml;
    }
}