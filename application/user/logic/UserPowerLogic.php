<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/13
 * Time: 上午11:48
 * @introduce
 */
namespace app\user\logic;

use app\common\logic\BaseLogic;
use app\system\model\SystemPower;
use app\user\model\UserPower;
use extend\helper\Utils;
use think\Exception;

class UserPowerLogic extends BaseLogic
{
    protected $power;
    protected $system;

    public function __construct()
    {
        $this->power = new UserPower();
        $this->system = new SystemPower();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-14
     *
     * @description 体力处理逻辑
     * @param $param
     * @return array|int
     */
    public function behaviorPower($param)
    {
        $max = 9999;
        $one = $this->system->getOne($param['title']);
        $user_power = new UserPower();
        $info = $user_power->getOne($param['openid']);
        //新注册用户
        if (!$info) {
            $num = $this->system->getOne('new');
            $user_power->addUserPower(['uid' => $param['openid'], 'power' => $num['num']]);
        }
        if (!empty($one)) {
            switch ($param['title']) {
                case 'everyday':
                    // 每日领取
                    if ($info['login_time'] == date('Y-m-d')) {
                        return 0; // 说明当日已领过
                    } elseif (($one['num']+$info['power'])>$max) {
                        $user_power->editUserPower(['power'=>$max,'login_time'=>date('Y-m-d')],$param['openid']); // 达到上限
                    } else {
                        $user_power->editUserPower(['power'=>['exp','power+'.$one['num']],'login_time'=>date('Y-m-d')],$param['openid']);
                    }
                    return $one['num'];
                    break;
                case 'new':
                    return 1;
                    break;
                case 'invite':
                    // 邀请赠送
                    if (($one['num']+$info['power'])>$max) {
                        $user_power->editUserPower(['power'=>$max,'login_time'=>date('Y-m-d')],$param['openid']); // 达到上限
                    } else {
                        $user_power->editUserPower(['power'=>['exp','power+'.$one['num']]],$param['openid']);
                    }
                    return 1;
                    break;
                case 'send':
                    // 大厅被领红包
                    if (($one['num']+$info['power'])>$max) {
                        $user_power->editUserPower(['power'=>$max,'login_time'=>date('Y-m-d')],$param['openid']); // 达到上限
                    } else {
                        $user_power->editUserPower(['power'=>['exp','power+'.$one['num']]],$param['openid']);
                    }
                    return 1;
                    break;
                case 'fetch':
                    // 大厅领红包消耗,不够直接不让点
                    $user_power->editUserPower(['power'=>['exp','power+'.$one['num']]],$param['openid']);
                    return 1;
                    break;
                case 'empty':
                    // 大厅领红包时已被抢光
                    $user_power->editUserPower(['power'=>['exp','power+'.$one['num']]],$param['openid']);
                    return 1;
                    break;
                default:
                    break;
            }
        }
        return 1;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-15
     *
     * @description 获取用户体力详情
     * @param array $param
     * @return array
     */
    public function getUserPower(array $param)
    {
        try {
            $data = $this->power->getOne($param['openid']);
            $data2 = $this->system->getOne('fetch');
            $info = ['have'=>$data['power'],'expend'=>$data2['num']];
            $result = $this->ajaxSuccess(202, ['list' => $info]);
        } catch (Exception $exception) {

            $result = $this->ajaxError(205);
        }
        return $result;
    }
}