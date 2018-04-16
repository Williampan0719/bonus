<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/22
 * Time: 09:20
 * @introduce
 */
namespace app\message\logic;

use app\common\logic\BaseLogic;
use extend\service\DingTalkService;

class DingTalkLogic extends BaseLogic
{
    #发送确认信息
    public function sendJdMsg($url, $content = 'testasssssss',$atMobiles=[],$isAtAll=false)
    {
        #组装发送消息
        $data = [
            'msgType' => 'text',
            'content' => $content,
            'atMobiles' => $atMobiles,
            'isAtAll' => $isAtAll
        ];
        $new = new DingTalkService($url, $data);
        $res = json_decode($new->sendMsg(), true);
        if ($res['errcode'] == 0 && $res['errmsg'] == 'ok') {
            return true;
        }
        return false;
    }
}