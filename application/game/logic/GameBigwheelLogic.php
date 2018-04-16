<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/27
 * Time: 下午3:38
 */

namespace app\game\logic;


use app\common\logic\BaseLogic;
use app\game\model\GameCoinLog;
use app\payment\model\Wallet;
use app\user\model\UserSignin;
use extend\helper\Utils;
use think\Db;
use think\Exception;

class GameBigwheelLogic extends BaseLogic
{
    private $wallet = null;
    private $prize_arr = [];
    private $gamelog = null;
    private $config = [];
    private $signin = null;
    function __construct()
    {
//        $this->prize_arr = array(
//            0 => array('id'=>1,'prize'=>'谢谢','v'=>40),
//            1 => array('id'=>2,'prize'=>'8金币','v'=>10),
//            2 => array('id'=>3,'prize'=>'88金币','v'=>5),
//            3 => array('id'=>4,'prize'=>'100元','v'=>0),
//            4 => array('id'=>5,'prize'=>'0.1元','v'=>40),
//            5 => array('id'=>6,'prize'=>'1元','v'=>5),
//            6 => array('id'=>7,'prize'=>'10元','v'=>0),
//            7 => array('id'=>8,'prize'=>'1000元','v'=>0),
//        );
        $this->prize_arr = Utils::getBwheelConfig();
        $this->config = [
            'oncoin'=>20, //每次消耗
            'snum'=>8,    //开始8个金币
            'enum'=>50,   //最大50个
        ];
        $this->wallet = new Wallet();
        $this->gamelog = new GameCoinLog();
        $this->signin = new UserSignin();
    }

    /** 签到
     * auth smallzz
     * @param $uid
     * @return array
     */
    public function signIn(string $uid){
        $one = $this->signin->getOne(['uid'=>$uid]);
        $coin_log = new GameCoinLog();
        //从未签到过
        if (empty($one)) {
            $a = $this->signin->initOne(['uid'=>$uid,'con_num'=>$this->config['snum'],'con_time'=>date('Y-m-d')]);
            $this->wallet->setVirtual($uid,$this->config['snum']);
            //加明细
            $balance = $this->wallet->getVirtual($uid);
            $coin_log->addLog($uid,8,$this->config['snum'],1,$balance);
            return $this->ajaxSuccess(1808,['coin'=>$this->config['snum']]);
        }else{
            $today = $this->signin->getOne(['uid'=>$uid,'con_time'=>date('Y-m-d')]);
            //未签到
            if (empty($today)) {
                $coin = $one['con_num'] + 1;
                if ($one['con_num'] >= $this->config['enum']) {
                    $coin = $this->config['enum'];
                }
                $this->signin->editOne(['uid'=>$uid,'con_time'=>date('Y-m-d'),'con_num'=>$coin]);
                $this->wallet->setVirtual($uid,$coin);
                //加明细
                $balance = $this->wallet->getVirtual($uid);
                $coin_log->addLog($uid,8,$coin,1,$balance);
                return $this->ajaxSuccess(1808,['coin'=>$coin]);
            }
            return $this->ajaxSuccess(99,[],'您已签到');
        }

    }

    /**
     * auth smallzz
     * @param string $uid
     * @return array
     */
    public function getList(string $uid){
        #是否可以签到
        $result = $this->signin->getOne(['uid'=>$uid,'con_time'=>date('Y-m-d')]);
        //默认未签到
        $signin = 0;
        if(!empty($result)){
            $signin = 1;
        }
        $one = $this->signin->getOne(['uid'=>$uid]);
        if (empty($one)) {
            $one['con_num'] = $this->config['snum'] - 1;
            $one['con_time'] = '1970-01-01';
        }
        $nums = $one['con_num'] - $this->config['snum'] + 1;
        if ($one['con_time'] == date('Y-m-d')) {
            $one['con_num'] = $one['con_num'] - 1;
        }
        #获取虚拟币
        $virtual = $this->wallet->getVirtual($uid);
        return $this->ajaxSuccess(109,['prize'=>$this->prize_arr,'coin'=>$virtual,'signin'=>$signin,'daynum'=>$one['con_num'] + 1,'signin_num'=>$nums]);
    }

    /** 抽奖
     * auth smallzz
     * @param string $uid
     * @return array
     */
    public function popRate(string $uid){
        $virtual = $this->wallet->getVirtual($uid);
        if($virtual < $this->config['oncoin']){
            return $this->ajaxError(1800);
        }
        Db::startTrans();
        try{
            #减虚拟币
            $this->wallet->delVirtual($uid,$this->config['oncoin']);
            $this->gamelog->addLog($uid,5,$this->config['oncoin'],0,$virtual-$this->config['oncoin']);  #添加记录
            foreach ($this->prize_arr as $key => $val) {
                $arr[$val['id']] = $val['v'];
            }
            $rid = $this->get_rand(array_values($arr)); //根据概率获取奖项id
            $res['yes'] = $this->prize_arr[$rid]; //中奖项
            $this->dataSet($res['yes'],$uid); //中奖
        }catch (Exception $exception){
            Db::rollback();
            return $this->ajaxError(110);
        }
        Db::commit();
        return $this->ajaxSuccess(109,$res['yes']);
    }

    /** 明细
     * auth smallzz
     * @param array $param
     * @return array
     */
    public function detail_list(array $param){
        $page = $param['page'] ?? config('paginate.default_page');
        $size = $param['size'] ?? config('paginate.default_size');
        $list = $this->gamelog->listLog($param['uid'],$page,$size);
        return $this->ajaxSuccess(109,['data'=>$list]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-11
     *
     * @description 概率
     * @param $proArr
     * @return int|string
     */
    public function get_rand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        $randNum = mt_rand(1, $proSum);
        $temp = 0;
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $temp = $temp + $proCur;

            if ($randNum <= $temp) {
                $result = $key;
                break;
            }
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-11
     *
     * @description 获奖
     * @param $val
     * @param $uid
     * @return bool
     */
    public function dataSet($val,$uid){
        $virtual = $this->wallet->getVirtual($uid);

        if ($val['reward'] > 0) {
            if ($val['unit'] == 1) {
                $this->wallet->setBalance($uid,$val['reward']);
                $this->gamelog->addLog($uid,6,$val['reward'].'元',1,$virtual);  #添加记录
            }else{
                $this->wallet->setVirtual($uid,$val['reward']);
                $this->gamelog->addLog($uid,6,$val['reward'],1,$virtual+$val['reward']);  #添加记录
            }
        }
        return true;
    }
}