<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/13
 * Time: 上午11:15
 * @introduce
 */
namespace app\index\behavior;

use app\user\logic\UserPowerLogic;

class Power
{
    public function userPower(&$param)
    {
        $user_power = new UserPowerLogic();
        return $user_power->behaviorPower($param);
    }
}