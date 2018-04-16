<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/15
 * Time: 上午9:30
 * @introduce
 */
namespace app\user\logic;

use app\common\logic\BaseLogic;
use app\user\model\UserLevel;
use extend\service\RedisService;
use extend\service\WechatService;

class UserLevelLogic extends BaseLogic
{
    protected $level;

    public function __construct()
    {
        $this->level = new UserLevel();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-15
     *
     * @description 用户等级变化推送
     * @param string $openid
     * @return int
     */
    public function getLevelPush(string $openid)
    {
        $one = $this->level->getExist($openid);
        if (empty($one)) {
            $this->level->initUserLevel($openid);
        } else {
            $new_level = config('level');
            $count = $one['number']+1;
            $temp_level = '暂无等级';
            foreach ($new_level as $k => $v) {
                if ($k<=$count) {
                    $temp_level = $v;
                } else {
                    break;
                }
            }
            $this->level->editUserLevel(['number'=>['exp','number+1'],'level'=>$temp_level,'uid'=>$openid]);
            if ($one['level'] != $temp_level) {
                $redisService = new RedisService();
                $wx = new WechatService();
                $tpl = [
                    'type' => 'gradeChange',
                    //'page' => 'pages/hall/hall',
                    'form_id' => $redisService->lpop($openid),
                    'openid' => $openid,
                    'key1' => $temp_level, // 现等级
                    'key3' => $one['number'] + 1,
                    'key2' => $one['level'], // 原等级
                ];
                $wx->tplSend($tpl);
            }
        }
    }
}