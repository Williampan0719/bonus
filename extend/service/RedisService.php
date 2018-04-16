<?php
/**
 * Created by PhpStorm.
 * User: zzhh
 * Date: 2017/12/13
 * Time: 下午1:10
 */

namespace extend\service;


class RedisService
{
    protected $redis = null;
    protected $options = [];
    public function __construct($options = []){
        $this->options = config('redis');
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $func          = $this->options['persistent'] ? 'connect' : 'connect';
        $this->redis = new \Redis;
        $this->redis->$func($this->options['host'], $this->options['port'], $this->options['timeout']);
        $this->redis->auth($this->options['password']);
        $this->redis->select($this->options['select']);
    }
    /**在名称为key的list左边（头）添加一个值为value的 元素
     * @param $key
     * @param $value1
     * @param null $value2
     * @param null $valueN
     * @return int
     */
    public function lpush($key, $value1){
        return $this->redis->lPush($key, $value1);
    }

    /**在名称为key的list右边（尾）添加一个值为value的 元素
     * @param $key
     * @param $value1
     */
    public function rpush($key, $value1){
        return $this->redis->rPush($key, $value1);
    }

    /**输出名称为key的list左(头)起的第一个元素，删除该元素
     * @param $key
     * @return string
     */
    public function lpop($key){
        return $this->redis->lPop($key);
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-23
     *
     * @description 输出名称为key的list左(头)起的第一个元素，删除该元素
     * @param $key
     * @param int $time
     * @return array
     */
    public function blpop($key,$time=0)
    {
        return $this->redis->blPop($key, $time);
    }

    /**输出名称为key的list右（尾）起起的第一个元素，删除该元素
     * @param $key
     * @return string
     */
    public function rpop($key){
        return $this->redis->rPop($key);
    }
    /**返回名称为key的list中start至end之间的元素（end为 -1 ，返回所有）
     * @param $key
     * @param $start
     * @param $end
     * @return array
     */
    public function lrange($key, $start, $end){
        return $this->redis->lrange($key, $start, $end);
    }

    /** 设置过期时间
     * auth smallzz
     * @param $key
     * @param $time
     * @return bool
     */
    public function expire($key,$time){
        return $this->redis->expire($key,$time);
    }
    /**单个插入
     * auth smallzz----bilibilihome
     * @param $key  键
     * @param $vol  值
     * @return bool
     */
    public function set($key,$vol){
        return $this->redis->set($key,$vol);
    }

    /**获取单个
     * auth smallzz----bilibilihome
     * @param $key  健
     * @return bool|string
     */
    public function get($key){
        return $this->redis->get($key);
    }
    public function del($key){
        return $this->redis->del($key);
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-11
     *
     * @description 获取队列的长度
     * @param $key
     * @return int
     */
    public function llen($key)
    {
        return $this->redis->lLen($key);
    }
}