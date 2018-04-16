<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/16
 * Time: 下午3:29
 */

namespace app\cron\logic;


use app\common\logic\BaseLogic;
use app\payment\model\BonusHall;
use extend\service\RedisService;

#定时刷新榜单
class UserListLogic extends BaseLogic
{
    protected $hall = null;
    protected $redis = null;
    function __construct()
    {
        $this->hall = new BonusHall();
        $this->redis = new RedisService();
    }

    #Local tyrants  土豪榜
    public function localtyrants(){
        $list = $this->hall->getLocalTyrants();
        $this->redis->set('localtyrants',json_encode($list));
        return 'done';
    }
    #bestluck 手气榜
    public function bestluck(){
        $list = $this->hall->getBestLuck();
        $this->redis->set('bestluck',json_encode($list));
        return 'done';
    }
}