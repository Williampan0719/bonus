<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/16
 * Time: 下午3:39
 */

namespace app\cron\command;


use app\cron\logic\UserListLogic;
use app\payment\model\BonusReceive;
use app\user\model\UserLog;
use extend\service\RedisService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
#榜单定时更新 10分钟
class Bangdan extends Command
{
    protected function configure()
    {
        $this->setName('Bangdan')->setDescription('Here is the Bangdan ');
    }
    protected function execute(Input $input, Output $output)
    {

        $redis = new RedisService();
        $bangdan = new UserListLogic();
        $receive = new BonusReceive();
        $userlog = new UserLog();
        $resultlocaltyrants = $bangdan->localtyrants();
        $resultbestluck = $bangdan->bestluck();
        #人数逐渐上升
        $getnum = $receive->getDayRenshu();
        /*$rdnum = $redis->get('bonus_day_get_num');
        if($getnum > $rdnum){
            $newnum = $getnum+0;
        }else{
            $newnum = $rdnum+0;
        }*/
        #浏览数逐渐上升
        $viewnum = $userlog->getDayViewNum();
        /*$rdnums = $redis->get('bonus_day_view_num');
        if($viewnum>$rdnums){
            $newview = $viewnum + 5;
        }else{
            $newview = $rdnums + 5;
        }*/
        $redis->set('bonus_day_get_num',$getnum);
        $redis->set('bonus_day_view_num',$viewnum);
        $output->writeln("CronCommand:$resultlocaltyrants");
        $output->writeln("CronCommand:$resultbestluck");
    }
}