<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/7
 * Time: 下午5:42
 */

namespace extend\service;

#字符串相似度对比
class CharDb
{
    private $str1;
    private $str2;
    private $c = [];
    /**返回串一和串二的最长公共子序列
     * auth smallzz
     * @param $str1
     * @param $str2
     * @param int $len1
     * @param int $len2
     * @return string
     */
    private function getLCS($str1, $str2, $len1 = 0, $len2 = 0) {
        $this->str1 = $str1;
        $this->str2 = $str2;
        if ($len1 == 0) $len1 = strlen($str1);
        if ($len2 == 0) $len2 = strlen($str2);
        $this->initC($len1, $len2);
        return $this->printLCS($this->c, $len1 - 1, $len2 - 1);
    }

    /**返回两个串的相似度
     * auth smallzz
     * @param $str1
     * @param $str2
     * @return float|int
     */
    public function getSimilar($str1, $str2) {
        $len1 = strlen($str1);
        $len2 = strlen($str2);
        $len = strlen($this->getLCS($str1, $str2, $len1, $len2));
        return sprintf('%.2f',($len * 2 / ($len1 + $len2))*100);
    }
    private function initC($len1, $len2) {
        for ($i = 0; $i < $len1; $i++) $this->c[$i][0] = 0;
        for ($j = 0; $j < $len2; $j++) $this->c[0][$j] = 0;
        for ($i = 1; $i < $len1; $i++) {
            for ($j = 1; $j < $len2; $j++) {
                if ($this->str1[$i] == $this->str2[$j]) {
                    $this->c[$i][$j] = $this->c[$i - 1][$j - 1] + 1;
                } elseif ($this->c[$i - 1][$j] >= $this->c[$i][$j - 1]) {
                    $this->c[$i][$j] = $this->c[$i - 1][$j];
                } else {
                    $this->c[$i][$j] = $this->c[$i][$j - 1];
                }
            }
        }
    }

    /**返回最长公共子序列
     * auth smallzz
     * @param $c
     * @param $i
     * @param $j
     * @return string
     */
    private function printLCS($c, $i, $j) {
        if ($i == 0 || $j == 0) {
            if ($this->str1[$i] == $this->str2[$j]) return $this->str2[$j];
            else return "";
        }
        if ($this->str1[$i] == $this->str2[$j]) {
            return $this->printLCS($this->c, $i - 1, $j - 1).$this->str2[$j];
        } elseif ($this->c[$i - 1][$j] >= $this->c[$i][$j - 1]) {
            return $this->printLCS($this->c, $i - 1, $j);
        } else {
            return $this->printLCS($this->c, $i, $j - 1);
        }
    }
}