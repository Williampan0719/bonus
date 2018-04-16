<?php
/**
 * 创蓝短信
 * User: dongmingcui
 * Date: 2017/12/6
 * Time: 下午5:29
 */

namespace extend\service\message;

use extend\helper\Curl;

class CreateBlueService
{
    protected $config;
    protected $account;
    public $setAccountType;
    private $header;

    /**
     * CreateBlueService constructor.
     */
    public function __construct()
    {
        $this->config = config('createBlue');
        $this->setAccountType();

        $this->header = ['Content-Type: application/json; charset=utf-8'];
    }

    /**
     * 设置发送账号
     * @param $accountType
     */
    public function setAccountType($accountType = 'code')
    {
        if ($accountType == 'code') {
            $this->account = [
                'account' => $this->config['code_account'],
                'password' => $this->config['code_password']
            ];
        } else {
            $this->account = [
                'account' => $this->config['notice_account'],
                'password' => $this->config['notice_password']
            ];
        }
    }

    /**
     * 发送单条
     * @param $mobile
     * @param $msg
     * @param string $needStatus
     * @return mixed|string
     */
    public function sendSMS($mobile, $msg, $needStatus = 'true')
    {

        $data = [
            'account' => $this->account['account'],
            'password' => $this->account['password'],
            'msg' => urlencode($msg),
            'phone' => $mobile,
            'report' => $needStatus
        ];


        $data = json_encode($data);

        $url = $this->config['sms_gate_way'] . '/msg/send/json';
        $result = Curl::buildHttp($url, $data, 'POST', $this->header, true);

        return $result;
    }

    /**
     * 发送模版短信
     * @param $msg
     * @param $params
     * @return mixed
     */
    public function sendVariableSMS($msg, $params)
    {

        $data = [
            'account' => $this->account['account'],
            'password' => $this->account['password'],
            'msg' => $msg,
            'params' => $params,
            'report' => 'true'
        ];

        $data = json_encode($data);

        $url = $this->config['sms_gate_way'] . '/msg/variable/json';
        $result = Curl::buildHttp($url, $data, 'POST', $this->header, true);

        return $result;
    }

    /**
     * 查询余额
     * @return mixed
     */
    public function queryBalance()
    {
        $data = [
            'account' => $this->account['account'],
            'password' => $this->account['password'],
        ];

        $data = json_encode($data);

        $url = $this->config['sms_gate_way'] . '/msg/balance/json';
        $result = Curl::buildHttp($url, $data, 'POST', $this->header, true);

        return $result;
    }

}