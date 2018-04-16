<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/22
 * Time: 17:04
 * @introduce
 */
namespace app\cron\logic;

use app\common\logic\BaseLogic;
use app\message\logic\DingTalkLogic;
use app\message\logic\EmailLogic;
use extend\service\RedisService;

class MessageLogic extends BaseLogic
{
    public function sendMessage()
    {
        $redisService=new RedisService();
        $emailLogic=new EmailLogic();
        $dingTalkLogic=new DingTalkLogic();
        for($i=0;$i<3;$i++) {
            $messageJson = $redisService->lpop('send_message');
            if (!empty($messageJson)) {
                $send = json_decode($messageJson, true);
                if ($send['type'] == 'email') {
                    //邮件
                    $emailLogic->sendEmail($send['toemail'], $send['name'], $send['subject'], $send['content']);
                } elseif ($send['type'] == 'dingText') {
                    //钉钉text推送
                    $dingTalkLogic->sendJdMsg($send['url'], $send['content'], $send['atMobiles'], $send['isAtAll']);
                }
            }else{
                break;
            }
        }
        return true;
    }
}