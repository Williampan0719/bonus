<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/11/17
 * Time: 下午12:30
 */

namespace extend\helper;


use app\backend\logic\ConfigLogic;
use app\system\model\SystemConfigBwheel;
use app\system\model\SystemConfigVirtual;
use extend\service\AudioService;
use extend\service\RedisService;
use extend\service\storage\OssService;
use think\Cache;

class Utils
{

    /**
     *
     * 用户的密码进行加密
     * @param $password
     * @param string $encrypt
     * @return array|string
     */
    public static function genPassword($password, $encrypt = '')
    {
        $pwd = [];
        $pwd['encrypt'] = $encrypt ? $encrypt : self::salt();
        $pwd['password'] = sha1(md5(trim($password)) . $pwd['encrypt']);
        return $encrypt ? $pwd['password'] : $pwd;
    }

    /**
     * 支付密码
     * @param $password
     * @return string
     */
    public static function genPayPassword($password)
    {
        return sha1(md5(trim($password)));
    }


    /**
     * 生成盐值
     * @return string
     */
    public static function salt()
    {
        return substr(uniqid(), -5);
    }

    /**
     * 生成UUID
     * @param string $prefix 前缀 O/AO 订单SN,
     * @return string
     */
    public static function genUUID($prefix = "")
    {
        $uuid = time() . mt_rand(1000000, 9999999);
        $uuid = substr($uuid, 0, 17);
        return $prefix . $uuid;
    }

    /**
     * 生成token
     * @param int $length
     * @return string
     */
    public static function createToken($length = 88)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+=";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-10
     *
     * @description 生成随机字符串
     * @param int $length
     * @return string
     */
    public static function randomString($length = 88)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取随机码如验证码
     * @param int $nums
     * @return string
     */
    public static function randNum($nums = 6)
    {
        $num = "";
        for ($i = 0; $i < $nums; $i++) {
            $num .= rand(0, 9);
        }
        return $num;
    }


    /**
     * 处理json
     * @param $str
     * @return array|mixed
     */
    public static function parseJson($str)
    {
        $result = json_decode(str_replace('&quot;', '"', $str), true) ? json_decode(str_replace('&quot;', '"', $str), true) : [];

        return $result;
    }

    /**
     * 生成昵称
     * @param int $length
     * @return string
     */
    public static function createNickname($length = 10)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-26
     *
     * @description 上传oss图片(base64)
     * @param array $img
     * @param string $type
     * @return array
     */
    public static function ossUpload64(array $img, string $type)
    {
        //上传图片
        $oss = new OssService(config('oss.default_bucket_name'));
        $data = [];
        foreach ($img as $key => $value) {
            $time = Utils::getMicroTime();
            $tempImgUrl = $oss->ossBase64Upload($value);

            $ossUrl = $type . '/' . $time . config('oss.temp_pic_suffix');
            $oss->uploadOss($ossUrl, $tempImgUrl, true);
            $data[] = [config('oss.outer_host') . $ossUrl, $ossUrl];
        }

        return $data;
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-30
     *
     * @description 上传oss图片(file)
     * @param array $img
     * @param string $type
     * @return array
     */
    public static function ossUpload(array $img, string $type)
    {
        //上传图片
        $oss = new OssService(config('oss.default_bucket_name'));
        $data = [];
        foreach ($img as $key => $value) {
            $time = Utils::getMicroTime();

            $ossUrl = 'api/' . $type . '/' . $time . config('oss.temp_file_suffix');
            $oss->uploadOss($ossUrl, $value, true);
            $data[] = $ossUrl;
        }

        return $data;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-04
     *
     * @description 上传图片文件(内部调用)
     * @param $file
     * @param string $path
     * @return array|bool
     */
    public static function uploadPic($file,$path = 'template')
    {
        $filetime = Files::createFileName();
        $newfile = config('audio')['pic_dir'] . $filetime . '.jpg';
        $res = move_uploaded_file($file['file']['tmp_name'], $newfile);
        if (!empty($res)) {
            $audio = new AudioService();
            return $audio->picOssUp($filetime . '.jpg', $newfile,$path);
        }
        return [];
    }

    /**
     * @Author panhao
     * @DateTime 2017-12-27
     *
     * @description 获取当前时间毫秒级
     * @return float
     */
    public static function getMicroTime()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }


    /**
     * @Author zhanglei
     * @DateTime 2017-12-26
     *
     * @description 计算时间差
     * @param $startTime
     * @param $endTime
     * @return string
     */
    public static function getTimeDiff($startTime, $endTime)
    {

        //计算分钟
        $timeDiff = abs($startTime - $endTime);
        $mins = intval($timeDiff / 60);
        //计算秒数
        $secs = $timeDiff % 60;
        return "{$mins}分{$secs}秒";
    }


    /**
     * @Author zhanglei
     * @DateTime 2017-12-13
     *
     * @description 获取年龄
     * @param $birthday
     * @return bool|int
     */
    public static function getAge($birthday)
    {
        $age = strtotime($birthday);
        if ($age === false) {
            return false;
        }
        list($y1, $m1, $d1) = explode('-', date('Y-m-d', $age));
        $now = strtotime('now');
        list($y2, $m2, $d2) = explode('-', date('Y-m-d', $now));
        $age = $y2 - $y1;
        if ((int)($m2 . $d2) < (int)($m1 . $d1)) {
            $age -= 1;
        }

        return $age;
    }


    /**
     * @Author zhanglei
     * @DateTime 2017-12-13
     *
     * @description 获取星座
     * @param $birthday
     * @return mixed
     */
    public static function getConstell($birthday)
    {
        list($year, $month, $day) = explode('-', $birthday);
        $signs = array(
            array('20' => '宝瓶座'), array('19' => '双鱼座'),
            array('21' => '白羊座'), array('20' => '金牛座'),
            array('21' => '双子座'), array('22' => '巨蟹座'),
            array('23' => '狮子座'), array('23' => '处女座'),
            array('23' => '天秤座'), array('24' => '天蝎座'),
            array('22' => '射手座'), array('22' => '摩羯座')
        );
        $key = (int)$month - 1;
        list($startSign, $signName) = each($signs[$key]);
        if ($day < $startSign) {
            $key = $month - 2 < 0 ? $month = 11 : $month -= 2;
            list($startSign, $signName) = each($signs[$key]);
        }
        $data['id'] = $key;
        $data['name'] = $signName;
        return $data;
    }

    /**
     * @Author zhanglei
     * @DateTime 2017-12-26
     *
     * @description 返回阿里云地址
     * @param $params
     * @return array|string
     */
    public static function getOSSImage($params)
    {

        if (!empty($params)) {
            if (is_array($params)) {
                foreach ($params as $key => $val) {
                    $img = $val['img_path'];
                    if (strtolower(substr($img, 0, 4)) != 'http') {
                        $newImg = config('oss.outer_host') . $img;
                        $params[$key]['img_path'] = $newImg;
                    }


                }
            } else {
                if (strtolower(substr($params, 0, 4)) != 'http') {
                    $params = config('oss.outer_host') . $params;

                }
            }

        }

        return $params;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-07
     *
     * @description 解析XML
     * @param string $msgData
     * @return array|bool
     */
    public static function parseMsgData(string $msgData)
    {
        $pos = strpos($msgData, 'xml');

        if (!$pos) {
            return false;
        }

        $message = simplexml_load_string($msgData, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (is_object($message)) {
            return get_object_vars($message);
        }

        return false;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-16
     *
     * @description 获取常量缓存
     * @param string $key
     * @param string $prefix
     * @return bool|string
     */
    public static function getConstant(string $key, string $prefix)
    {
        $redis = new RedisService();
        $keys = $prefix.'_'.$key;
        //$redis->del($keys);
        if ($redis->get($keys) == false) {
            $system = new ConfigLogic();
            $data = $system->getOne($key,$prefix);
            if (!empty($data)) {
                $redis->set($keys,$data['num']);
                return $redis->get($keys);
            }
            return '';
        }
        return $redis->get($keys);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-11
     *
     * @description 获取虚拟币配置
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public static function getVirtualConfig()
    {
        $data = Cache::get('virtual');
        if ($data == false) {
            $virtual = new SystemConfigVirtual();
            $data = $virtual->getAll();
            Cache::set('virtual',$data,3600);
        }
        $list = [];
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $list[$v['money']] = $v['coin'];
            }
        }
        return $list;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-11
     *
     * @description 获取虚拟币配置
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public static function getBwheelConfig()
    {
        $data = Cache::get('bwheel');
        if ($data == false) {
            $wheel = new SystemConfigBwheel();
            $data = $wheel->getAll();
            Cache::set('bwheel',$data,3600);
        }
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $data[$k]['v'] = $v['rate'];
            }
        }
        return $data;
    }
}