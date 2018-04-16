<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/25
 * Time: 下午2:18
 * @introduce
 */
namespace app\payment\logic;

use app\common\logic\BaseLogic;
use app\payment\model\Abonus;
use app\payment\model\AbonusSend;
use app\payment\model\BillLog;
use app\payment\model\Wallet;
use app\system\model\SystemAbonusTemplate;
use app\user\model\User;
use app\user\model\UserCodeimg;
use extend\helper\Files;
use extend\service\payment\WeChatPayService;
use extend\service\RedisService;
use extend\service\WechatService;
use think\Db;
use think\Exception;
use think\Loader;

Loader::import('thirdpart.wxpay.WxPayPubHelper.WxPayPubHelper');
Loader::import('thirdpart.wxpay.lib.WxPay');

class AbonusLogic extends BaseLogic
{
    protected $abonus;
    protected $abonusSend;
    protected $weService;

    public function __construct()
    {
        $this->abonus = new Abonus();
        $this->abonusSend = new AbonusSend();
        $this->weService = new WechatService();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-29
     *
     * @description 模板列表
     * @return array
     */
    public function getTemplateList()
    {
        try {
            $template = new SystemAbonusTemplate();
            $where = ['scenes' => 'ask'];
            $list = $template->getTemplateList($where);
            if (!empty($list)) {
                $result = $this->ajaxSuccess(102,['list'=>$list]);
            } else {
                $result = $this->ajaxError(105);
            }

        } catch (Exception $exception) {
            $result = $this->ajaxError(105);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-25
     *
     * @description 生成讨红包
     * @param array $param
     * @return bool
     */
    public function saveAskingBonus(array $param, $file)
    {
        try {
            $where = [
                'uid' => $param['openid'],
                'template_class' => $param['template_class'],
                'remark_type' => $param['remark_type'],
                'remark_word' => $param['remark_word'] ?? '',
                'timelength'  => $param['timelength'] ?? 0,
                'receive_money'=> 0,
                'status' => 0,
            ];
            //语音红包录音上传
            if (!empty($file)) {
                $payLogic = new PayLogic();
                $mp3 = $payLogic->uploadMp3($file);
                if (!empty($mp3)) {
                    $where['remark_voice'] = $mp3;
                }else {
                    return $this->ajaxError(106);
                }
            }
            $id = $this->abonus->saveAskingBonus($where);
            if (!empty($id)) {
                $result = $this->ajaxSuccess(100,['list'=>$id]);
            } else {
                $result = $this->ajaxError(106);
            }

        } catch (Exception $exception) {
            $result = $this->ajaxError(106);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-25
     *
     * @description 生成分享
     * @param array $param
     * @return array
     */
    public function abonusShare(array $param)
    {
        try {
            $info = $this->abonus->getDetail($param['id']);
            $data = [];
            if (!empty($info)) {
                $usercodeimg = new UserCodeimg();
                $bonus_img = $usercodeimg->getBonusCode($info['id'], 2,2);
                $data['bonus_img'] = $bonus_img ?? 0;
                $template = new SystemAbonusTemplate();
                $temp = $template->getOne(['class'=>$info['template_class'],'scenes'=>'share']);
                $data['url'] = $temp['url'] ?? '';
                $user = new User();
                $nick = $user->userDetail($info['uid']);
                $data['nick_name'] = $nick['nickname'];
                $data['avatarulr'] = $nick['avatarulr'];
            }
            if (!empty($data)) {
                $result = $this->ajaxSuccess(102,['list'=>$data]);
            } else {
                $result = $this->ajaxError(105);
            }

        } catch (Exception $exception) {
            $result = $this->ajaxError(105);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-25
     *
     * @description 赏红包进入首页
     * @param array $param
     * @return array
     */
    public function abonusIndex(array $param)
    {
        try {
            //生成浏览记录
            $one = $this->abonusSend->getOne(['uid'=>$param['openid'],'abonus_id'=>$param['id']]);
            if (empty($one)) {
                $bonus_uid = $this->abonus->getDetail($param['id']);
                $where = ['uid'=>$param['openid'],'abonus_user'=>$bonus_uid['uid'],'abonus_id'=>$param['id']];
                $view = $this->abonusSend->initOne($where);
                if (empty($view)) {
                    return $result = $this->ajaxError(105);
                }
            }
            $info = $this->abonus->getDetail($param['id']);
            $template = new SystemAbonusTemplate();
            $temp = $template->getOne(['class'=>$info['template_class'],'scenes'=>'send']);
            $data = [];
            if (!empty($info)) {
                $user = new User();
                $nick = $user->userDetail($info['uid']);
                $data['uid'] = $info['uid'];
                $data['nick_name'] = $nick['nickname'];
                $data['avatarulr'] = $nick['avatarulr'];
                $data['url'] = $temp['url'] ?? '';
                $data['remark_type'] = $info['remark_type'];
                $data['remark_word'] = $info['remark_word'];
                $data['remark_voice'] = $info['remark_voice'];
                $data['timelength'] = $info['timelength'];
            }
            $list = config('abonus.send_bonus');
            if (!empty($data)) {
                $result = $this->ajaxSuccess(102,['money'=>$list,'list'=>$data]);
            } else {
                $result = $this->ajaxError(105);
            }

        } catch (Exception $exception) {
            $result = $this->ajaxError(105);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-26
     *
     * @description 支付统一调用接口
     * @param array $param
     * @return array
     */
    public function pay(array $param)
    {
        $wallet = new Wallet();
        $walletInfo = $wallet->walletDetail($param['openid']);
        // 余额支付
        if ($param['money'] <= $walletInfo['balance']) {
            $result = $this->_balancePay($param);
        } else { // 混合支付
            $result = $this->_mixedPay($param);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-03
     *
     * @description 打赏完读模板
     * @param array $param
     * @return array
     */
    public function templateAfterSend(array $param)
    {
        $temp = $this->abonus->getDetail($param['abonus_id']);
        $user = new User();
        $nickname = $user->userDetail($temp['uid']);
        $temp['nickname'] = $nickname['nickname'];
        $temp['avatarulr'] = $nickname['avatarulr'];
        $template = new SystemAbonusTemplate();
        $where = ['class' => $temp['template_class'],'scenes'=>'send'];
        $url = $template->getOne($where);
        $temp['url'] = $url['url'];
        if (!empty($temp)) {
            return $this->ajaxSuccess(102,['list'=>$temp]);
        } else {
            return $this->ajaxError(105);
        }
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-03
     *
     * @description 赏完红包添加备注
     * @param array $param
     * @param $file
     * @return array
     */
    public function sendAbonusRemark(array $param, $file)
    {
        try {
            //语音红包录音上传
            if (!empty($file)) {
                $payLogic = new PayLogic();
                $mp3 = $payLogic->uploadMp3($file);
                if (!empty($mp3)) {
                    $param['remark_voice'] = $mp3;
                }else {
                    return $this->ajaxError(107);
                }
            }
            $id = $this->abonusSend->editBonus($param,$param['id']);
            if (!empty($id)) {
                $result = $this->ajaxSuccess(101);
            } else {
                $result = $this->ajaxError(107);
            }

        } catch (Exception $exception) {
            $result = $this->ajaxError(107);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-26
     *
     * @description 讨红包结果展示逻辑
     * @param array $param
     * @return array
     */
    public function abonusShow(array $param)
    {
        try {
            $info = $this->abonus->getDetail($param['id']);
            $data = [];
            if (!empty($info)) {
                $user = new User();
                $nick = $user->userDetail($info['uid']);
                $money = $this->abonusSend->getSum(['uid'=>$param['openid'],'abonus_id'=>$param['id'],'is_pay'=>1]);
                $data['money'] = sprintf("%01.2f",$money);
                $data['nick_name'] = $nick['nickname'];
                $data['avatarulr'] = $nick['avatarulr'];
                $data['remark_type'] = $info['remark_type'];
                $data['remark_word'] = $info['remark_word'];
                $data['remark_voice'] = $info['remark_voice'];
                $data['timelength'] = $info['timelength'];
                $data['gender'] = $nick['gender'];
                $data['self'] = 0;
                $data['uid'] = $info['uid'];
                if ($info['uid'] == $param['openid']) {
                    $data['self'] = 1;
                    $data['money'] = sprintf("%01.2f",$info['receive_money']);
                }

                //已支援
                $list = $this->abonusSend->getList(['abonus_id'=>$param['id'],'is_pay'=>1],'uid,money,timelength,remark_voice,remark_word,remark_type,created_at');
                //正在包
                $reading = $this->abonusSend->getList(['abonus_id'=>$param['id'],'is_pay'=>0,'is_send'=>0,'uid'=>['neq',$info['uid']]],'uid');

                //获取头像昵称
                $ids = array_merge(array_column($list,'uid'),array_column($reading,'uid'));
                $nicknames = $user->getNameList($ids);
                $names = [];
                foreach ($nicknames as $key => $value) {
                    $names[$value['openid']]['nickname'] = $value['nickname'];
                    $names[$value['openid']]['avatarulr'] = $value['avatarulr'];
                    $names[$value['openid']]['gender'] = $value['gender'];
                }
                if (!empty($list)) {
                    $max = max(array_column($list,'money'));
                    foreach ($list as $k => $v) {
                        $list[$k]['nickname'] = $names[$v['uid']]['nickname'] ?? '';
                        $list[$k]['avatarulr'] = $names[$v['uid']]['avatarulr'] ?? '';
                        $list[$k]['gender'] = $names[$v['uid']]['gender'] ?? 0;
                        $list[$k]['is_max'] = 0;
                        if ($v['money'] == $max) {
                            $list[$k]['is_max'] = 1;
                            $max_arr[] = $list[$k];
                            unset($list[$k]);
                        }
                    }
                    //置顶插入
                    if (!empty($max_arr)) {
                        $list = array_merge($max_arr,array_values($list));
                    }
                }
                foreach ($reading as $k => $v) {
                    $reading[$k]['avatarulr'] = $names[$v['uid']]['avatarulr'] ?? '';
                }
                $data['list'] = $list;
                $data['reading'] = $reading;
            }
            if (!empty($data)) {
                $result = $this->ajaxSuccess(102,['list'=>$data]);
            } else {
                $result = $this->ajaxError(105);
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(105);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-26
     *
     * @description 余额支付
     * @param array $params
     * @return array
     */
    private function _balancePay(array $params)
    {
        try {
            Db::startTrans();
            $order = new OrderLogic();
            $walletModel = new Wallet();
            $billLogModel = new BillLog();
            //修改初始红包或继续发红包
            $exist = $this->abonusSend->getOne(['uid'=>$params['openid'],'abonus_id'=>$params['id'],'is_pay'=>0]);
            if (!empty($exist)) {
                $where = ['money'=>$params['money'],'is_pay'=>1,'is_send'=>1];
                $one = $this->abonusSend->editPay($where,$exist['id']);
            } else {
                $bonus_uid = $this->abonus->getDetail($params['id']);
                $where = ['uid'=>$params['openid'],'abonus_id'=>$params['id'],'abonus_user'=>$bonus_uid['uid'],'money'=>$params['money'],'is_pay'=>1,'is_send'=>1];
                $one = $this->abonusSend->initOne($where);
                $exist['id'] = $one;
            }
            //讨红包表增加金额
            $service = $params['money']*config('RECEIVE_RATE');
            $money = $params['money'] - $service;
            $receive_money = $this->abonus->editAbonus(['receive_money'=>['exp','receive_money+'.$money],'service_money'=>['exp','service_money+'.$service],'num'=>['exp','num+1']],$params['id']);
            if ($one && $receive_money) {
                //生成订单
                $orderParams['bonus_id'] = $exist['id'];
                $orderParams['uid'] = $params['openid'];
                $orderParams['money'] = $params['money'];
                $orderParams['finish_at'] = date('Y-m-d H:i:s');
                $orderParams['wx_money'] = 0;
                $orderParams['type'] = 2;
                $orders = $order->orderAdd($orderParams);
                if ($orders == false) {
                    Db::rollback();
                    return $this->ajaxError(106, [], '生成失败');
                }

                //钱包余额变化
                $walletInfo = $walletModel->walletDetail($params['openid']);
                $walletModel->walletEdit([
                    'uid' => $params['openid'],
                    'balance' => $walletInfo['balance'] - $params['money'],
                ]);
                $abonusInfo = $this->abonus->getDetail($params['id']);
                $walletInfo2 = $walletModel->walletDetail($abonusInfo['uid']);
                $walletModel->walletEdit([
                    'uid' => $abonusInfo['uid'],
                    'balance' => $walletInfo2['balance'] + $money,
                ]);
                //赏红包
                $bill = [
                    [// 赏
                        'uid' => $params['openid'],
                        'type' => 6,
                        'affect_money' => '-' . $params['money'],
                        'balance_money' => $walletInfo['balance'] - $params['money'],
                        'money_source' => 2,
                        'from_uid'=>$abonusInfo['uid'],
                    ],
                    [ //收
                        'uid' => $abonusInfo['uid'],
                        'type' => 7,
                        'affect_money' => $money,
                        'balance_money' => $walletInfo2['balance'] + $money,
                        'money_source' => 3,
                        'from_uid'=>$params['openid'],
                    ]
                ];
                $billLogModel->billLogAddAll($bill);
                //收益模板推送
                $user = new User();
                $redis = new RedisService();
                $nick = $user->userDetail($abonusInfo['uid']);
                $tpl = [
                    'type' => 'rewardMoney',
                    'page' => 'pages/beg_details/beg_details?beg_id='.$params['id'],
                    'form_id' => $redis->lpop($abonusInfo['uid']),
                    'openid' => $abonusInfo['uid'],
                    'key1' => $nick['nickname'],
                    'key2' => $money,
                    'key3' => '当前你发出的讨红包共收到'.$abonusInfo['receive_money'].'元',
                ];
                $this->weService->tplSend($tpl);

                $result = $this->ajaxSuccess(1307,['id'=>$exist['id'],'list'=>$tpl]);
                Db::commit();
            } else {
                Db::rollback();
                $result = $this->ajaxError(106, [], '生成失败');
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(106, [], '生成失败');
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-27
     *
     * @description 混合支付
     * @param array $params
     * @return array
     */
    private function _mixedPay(array $params)
    {
        try {
            Db::startTrans();
            $order = new OrderLogic();
            $walletModel = new Wallet();
            //修改初始红包或继续发红包
            $exist = $this->abonusSend->getOne(['uid'=>$params['openid'],'abonus_id'=>$params['id'],'is_pay'=>0]);
            if (!empty($exist)) {
                $where = ['money'=>$params['money'],'is_pay'=>0,'is_send'=>1];
                $one = $this->abonusSend->editPay($where,$exist['id']);
            } else {
                $bonus_uid = $this->abonus->getDetail($params['id']);
                $where = ['uid'=>$params['openid'],'abonus_id'=>$params['id'],'abonus_user'=>$bonus_uid['uid'],'money'=>$params['money'],'is_pay'=>0,'is_send'=>1];
                $one = $this->abonusSend->initOne($where);
                $exist['id'] = $one;
            }
            if ($one) {
                $walletInfo = $walletModel->walletDetail($params['openid']);
                //生成订单
                $orderParams['bonus_id'] = $exist['id'];
                $orderParams['uid'] = $params['openid'];
                $orderParams['money'] = $params['money'];
                $orderParams['wx_money'] = $params['money'] - sprintf("%01.2f",intval($walletInfo['balance']*100)/100);
                $orderParams['type'] = 2;
                $orderInfo = $order->orderAdd($orderParams);
                if ($orderInfo != false) {
                    //生成微信支付
                    $wcService = new WeChatPayService();
                    $orderInfo['uid'] = $params['openid'];
                    $response = $wcService->payInfo($orderInfo);
                    //$response = ['prepay_id'=>'01271417williampan','response'=>'helloworld'];
                    Db::commit();
                    $this->abonusSend->editPay(['prepay_id' => $response['prepay_id']],$exist['id']);
                    Files::CreateLog('abonus.txt',1059);
                    $result = $this->ajaxSuccess(1306, ['id'=>$exist['id'],'sign' => $response['response'], 'bonus_id' => $params['id']], '订单支付成功');
                } else {
                    Db::rollback();
                    $result = $this->ajaxError(106, [], '生成失败');
                }
            } else {
                Db::rollback();
                $result = $this->ajaxError(106, [], '生成失败');
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(106, [], '生成失败');
        }
        return $result;
    }
}