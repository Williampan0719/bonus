<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/9
 * Time: 上午10:26
 */

namespace extend\service;


class ToPinyin
{
    protected static $dict = null;
    function __construct()
    {
        self::$dict = config('pinyin');
    }
    /**
     * 把字符串转为拼音结果
     * @param string $string
     * @return array
     */
    public static function convert($string,$split = '',&$allWord,&$firstWord)
    {
        return self::parseResult(self::getResult($string),$split,$allWord,$firstWord);
    }
    /**
     * 处理结果
     * @param array $string
     * @return array
     */
    public static function parseResult($result,$split = '',&$allWord,&$firstWord)
    {
        $allWord = $firstWord = array('');
        foreach($result as $pinyins)
        {
            $count = count($pinyins);
            $oldResultCount = count($allWord);
            $oldResult = $allWord;
            $oldResult2 = $firstWord;
            for($i=0;$i<$count - 1;++$i)
            {
                $allWord = array_merge($allWord,$oldResult);
                $firstWord = array_merge($firstWord,$oldResult2);
            }
            foreach($pinyins as $index => $pinyin)
            {
                for($i = 0; $i < $oldResultCount; ++$i)
                {
                    $j = $index * $oldResultCount + $i;
                    $allWord[$j] .= $pinyin . $split;
                    $firstWord[$j] .= mb_substr($pinyin,0,1) . $split;
                }
            }
        }
    }
    /**
     * 把字符串转为拼音数组结果
     * @param string $string
     * @return array
     */
    public static function getResult($string)
    {
        $len = mb_strlen($string,'UTF-8');
        $list = array();
        for($i = 0; $i < $len; ++$i)
        {
            $word = mb_substr($string,$i,1,'UTF-8');
            if(isset(self::$dict[$word]))
            {
                $list[] = self::$dict[$word];
            }
            else
            {
                $list[] = array($word);
            }
        }
        return $list;
    }
}