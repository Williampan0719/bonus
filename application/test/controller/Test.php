<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/6
 * Time: 上午10:59
 */

namespace app\test\controller;


use app\payment\model\Wallet;
use app\user\model\UserReceiveHot;
use extend\service\AudioService;
use extend\service\bonusRules;
use extend\service\CharDb;
use extend\service\RedisService;
use extend\service\storage\OssService;
use extend\service\ToPinyin;
use extend\service\WechatService;
use think\Exception;

class Test
{
    public $redis = null;
    function __construct()
    {
        $this->redis = new RedisService();
    }

    public function delredis($id){



        $new = new RedisService();
        $res = $new->lrange('bonus_'.$id,0,-1);
        var_dump($res);
    }
    public function test(){
        echo str_replace('.', '', microtime(true));



    }
    function randFloat($min=0, $max=1){
        return $min + mt_rand()/mt_getrandmax() * ($max-$min);
    }
    public function tests(){
        $oss = new OssService('pgy-hongbao');
        $res = $oss->uploadOss('audio/'.time().'.wav','/zzhh/wechat-api/public/video/4.wav',true);

    }
    function replaceSpecialChar($strParam){
        $regex = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        return preg_replace($regex,"",$strParam);
    }

    public function excshell()
    {
        $filename = $_SERVER['DOCUMENT_ROOT'].'/video/4.wav';

        $outPath = '/home/srv/webroot/wechat-api-test/public/tmp/cdm.pcm';

        $shell = '/usr/local/bin/ffmpeg -y  -i '.$filename.' -acodec pcm_s16le -f s16le -ac 1 -ar 16000 '.$outPath.' 2>&1';

        $result = exec($shell, $output, $return_val);

        var_dump($output);

    }
    function strFilter($str){
        $str = str_replace(';', '', $str);
        $str = str_replace('；', '', $str);
        $str = str_replace(':', '', $str);
        $str = str_replace('：', '', $str);
        $str = str_replace('"', '', $str);
        $str = str_replace('“', '', $str);
        $str = str_replace('”', '', $str);
        $str = str_replace('，', '', $str);
        $str = str_replace('.', '', $str);
        $str = str_replace('。', '', $str);
        $str = str_replace('?', '', $str);
        $str = str_replace('？', '', $str);
        return trim($str);
    }

    public function collDis($num=10){

        if(empty($this->redis->get('collDis'))){
            $res = $this->redis->set('collDis',$this->ren().'_1');
        }else{
            $fp = $this->redis->get('collDis');
            $arr = explode('_',$fp);
            if($arr[1] >= $num){
                $res = $this->redis->set('collDis',$this->ren($arr[0]).'_1');
            }else{
                $val = intval($arr[1])+1;
                $res = $this->redis->set('collDis',$arr[0].'_'.$val);
            }
        }
        $fp = explode('_',$this->redis->get('collDis'));
        return $fp[0];
    }
    public function ren($lastid=0){
        $arr = [22=>22,23=>23,24=>24];
        sort($arr);
        $soval = reset($arr);#首
        $moval = end($arr);  #尾
        if(empty($lastid)){
            return  $moval;
        }
        if($lastid == $moval){
            return $soval;
        }else{
            $i = 0;
            foreach ($arr as $k=>$v){
                if($i == 1){
                    return $v;
                }
                if($v==$lastid){
                    $i = 1;
                }
            }
        }
    }

    public function index(){
        echo 1;
        return view('index',['a'=>1]);
    }
    public function index2(){
        return view('index2',['a'=>1]);
    }
    public function rediss(){
        try{
            $new = new RedisService();
            $a = $new->set('a','123');
            $a = $new->get('a');
            var_dump($a);
        }catch (Exception $exception){
var_dump($exception->getMessage());
        }

    }

    public function lll($uid){
        $new = new UserReceiveHot();
        $res = $new->getReceiveHot($uid);
        var_dump($res);
    }

}