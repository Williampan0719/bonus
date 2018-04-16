<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/6
 * Time: 上午9:29
 */

namespace app\backend\logic;


use app\backend\model\User;
use app\common\logic\BaseLogic;
use app\game\model\GameCoinLog;
use app\payment\model\BillLog;
use app\payment\model\Bonus;
use app\payment\model\BonusDetail;
use app\payment\model\BonusReceive;
use app\payment\model\Wallet;
use think\Exception;
use think\Request;

class UserLogic extends BaseLogic
{
    protected $user;
    protected $type;
    protected $coin;

    function __construct()
    {
        $this->type = [
            '1' => '发红包',
            '2' => '收红包',
            '3' => '提现',
            '4' => '退款',
            '5' => '提成',
            '6' => '赏红包',
            '7' => '讨红包',
            '8' => '充值金币',
        ];
        $this->coin = [
            '0' => '发起挑战',
            '1' => '押注',
            '2' => '押注奖励',
            '3' => '应战',
            '4' => '应战奖励',
            '5' => '欢乐大转盘',
            '6' => '欢乐大转盘奖励',
            '7' => '充值',
            '8' => '签到',
            '9' => '退款',
            '10'=> '挑战奖励',
            '11'=> '平局',
        ];

        $this->user = new User();
    }

    /** 用户列表
     * auth smallzz
     * @param $param
     * @return array
     */
    public function userList($param){
        try {
            $page = $param['page'] ?? config('paginate.default_page');
            $size = $param['size'] ?? config('paginate.default_size');
            $where = [];
            if (!empty($param['keyword'])) {
                $where['nickname'] = $param['keyword'];
            }
            if (!empty($param['sex'])) {
                $where['gender'] = $param['sex'];
            }
            if (!empty($param['start_time']) && !empty($param['end_time'])) {
                $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
            }elseif (!empty($param['start_time'])) {
                $where['created_at'] = ['gt',$param['start_time']];
            }elseif (!empty($param['end_time'])) {
                $where['created_at'] = ['lt',$param['end_time']];
            }
            $list = $this->user->UListS($where,$page,$size);
            if ($list) {
                $result = $this->ajaxSuccess(202,$list);
            }else{
                $result = $this->ajaxError(205);
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(205);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-11
     *
     * @description 用户面板
     * @param array $param
     * @return array
     */
    public function userPanel(array $param)
    {
        try {
            $user = new \app\user\model\User();
            $list = $user->userPanel($param['openid']);
            $page = $param['page'] ?? 1;
            $size = $param['size'] ?? 10;
            if ($list) {
                $list['is_distribute'] = 1;
                if (empty($list['mobile']) || empty($list['truename'])) {
                    $list['distribute_time'] = '';
                    $list['is_distribute'] = 0;
                }
                $wallet = new Wallet();
                $user_wallet = $wallet->walletDetail($param['openid'],'balance,virtual');
                $list['balance'] = sprintf("%01.2f",$user_wallet['balance']);
                $list['virtual'] = $user_wallet['virtual'];

                switch ($param['type']) {
                    case 0:
                        $list['list'] = $this->_sendPassword($param, $page, $size); // 发口令
                        break;
                    case 1:
                        $list['list'] = $this->_sendListen($param, $page, $size); // 发语音
                        break;
                    case 2:
                        $list['list'] = $this->_receivePassword($param, $page, $size); // 抢口令
                        break;
                    case 3:
                        $list['list'] = $this->_receiveListen($param, $page, $size); // 抢语音
                        break;
                    case 4:
                        $list['list'] = $this->_moneyChange($param, $page, $size); //资金变动
                        break;
                    case 5:
                        $list['list'] = $this->_coinChange($param, $page, $size); //金币变动
                        break;
                }
                $result = $this->ajaxSuccess(202,['list'=>$list]);
            }else{
                $result = $this->ajaxError(205);
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(205);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-12
     *
     * @description 发口令
     * @param array $param
     * @param $page
     * @param $size
     * @return array
     */
    private function _sendPassword(array $param,int $page,int $size)
    {
        $where = ['is_pay'=>1,'class'=>0,'uid'=>$param['openid']];
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
        }elseif (!empty($param['start_time'])) {
            $where['created_at'] = ['gt',$param['start_time']];
        }elseif (!empty($param['end_time'])) {
            $where['created_at'] = ['lt',$param['end_time']];
        }
        $bonuss = new Bonus();
        $field = 'id,bonus_money,bonus_password,service_money,refund_money,is_done,finish_at,created_at';
        $count = $bonuss->getUserCount($where);
        $detail = new BonusDetail();
        $bonus = [];
        if ($count > 0) {
            $bonus = $bonuss->searchBonusList($where,$page,$size,$field);
            foreach ($bonus as $key => $value) {
                $receive_money=$detail->getReceiveMoney($value['id']);
                $bonus[$key]['receive_money']=''.$receive_money;
                $bonus[$key]['status'] = $value['is_done'] == 1 ? '已领取完' : (!$value['finish_at'] ? '未领取完' : '已过期-未领取完');
            }
        }
        return ['total'=>$count,'list'=>$bonus];
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-12
     *
     * @description 发语音
     * @param array $param
     * @param $page
     * @param $size
     * @return array
     */
    private function _sendListen(array $param,int $page,int $size)
    {
        $where = ['is_pay'=>1,'class'=>1,'uid'=>$param['openid']];
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
        }elseif (!empty($param['start_time'])) {
            $where['created_at'] = ['gt',$param['start_time']];
        }elseif (!empty($param['end_time'])) {
            $where['created_at'] = ['lt',$param['end_time']];
        }
        $bonuss = new Bonus();
        $field = 'id,bonus_money,voice_path,service_money,refund_money,is_done,finish_at,created_at';
        $count = $bonuss->getUserCount($where);
        $detail = new BonusDetail();
        $bonus = [];
        if ($count > 0) {
            $bonus = $bonuss->searchBonusList($where,$page,$size,$field);
            foreach ($bonus as $key => $value) {
                $receive_money=$detail->getReceiveMoney($value['id']);
                $bonus[$key]['receive_money']=''.$receive_money;
                $bonus[$key]['status'] = $value['is_done'] == 1 ? '已领取完' : (!$value['finish_at'] ? '未领取完' : '已过期-未领取完');
            }
        }
        return ['total'=>$count,'list'=>$bonus];
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-12
     *
     * @description 抢口令
     * @param array $param
     * @param $page
     * @param $size
     * @return array
     */
    public function _receivePassword(array $param,int $page,int $size)
    {
        $where = ['receive_voice'=>['neq',''],'receive_uid'=>$param['openid']];
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
        }elseif (!empty($param['start_time'])) {
            $where['created_at'] = ['gt',$param['start_time']];
        }elseif (!empty($param['end_time'])) {
            $where['created_at'] = ['lt',$param['end_time']];
        }
        $bonuss = new BonusReceive();
        $detail = new BonusDetail();
        $b = new Bonus();
        $count = $bonuss->getMeGroupCount($where);
        $bonus = [];
        if ($count > 0) {
            $field = 'id,bonus_id,detail_id,balance,created_at';
            $bonus = $bonuss->searchBonusList($where,$page,$size,$field);
            foreach ($bonus as $key => $value) {
                $bonus[$key]['receive_money'] = $detail->getReceiveMoneyOne($value['detail_id']);
                $send_user = $b->bonusDerail($value['bonus_id']);
                $bonus[$key]['send_user'] = $send_user['uid'];
                $bonus[$key]['bonus_password'] = $send_user['bonus_password'];
            }
        }
        return ['total'=>$count,'list'=>$bonus];
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-12
     *
     * @description 抢语音
     * @param array $param
     * @param $page
     * @param $size
     * @return array
     */
    public function _receiveListen(array $param,int $page,int $size)
    {
        $where = ['receive_voice'=>'','receive_uid'=>$param['openid']];
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
        }elseif (!empty($param['start_time'])) {
            $where['created_at'] = ['gt',$param['start_time']];
        }elseif (!empty($param['end_time'])) {
            $where['created_at'] = ['lt',$param['end_time']];
        }
        $bonuss = new BonusReceive();
        $detail = new BonusDetail();
        $b = new Bonus();
        $count = $bonuss->getMeGroupCount($where);
        $bonus = [];
        if ($count > 0) {
            $field = 'id,bonus_id,detail_id,balance,created_at';
            $bonus = $bonuss->searchBonusList($where,$page,$size,$field);
            foreach ($bonus as $key => $value) {
                $bonus[$key]['receive_money'] = $detail->getReceiveMoneyOne($value['detail_id']);
                $send_user = $b->bonusDerail($value['bonus_id']);
                $bonus[$key]['send_user'] = $send_user['uid'];
                $bonus[$key]['voice_path'] = $send_user['voice_path'];
            }
        }
        return ['total'=>$count,'list'=>$bonus];
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-12
     *
     * @description 资金变动
     * @param array $param
     * @param int $page
     * @param int $size
     * @return array
     */
    public function _moneyChange(array $param, int $page,int $size)
    {
        $where = ['uid'=>$param['openid']];
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
        }elseif (!empty($param['start_time'])) {
            $where['created_at'] = ['gt',$param['start_time']];
        }elseif (!empty($param['end_time'])) {
            $where['created_at'] = ['lt',$param['end_time']];
        }
        $bill = new BillLog();
        $count = $bill->getMeGroupCount($where);
        $log = [];
        if ($count > 0) {
            $field = 'uid,type,affect_money,created_at';
            $log = $bill->searchBillList($where,$page,$size,$field);
            foreach ($log as $key => $value) {
                $log[$key]['type'] = $this->type[$value['type']];
                $log[$key]['affect_money'] = $value['affect_money'].'元';
            }
        }
        return ['total'=>$count,'list'=>$log];
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-12
     *
     * @description 金币变化
     * @param array $param
     * @param int $page
     * @param int $size
     * @return array
     */
    public function _coinChange(array $param,int $page,int $size)
    {
        $where = ['uid'=>$param['openid']];
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
        }elseif (!empty($param['start_time'])) {
            $where['created_at'] = ['gt',$param['start_time']];
        }elseif (!empty($param['end_time'])) {
            $where['created_at'] = ['lt',$param['end_time']];
        }
        $coin = new GameCoinLog();
        $count = $coin->getCount($where);
        $log = [];
        if ($count > 0) {
            $log = $coin->searchDetailRows($where,$page,$size);
            foreach ($log as $key => $value) {
                $log[$key]['type'] = $this->coin[$value['type']];
                $log[$key]['money'] = $value['symbol'] == 0 ? '-'.$value['coin'] : $value['coin'];
                $log[$key]['balance'] = $value['balance'].'金币';
            }
        }
        return ['total'=>$count,'list'=>$log];
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-13
     *
     * @description 封号/解封
     * @param array $param
     * @return array
     */
    public function forbidUser(array $param)
    {
        $user = new \app\user\model\User();
        $where = ['openid' => $param['openid'],'status'=>$param['status']];
        $data = $user->forbidUser($where);
        if ($data == 0) {
            return $this->ajaxError(207);
        }
        return $this->ajaxSuccess(201);
    }


}