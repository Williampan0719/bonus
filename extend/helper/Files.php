<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/10
 * Time: 上午10:25
 */

namespace extend\helper;


class Files
{
    /** 按照毫秒创建不重复的文件名
     * auth smallzz
     */
    public static function createFileName(){
        return str_replace('.', '', microtime(true)).rand(0,9).rand(0,99999);
    }

    /** 打印log
     * auth smallzz
     * @param string $name
     * @return bool|int
     */
    public static function CreateLog(string $name,$con){
        return file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/tmp/'.$name,$con,FILE_APPEND);
    }

    /** xml转数组
     * auth smallzz
     * @param $xml
     * @return mixed
     */
    public static function xmlToArray($xml){
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }
}