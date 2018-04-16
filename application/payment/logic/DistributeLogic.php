<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/10
 * Time: 下午12:25
 * @introduce 提成记录logic
 */
namespace app\payment\logic;

use app\common\logic\BaseLogic;
use app\payment\model\BillLog;
use app\payment\model\Bonus;
use app\payment\model\BonusDetail;
use app\payment\model\Distribute;
use app\payment\model\Wallet;
use app\user\model\User;
use app\user\model\UserCodeimg;
use app\user\model\UserLevel;
use app\user\model\UserLog;
use app\user\model\UserRelation;
use extend\helper\Utils;
use extend\service\RedisService;
use extend\service\WechatService;
use think\Exception;

class DistributeLogic extends BaseLogic
{
    protected $distribute;
    protected $bonus;
    protected $relation;
    protected $user;

    public function __construct()
    {
        $this->distribute = new Distribute();
        $this->bonus = new Bonus();
        $this->relation = new UserRelation();
        $this->user = new User();

    }

    /**
     * @Author panhao
     * @DateTime 2018-1-22
     *
     * @description 提成日志生成
     * @param int $type 1全部领完 2到期未领完
     * @param int $bonus_id 红包id
     * @param string $open_id 发红包者open_id
     * @return bool
     */
    public function addLog(int $type, int $bonus_id, string $open_id, int $count = 1)
    {
        $exist = $this->relation->getOne($open_id);
        if ($count > 1) {
            //新子级open_id
            $a = explode('||',$exist);
            $a = $a[$exist['depth']-$count+1];
            if (!empty($a)) {
                $exist = $this->relation->getOne($a);
            }else {
                return 1;
            }
        }
        $info = $this->bonus->bonusDerail($bonus_id);
        $user = [];
        if (!empty($exist['pid'])) {
            $user = $this->user->userDetailAll($exist['pid'],'truename,mobile');
        }
        //有上级且是群发红包
        if (!empty($exist['pid']) && $info['bonus_num'] > 1 && $user['truename'] && $user['mobile']) {
            //提成率
            if ($count == 1) {
                $per = Utils::getConstant('first','distribute');
                $per = $per ? $per/100 : 0.02;
            } elseif ($count == 2) {
                $per = Utils::getConstant('second','distribute');
                $per = $per ? $per/100 : 0.01;
            } else {
                $per = 0.01;
            }
            $first = $this->distribute->getLastOne(['to_uid'=>$exist['pid']]);
            $where = [];
            $data = 1;
            //全部领完
            if ($type == 1){
                $where = [
                    'bonus_id'=>$bonus_id,
                    'time'=>substr($exist['created_at'],0,10), // 绑定时间
                    'uid'=>$open_id,
                    'bonus_money'=>$info['bonus_money'],
                    'payable_money'=>$info['bonus_money'],
                    'to_uid'=>$exist['pid'],
                    'commission'=>$info['bonus_money']*$per,
                    'all_commission'=>$info['bonus_money']*$per+($first['all_commission']??0),
                    'level'=>$count,
                ];
                $data = $this->distribute->addLog($where);
            }elseif ($type == 2) {
                $detail = new BonusDetail();
                $where = [
                    'bonus_id' =>$bonus_id,
                    'time'=>substr($exist['created_at'],0,10),
                    'uid'=>$open_id,
                    'bonus_money'=>$info['bonus_money'],
                    'to_uid'=>$exist['pid'],
                    'level'=>$count,
                ];
                $where['payable_money'] = $detail->getPayableMoney($bonus_id);
                $where['commission'] = $where['payable_money']*$per;
                $where['all_commission'] = $where['payable_money']*$per+($first['all_commission']??0);
                $data = $this->distribute->addLog($where);
            }
            $wallet = new Wallet();
            //变化余额
            $change = $wallet->walletEdit(['uid'=>$exist['pid'],'balance'=>['exp','balance+'.$where['commission']]]);
            $balance = $wallet->walletDetail($exist['pid']);
            $balance = $balance['balance'];
            $bill_log = new BillLog();
            $a = [
                'uid'=>$exist['pid'], // 受益人openid
                'type'=>5,
                'affect_money'=>$where['commission'],
                'balance_money'=>$balance,
                'money_source'=>4,
                'from_uid'=>$open_id, // 发包者openid
            ];
            $log = $bill_log->billLogAdd($a);

            if (!$change || !$log || !$data) {
                return 0;
            }

            //提成推送逻辑
            $nick_name = $this->user->userDetail($open_id);
            if (!$first) {
                $key1 = $nick_name['nickname'];
                $key2 = $where['commission'];
                $key3 = '第一次获得邀请好友的奖励，快去看看收益吧';
            } elseif ($first['all_commission'] < 10 && ($where['all_commission'] >= 10)) {
                $key1 = $nick_name['nickname'];
                $key2 = $where['commission'];
                $key3 = '您的邀请好友奖励已经超过10元啦，快去看看收益吧。';
            } elseif (intval($first['all_commission']/100) < intval($where['all_commission']/100)) {
                $key1 = $nick_name['nickname'];
                $key2 = $where['commission'];
                $key3 = '您的邀请好友奖励已经超过'.((int)($where['all_commission']/100)*100).'元啦，快去看看收益吧。';
            }
            if (!empty($key1) && !empty($key2) && !empty($key3)) {
                $redisService = new RedisService();
                $wx = new WechatService();
                $tpl = [
                    'type' => 'distribution',
                    'page' => 'pages/my_more/my_more',
                    'form_id' => $redisService->lpop($exist['pid']),
                    'openid' => $exist['pid'],
                    'key1' => $key1,
                    'key2' => $key2,
                    'key3' => $key3,
                ];
                $wx->tplSend($tpl);
            }
            //目前仅限二级分类，后期拓展层级可直接改count递归
            if ($exist['depth'] >= 3 && ($count < 2)) {
                $count = !empty($count) ? ($count+1) : 2;
                $this->addLog($type, $bonus_id, $open_id, $count);
            }

        }
        return 1;
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-11
     *
     * @description 我的页面
     * @param array $param
     * @return array|string
     */
    public function myPage(array $param)
    {
        try {
            #增加浏览
            $userlog = new UserLog();
            $userlog->uLogAdd($param['openid']);
            //获取会员等级
            $level = new UserLevel();
            $temp = $level->getExist($param['openid']);
            $temp_level = $temp['level'] ?? '暂无等级';

            $hasInfo = $this->user->userDetailAll($param['openid'],'mobile,truename,avatarulr,nickname');
            $has_info = (!empty($hasInfo['mobile']) && !empty($hasInfo['truename'])) ? 1 : 0;

            $data = $this->distribute->searchMyByUid(['to_uid'=>$param['openid']],'time desc');
            $new = [];
            //把产生提成用户信息的先取出来
            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    $new[$value['uid']]['num'] = 1+($new[$value['uid']]['num'] ?? 0);
                    $new[$value['uid']]['money'] = $value['commission']+($new[$value['uid']]['money'] ?? 0);
                    $new[$value['uid']]['uid'] = $value['uid'];
                    $new[$value['uid']]['time'] = $value['time'];
                }
            }

            //取所有一级下线
            $list = $this->relation->getUidList(['pid'=>$param['openid']]);
            $info = [];
            if (!empty($list)) {
                foreach ($list as $index => $item) {
                    $list[$index]['time'] = substr($item['created_at'],0,10);
                    $list[$index]['num'] = $new[$item['uid']]['num'] ?? 0;
                    $list[$index]['money'] = !empty($new[$item['uid']]['money']) ? sprintf("%01.2f",$new[$item['uid']]['money']) : '0.00';
                }
                //取昵称，头像，性别
                $uids = array_column($list,'uid');
                $name = $this->user->getNameList($uids);
                $names = [];
                foreach ($name as $k => $v) {
                    $names[$v['openid']] = $v;
                }
                foreach ($list as $key => $value) {
                    $list[$key]['nickname'] = $names[$value['uid']]['nickname'] ?? '';
                    $list[$key]['avatarulr'] = $names[$value['uid']]['avatarulr'] ?? '';
                    $list[$key]['gender'] = $names[$value['uid']]['gender'] ?? 0;
                }
                //按日期分组
                foreach ($list as $k => $v) {
                    $info[$v['time']][] = $v;
                }
            }

            //取所有二级下线
            $one = $this->relation->getOne($param['openid']);
            $list2 = $this->relation->getUidList(['depth'=>($one['depth']+2),'path'=>['like',$one['path'].'%']]);
            $info2 = [];
            if (!empty($list2)) {
                foreach ($list2 as $index => $item) {
                    $list2[$index]['time'] = substr($item['created_at'],0,10);
                    $list2[$index]['num'] = $new[$item['uid']]['num'] ?? 0;
                    $list2[$index]['money'] = !empty($new[$item['uid']]['money']) ? sprintf("%01.2f",$new[$item['uid']]['money']) : '0.00';
                }
                //取昵称，头像，性别
                $uids2 = array_merge(array_column($list2,'uid'),array_column($list2,'pid'));
                $name2 = $this->user->getNameList($uids2);
                $names2 = [];
                foreach ($name2 as $k => $v) {
                    $names2[$v['openid']] = $v;
                }
                foreach ($list2 as $key => $value) {
                    $list2[$key]['nickname'] = $names2[$value['uid']]['nickname'] ?? '';
                    $list2[$key]['avatarulr'] = $names2[$value['uid']]['avatarulr'] ?? '';
                    $list2[$key]['gender'] = $names2[$value['uid']]['gender'] ?? 0;
                    $list2[$key]['from_name'] = $names2[$value['pid']]['nickname'] ?? '';
                }
                //按日期分组
                foreach ($list2 as $k => $v) {
                    $info2[$v['time']][] = $v;
                }
            }
            $count = count($list)+count($list2);

            $invite_logo = (new UserCodeimg())->isCheckInviteCode($param['openid'],0);
            $invite_logos = (new UserCodeimg())->isCheckInviteCode($param['openid'],3);
            $result = $this->ajaxSuccess(102,[
                'level'=>$temp_level, //用户等级
                'logo'=>$hasInfo['avatarulr'], // 头像
                'nick_name'=>$hasInfo['nickname'], // 昵称
                'invite_logo'=>$invite_logo??'', // 邀请码
                'invite_logos'=>$invite_logos??'', // 二维码
                'has_info'=>$has_info, // 是否填写过共享收益
                'total'=>$count, // 总计
                'list'=>$info, // 一级下线收益
                'list2'=>$info2, // 二级下线收益
            ]);
        } catch (Exception $exception) {

            $result = $this->ajaxError(105);
        }
        return $result;
    }
}