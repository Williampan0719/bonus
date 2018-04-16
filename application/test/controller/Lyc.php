<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/18
 * Time: 10:47
 * @introduce
 */

namespace app\test\controller;

use app\cron\logic\UserLogic;
use app\message\logic\DingTalkLogic;
use app\message\logic\EmailLogic;
use app\payment\logic\OrderLogic;
use app\payment\logic\WalletLogic;
use app\payment\model\Bonus;
use app\payment\model\Order;
use app\payment\model\Wallet;
use extend\service\RedisService;
use think\Cache;
use think\Controller;

class Lyc extends Controller
{
    public function test()
    {
        $user=new UserLogic();
        return $user->automaticRefund();
    }

    public function ess()
    {
        $redisService = new RedisService();
        $emailLogic = new EmailLogic();
        $dingTalkLogic = new DingTalkLogic();
        while (true) {
            $messageJson = $redisService->lpop('send_message');
            if (!empty($messageJson)) {
                $send = json_decode($messageJson, true);
                if ($send['type'] == 'email') {
                    //邮件
                    $emailLogic->sendEmail($send['toemail'], $send['name'], $send['subject'], $send['content']);
                } elseif ($send['type'] == 'dingText') {
                    //钉钉text推送
                    $dingTalkLogic->sendJdMsg($send['url'],$send['content'],$send['atMobiles'],$send['isAtAll']);
                }
            }
        }
    }
}