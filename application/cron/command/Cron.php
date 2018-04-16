<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/11/15
 * Time: 上午10:40
 */

namespace app\cron\command;

use app\cron\logic\MessageLogic;
use app\cron\logic\UserLogic;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Cron extends Command
{
    protected function configure()
    {
        $this->setName('cron')->setDescription('Here is the cron ');
    }

    protected function execute(Input $input, Output $output)
    {
        $pornQuery = new UserLogic();
        $automaticRefund = $pornQuery->automaticRefund();
        $automaticGameRefund = $pornQuery->automaticGameRefund();
        $generalPresentation=$pornQuery->generalPresentation();
        $output->writeln("CronCommand:$automaticRefund");
        $output->writeln("CronCommand:$automaticGameRefund");
        $output->writeln("CronCommand:$generalPresentation");
        $pornQuery = new MessageLogic();
        $remindUser = $pornQuery->sendMessage();
        $output->writeln("CronCommand:$remindUser");
    }
}