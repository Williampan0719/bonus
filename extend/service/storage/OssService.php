<?php
/**
 * oss 图片上传
 * User: dongmingcui
 * Date: 2017/11/9
 * Time: 下午2:56
 */

namespace extend\service\storage;

use extend\exception\AliyunOssException;
use extend\helper\Utils;
use OSS\Core\OssException;
use OSS\OssClient;
use think\Exception;

class OssService
{
    protected $ossClient;
    private $bucket;
    private $config;

    /**
     * OssService constructor.
     */
    public function __construct(string $bucket = '')
    {
        $this->bucket = trim($bucket);
        $this->config = config('oss');

        try {
            $this->ossClient = new OssClient(
                config('oss.access_key'),
                config('oss.access_secret_key'),
                config('oss.outer_endpoint')
            );
        } catch (OssException $e) {
            throw new OssException($e->getMessage());
        }
    }

    /**
     * 创建一存储空间
     * acl 指的是bucket的访问控制权限，有三种，私有读写，公共读私有写，公共读写。
     * 私有读写就是只有bucket的拥有者或授权用户才有权限操作
     * 三种权限分别对应OSSClient::OSS_ACL_TYPE_PRIVATE，
     *               OssClient::OSS_ACL_TYPE_PUBLIC_READ,
     *               OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE
     * @param string $bucket 要创建的bucket名字
     * @return void
     * @throws AliyunOssException
     */
    public function createBucket(string $bucket)
    {
        try {
            $this->ossClient->createBucket($bucket, OssClient::OSS_ACL_TYPE_PUBLIC_READ);
        } catch (OssException $e) {
            throw new OssException($e->getMessage());
        }
    }

    /**
     * 获取bucket
     * @return string
     * @throws AliyunOssException
     */
    private function getBucket(): string
    {
        if (!$this->bucket) {
            throw new OssException('bucket 不能为空');
        }
        return $this->bucket;
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description oss上传
     * @param string $object oss路径
     * @param string $content 本地路径
     * @param bool $isfile 是否是文件上传
     * @throws AliyunOssException
     */
    public function uploadOss(string $object, string $content, bool $isfile = false)
    {
        # 不能以“/”或者“\”字符开头
        $object = trim($object, '\\/ ');
        try {
            if ($isfile) {
                if (!is_file($content) or !is_readable($content)) throw new Exception("上传文件'$content'不存在或者不可读");
                return $this->ossClient->uploadFile($this->getBucket(), $object, $content);
            } else {
                return $this->ossClient->putObject($this->getBucket(), $object, $content);
            }
            // $this->doesObject($object,$content);
        } catch (OssException $e) {
            throw new OssException($e->getMessage());
        }

    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 判断是否上传oss成功,成功则删除零时图片
     * @param string $oss_file_path oss路径
     * @param string $temp_file_path 临时路径
     */
    public function doesObject(string $oss_file_path, string $temp_file_path)
    {
        $res = $this->ossClient->doesObjectExist($this->getBucket(), $oss_file_path);
        if ($res) {
            #若OSS上传成功,则删除临时图片
            if (file_exists($temp_file_path)) {
                unlink($temp_file_path);
            }
        }
    }

    /**
     * @Author liyongchuan
     * @DateTime
     *
     * @description 上传本地
     * @param string $tempPath 本地临时路径
     * @param string $base64img 图片base64字符串
     * @return bool|null
     */
    public function ossBase64Upload(string $base64img = null, string $tempPath = null)
    {
        if ($tempPath == null) {
            $tempPath = config('oss.temp_file_path');//默认临时文件路径
        }
        preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64img, $result);
        $images = base64_decode(str_replace($result[0], '', $base64img));
        $time = Utils::getMicroTime();
        $file = $time . config('oss.temp_file_suffix');//临时文件名
//        if (!is_dir($_SERVER['DOCUMENT_ROOT'].'/'.$tempPath)) {
//            //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
//            mkdir(iconv("UTF-8", "GBK", $tempPath), 0777, true);
//        }
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $tempPath . '/' . $file;
        try {
            file_put_contents($imagePath, $images);
        } catch (Exception $exception) {
            return false;
        }

        return $imagePath;
    }


}