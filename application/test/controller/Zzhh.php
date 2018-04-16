<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/13
 * Time: 下午1:31
 */

namespace app\test\controller;


use app\backend\logic\BonusLogic;
use app\backend\logic\CardLogic;
use app\backend\model\Card;
use app\game\logic\GameBigwheelLogic;
use app\game\logic\GameSscLogic;
use app\game\model\GameCoinLog;
use app\game\model\GameSsc;
use app\game\model\GameSscTo;
use app\payment\logic\BonusHallLogic;
use app\payment\model\BonusHall;
use app\payment\model\Wallet;
use extend\helper\Curl;
use extend\helper\Utils;
use extend\service\RedisService;
use extend\service\WechatCard;
use extend\service\WechatService;
use think\db\Query;

class Zzhh
{
    private $bonus = null;
    private $redis = null;
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
        $this->bonus = new \app\payment\logic\BonusLogic();
        $this->redis = new RedisService();
    }

    public function gets(){

        $hongbId = explode('-', '');
        var_dump($hongbId);
        if (empty($hongbId)) {
            echo 1;
        }
    }
    //模拟发红包
    public function lins(){
        #$this->redis->expire('hallbonus',10);
        $bonusParams['bonus_num'] = 11;
        $bonusParams['bonus_money'] = 22;
        $bonusParams['bonus_password'] = '手动口令';
        $bonusParams['uid'] = 'okPcX0QZTrJpOtnER2ZDOncg5SVU';
        $bonusParams['type'] = 1;
        $bonusParams['form_id']='000';
        $res = $this->bonus->bonusAdd($bonusParams);
        $bonusDetail = $this->bonus->bonusDistribution($res, 22);



        var_dump($res);

    }
    public function getHall(){
        $list = $this->redis->lrange('hallbonus',0,-1);
        foreach ($list as $k=>$v){
            $res = json_decode($v,true);
            $lists[$k]['bonus_id'] = $res['bonus_id'];
            $lists[$k]['bonus_password'] = $res['bonus_password'];
            $lists[$k]['nickname'] = $res['nickname'];
        }
        return $lists;
    }
    public function mm(){
        $query = new Query();
        $list = $query->table('wx_payment_bonus_detail')->alias('bd')
            ->join('wx_payment_bonus_receive br','br.detail_id = bd.id','inner')
            ->join('wx_user u','u.openid = br.receive_uid','inner')
            ->where('br.created_at >= "'.date('Y-m-d').' 00:00:00" and br.created_at <= "'.date('Y-m-d').' 23:59:59"')
            ->field('u.nickname,bd.receive_money,u.avatarulr')
            ->order('bd.receive_money desc')
            ->limit(10)
            ->select();
        var_dump($query->getLastSql());exit;
    }
    public function sorts(){
        $new = new BonusHallLogic();
        $a = $new->getHalls(1);
        var_dump($a);
    }
    public function getsql(){
        $query = new Query();
        $list = $query->table('wx_payment_bonus_hall')->alias('ah')
            ->join('wx_payment_bonus pb','pb.id = ah.bonus_id','inner')
            ->join('wx_user u','u.openid = ah.uid','inner')
            ->Field('ah.bonus_id,u.nickname,u.avatarulr,pb.bonus_num,pb.bonus_password,ah.uid')
            ->order('pb.finish_at asc,pb.id desc')
            ->page(0,20)
            ->select();
        var_dump($query->getLastSql());
    }
    public function card(){
        $new = new WechatCard();
        #$img = $new->createCard();
        $img =  $new->getTicket();
        //$img = $new->uploadImg();
        var_dump($img);

    }
    public function cardAdd(){
        $new  =  new CardLogic();
        $res = $new->createCard();
        var_dump($res);
    }
    public function getCardList(){
        $new  =  new CardLogic();
        $list = $new->getCardList();
        foreach ($list as $k=>$v){

        }
    }
    public function smallCard(){
        $new = new WechatCard();
        $res = $new->cardSmall();
        var_dump($res);
    }
    public function cardLog(){
        $new = new WechatCard();
        $url = "https://api.weixin.qq.com/card/update?access_token=".$new->getAToken();
        /*$data = '{ "card": {
            "card_type": "CASH",
            "cash": {
                        "base_info": {
                            "custom_url_name": "立即使用",
                    "custom_url": "wxapitest.pgyxwd.com/test/Zzhh/cardLog",
                    "custom_app_brand_user_name": "gh_3f467cae5c9f@app",
                    "custom_app_brand_pass":"pages/hall/hall",
                    "custom_url_sub_title": "6个汉字tips",
                    "promotion_url_name": "更多优惠",
                    "promotion_url": "http://www.qq.com",
                    "promotion_app_brand_user_name": "gh_3f467cae5c9f@app",
                    "promotion_app_brand_pass":"pages/hall/hall"        }
                }
        }';*/
        $data = '{
                    "card_id":"pJTM6xJbKO6kKNfDqxZn6_An8I_Q",
                    "cash": {
                            "base_info": {
                                    "custom_url_name": "小程序",
                                    "custom_url": "http://www.qq.com",
                                    "custom_app_brand_user_name": "gh_3f467cae5c9f@app",
                                    "custom_app_brand_pass":"pages/hall/hall",
                                    "custom_url_sub_title": "点击进入",
                                    "promotion_url_name": "更多信息",
                                    "promotion_url": "http://www.qq.com",
                                    "promotion_app_brand_user_name": "gh_3f467cae5c9f@app",
                                    "promotion_app_brand_pass":"pages/hall/hall"
                            }
                    }
                }';
        #$result = Curl::postJson($url,json_encode($data,JSON_UNESCAPED_UNICODE));
        $result = Curl::postJson($url,$data);
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/tmp/cardlog.txt',var_export($result)."\r\n",FILE_APPEND);
        echo "卡劵升级完成";
    }
    public function getCardlists(){
        //$new = new WechatCard();
        $card = new Card();
        $wechatser = new WechatService();
        //$res = $new->getCard();
        $list = $card->getCardSelect('card_id');
        $data = [];
        foreach ($list as $k=>$v){
            $time = (string)time();
            $nonce = $wechatser->_getNonceStr();
            $tick = $wechatser->getTicket();
            #var_dump([$time,$nonce,$tick,$v['card_id']]);
            #生成对象给前端
            $data[$k]['cardId'] = $v['card_id'];
            $data[$k]['cardExt'] = [
                'timestamp'=>$time,
                'nonce_str'=>$nonce,
                'signature'=>$wechatser->_signature([$time,$nonce,$tick,$v['card_id']])
            ];
        }
        var_dump(json_encode($data));
    }
    public function getopenidcrd(){
        $new = new WechatCard();
        $res = $new->getOpenidCard();
        var_dump($res);
    }
    public function zz(){
        $share_id = 1;
        $new = new Query();
        $list = $new->table('wx_user_card_reveive')->alias('cr')
            ->join('wx_user u','u.openid = cr.uid','inner')
            ->where(['cr.share_id'=>$share_id])
            ->field('u.nickname,u.avatarulr,cr.created_at')
            ->select();
        var_dump($new->getLastSql());
        exit;
        $list = $new->table('wx_user_card_share')->alias('cs')
            ->join('wx_card c','c.id = cs.card_id','inner')
            ->join('wx_user u','u.openid = cs.uid','inner')
            ->where(['cs.id'=>$share_id])
            ->field('c.brand_name,c.title,u.nickname,u.avatarulr')
            ->find();
        var_dump($new->getLastSql());
       // return $list;
    }
    public function geti(){
        $query = new Query();
        $query->table('wx_payment_wallet')->where(['uid'=>'ok4Em0YQmR41X1Q2Sn9MepZqodyY'])->setInc('balance',0.1);
        echo $query->getLastSql();
        exit;
        $new = new GameBigwheelLogic();
        $arr = $new->getList('ok4Em0YQmR41X1Q2Sn9MepZqodyY');
        var_dump($arr);
    }
    public function popr(){
        $new  =  new GameBigwheelLogic();
        $res = $new->popRate('ok4Em0YQmR41X1Q2Sn9MepZqodyY');
        var_dump($res);
    }
    public function getlist(){
        $info = $this->ssc->getInfo(49,'uid,yuid,coin,result');
        #押注结果（0未出结果，1平局，2胜利，3失败）
        switch ($info['result']){
            case 1:
                #平局退钱
                $this->wallet->setVirtual($info['uid'],$info['coin']);
                $this->gamelog->addLog($info['uid'],10,$info['coin'],1);  #添加记录
                $this->wallet->setVirtual($info['yuid'],$info['coin']);
                $this->gamelog->addLog($info['yuid'],4,$info['coin'],1);  #添加记录
                return true;
            case 2:
                #挑战者胜利退钱
                $this->wallet->setVirtual($info['yuid'],$info['coin']*2);
                $this->gamelog->addLog($info['yuid'],4,$info['coin'],1);  #添加记录
                return true;
            case 3:
                #发起者胜利退钱
                $this->wallet->setVirtual($info['uid'],$info['coin']*2);
                $this->gamelog->addLog($info['uid'],10,$info['coin'],1);  #添加记录
                return true;
        }
    }
}