<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/19
 * Time: 下午2:17
 */

namespace app\cron\command;


use app\payment\model\Bonus;
use app\user\model\UserReceiveHot;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\db\Query;

#热门语音推荐
class ReceiveHot extends Command
{
    protected function configure()
    {
        $this->setName('ReceiveHot')->setDescription('Here is the ReceiveHot ');
    }
    protected function execute(Input $input, Output $output)
    {
        $query = new Query();
        $query->execute('truncate table wx_user_receive_hot');
        $receiveHot = new UserReceiveHot();
        #所有热门limit5
        $bonus = new Bonus();
        $bonus_list = $bonus->getHot();
        $receiveHot->hotAllHot();
        foreach ($bonus_list as $k=>$v){
            $receiveHot = new UserReceiveHot();
            $receiveHot->hotAdd(['type'=>0,'content'=>$v['bonus_password']]);
        }
        #获取单个用户的limit
        /*$count = $query->table('wx_user')->count();
        $num = ceil($count/2);
        for ($i=1;$i<=$num;$i++){
            $userlist = $query->table('wx_user')->page($i,2)->select();
            foreach ($userlist as $ku=>$vu){
                #发过的limit5
                $uli = $query->table('wx_payment_bonus')
                    ->where(['uid'=>$vu['openid'],'class'=>0])
                    ->group('bonus_password')
                    ->order('count(bonus_password) desc')
                    ->field('bonus_password')
                    ->limit(5)
                    ->select();
                foreach ($uli as $kli=>$vli){
                    (new UserReceiveHot())->hotAdd(['uid'=>$vu['openid'],'type'=>1,'content'=>$vli['bonus_password']]);
                }
                #收到的limit5
                $sendli = $query->table('wx_payment_bonus_receive')->alias('br')
                    ->join('wx_payment_bonus b','b.id=br.bonus_id','inner')
                    ->where(['br.receive_uid'=>$vu['openid'],'b.class'=>0])
                    ->group('br.bonus_id')
                    ->order('count(br.bonus_id) desc')
                    ->field('b.bonus_password')
                    ->limit(5)
                    ->select();
                foreach ($sendli as $sendk=>$sendv){
                    (new UserReceiveHot())->hotAdd(['uid'=>$vu['openid'],'type'=>2,'content'=>$sendv['bonus_password']]);
                }
            }

        }*/


        $output->writeln("CronCommand:1");
        $output->writeln("CronCommand:2");
    }
}