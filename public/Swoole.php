<?php

/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/11
 * Time: 上午11:43
 */
class Swoole
{
    protected $config = [];
    protected $redis = [];

    function __construct()
    {
        $this->config = [
            'host' => '127.0.0.1',
            'prot' => 9502,
        ];
        #$this->redis = new Redis();
        #$this->redis->connect('127.0.0.1', 6379);
    }
    public function longLink()
    {
        $table = new swoole_table(1024);
        $table->column('fd', swoole_table::TYPE_INT);
        $table->create();

        $ws = new swoole_websocket_server($this->config['host'], $this->config['prot']);
        $ws->set([
            'worker_num' => 4,
        ]);
        $ws->table = $table;
        //监听WebSocket连接打开事件
        $ws->on('open', function ($ws, $request) {
            $ws->table->set($request->fd, array('fd' => $request->fd));//获取客户端id插入table
        });
        //监听WebSocket消息事件
        $ws->on('message', function ($ws, $frame) {
            foreach ($ws->table as $u) {
                //消息广播给所有客户端
                if(!empty($u['fd'])){
                    $ws->push($u['fd'], $frame->data);
                }
            }
        });
        $ws->on('close', function ($ws, $fd) {
            //从table中删除断开的id
            $ws->table->del($fd);
        });
        $ws->start();
    }
}

(new Swoole())->longLink();
