<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/29
 * Time: 下午7:12
 */

namespace extend\helper;


class Time
{
    /** 秒数转换
     * auth smallzz
     * @param $secs  miao
     * @return string
     */
    public static function secsToStr($secs)
    {
        $r = '';
        if ($secs >= 86400) {
            $days = floor($secs / 86400);
            $secs = $secs % 86400;
            $r = $days . '天';
        }
        if ($secs >= 3600) {
            $hours = floor($secs / 3600);
            $secs = $secs % 3600;
            $r .= $hours . '时';
        }
        if ($secs >= 60) {
            $minutes = floor($secs / 60);
            $secs = $secs % 60;
            $r .= $minutes . '分';
        }
        $r .= $secs . '秒';
        return $r;
    }
}