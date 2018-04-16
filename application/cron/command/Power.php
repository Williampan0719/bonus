<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/15
 * Time: 14:59
 * @introduce
 */
namespace app\cron;

use app\cron\logic\UserLogic;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Power extends Command
{
    protected function configure()
    {
        $this->setName('power')->setDescription('Here is the power ');
    }

    protected function execute(Input $input,Output $output)
    {
        $pornQuery = new UserLogic();
        $remindUser = $pornQuery->remindUser();
        $output->writeln("CronCommand:$remindUser");
    }
}