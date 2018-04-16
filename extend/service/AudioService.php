<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/6
 * Time: 上午10:04
 */

namespace extend\service;
use extend\helper\Curl;
use extend\service\storage\OssService;
use extend\thirdpart\baiduAudio\AipSpeech;
use think\Exception;

class AudioService
{
    protected $redis = null;
    protected $apispeech = null;
    protected $config = [];
    function __construct()
    {
        $this->redis = new RedisService();
        $this->apispeech = new AipSpeech();
        $this->config = config('oss');
    }

    public function audioIdentify($filepath,$filename,$type=1){
        #先转码
        $newfilename = $_SERVER['DOCUMENT_ROOT'].'/video/'.$filename.'.pcm';
        $filepath = $this->mp3ToPcm($filepath,$newfilename);
        if($type == 1){  #本地识别
            $res = $this->apispeech->asr(file_get_contents($filepath), 'pcm', 16000, array(
                'lan' => 'zh',
            ));
            if(is_file($newfilename)){
                unlink($newfilename);
            }
            if(isset($res['result'])){
                return $res['result'][0];
            }else{
                return false;
            }

        }
        return false;
    }
    //TODO  php不执行ffmpeg

    /** mp3转pcm
     * auth smallzz
     * @param $filepath
     * @param $newpsth
     * @return bool
     */
    public function mp3ToPcm($filepath,$newpsth){
        #微信录音文件格式
        $shell = config('audio')['ffpeg_dir'].' -y  -i '.$filepath.' -acodec pcm_s16le -f s16le -ac 1 -ar 16000 '.$newpsth.' 2>&1';
        try {
            $result = exec($shell, $output, $return_val);
        }catch (Exception $exception){
            return false;
        }
        return $newpsth;
    }

    /** 语音上传Oss
     * auth smallzz
     * @return bool
     */
    public function audioOssUp($newupfile,$upfile){
        $oss = new OssService(config('oss')['default_bucket_name']);
        try{
            $info = $oss->uploadOss(config('oss')['oss_dir'].$newupfile,$upfile,true);
            #清理垃圾
            if(is_file($upfile)){
                unlink($upfile);
            }
        }catch (Exception $exception){
            return false;
        }
        return $info['info']['url'];

    }

    /**
     * @Author panhao
     * @DateTime 2018-02-04
     *
     * @description 上传图片
     * @param $newupfile
     * @param $upfile
     * @param string $path
     * @return bool
     */
    public function picOssUp($newupfile,$upfile,$path = 'template'){
        $oss = new OssService(config('oss')['default_bucket_name']);
        try{
            $info = $oss->uploadOss($path.'/'.$newupfile,$upfile,true);
            #清理垃圾
            if(is_file($upfile)){
                unlink($upfile);
            }
        }catch (Exception $exception){
            return false;
        }
        return $info['info']['url'];

    }

    /** 移动个文件到新的位置
     * auth smallzz
     * @param string $file
     * @param string $newfile
     * @return bool
     */
    public function moveFile(string $file,string $newfile){
        return move_uploaded_file($file,$newfile);

    }
    /** 小程序码oss上传
     * auth smallzz
     * @return bool
     */
    public function CodeOssUp($newupfile,$upfile){
        $oss = new OssService(config('oss')['default_bucket_name']);
        try{
            $info = $oss->uploadOss(config('oss')['oss_dir_code'].$newupfile,$upfile,true);
            #清理垃圾
            if(is_file($upfile)){
                unlink($upfile);
            }
        }catch (Exception $exception){
            return false;
        }
        return $info['info']['url'];

    }
}