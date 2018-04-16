<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/11/17
 * Time: 下午12:40
 */

namespace extend\helper;


class Curl
{


    /**
     * 执行curl
     * @param string $url
     * @param string $method
     * @param string $data
     */
    public static function executeCurl($url = '', $method = 'post', $data = '')
    {
        $ch = curl_init();
        //调用接口方式
        if (strtoupper($method) == 'POST') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * 获得文件大小
     * @param $imgUrl
     * @return mixed
     */
    public static function getFileSize($imgUrl)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $imgUrl);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 60);
        $buf = curl_exec($c);
        $info = json_decode($buf);
        return $info->FileSize->value;
    }

    /**
     * 下载
     * @param string $url
     * @param string $path
     */
    protected function download(string $url, string $path)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        is_dir($pdir = dirname($path)) or mkdir($pdir, 0777, true);
        $resource = fopen($path, 'w');
        fwrite($resource, $file);
        fclose($resource);
    }

    /**
     * 建立curl连接
     * @param $url
     * @param $params
     * @param string $method
     * @param array $header
     * @param bool $multi
     * @return mixed|string
     */
    public static function buildHttp($url, $params, $method = 'GET', $header = [], $multi = false)
    {
        $opts = [
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => $header
        ];


        /* 根据请求类型设置特定参数 */
        switch (strtoupper($method)) {
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_SSL_VERIFYPEER] = 1;
                $opts[CURLOPT_HEADER] = false;
                $opts[CURLOPT_TIMEOUT] = 30;
                $opts[CURLOPT_RETURNTRANSFER] = true;
                $opts[CURLOPT_POSTFIELDS] = $params;

                break;
            default:
                throw new Exception('不支持的请求方式！');
        }

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error)
            return $error;
        return $data;
    }
    /** 新一代的洗衣粉新一代的人
     * auth smallzz----bilibilihome
     * @param $url
     * @param $data
     * @return bool|mixed
     */
    public static function postJson($url,$data,$type=true){
        $ch = curl_init();
        if (strpos($url, 'https://') === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
        ]);
        $response = curl_exec($ch);
        if (false === $response) {
            return false;
        } else {
            if($type){
                $resdata = json_decode($response, true);
            }else{
                $resdata = $response;
            }
            return $resdata;
        }
    }
    /** 新一代的人写新一代的代码
     * auth smallzz----bilibilihome
     * @param $url
     * @return bool|mixed
     */
    public static function getJson($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        if (false === $response) {
            return false;
        } else {
            $resdata = json_decode($response, true);
            return $resdata;
        }
    }
    /**
     *    作用：使用证书，以post方式提交xml到对应的接口url
     */
    public static function postXmlSSLCurl($xml, $url,$sslcert,$sslkey,$second = 30)
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, $sslcert);
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, $sslkey);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }

    /** 微信流媒体上传图片
     * auth smallzz
     * @param $url
     * @param $filedata
     * @return mixed
     */
    public static function upload($url, $filedata) {
        $curl = curl_init ();
        if (class_exists ( '\CURLFile' )) {//php5.5跟php5.6中的CURLOPT_SAFE_UPLOAD的默认值不同
            curl_setopt ( $curl, CURLOPT_SAFE_UPLOAD, true );
        } else {
            if (defined ( 'CURLOPT_SAFE_UPLOAD' )) {
                curl_setopt ( $curl, CURLOPT_SAFE_UPLOAD, false );
            }
        }
        curl_setopt ( $curl, CURLOPT_URL, $url );
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        if (! empty ( $filedata )) {
            curl_setopt ( $curl, CURLOPT_POST, 1 );
            curl_setopt ( $curl, CURLOPT_POSTFIELDS, $filedata );
        }
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
        $output = curl_exec ( $curl );
        curl_close ( $curl );
        return $output;

    }
    public function uploads($url,$path){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
        $data = array('file' => new \CURLFile(realpath($path)));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1 );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT,"TEST");
        $result = curl_exec($curl);
        $error = curl_error($curl);
        return $result;
    }
}