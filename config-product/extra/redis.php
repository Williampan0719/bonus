<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/11
 * Time: 上午9:47
 */

return [
    'host'       => 'r-uf66c5b3e18fe724.redis.rds.aliyuncs.com', #连接地址
    #'host'       => '127.0.0.1', #连接地址
    'port'       => 6379,        #端口
    'password'   => 'Pgyxwd8888',
    'select'     => 3,           #正式数据库 1赶紧说啊  3赶快说  5 7
    'timeout'    => 0,
    'expire'     => 0,
    'persistent' => false,
    'prefix'     => '',
];
