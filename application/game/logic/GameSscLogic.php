<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/22
 * Time: 下午3:41
 */

namespace app\game\logic;


use app\common\logic\BaseLogic;
use app\game\model\GameCoinLog;
use app\game\model\GameSsc;
use app\game\model\GameSscTo;
use app\payment\model\Wallet;
use app\user\model\User;
use app\user\model\UserSignin;
use extend\helper\Files;
use extend\helper\Utils;
use extend\service\RedisService;
use extend\service\WechatService;
use think\Db;
use think\Exception;

class GameSscLogic extends BaseLogic
{
    private $ssc = null;
    private $sscto = null;
    private $wallet = null;
    private $gamelog = null;
    private $virtual = [];
    private $xiaohao = [];
    function __construct()
    {
        $this->ssc = new GameSsc();
        $this->sscto = new GameSscTo();
        $this->wallet = new Wallet();
        $this->gamelog = new GameCoinLog();
        $this->virtual = Utils::getVirtualConfig();
        $this->xiaohao = [
            'guanzhan' => 10
        ];
    }

    /** 获取列表未出结果的pk
     * auth smallzz
     * @return array
     */
    public function getList(){
        try{
            $list = $this->ssc->getSscList();
        }catch (Exception $exception){
            return $this->ajaxError(110);
        }
        return $this->ajaxSuccess(109,$list);
    }
    /** 发起挑战
     * auth smallzz
     * @param array $param
     * @return array
     */
    public function createSsc(array $param){
        Db::startTrans();
        if($param['coin'] < $this->xiaohao['guanzhan']){
            //挑战金额太小
            return $this->ajaxError(1804);
        }
        $yue = $this->wallet->getVirtual($param['uid']);
        if($yue < $param['coin']){
            //金币不足
            return $this->ajaxError(1800);
        }
        try{
            $id = $this->ssc->sscAdd($param);
            #减虚拟币
            $this->wallet->delVirtual($param['uid'],$param['coin']);
            $this->gamelog->addLog($param['uid'],0,$param['coin'],0,$yue-$param['coin']);  #添加记录
        }catch (Exception $exception){
            Db::rollback();
            return $this->ajaxError(106);
        }
        Db::commit();
        return $this->ajaxSuccess(100,['id'=>$id]);
    }

    /** 挑战开始
     * auth smallzz
     * @param array $param
     * @return array
     */
    public function createSscTo(array $param){
        Db::startTrans();
        try{
            #获取发起者信息
            $info = $this->ssc->getInfo($param['ssid'],'val,result,coin');
            if(!empty($info['result'])){
                return $this->ajaxError(1801);
            }
            switch ($param['type']){
                case 1:   #果断应战
                    $virtual = $this->wallet->getVirtual($param['uid']);
                    if($virtual < $info['coin']){
                        //余额不足
                        return $this->ajaxError(1800);
                    }
                    $result = $this->_contrast($info['val'],$param['val']);
                    $this->sscto->save([
                            'coin'=>$info['coin'],
                            'type'=>$param['type'],
                            'result'=>$result
                        ],['ssid'=>$param['ssid'],'uid'=>$param['uid']]);
                    $result_s = 1;
                    if($result == 3){
                        $result_s = 2;
                    }elseif ($result == 2){
                        $result_s = 3;
                    }
                    $this->ssc->save(['result'=>$result_s,'yuid'=>$param['uid']],['id'=>$param['ssid']]);
                    #减虚拟币
                    $this->wallet->delVirtual($param['uid'],$info['coin']);
                    $this->gamelog->addLog($param['uid'],3,$info['coin'],0,$virtual-$info['coin']);  #添加记录
                    Db::commit();
                    #这里出正面刚结果
                    $res = $this->_resultPk($param['ssid']);
                    if($res === false){  #卧槽，居然不退钱
                        Db::rollback();
                        return $this->ajaxError(1803);
                    }
                    #这里出观战结果
                    $res = $this->_resultWatchPk($param['ssid']);
                    if($res === false){  #卧槽，居然不退钱
                        Db::rollback();
                        return $this->ajaxError(1803);
                    }
                    break;
                case 2:   #押注观战
                    $virtual = $this->wallet->getVirtual($param['uid']);

                    if($virtual < $this->xiaohao['guanzhan']){
                        //余额不足
                        return $this->ajaxError(1800);
                    }
                    $result = $this->_contrast($info['val'],$param['val']);
                    $this->sscto->save([
                        'coin'=>$this->xiaohao['guanzhan'],
                        'type'=>$param['type'],
                        'result'=>$result
                    ],['ssid'=>$param['ssid'],'uid'=>$param['uid']]);

                    #减虚拟币
                    $this->wallet->delVirtual($param['uid'],$this->xiaohao['guanzhan']);
                    $this->gamelog->addLog($param['uid'],1,$this->xiaohao['guanzhan'],0,$virtual-$this->xiaohao['guanzhan']);  #添加记录
                    Db::commit();
                    break;
                default:  #默认进入
                    $co = $this->sscto->where(['ssid'=>$param['ssid'],'uid'=>$param['uid']])->count();
                   # var_dump($co);exit;
                    if(empty($co)){
                        $this->sscto->save([
                            'ssid'=>$param['ssid'],
                            'uid'=>$param['uid'],
                            'val'=>$param['val'],
                        ]);
                    }else{
                        $this->sscto->save(['val'=>$param['val']],['ssid'=>$param['ssid'],'uid'=>$param['uid']]);
                    }
                    Db::commit();
                    break;
            }
        }catch (Exception $exception){
            Db::rollback();
            return $this->ajaxError(1803);
        }
        return $this->ajaxSuccess(1802);
    }

    /** 获取我参与的挑战
     * auth smallzz
     * @param array $param
     */
    public function MyListTo(array $param){
        $this->sscto->getMyListTo($param['uid']);
    }
    public function pkList(array $param){
        $this->virtual;
    }

    /** 获取此挑战的详情
     * auth smallzz
     * @param int $ssid
     * @return array
     */
    public function getVirtualInfo(array $param){

        $info = $this->ssc->getVirtualDone($param['ssid'],$param['uid']);
        if($info == false){
            return $this->ajaxError(1810);
        }
        return $this->ajaxSuccess(109,$info);
    }
    /** 判断胜负
     * auth smallzz
     * @param $createz 创建者选择的
     * @param $tzhanz  挑战者选择的
     */
    private function _contrast(int $createz,int $tzhanz){
        $arr = [1=>"石头",2=>"剪刀",3=>"布"];
        $guize = [
            ["石头","剪刀"],
            ["剪刀","布"],
            ["布","石头"]
        ];
        $con = [$arr[$tzhanz],$arr[$createz]];
        #押注结果（0未出结果，1平局，2胜利，3失败）
        if($arr[$createz] == $arr[$tzhanz]){
            return 1;
        }elseif(in_array($con,$guize)){
            return 2;
        }else{
            return 3;
        }

    }

    /** 结果匹配退款 庄家
     * auth smallzz
     * @param int $ssid
     * @return bool
     */
    private function _resultPk(int $ssid){
        try{
            $redis = new RedisService();
            $we = new WechatService();
            $info = $this->ssc->getInfo($ssid,'uid,yuid,coin,result');
            $user = new User();
            $nick1 = $user->userDetail($info['uid']);
            $nick2 = $user->userDetail($info['yuid']);
            $virtual = $this->wallet->getVirtual($info['uid']);
            $virtual2 = $this->wallet->getVirtual($info['yuid']);
            $tpl = '';
            #押注结果（0未出结果，1平局，2胜利，3失败）
            switch ($info['result']){
                case 1:
                    $this->wallet->setVirtual($info['uid'],$info['coin']);
                    $this->gamelog->addLog($info['uid'],11,$info['coin'],1,$virtual+$info['coin']);  #添加记录
                    $this->wallet->setVirtual($info['yuid'],$info['coin']);
                    $this->gamelog->addLog($info['yuid'],11,$info['coin'],1,$virtual2+$info['coin']);  #添加记录
                    #平局退钱
                    $tpl = [
                        'type' => 'gameResult',
                        'page' => 'pages/pk/pk?ssid='.$ssid,
                        'form_id' => $redis->lpop($info['uid']),
                        'openid' => $info['uid'],
                        'key1' => $nick1['nickname'].'VS'.$nick2['nickname'],
                        'key2' => '平局',
                        'key3' => $info['coin'],
                    ];
                    $we->tplSend($tpl);
                    #平局退钱
                    $tpl2 = [
                        'type' => 'gameResult',
                        'page' => 'pages/pk/pk?ssid='.$ssid,
                        'form_id' => $redis->lpop($info['yuid']),
                        'openid' => $info['yuid'],
                        'key1' => $nick2['nickname'].'VS'.$nick1['nickname'],
                        'key2' => '平局',
                        'key3' => $info['coin'],
                    ];
                    $we->tplSend($tpl2);
                    return true;
                case 2:
                    #发起者胜利退钱
                    $this->wallet->setVirtual($info['uid'],$info['coin']*2);
                    $this->gamelog->addLog($info['uid'],10,$info['coin']*2,1,$virtual+$info['coin']*2);  #添加记录
                    #胜利退钱
                    $tpl = [
                        'type' => 'gameResult',
                        'page' => 'pages/pk/pk?ssid='.$ssid,
                        'form_id' => $redis->lpop($info['uid']),
                        'openid' => $info['uid'],
                        'key1' => $nick1['nickname'].'VS'.$nick2['nickname'],
                        'key2' => '胜利',
                        'key3' => $info['coin']*2,
                    ];
                    $we->tplSend($tpl);
                    #失败退钱
                    $tpl2 = [
                        'type' => 'gameResult',
                        'page' => 'pages/pk/pk?ssid='.$ssid,
                        'form_id' => $redis->lpop($info['yuid']),
                        'openid' => $info['yuid'],
                        'key1' => $nick2['nickname'].'VS'.$nick1['nickname'],
                        'key2' => '失败',
                        'key3' => 0,
                    ];
                    $we->tplSend($tpl2);
                    return true;
                case 3:
                    #挑战者胜利退钱
                    $this->wallet->setVirtual($info['yuid'],$info['coin']*2);
                    $this->gamelog->addLog($info['yuid'],4,$info['coin']*2,1,$virtual2+$info['coin']*2);  #添加记录
                    #胜利退钱
                    $tpl = [
                        'type' => 'gameResult',
                        'page' => 'pages/pk/pk?ssid='.$ssid,
                        'form_id' => $redis->lpop($info['uid']),
                        'openid' => $info['uid'],
                        'key1' => $nick1['nickname'].'VS'.$nick2['nickname'],
                        'key2' => '失败',
                        'key3' => 0,
                    ];
                    $we->tplSend($tpl);
                    #失败退钱
                    $tpl2 = [
                        'type' => 'gameResult',
                        'page' => 'pages/pk/pk?ssid='.$ssid,
                        'form_id' => $redis->lpop($info['yuid']),
                        'openid' => $info['yuid'],
                        'key1' => $nick2['nickname'].'VS'.$nick1['nickname'],
                        'key2' => '胜利',
                        'key3' => $info['coin']*2,
                    ];
                    $we->tplSend($tpl2);
                    return true;
            }
        }catch (Exception $exception){
            return false;
        }
        return true;
    }

    /** 结果匹配退钱哟 闲家（看戏的人）
     * auth smallzz
     * @param int $ssid
     * @return bool
     */
    private function _resultWatchPk(int $ssid){
        try{
            $redis = new RedisService();
            $we = new WechatService();
            $user = new User();
            $list = $this->sscto->where(['ssid'=>$ssid,'type'=>2])->field('uid,coin,result')->select();
            if(!empty($list)){
                //发起者
                $info = $this->ssc->getInfo($ssid,'uid');
                foreach ($list as $k=>$v){
                    $virtual = $this->wallet->getVirtual($v['uid']);
                    $nick = $user->userDetail($v['uid']);
                    $nick2 = $user->userDetail($info['uid']);
                    if($v['result'] == 1){
                        $this->wallet->setVirtual($v['uid'],$v['coin']);
                        $this->gamelog->addLog($v['uid'],11,$v['coin'],1,$virtual+$v['coin']);
                        #平局退钱
                        $tpl = [
                            'type' => 'gameResult',
                            'page' => 'pages/pk/pk?ssid='.$ssid,
                            'form_id' => $redis->lpop($v['uid']),
                            'openid' => $v['uid'],
                            'key1' => $nick['nickname'].'VS'.$nick2['nickname'],
                            'key2' => '平局',
                            'key3' => $v['coin'],
                        ];
                        $we->tplSend($tpl);
                    }elseif($v['result'] == 2){
                        #胜利退钱
                        $tpl = [
                            'type' => 'gameResult',
                            'page' => 'pages/pk/pk?ssid='.$ssid,
                            'form_id' => $redis->lpop($v['uid']),
                            'openid' => $v['uid'],
                            'key1' => $nick['nickname'].'VS'.$nick2['nickname'],
                            'key2' => '胜利',
                            'key3' => $v['coin']*2,
                        ];
                        $we->tplSend($tpl);
                        $this->wallet->setVirtual($v['uid'],$v['coin']*2);
                        $this->gamelog->addLog($v['uid'],2,$v['coin']*2,1,$virtual+$v['coin']*2);
                    }elseif($v['result'] == 3){
                        #胜利退钱
                        $tpl = [
                            'type' => 'gameResult',
                            'page' => 'pages/pk/pk?ssid='.$ssid,
                            'form_id' => $redis->lpop($v['uid']),
                            'openid' => $v['uid'],
                            'key1' => $nick['nickname'].'VS'.$nick2['nickname'],
                            'key2' => '失败',
                            'key3' => 0,
                        ];
                        $we->tplSend($tpl);
                    }
                }
            }
        }catch (Exception $exception){
            return false;
        }
        return true;
    }

    /**
     * auth smallzz
     * @param string $uid
     * @return array
     */
    public function getYuE(string $uid){
        $yue = $this->wallet->getVirtual($uid);
        $sign = new UserSignin();
        $num = $sign->getOne(['uid'=>$uid,'con_time'=>date('Y-m-d')]);
        $list['yue'] = $yue;
        $list['sign'] = !empty($num) ? 1 : 0;
        return $this->ajaxSuccess(109,['list'=>$list]);
    }

    /** 是否参与过
     * auth smallzz
     * @param string $uid
     * @param int $ssid
     * @return array
     */
    public function getIsToIn(string $uid,int $ssid){

        $res = $this->sscto->getSscTo($uid,$ssid);
        switch ($res){
            case 1:
                return $this->ajaxError(1809);
            case 8:
                return $this->ajaxError(1811);
            case 0:
                return $this->ajaxSuccess(109);
        }

    }
}