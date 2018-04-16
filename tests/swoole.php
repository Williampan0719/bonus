<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/11
 * Time: 下午1:53
 */

class swoole
{
    private $pdo = null;
    function __construct()
    {
        $this->pdo = new PDO("mysql:host=rm-uf684r31g08zh100bo.mysql.rds.aliyuncs.com;dbname=wechat_test","wechattest","wechat123");
    }

    public function openAction()
    {
        $table = new swoole_table(1024);
        $table->column('fd', swoole_table::TYPE_INT);
        $table->create();

        $ws = new swoole_websocket_server("127.0.0.1", 9502);
        $ws->table = $table;;
        $ws->set([
            'worker_num' => 4,
        ]);
        //监听WebSocket连接打开事件
        $ws->on('open', function ($ws, $request) {
            $ws->table->set($request->fd, array('fd' => $request->fd));//获取客户端id插入table
            foreach ($ws->table as $u) {
                $ws->push($u['fd'], '欢迎来到红包大厅');//消息广播给所有客户端
            }
        });
        //监听WebSocket消息事件
        $ws->on('message', function ($ws, $frame) {
            //echo $frame->fd.":{$frame->data}";
            //var_dump($frame->data);
            foreach ($ws->table as $u) {
                $ws->push($u['fd'],$frame->data);//消息广播给所有客户端
            }
        });
        //监听WebSocket连接关闭事件
        $ws->on('close', function ($ws, $fd) {
            //断开逻辑
            echo "client-{$fd} is closed\n";
            $ws->table->del($fd);//从table中删除断开的id
        });
        $ws->start();
    }

}
(new swoole())->openAction();
