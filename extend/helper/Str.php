<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/10
 * Time: 上午10:18
 */

namespace extend\helper;


class Str
{
    /** 特殊符号替换 ，按需添加可
     * auth smallzz
     * @param $str
     * @return mixed
     */
    public static function filter($str){
        return str_replace([';','；',':','：','"','“', '”','，','.','。','?','？',], '', $str);
    }

    /** 判断是否包含敏感词
     * auth smallzz
     * @param $str
     * @return bool   true包含  false不包含
     */
    public static function filters($str){
        foreach (config('Str') as $v){
            if(stripos($str,$v) !== false){
                return true;
            }
        }
        return false;
    }
}