<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/6
 * Time: 下午12:52
 */

namespace extend\thirdpart\baiduAudio\lib;


class AipImageUtil
{
    /**
     * 获取图片信息
     * @param  $content string
     * @return array
     */
    public static function getImageInfo($content){
        $info = getimagesizefromstring($content);

        return array(
            'mime' => $info['mime'],
            'width' => $info[0],
            'height' => $info[1],
        );
    }
}