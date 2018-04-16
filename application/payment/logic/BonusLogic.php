<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/6
 * Time: 08:49
 * @introduce
 */

namespace app\payment\logic;

use app\common\logic\BaseLogic;
use app\payment\model\Abonus;
use app\payment\model\AbonusSend;
use app\payment\model\Bonus;
use app\payment\model\BonusDetail;
use app\payment\model\BonusReceive;
use app\payment\model\BonusRemark;
use app\user\model\User;
use app\user\model\UserCodeimg;
use app\user\model\UserLevel;
use extend\service\bonusRules;
use extend\service\RedisService;
use think\Exception;

class BonusLogic extends BaseLogic
{
    protected $bonusModel;
    protected $redis = null;

    public function __construct()
    {
        $this->bonusModel = new Bonus();
        $this->redis = new RedisService();
    }

    /**
     * @Author liyongchuan(内部调用)
     * @DateTime 2018-01-06
     *
     * @description 红包的新增(在用)
     * @param array $params
     * @return bool|string
     */
    public function bonusAdd(array $params)
    {

        try {
            $bonusParams['bonus_num'] = $params['bonus_num'];
            $bonusParams['bonus_money'] = $params['bonus_money'];
            $bonusParams['bonus_password'] = $params['bonus_password'];
            $bonusParams['uid'] = $params['uid'];
            $bonusParams['form_id'] = $params['form_id'] ?? '';
            $bonusParams['type'] = $params['type'] ?? 0;
            $bonusParams['class'] = $params['class'] ?? 0;
            $bonusParams['voice_path'] = $params['voice_path'] ?? '';
            $bonusParams['voice_type'] = $params['voice_type'] ?? 0;
            $bonusParams['timelength'] = $params['timelength'] ?? 0;
            $bonusParams['adv_name'] = $params['adv_name'] ?? '';
            $bonusParams['adv_logo'] = $params['adv_logo'] ?? '';
            $bonusParams['service_money'] = $params['bonus_money'] * ($bonusParams['bonus_num'] == 1 ? 0.02 : 0.06);
            $id = $this->bonusModel->bonusAdd($bonusParams);
            if ($id > 0) {
                if ($bonusParams['type'] == 1) {      #新增type类型 0内部红包 1大厅红包
                    $bonushall = new BonusHallLogic();
                    $params['bonus_id'] = $id;
                    $bonushall->addHall($params);
                }
                $result = $id;
            } else {
                $result = false;
            }
        } catch (Exception $exception) {
            $result = false;
        }
        return $result;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-08
     *
     * @description 红包分配详情(在用)
     * @param int $bonusId
     * @return bool
     */
    public function bonusDistribution(int $bonusId, $service_money)
    {
        try {
            $bonusDetail = new BonusDetail();
            $bonusInfo = $this->bonusModel->bonusDerail($bonusId);
            if ($bonusInfo['bonus_num'] == 1) {
                $id = $bonusDetail->detailAdd([
                    'bonus_id' => $bonusId,
                    'receive_money' => $bonusInfo['bonus_money'],
                    'receive_service_money' => $service_money,
                    'payable_money' => $bonusInfo['bonus_money'],
                    'is_optimum' => 1,
                ]);
            } else {
                $bonusRules = new bonusRules($bonusInfo['bonus_money'], $bonusInfo['bonus_num']);
                $moneyArray = $bonusRules->distribution();
                $data = [];
                $maxMoney = max($moneyArray);
                foreach ($moneyArray as $key => $vo) {
                    if ($maxMoney == $vo) {
                        $data[$key]['is_optimum'] = 1;
                    } else {
                        $data[$key]['is_optimum'] = 0;
                    }
                    $data[$key]['payable_money'] = $vo;
                    $data[$key]['bonus_id'] = $bonusId;
                    $data[$key]['receive_money'] = $vo - floor($vo * config('RECEIVE_RATE') * 100) / 100;
                    $data[$key]['receive_service_money'] = round($vo * config('RECEIVE_RATE'), 4);
                }
                //$arr=$bonusDetail->detailAddAll($data);

                //$id=count($arr);
                $id = $bonusDetail->detailAdd1($data);
            }
            if ($id > 0) {
//                #查询新增的数据
//                $returnlist = $bonusDetail->getAddData($bonusId);
//                #$this->redis->set('bonusDone_'.$bonusId,1);
//                foreach ($returnlist as $k=>$v){
//                    if(!empty($v['receive_money'])){
//                        $this->redis->lpush('bonus_'.$bonusId,$v['id'].'-'.$v['receive_money']);
//                    }
//                }
//                $this->redis->expire('bonus_'.$bonusId,86400); #设置红包的生命周期
                $result = true;
            } else {
                $result = false;
            }
        } catch (Exception $exception) {
            $result = false;
        }
        return $result;
    }

    /**
     * @Author liyongchuan panhao
     * @DateTime 2018-01-07 2018-01-30
     *
     * @description 红包记录(在用)
     * @param array $params
     * @return array
     */
    public function bonusRecord(array $params)
    {
        try {
            $userModel = new User();
            $abonusSendModel = new AbonusSend();
            $userInfo = $userModel->userDetail($params['uid']);//用户详情
            //发出的红包
            $bonusList = $this->bonusModel->bonusFindByUid($params['uid']);
            foreach ($bonusList as $k => $v) {
                $bonusList[$k]['name'] = ($v['class'] == 1 ? '语音红包' : (($v['class'] == 2) ? '广告红包' : '口令红包'));
                $bonusList[$k]['status'] = (!empty($v['finish_at']) ? 0 : 1);
            }
            $abonusList = $abonusSendModel->getList(['uid'=>$params['uid']],'money,created_at,abonus_id');
            foreach ($abonusList as $k => $v) {
                $abonusList[$k]['class'] = 5;
                $abonusList[$k]['name'] = '讨红包';
                $abonusList[$k]['id'] = $v['abonus_id'];
            }
            $bonusDetailModel = new BonusDetail();
            //总支出
            $money = array_sum(array_column($bonusList,'bonus_money'))+array_sum(array_column($abonusList,'money'));
            $send = array_merge($bonusList,$abonusList);
            $time = array_column($send,'created_at');
            //根据时间倒叙组装
            array_multisort($time,SORT_DESC,$send);

            $bonusIssue = [
                'bonus_money' => sprintf("%01.2f",$money),
                'bonus_number' => count($send),
                'bonus_list' => $send,
            ];

            //收到的红包
            $bonusReceiveModel = new BonusReceive();
            $receiveModel = $bonusReceiveModel->bonusReceive($params['uid']);
            $rmoney = 0;
            foreach ($receiveModel as $key => $vo) {
                $detail = $bonusDetailModel->bonusReceiveDetail($vo['detail_id']);
                $class = $this->bonusModel->bonusDerail($vo['bonus_id']);
                $rmoney += $detail['receive_money'];
                $receiveModel[$key]['bonus_money'] = $detail['receive_money'];
                $receiveModel[$key]['class'] = $class['class'];
                $receiveModel[$key]['name'] = ($vo['class'] == 1 ? '语音红包' : (($vo['class'] == 2) ? '广告红包' : '口令红包'));
                $receiveModel[$key]['id'] = $vo['bonus_id'];
            }
            $abonusModel = new Abonus();
            $rabonusList = $abonusModel->getList(['uid'=>$params['uid']],'receive_money,created_at,id');
            foreach ($rabonusList as $k => $v) {
                $rabonusList[$k]['money'] = sprintf("%01.2f",$v['receive_money']);
                $rabonusList[$k]['class'] = 5;
                $rabonusList[$k]['name'] = '讨红包';
            }
            $rmoney = $rmoney + array_sum(array_column($rabonusList,'receive_money'));
            $receive = array_merge($receiveModel,$rabonusList);
            $bonusReceive = [
                'bonus_money' => sprintf("%01.2f",$rmoney),
                'bonus_number' => count($receive),
                'bonus_list' => $receive,
            ];

            $result = $this->ajaxSuccess(102, ['userInfo' => $userInfo, 'bonusIssue' => $bonusIssue, 'bonusReceive' => $bonusReceive]);
        } catch (Exception $exception) {
            $result = $this->ajaxError(105);
        }
        return $result;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-08
     *
     * @description 红包首页
     * @param array $params
     * @return array
     */
    public function bonusIndex(array $params)
    {
        try {
            $bonusInfo = $this->bonusModel->bonusDerail($params['bonus_id']);
            //广告红包
            if ($bonusInfo && $bonusInfo['class'] == 2) {
                $remark = new BonusRemark();
                $remark->addViewNum(['bonus_id'=>$params['bonus_id']]);
            }
            $userModel = new User();
            $userLevelModel = new UserLevel();
            $userInfo = $userModel->userDetail($bonusInfo['uid']);
            $userInfo['bonus_password'] = $bonusInfo['bonus_password'];
            $userInfo['bonus_money'] = $bonusInfo['bonus_money'];
            $userInfo['bonus_num'] = $bonusInfo['bonus_num'];
            $userInfo['bonus_created_at'] = $bonusInfo['created_at'];
            $userInfo['bonus_uid'] = $bonusInfo['uid'];
            $userInfo['type'] = $bonusInfo['type'];
            $time = time() - config('payment.wx_notify')['bonus_effective_time'];
            if (strtotime($bonusInfo['created_at']) < $time) {
                $userInfo['is_overdue'] = 1;
            } else {
                $userInfo['is_overdue'] = 0;
            }
            if ($bonusInfo['finish_at'] == null) {
                $is_finish = 0;
            } else {
                $is_finish = 1;
            }
            $bonusReceiveModel = new BonusReceive();
            $bonusDetailModel = new BonusDetail();
            $receiveInfo = $bonusReceiveModel->bonusReceiveByBonusID($params['bonus_id']);
            foreach ($receiveInfo as $key => $vo) {
                $receiveUser = $userModel->userDetail($vo['receive_uid']);
                $userLevel = $userLevelModel->getExist($vo['receive_uid']);
                if ($userLevel) {
                    $receiveInfo[$key]['level'] = $userLevel->level;
                } else {
                    $receiveInfo[$key]['level'] = '暂无等级';
                }
                $receiveBonus = $bonusDetailModel->bonusReceiveDetail($vo['detail_id']);
                $receiveInfo[$key]['nickname'] = $receiveUser['nickname'];
                $receiveInfo[$key]['avatarulr'] = $receiveUser['avatarulr'];
                $receiveInfo[$key]['gender'] = $receiveUser['gender'];
                $receiveInfo[$key]['receive_money'] = $receiveBonus['receive_money'];
                if ($is_finish == 1) {
                    $receiveInfo[$key]['is_optimum'] = $receiveBonus->is_optimum;
                } else {
                    $receiveInfo[$key]['is_optimum'] = 0;
                }
            }
            $userInfo->receive_bonus_num = count($receiveInfo);
            $userInfo['voice_path'] = $bonusInfo['voice_path'];
            $userInfo['class'] = $bonusInfo['class'];
            $userInfo['timelength'] = $bonusInfo['timelength'];
            $result = $this->ajaxSuccess(102, ['userInfo' => $userInfo, 'bonusList' => $receiveInfo]);
        } catch (Exception $exception) {
            $result = $this->ajaxError(105);
        }
        return $result;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-10
     *
     * @description 红包分享页面接口
     * @param array $params
     * @return array
     */
    public function bonusShare(array $params)
    {
        try {
            $usercodeimg = new UserCodeimg();
            $bonusInfo = $this->bonusModel->bonusDerail($params['bonus_id']);
            $userModel = new User();
            $userInfo = $userModel->userDetail($bonusInfo['uid']);
            $bonus_fx_img = $usercodeimg->isCheckCode($params['bonus_id'], 1);
            $bonus = [
                'bonus_password' => $bonusInfo['bonus_password'],
                'avatarulr' => $userInfo->avatarulr,
                'nickname' => $userInfo->nickname,
                'bonus_img' => $usercodeimg->getBonusCode($params['bonus_id'], 2),
                'bonus_fx_img' => $bonus_fx_img ?? 0,
                'voice_path' => $bonusInfo['voice_path'] ?? '',
                'class' => $bonusInfo['class'],
                'timelength' => $bonusInfo['timelength'],
            ];
            $result = $this->ajaxSuccess(102, ['bonus' => $bonus]);
        } catch (Exception $exception) {
            $result = $this->ajaxError(105);
        }
        return $result;
    }
}