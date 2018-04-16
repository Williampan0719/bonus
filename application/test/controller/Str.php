<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/12
 * Time: 上午10:46
 */

namespace app\test\controller;


use extend\service\WechatService;

class Str
{
    public function ercode(){
        $new = new WechatService();
        echo $new->getQrCodes('','',430);
    }
    public function aa(){
        $str = 'okPcX0XbevTGZkvgYoyfnm_STrPM';
        #echo decbin($str); #十进制转二进制 decbin() 函数，如下实例
        #echo decoct($str); #十进制转八进制 decoct() 函数
        #echo dechex($str); #十进制转十六进制 dechex()
        #echo dechex($str); #二进制转十六制进 bin2hex() 函数
        #echo bindec($str); #二进制转十制进 bindec() 函数
        #echo octdec($str); #八进制转十进制 octdec() 函数
        $a = hexdec($str); #十六进制转十进制 hexdec()函数
        echo $a.'<br/>';

        $b = decoct($a);
        $c = decbin($a);

        $e = base_convert($str, 16, 2);
        echo base_convert($e, 2, 16);
    }
    function authcode($string, $operation = 'DECODE', $key = 'SMALLZZ', $expiry = 0) {

        $ckey_length = 4; // 随机密钥长度 取值 0-32;
        // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
        // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
        // 当此值为 0 时，则不产生随机密钥

        $key = md5($key ? $key : 'SMALLZZ'); //UC_KEY 为加密密钥 此为UcHome 中的代码，没有修改
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
    public function index(){
        $example = config('example');
        $rand = rand(0,count($example)-1);
        var_dump($example[$rand]);
    }
    public function gg(){
        $arr = explode("\r\n",file_get_contents('/zzhh/wechat-api/public/video/1.txt'));
        $str = 'return [';
        foreach ($arr as $item){
            $str .= "'".$item."',";
        }
        $arr = explode("\r\n",file_get_contents('/zzhh/wechat-api/public/video/2.txt'));
        foreach ($arr as $item){
            $str .= "'".$item."',";
        }
        $arr = explode("\r\n",file_get_contents('/zzhh/wechat-api/public/video/3.txt'));
        foreach ($arr as $item){
            $str .= "'".$item."',";
        }
        $str .= ']';
        file_put_contents('/zzhh/wechat-api/public/video/a.txt',$str);
        var_dump($str);
    }
}