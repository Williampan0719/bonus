<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/5
 * Time: 下午2:52
 */

namespace extend\service;


use extend\helper\Curl;
use extend\helper\Files;
use think\Exception;

class WechatService
{
    protected $config = [];
    protected $redis = null;
    protected $wechattpl = null;
    function __construct()
    {
        $this->config = config('wechat');
        $this->redis = new RedisService();
        $this->wechattpl = new WechatTpl();
    }

    /**code换取 session_key
     * auth smallzz
     * @param $code
     * @return bool|mixed
     */
    public function getSessionKey($code)
    {
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $this->config['small_appid'] . '&secret=' . $this->config['small_appsecret'] . '&js_code=' . $code . '&grant_type=authorization_code';
        $res = Curl::getJson($url);
        return $res;
    }

    /**检验数据的真实性，并且获取解密后的明文.
     * auth smallzz
     * @param $appid
     * @param $sessionKey
     * @param $encryptedData   加密的用户数据
     * @param $iv       与用户数据一同返回的初始向量
     * @param $data     解密后的原文
     * @return int      成功0，失败返回对应的错误码
     */
    public function decryptData($sessionKey, $encryptedData, $iv, &$data)
    {
        if (strlen($sessionKey) != 24) {
            return -41001;
        }
        $aesKey = base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return -41002;
        }
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj = json_decode($result);
        if ($dataObj == NULL) {
            return -41003;
        }
        if ($dataObj->watermark->appid != $this->config['small_appid']) {
            return -41004;
        }
        $data = $result;
        return $data;
    }

    /** 获取小程序access_token
     * auth smallzz
     * @param $key
     * @param $data
     * @return bool
     */
    public function getAccessToken()
    {
        #$this->redis->del($this->config['small_accesstoken']);
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->config['small_appid'] . '&secret=' . $this->config['small_appsecret'];
        $tokenCache = $this->redis->get($this->config['small_accesstoken']);
        if ($tokenCache == false) {
            $result = Curl::getJson($url);
            #$result = json_decode($json, true);
            $result['time'] = time();
            $this->redis->set($this->config['small_accesstoken'], json_encode($result));
            $accessToken = $result['access_token'] ?? false;
        } else {
            $tokens = json_decode($tokenCache, true);

            if ($tokens['time'] + 7000 < time()) {
                $result = Curl::getJson($url);
                #$result = json_decode($json, true);
                $result['time'] = time();
                $this->redis->set($this->config['small_accesstoken'], json_encode($result));
                $accessToken = $result['access_token'] ?? false;
            } else {
                $accessToken = $tokens['access_token'];
            }
        }
        return $accessToken;
    }
    /** 获取微信公众号access_token
     * auth smallzz
     * @param $key
     * @param $data
     * @return bool
     */
    public function getAToken()
    {
        #$this->redis->del($this->config['wechat_accesstoken']);
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->config['wx_appid'] . '&secret=' . $this->config['wx_appsecret'];
        $tokenCache = $this->redis->get($this->config['wechat_accesstoken']);
        if ($tokenCache == false) {
            $result = Curl::getJson($url);
            #$result = json_decode($json, true);
            $result['time'] = time();
            $this->redis->set($this->config['wechat_accesstoken'], json_encode($result));
            $accessToken = $result['access_token'] ?? false;
        } else {
            $tokens = json_decode($tokenCache, true);
            if ($tokens['time'] + 7000 < time()) {
                $result = Curl::getJson($url);
                #$result = json_decode($json, true);
                $result['time'] = time();
                $this->redis->set($this->config['wechat_accesstoken'], json_encode($result));
                $accessToken = $result['access_token'] ?? false;
            } else {
                $accessToken = $tokens['access_token'];
            }
        }
        return $accessToken;
    }

    /** 获取api_ticket
     * auth smallzz
     * @return bool
     */
    public function getTicket(){
        #$this->redis->del($this->config['wechat_api_ticket']);
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$this->getAToken().'&type=wx_card';
        $tokenCache = $this->redis->get($this->config['wechat_api_ticket']);
        if ($tokenCache == false) {
            $result = Curl::getJson($url);

            //var_dump($result);exit;
            $result['time'] = time();
            $this->redis->set($this->config['wechat_api_ticket'], json_encode($result));
            $apiTicket = $result['ticket'] ?? false;
        } else {
            $tokens = json_decode($tokenCache, true);
            //var_dump($tokens);exit;
            if ($tokens['time'] + 7000 < time()) {
                $result = Curl::getJson($url);
                $result['time'] = time();
                $this->redis->set($this->config['wechat_api_ticket'], json_encode($result));
                $apiTicket = $result['ticket'] ?? false;
            } else {
                $apiTicket = $tokens['ticket'];
            }
        }
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/tmp/tick.txt','no:'.$apiTicket."\r\n",FILE_APPEND);
        return $apiTicket;
    }
    public function getSmallTicket(){
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$this->getAccessToken().'&type=wx_card';
        $tokenCache = $this->redis->get($this->config['small_wechat_api_ticket']);
        if ($tokenCache == false) {
            $result = Curl::getJson($url);
            $result['time'] = time();
            $this->redis->set($this->config['small_wechat_api_ticket'], json_encode($result));
            $apiTicket = $result['ticket'] ?? false;
        } else {
            $tokens = json_decode($tokenCache, true);
            if ($tokens['time'] + 7000 < time()) {
                $result = Curl::getJson($url);
                $result['time'] = time();
                $this->redis->set($this->config['small_wechat_api_ticket'], json_encode($result));
                $apiTicket = $result['ticket'] ?? false;
            } else {
                $apiTicket = $tokens['ticket'];
            }
        }
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/tmp/tick.txt','small:'.$apiTicket."\r\n",FILE_APPEND);
        return $apiTicket;
    }
    /** 生成二维码
     * auth smallzz
     * @param string $path
     * @param int $width
     * @return string
     */
    public function getQrCodes(string $scene,string $page, int $width,int $type)
    {
        $url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token=' . $this->getAccessToken();
        $data['path'] = $page.$scene;
        $data['width'] = $width;
        $result = Curl::postJson($url, $data, false);
        #创建画布
        $file = $_SERVER['DOCUMENT_ROOT'] . '/video/';
        $hz = Files::createFileName().'.png';
        $url = 'https://'.$_SERVER['HTTP_HOST'].'/video/'.$hz;
        file_put_contents($file.$hz,$result);
        if($type == 1){
            return $file.$hz;
        }
        return $url;
    }

    /**
     * auth smallzz
     * @param string $scene
     * @param string $page
     * @param int $width
     * @param $type  0返回url  1返回路径
     * @return string
     */
    public function getQrCode(string $scene='',string $page='',int $width=0,int $type){
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $this->getAccessToken();
        $data = [];
        if(!empty($scene)){
            $data['scene'] = $scene;
        }
        if(!empty($page)){
            $data['page'] = $page;
        }
        if(!empty($width)){
            $data['width'] = $width;
        }
        $result = Curl::postJson($url, $data, false);
        #var_dump($result);exit;
        #创建画布
        $file = $_SERVER['DOCUMENT_ROOT'] . '/video/';
        $hz = Files::createFileName().'.png';
        $url = 'https://'.$_SERVER['HTTP_HOST'].'/video/'.$hz;
        file_put_contents($file.$hz,$result);
        if($type == 1){
            return $file.$hz;
        }
        return $url;
        #return $file.$hz;
    }
    /**发送模版消息
     * auth smallzz
     * @param array $param
     * @return bool|mixed
     */
    public function tplSend(array $param){
        //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/video/log.txt',$param['type']."\r\n",FILE_APPEND);
        $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$this->getAccessToken();
        switch ($param['type']){
            case 'refund':
                $data = $this->wechattpl->refund($param);
                break;
            case 'done':
                $data = $this->wechattpl->done($param);
                break;
            case 'send':
                $data = $this->wechattpl->send($param);
                break;
            case 'withdrawals':
                $data = $this->wechattpl->withdrawals($param);
                break;
            case 'gradeChange':
                $data = $this->wechattpl->gradeChange($param);
                break;
            case 'give':
                $data = $this->wechattpl->give($param);
                break;
            case 'distribution':
                $data = $this->wechattpl->distribution($param);
                break;
            case 'rewardMoney':
                $data = $this->wechattpl->rewardMoney($param);
                break;
            case 'gameResult':
                $data = $this->wechattpl->gameResult($param);
                break;
            case 'gameRefund':
                $data = $this->wechattpl->gameRefund($param);
                break;
            default:
                return true;

        }
        $result=Curl::postJson($url,$data);
        return $result;

    }

    /** 获取用户信息
     * auth smallzz
     * @param string $openid
     * @return bool|mixed
     */
    public function getUnionid(string $openid){
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAToken().'&openid='.$openid.'&lang=zh_CN';
        try{
            $result = Curl::getJson($url);
        }catch (Exception $exception){
            return false;
        }
        return $result;
    }
    /** 随机32位
     * auth smallzz
     * @param int $length
     * @return string
     */
    public function _getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
    /** 生成签名
     * auth smallzz
     * @param string $arr
     * @return string
     */
    function _signature(array $array){
        sort($array);
        $str = "";
        foreach ($array as $k => $v) {
            $str .= $v;
        }
        $sign = sha1($str);
        return $sign;
    }

}