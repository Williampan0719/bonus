<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/5
 * Time: 下午2:25
 */

namespace app\user\logic;


use app\common\logic\BaseLogic;
use app\payment\logic\DistributeLogic;
use app\payment\logic\WalletLogic;
use app\payment\model\BillLog;
use app\payment\model\Bonus;
use app\payment\model\BonusDetail;
use app\payment\model\BonusReceive;
use app\payment\model\Wallet;
use app\user\model\User;
use app\user\model\UserCodeimg;
use app\user\model\UserLog;
use app\user\model\UserReceiveHot;
use app\user\model\UserRelation;
use extend\helper\Files;
use extend\helper\Str;
use extend\service\AudioService;
use extend\service\CharDb;
use extend\service\RedisService;
use extend\service\ToPinyin;
use extend\service\WechatService;
use think\Db;
use think\Exception;
use think\Hook;

class UserLogic extends BaseLogic
{
    protected $bonus = null;
    protected $bonusRece = null;
    protected $user = null;
    protected $wechatser = null;
    protected $audio = null;
    protected $rate = 80;      #识别率
    protected $bonusdetail = null;
    protected $wallet = null;
    protected $billlog = null;
    protected $redis = null;

    function __construct()
    {
        $this->bonus = new Bonus();
        $this->bonusRece = new BonusReceive();
        $this->user = new User();
        $this->wechatser = new WechatService();
        $this->audio = new AudioService();
        $this->bonusdetail = new BonusDetail();
        $this->wallet = new Wallet();
        $this->billlog = new BillLog();
        $this->redis = new RedisService();
    }

    /** code换取sessionkey
     * auth smallzz
     * @param $code
     */
    public function getSessionKey(array $params)
    {
        $codeInfo = $this->wechatser->getSessionKey($params['code']);

        if ($codeInfo) {
            try {
                $encryptedData = $params['encryptedData'];
                $iv = $params['iv'];
                $res = $this->userDataSave($codeInfo['session_key'], $encryptedData, $iv);
                $infos = json_decode($res, true);
                #保存用户基本信息
                $data['nickname'] = $infos['nickName'];
                $data['avatarulr'] = $infos['avatarUrl'];
                $data['gender'] = $infos['gender'];
                $data['city'] = $infos['city'];
                $data['province'] = $infos['province'];
                $data['country'] = $infos['country'];
                $data['language'] = $infos['language'];
                $data['channel'] = $params['channel'];

                #检查用户的信息存在么
                if ($this->user->checkOpenid($infos['openId']) > 0) {
                    #获取开放平台唯一标识
                    #$union = $this->wechatser->getUnionid($infos['openId']);
                    #$data['unionid'] = $union['unionid'];
                    $this->user->save($data, ['openid' => $infos['openId']]);
                } else {
                    $data['openid'] = $infos['openId'];
                    $this->user->save($data);

                    #生成钱包数据
                    $qb['uid'] = $infos['openId'];
                    $this->wallet->save($qb);
                }

                $uid = $this->user->userDetailAll($infos['openId'], 'id')['id'];
                $userlog = new UserLog();
                $userlog->uLogAdd($infos['openId']);
                $result = $this->ajaxSuccess(1202, ['openid' => $infos['openId'], 'uid' => $uid, 'nickname' => $infos['nickName'],'avatarulr'=>$data['avatarulr'] ?? '']);
            } catch (Exception $exception) {
                $result = $this->ajaxError(1201);
            }
        } else {
            $result = $this->ajaxError(1200);
        }
        return $result;
    }

    /** 授权获取手机号
     * auth smallzz
     * @param array $params
     * @return array
     */
    public function getMobile(array $params)
    {
        $codeInfo = $this->wechatser->getSessionKey($params['code']);
        if ($codeInfo) {
            try {
                $encryptedData = $params['encryptedData'];
                $iv = $params['iv'];
                $res = $this->userDataSave($codeInfo['session_key'], $encryptedData, $iv);
                $infos = json_decode($res, true);
                #保存用户基本信息
                $data['mobile'] = $infos['purePhoneNumber'];
                $this->user->save($data, ['openid' => $params['openid']]);
                $result = $this->ajaxSuccess(1202, $data);
            } catch (Exception $exception) {
                $result = $this->ajaxError(1201);
            }
        } else {
            $result = $this->ajaxError(1200);
        }
        return $result;
    }

    /**用户数据保存
     * auth smallzz
     * @param string $sessionKey
     * @param string $encryptedData
     * @param string $iv
     * @return bool|int
     */
    public function userDataSave(string $sessionKey, string $encryptedData, string $iv)
    {
        #先解密
        $wechat = new WechatService();
        $resut = $wechat->decryptData($sessionKey, $encryptedData, $iv, $data);
        if ($resut !== false) {
            return $resut;
        }
        return false;
    }

    /**用户信息
     * auth smallzz
     * @param $param
     * @return array
     */
    public function userGetInfo($param)
    {
        $uid = $param['openid'];
        try {
            $info = $this->user->userDetailAll($uid, '*');
            if ($info) {
                $result = $this->ajaxSuccess(202, $info);
            } else {
                $result = $this->ajaxError(205);
            }
        } catch (Exception $exception) {
            return $this->ajaxError(205);
        }
        return $result;
    }

    /** 处理文件 [并发]  修复 增加队列机制
     * auth smallzz
     * @param $file
     */
    public function audioExec($param, $file)
    {
        if (empty($param['openid'])) {
            return $this->ajaxError(1302, ['type' => 3, 'meg' => 'openidnull']);
        }
        //领红包时消耗
        $bonus_detail = $this->bonus->bonusDerail($param['bonus_id']);
        if ($bonus_detail['type'] == 1 && ($bonus_detail['class'] == 1)) {
            $power = ['title' => 'fetch', 'openid' => $param['openid']];
            Hook::exec('app\index\behavior\Power', 'userPower', $power);
        }
        $filetime = Files::createFileName();
        $newfile = config('audio')['mp3_dir'] . $param['bonus_id'] . $filetime . '.mp3';
        $res = $this->audio->moveFile($file['file']['tmp_name'], $newfile);
        $check = $this->bonusRece->checkReceStatus($param['openid'], $param['bonus_id']);
        if ($check) {
            return $this->ajaxError(1316, ['type' => 4, 'meg' => '已经领取过此红包']);
        }
        $allId = $this->redis->lrange('bonus_' . $param['bonus_id'], 0, -1);
        if (count($allId) == 0) {
            if ($bonus_detail['type'] == 1) {
                $power = ['title' => 'empty', 'openid' => $param['openid']];
                Hook::exec('app\index\behavior\Power', 'userPower', $power);
            }
            return $this->ajaxError(1304, ['type' => 1, 'meg' => '红包已经被抢完']);
        }
        if ($res) {
            #次数项
            $cznum = $this->redis->get($param['bonus_id'] . $param['openid']);
            if ($cznum) {
                $this->redis->set($param['bonus_id'] . $param['openid'], $cznum + 1);
                $newcznum = $cznum + 1;
            } else {
                $this->redis->set($param['bonus_id'] . $param['openid'], 1);
                $newcznum = 1;
            }
            #转码识别
            Db::startTrans();
            $info = $this->audio->audioIdentify($newfile, $param['bonus_id'] . $filetime, 1);
            if ($info) {
                #上传oss
                $resUrl = $this->audio->audioOssUp($param['bonus_id'] . $filetime . '.mp3', $newfile);
                $tit = Str::filter($info);
                $sBRate = $this->resultDb($param['bonus_id'], $tit);
                $rate = $this->_rateSelect($newcznum);       #识别率分析
                #file_put_contents($_SERVER['DOCUMENT_ROOT'].'/video/llllll.txt','识别率——'.$sBRate.'当前需要率_'.$rate.'_次数'.$newcznum."\r\n",FILE_APPEND);
                if (intval($sBRate) >= $rate && !empty($resUrl)) {
                    try {
                        #从队列抛出
                        $hongbId1 = $this->redis->rpop('bonus_' . $param['bonus_id']);

                        if (empty($hongbId1)) {
                            if ($bonus_detail['type'] == 1) {
                                $power = ['title' => 'empty', 'openid' => $param['openid']];
                                Hook::exec('app\index\behavior\Power', 'userPower', $power);
                            }
                            return $this->ajaxError(1304, ['type' => 1, 'meg' => '红包被抢完了']);
                        }
                        $hongbId = explode('-', $hongbId1);

                        $yuE = $this->wallet->getYuE($param['openid']) + $hongbId[1];
                        $this->bonusdetail->editReceBonus(['is_use' => 1], ['recedetail_id' => $hongbId[0]]);
                        $this->bonusRece->addRece(['bonus_id' => $param['bonus_id'], 'detail_id' => $hongbId[0], 'receive_uid' => $param['openid'], 'receive_voice' => $resUrl, 'time_length' => $param['timelength'], 'identify_num' => $newcznum,'balance'=>['exp','balance+'.$yuE]]);
                        #加log记录 查询钱余额
                        $info = $this->bonus->getInfo($param['bonus_id']);
                        //被领取数+1
                        $this->bonus->bonusEdit(['receive_bonus_num'=>['exp','receive_bonus_num+1'],'bonus_id'=>$param['bonus_id']]);
                        $this->billlog->save([
                            'uid' => $param['openid'],
                            'type' => 2,
                            'affect_money' => $hongbId[1],
                            'balance_money' => $yuE,
                            'money_source' => 3,
                            'from_uid' => $info['uid'],
                        ]);
                        #给钱
                        $this->wallet->where(['uid' => $param['openid']])->setInc('balance', $hongbId[1]);
                        #李永传的逻辑
                        $walletInfo = $this->wallet->walletDetail($param['openid']);
                        if ($walletInfo['is_first'] == 0 && $yuE >= 1) {
                            $walletLogic = new WalletLogic();
                            $walletLogic->userWalletFirst([
                                'uid' => $param['openid'],
                                'money' => $yuE
                            ]);
                        }
                        $this->wallet->where(['uid' => $param['openid']])->update(['is_first' => 1]);
                        #结束
                        if ($bonus_detail['type'] == 1) {
                            $power = ['title' => 'send', 'openid' => $bonus_detail['uid']];
                            Hook::exec('app\index\behavior\Power', 'userPower', $power);
                        }
                        if (count($this->redis->lrange('bonus_' . $param['bonus_id'], 0, -1)) == 0) {
                            $dist = new DistributeLogic();
                            $dist->addLog(1, $param['bonus_id'], $info['uid']);
                            #设置领取完毕
                            $this->bonus->save(['finish_at' => date('Y-m-d H:i:s'), 'is_done' => 1], ['id' => $param['bonus_id']]);
                            #发送模版消息
                            $miao = (time() - strtotime($info['created_at']));
                            $this->wechatser->tplSend(['openid' => $info['uid'], 'type' => 'done',
                                'form_id' => $this->redis->lpop($info['uid']),
                                'page' => 'pages/play/play?bonus_id=' . $param['bonus_id'],
                                'key2' => $miao . '秒']);
                        }
                        Db::commit();
                    } catch (Exception $exception) {
                        return $this->ajaxError(1302, ['type' => 3, 'meg' => '语音不清晰']);
                    }
                    return $this->ajaxSuccess(1305);
                }
                Db::rollback();
                return $this->ajaxError(1302, ['type' => 3, 'meg' => '语音不清晰']);
            }
            $resUrl = $this->audio->audioOssUp($param['bonus_id'] . $filetime . '.mp3', $newfile);
            $this->bonusRece->addRece(['bonus_id' => $param['bonus_id'], 'detail_id' => 0, 'receive_uid' => $param['openid'], 'receive_voice' => $resUrl, 'time_length' => $param['timelength'], 'identify_num' => $newcznum]);
            return $this->ajaxError(1301, ['type' => 1, 'meg' => '红包被抢完了']);
        } else {
            return $this->ajaxError(1300, ['type' => 0, 'meg' => '语音网络错误']);
        }
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-17
     *
     * @description 听语音领红包
     * @param array $param
     * @return array
     */
    public function listenBonus(array $param)
    {
        if (empty($param['openid'])) {
            return $this->ajaxError(1302, ['type' => 3, 'meg' => 'openidnull']);
        }
        //领红包时消耗体力
        $bonus_detail = $this->bonus->bonusDerail($param['bonus_id']);
        if ($bonus_detail['type'] == 1) {
            $power = ['title' => 'fetch', 'openid' => $param['openid']];
            Hook::exec('app\index\behavior\Power', 'userPower', $power);
        }
        $check = $this->bonusRece->checkReceStatus($param['openid'], $param['bonus_id']);
        if ($check) {
            return $this->ajaxError(1316, ['type' => 4, 'meg' => '已经领取过此红包']);
        }

        #次数项
        $cznum = $this->redis->get($param['bonus_id'] . $param['openid']);
        if ($cznum) {
            $this->redis->set($param['bonus_id'] . $param['openid'], $cznum + 1);
            $newcznum = $cznum + 1;
        } else {
            $this->redis->set($param['bonus_id'] . $param['openid'], 1);
            $newcznum = 1;
        }
        Db::startTrans();
        try {
            #从队列抛出
            $hongbId1 = $this->redis->rpop('bonus_' . $param['bonus_id']);

            if (empty($hongbId1)) {
                return $this->ajaxError(1304, ['type' => 1, 'meg' => '红包被抢完了']);
            }
            $hongbId = explode('-', $hongbId1);

            $yuE = $this->wallet->getYuE($param['openid']) + $hongbId[1];
            $this->bonusdetail->editReceBonus(['is_use' => 1], ['recedetail_id' => $hongbId[0]]);
            $this->bonusRece->addRece(['bonus_id' => $param['bonus_id'], 'detail_id' => $hongbId[0], 'receive_uid' => $param['openid'], 'receive_voice' => '', 'time_length' => 0, 'identify_num' => $newcznum,'balance'=>['exp','balance+'.$yuE]]);
            #加log记录 查询钱余额
            $info = $this->bonus->getInfo($param['bonus_id']);
            //被领取数+1
            $this->bonus->bonusEdit(['receive_bonus_num'=>['exp','receive_bonus_num+1'],'bonus_id'=>$param['bonus_id']]);
            $this->billlog->save([
                'uid' => $param['openid'],
                'type' => 2,
                'affect_money' => $hongbId[1],
                'balance_money' => $yuE,
                'money_source' => 3,
                'from_uid' => $info['uid'],
            ]);
            #给钱
            $this->wallet->where(['uid' => $param['openid']])->setInc('balance', $hongbId[1]);
            #李永传的逻辑
            $walletInfo = $this->wallet->walletDetail($param['openid']);
            if ($walletInfo['is_first'] == 0 && $yuE >= 1) {
                $walletLogic = new WalletLogic();
                $walletLogic->userWalletFirst([
                    'uid' => $param['openid'],
                    'money' => $yuE
                ]);
                $this->wallet->where(['uid' => $param['openid']])->update(['is_first' => 1]);
            }
            #结束
            if ($bonus_detail['type'] == 1) {
                $power = ['title' => 'send', 'openid' => $bonus_detail['uid']];
                Hook::exec('app\index\behavior\Power', 'userPower', $power);
            }
            if (count($this->redis->lrange('bonus_' . $param['bonus_id'], 0, -1)) == 0) {
                $dist = new DistributeLogic();
                $dist->addLog(1, $param['bonus_id'], $info['uid']);
                #设置领取完毕
                $this->bonus->save(['finish_at' => date('Y-m-d H:i:s'), 'is_done' => 1], ['id' => $param['bonus_id']]);
                #发送模版消息
                $miao = (time() - strtotime($info['created_at']));
                if($bonus_detail['class']==1){
                    $page='pages/packet/packet?bonus_id=' . $param['bonus_id'];;
                }else{
                    $page='pages/play/play?bonus_id=' . $param['bonus_id'];
                }
                $this->wechatser->tplSend(['openid' => $info['uid'], 'type' => 'done',
                    'form_id' => $this->redis->lpop($info['uid']),
                    'page' => $page,
                    'key2' => $miao . '秒']);
            }
            Db::commit();
        } catch (Exception $exception) {
            return $this->ajaxError(1302, ['type' => 3, 'meg' => '红包领取失败']);
        }
        return $this->ajaxError(1305);
    }

    /**识别对比
     * auth smallzz
     * @param $bonus_id
     * @param $tit
     * @return float|int
     */
    private function resultDb($bonus_id, $tit)
    {
        $nuwTit = $this->bonus->getBonusTit($bonus_id);
        $char = new CharDb();
        ToPinyin::convert($tit, '', $ztit1, $first);
        ToPinyin::convert($nuwTit, '', $ztit2, $first);
        $sBRate = $char->getSimilar($ztit1[0], $ztit2[0]);
        return $sBRate;
    }

    /** 多次识别不同概率返回
     * auth smallzz
     * @param int $num
     * @return int
     */
    private function _rateSelect(int $num = 1)
    {
        switch ($num) {
            case 1:
                return 80;
            case 2:
                return 50;
            case 3:
                return 30;
            default:
                return 30;
        }

    }

    /**
     * @Author panhao
     * @DateTime 2018-02-05
     *
     * @description 生成小程序二维码
     * @param $scene
     * @param $page
     * @param $width
     * @param $bonus_id
     * @param $uid
     * @param int $class
     * @return array
     */
    public function createQrcode($scene, $page, $width, $bonus_id, $uid, $class = 1)
    {
        try {
            if($page == 'pages/voice-packet/voice-packet'){
                $page = 'pages/hall/hall';
            }
            //临时
            if ($page == 'pages/beg_request/beg_request'){
                $page = 'pages/hall/hall';
            }
            $res = $this->wechatser->getQrCode($scene, $page, $width, 0);
            if (!empty($bonus_id)) {
                //$alifile = 'fxbonus_id'.$bonus_id . '.png';  #阿里路径
                //$imgurl = $this->audio->CodeOssUp($alifile,$res);
                $new = new UserCodeimg();
                $new->save(['bonus_id' => $bonus_id, 'type' => 2, 'imgurl' => $res, 'class'=>$class]);
            }
            if (!empty($uid)) {
                $new = new UserCodeimg();
                $new->save(['uid' => $uid, 'type' => 3, 'imgurl' => $res]);
            }
        } catch (Exception $exception) {
            return $this->ajaxError(1350);
        }
        return $this->ajaxSuccess(1351, ['img' => $res]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-10
     *
     * @description 绑定用户
     * @param $param
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function bindingUser($param)
    {
        Db::startTrans();
        try {
            //添加访问ip
            $this->user->forbidUser(['ip'=>$_SERVER['REMOTE_ADDR'],'openid'=>$param['openid']]);
            //每日首次登录用户
            $power = ['title' => 'everyday', 'openid' => $param['openid']];
            $every_power = Hook::exec('app\index\behavior\Power', 'userPower', $power);

            $relation = new UserRelation();
            $count = $relation->getExist(['uid' => $param['openid']]);
            //已存在绑定关系
            if (!empty($count)) {
                Db::commit();
                return $this->ajaxSuccess(100, ['list' => '已存在账户', 'power' => $every_power]);
            }
            $data = '';
            if (!empty($param['from_uid'])) {
                $param['from_openid'] = $this->user->getOpenidById($param['from_uid']);
                if (!empty($param['from_openid'])) {
                    //邀请体力钩子
                    $power = ['title' => 'invite', 'openid' => $param['from_openid']];
                    Hook::exec('app\index\behavior\Power', 'userPower', $power);
                    //等级变化推送
                    $level = new UserLevelLogic();
                    $level->getLevelPush($param['from_openid']);
                    //获得用户信息
                    $info = $relation->getOne($param['from_openid']);
                    //父级绑定过
                    if (!empty($info)) {
                        $info['depth'] = $info['depth'] + 1;
                        $param['path'] = $info['path'] . '||' . $param['openid'];
                        $where = ['uid' => $param['openid'], 'pid' => $param['from_openid'], 'depth' => $info['depth'], 'path' => $param['path']];
                        $data = $relation->bindingUser($where);
                    } else { // 父级没有绑定过
                        $param['depth'] = 2;
                        $param['path'] = '||' . $param['from_openid'] . '||' . $param['openid'];

                        $where = [
                            ['uid' => $param['from_openid'], 'path' => '||' . $param['from_openid']], // 绑定父级
                            ['uid' => $param['openid'], 'pid' => $param['from_openid'], 'depth' => $param['depth'], 'path' => $param['path']], // 绑定子级
                        ];
                        $data = $relation->bindingUserAll($where);
                    }
                }
            } else {
                //第一级
                $where = ['uid' => $param['openid'], 'path' => '||' . $param['openid']];
                $data = $relation->bindingUser($where);
            }
            if ($data) {
                Db::commit();
                $result = $this->ajaxSuccess(100, ['power' => $every_power]);
            } else {
                Db::rollback();
                $result = $this->ajaxError(106);
            }
        } catch (Exception $exception) {
            Db::rollback();
            $result = $this->ajaxError(106);
        }
        return $result;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-11
     *
     * @description 个人资金明细
     * @param array $params
     * @return array
     */
    public function userCapital(array $params)
    {
        try {
            $page = $params['page'] ?? config('paginate.default_page');
            $size = $params['size'] ?? config('paginate.default_size');
            $total = $this->billlog->billLogCountApi($params['uid']);
            if ($total > 0) {
                $list = $this->billlog->billLogListApi($params['uid'], $page, $size);
                foreach ($list as $key => $vo) {
                    switch ($vo->type) {//1发红包,2收红包,3提现,4退款,5提成,6赏红包,7讨红包，8充值金币
                        case 1:
                            $list[$key]['capital_detail'] = config('wallet.CAPITAL_REMARKS')['1'];
                            break;
                        case 2:
                            $userInfo = $this->user->userDetail($vo['from_uid']);
                            $list[$key]['capital_detail'] = config('wallet.CAPITAL_REMARKS')['0'] . '@' .
                                $userInfo['nickname'] . config('wallet.CAPITAL_REMARKS')['2'];
                            break;
                        case 3:
                            $list[$key]['capital_detail'] = config('wallet.CAPITAL_REMARKS')['3'];
                            break;
                        case 4:
                            $list[$key]['capital_detail'] = config('wallet.CAPITAL_REMARKS')['4'];
                            break;
                        case 5:
                            $userInfo = $this->user->userDetail($vo['from_uid']);
                            $list[$key]['capital_detail'] = '@' . $userInfo['nickname'] . config('wallet.CAPITAL_REMARKS')['5'];
                            break;
                        case 6:
                            $userInfo = $this->user->userDetail($vo['from_uid']);
                            $list[$key]['capital_detail'] =  config('wallet.CAPITAL_REMARKS')['0-1'] . '@' .
                                $userInfo['nickname'] . config('wallet.CAPITAL_REMARKS')['6'];
                            break;
                        case 7:
                            $userInfo = $this->user->userDetail($vo['from_uid']);
                            $list[$key]['capital_detail'] =  config('wallet.CAPITAL_REMARKS')['0'] . '@' .
                                $userInfo['nickname'] . config('wallet.CAPITAL_REMARKS')['7'];
                            break;
                        case 8:
                            $list[$key]['capital_detail'] = config('wallet.CAPITAL_REMARKS')['8'];
                            $list[$key]['type'] = 1; //临时变颜色用 William pan
                            break;
                    }
                }
                $result = $this->ajaxSuccess(202, ['list' => $list, 'total' => $total]);
            } else {
                $result = $this->ajaxSuccess(202, ['list' => [], 'total' => $total]);
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(105);
        }
        return $result;
    }

    /** 创建分享二维码
     * auth smallzz
     * @param $param
     * @param $file
     */
    public function createCodeImg($param, $file)
    {
        #验证二维码是否存在
        $new = new UserCodeimg();
        if (!empty($param['bonus_id'])) {
            $setid = 'bonus_id' . $param['bonus_id'];
            $data['bonus_id'] = $param['bonus_id'];
            $data['type'] = 1;
            $ischeck = $new->isCheckCode($param['bonus_id']);
        } else {
            $setid = 'uid' . $param['uid'];
            $data['uid'] = $param['uid'];
            $data['type'] = 0;
            $ischeck = $new->isCheckInviteCode($param['uid'],0);
        }
        if (!empty($ischeck)) {
            return $this->ajaxSuccess(102, ['imgurl' => $ischeck]);
        }
        $alifile = 'code' . $setid . '.png';  #阿里路径
        $newfile = config('audio')['mp3_dir'] . $setid . '.png';
        $res = $this->audio->moveFile($file['file']['tmp_name'], $newfile);
        if ($res) {
            $new = new UserCodeimg();
            #上传oss
            $res = $this->audio->CodeOssUp($alifile, $newfile);
            $data['imgurl'] = $res;
            $new->save($data);

            return $this->ajaxSuccess(102, ['imgurl' => $res]);
        }
        return $this->ajaxError(105);
    }

    /** 修改口令
     * auth smallzz
     * @param $param
     * @return array
     */
    public function example($param)
    {
        try {
            $this->bonus->example($param['bonus_id'], $param['bonus_password']);
        } catch (Exception $exception) {
            return $this->ajaxError(107);
        }
        return $this->ajaxSuccess(101);
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-17
     *
     * @description 保存用户信息
     * @param array $param
     * @return array
     */
    public function savePreconditionInfo(array $param)
    {
        try {
            if (!$param['mobile'] || !$param['true_name']) {
                return $this->ajaxError(1201);
            }
            $data = [
                'mobile' => $param['mobile'],
                'truename' => $param['true_name'],
                'distribute_time' => date('Y-m-d H:i:s'),
            ];
            $this->user->save($data, ['openid' => $param['openid']]);
            $result = $this->ajaxSuccess(1202);
        } catch (Exception $exception) {
            $result = $this->ajaxError(1201);
        }
        return $result;
    }

    /** 获取热门推荐
     * auth smallzz
     * @param array $param
     */
    public function getHot(array $param)
    {
        try {
            $UserReceiveHot = new UserReceiveHot();
            $list = $UserReceiveHot->getReceiveHot($param['uid']);
        } catch (Exception $exception) {
            return $this->ajaxSuccess(105);
        }
        return $this->ajaxSuccess(102, ['list' => $list]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-24
     *
     * @description 判断红包状态
     * @param array $param
     * @return array
     */
    public function checkVoiceBonus(array $param)
    {
        try {
            $bonus_detail = $this->bonus->bonusDerail($param['bonus_id']);
            $check = $this->bonusRece->checkReceStatus($param['openid'], $param['bonus_id']);
            if (!empty($bonus_detail['finish_at'])) {
                return $this->ajaxError(1309, ['type' => 3, 'meg' => '红包已过期']);
            } elseif ($bonus_detail['is_done'] == 1) {
                return $this->ajaxError(1304, ['type' => 1, 'meg' => '红包被抢完了']);
            } elseif ($check) {
                return $this->ajaxError(1316, ['type' => 4, 'meg' => '已经领取过此红包']);
            }
            return $this->ajaxSuccess(102);
        } catch (Exception $exception) {
            return $this->ajaxError(105);
        }
    }

    /**
     * @Author yefan
     * @DateTime 2018-01-19
     *
     * @description qq音乐歌曲列表
     * @param array $params
     * @return array
     */
    public function getQqMusic(array $params)
    {
        try {
            // 搜索qq音乐
            $number = $params['number'] ?? config('qqmusic.number'); // $number = 5;
            $p = $params['p'] ?? config('qqmusic.p'); // $p = 10;
            //$name = '周杰伦';
            //$name = '黄子韬';
            $name = $params['condition'];
            $searchHost = config('qqmusic.music_search_host');
            $urlString = $searchHost . '?t=0&n=' . $number . '&aggr=1&cr=1&loginUin=0&format=json&inCharset=GB2312&outCharset=utf-8¬ice=0&platform=jqminiframe.json&needNewCode=0&p=' . $p . '&catZhida=0&remoteplace=sizer.newclient.next_song&w=' . $name;
            $res = file_get_contents($urlString);
            $res = json_decode($res, true);
            $music = [];
            if ($res['code'] == 0) {
                $song = $res['data']['song']['list'];
                //var_dump($song);
                if (!empty($song)) {
                    // 歌曲参数获取
                    foreach ($song as $k => $v) {
                        $f = explode('|', $song[$k]['f']);
                        //var_dump($f);
                        $temp = [
                            //"f" => $f,
                            "id" => $f[20],
                            "fsong" => $song[$k]['fsong'],
                            "fsinger" => $song[$k]['fsinger'],
                            //"album" => $f[5],   // 专辑名称
                            "img" => $f[22],    // 歌曲图片
                            //"lrc" => $f[0],     //歌词
                        ];
                        $music[] = $temp;
                    }

                    $g_tk = '5381'; // 5381  1144349687
                    $guid = $params['guid'] ?? config('qqmusic.guid'); // 相当于用户标识
                    $cid = config('qqmusic.cid');
                    $playHost = config('qqmusic.play_host'); //http://dl.stream.qqmusic.qq.com/';
                    $playKeyHost = config('qqmusic.play_key_host'); //$playKeyHost = 'https://c.y.qq.com/base/fcgi-bin/fcg_music_express_mobile3.fcg';
                    $musicJsonCallback = 'MusicJsonCallback11057453424938712';
                    foreach ($music as &$item) {
                        // 歌曲图片API
                        $img_zero = $item['img'];
                        $img_one = substr($item['img'], -2, -1);
                        $img_two = substr($item['img'], -1);
                        $imgcacheHost = config('qqmusic.img_cache_host');
                        $imgUrl = $imgcacheHost . $img_one . '/' . $img_two . '/' . $img_zero . '.jpg';
                        $item['img_url'] = $imgUrl;
                        unset($item['img']);
                        // 歌词API：
                        /*$lyric = $item['lrc'];
                        $lyricUrl = 'http://music.qq.com/miniportal/static/lyric/' . ($lyric % 100) . '/' . $lyric . '.xml';
                        $item['lyric_url'] = $lyricUrl;*/
                        // 获取播放key
                        $song_mid = $item['id'];
                        $file_name = 'C400' . $song_mid . '.m4a';
                        $playKeyUrl = $playKeyHost . '?g_tk=' . $g_tk . '&jsonpCallback=' . $musicJsonCallback . '&loginUin=0&hostUin=0&format=json&inCharset=utf8&outCharset=utf-8&notice=0&platform=yqq&needNewCode=0&cid=' . $cid . '&callback=' . $musicJsonCallback . '&uin=0&songmid=' . $song_mid . '&filename=' . $file_name . '&guid=' . $guid; //C40000378Vgm4TjsTs.m4a
                        $resPay = file_get_contents($playKeyUrl);
                        $resPay = str_replace($musicJsonCallback . '(', '[', $resPay);
                        $resPay = str_replace(')', ']', $resPay);
                        $resPay = json_decode($resPay, true);
                        $data = $resPay[0]['data']['items'][0];
                        $filename = $data['filename'];
                        $key = $data['vkey'];

                        // 播放地址
                        //$playerUrl = $playHost . 'C400' . $item['id'] . '.m4a?vkey=' . $key . '&guid=4336774572&uin=0&fromtag=0';
                        $playerUrl = $playHost . $filename . '?vkey=' . $key . '&guid=' . $guid . '&uin=0&fromtag=66';
                        $item['player_url'] = $playerUrl;
                    }

                    $result = $this->ajaxSuccess(1400, $music);
                } else {
                    $result = $this->ajaxError(1401, [], '未获取到歌曲');
                }

            } else {
                $result = $this->ajaxError(1401, [], '歌曲获取失败');
            }

        } catch (Exception $exception) {
            $result = $this->ajaxError(1401, [], '服务器异常、未获取到歌曲');
        }
        return $result;
    }
}