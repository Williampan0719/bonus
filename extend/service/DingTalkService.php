<?php
/**
 * 钉钉.
 * User: dongmingcui
 * Date: 2017/12/13
 * Time: 上午9:23
 */

namespace extend\service;

use extend\helper\Curl;

class DingTalkService
{
    protected $config;
    protected $header;
    public $msgBody;
    public $url;

    /**
     * DingTalkService constructor.
     */
    public function __construct($url,$msgBody)
    {
        //$this->url = 'https://oapi.dingtalk.com/robot/send?access_token=938aa94385d403a63d5a5fb477800be057fbc4e60c86a6dba0a562a99f7cc640';
        $this->url = $url;
        $this->msgBody = $msgBody;
        $this->header = ['Content-Type: application/json; charset=utf-8'];
        $this->config = config('dingtalk');
    }

    /**
     * 获取钉钉token
     * @return mixed
     */
    public function getToken()
    {
        $url = 'https://oapi.dingtalk.com/sns/gettoken?appid=' . $this->config['APP_ID'] . '&appsecret=' . $this->config['APP_SECRET'];

        $result = Curl::buildHttp($url, []);

        return $result['access_token'];
    }

    /**
     * 设置webhook url
     * @param $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }


    /**
     * 发送消息
     * @return mixed|string
     */
    public function sendMsg()
    {

        $params = is_array($this->msgBody) ? $this->templateMsg() : $this->msgBody;

        $result = Curl::buildHttp($this->url, $params, 'POST', $this->header, true);

        return $result;

    }


    /**
     * 消息模版组装
     * @return string
     */
    protected function templateMsg()
    {
        switch ($this->msgBody['msgType']) {
            case 'text':
                $msg['msgtype'] = 'text';
                $msg['text'] = ['content' => $this->msgBody['content']];
                $msg['at'] = [
                    'atMobiles' => $this->msgBody['atMobiles'],
                    'isAtAll' => $this->msgBody['isAtAll']
                ];
                break;
            case 'link':
                $msg['msgtype'] = 'link';
                $msg['link'] = [
                    'title' => $this->msgBody['title'],
                    'text' => $this->msgBody['content'],
                    'picUrl' => $this->msgBody['picUrl'],
                    'messageUrl' => $this->msgBody['messageUrl'],
                ];
                break;
            case 'markdown':
                $msg['msgtype'] = 'markdown';
                $msg['markdown'] = [
                    'title' => $this->msgBody['title'],
                    'text' => $this->msgBody['content'],
                ];
                $msg['at'] = [
                    'atMobiles' => $this->msgBody['atMobiles'],
                    'isAtAll' => $this->msgBody['isAtAll']
                ];
                break;
            case 'actionCard':
                $msg['msgtype'] = 'actionCard';
                $msg['actionCard'] = [
                    'title' => $this->msgBody['title'],
                    'text' => $this->msgBody['content'],
                    'hideAvatar' => $this->msgBody['hideAvatar'],
                    'btnOrientation' => $this->msgBody['btnOrientation'],
                    'btns' => $this->msgBody['btns'],
                ];
                break;
            case 'feedCard':
                break;
        }

        return json_encode($msg);

    }

}