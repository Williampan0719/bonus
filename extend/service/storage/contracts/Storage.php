<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/12/6
 * Time: 下午5:11
 */

namespace extend\service\storage\contracts;

interface Storage
{

    /**
     * 上传图片
     * @param array $data
     * @param null $callback
     * @return mixed
     */
    public function upload(array $data = [], $callback = null);

    /**
     *  生成base64
     * @param array $data
     * @param null $callback
     * @return mixed
     */
    public function createBase64(array $data = [], $callback = null);



}