<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/26
 * Time: 上午10:00
 */

namespace app\user\logic;


use app\backend\model\Card;
use app\common\logic\BaseLogic;
use app\user\model\UserCardReceive;
use app\user\model\UserCardShare;
use extend\service\WechatCard;
use extend\service\WechatService;
use think\Exception;

class CardLogic extends BaseLogic
{
    protected $card = null;
    protected $cardrecevie = null;
    protected $wechatcard = null;
    function __construct()
    {
        $this->card = new Card();
        $this->cardrecevie = new UserCardReceive();
        $this->wechatcard = new WechatCard();
    }
    /** 卡卷添加生成object
     * auth smallzz
     * @return array
     */
    public function getCardlists(){
        $card = new Card();
        $wechatser = new WechatService();
        $list = $card->getCardSelect('card_id');
        $data = [];
        foreach ($list as $k=>$v){
            $time = (string)time();
            $nonce = $wechatser->_getNonceStr();
            $tick = $wechatser->getTicket();
            #生成对象给前端
            $data[$k]['cardId'] = $v['card_id'];
            $data[$k]['cardExt'] =  [
                "timestamp"=>$time,
                "nonce_str"=>$nonce,
                "signature"=>$wechatser->_signature([$time,$nonce,$tick,$v["card_id"]])
            ];
        }
        return $this->ajaxSuccess(102,$data);
    }

    /** 领取记录添加
     * auth smallzz
     * @param array $param [uid,card_id,share_id,code]
     * @return mixed
     */
    public function getCardRevice(array $param){
        $data = $this->_arrComb($param);
        $insert = [];
        $data_ = [];
        foreach ($data as $k=>$v){
            $ucr = new UserCardReceive();
            $insert['code'] = $this->wechatcard->deCode($v['code']);
            if(!$insert['code']){
                return $this->ajaxError(1703);
            }
            $insert['uid'] = $v['uid'];
            $insert['card_id'] = $v['card_id'];
            $insert['share_id'] = $v['share_id'];
            $ucr->cardReceiveAdd($insert);
            $data_[$k]['cardId'] = $v['card_id'];
            $data_[$k]['code'] = $insert['code'];
        }
        return $this->ajaxSuccess(1705,$data_);
    }

    /** 多个卡劵操作
     * auth smallzz
     * @param array $array
     * @return array
     */
    private function _arrComb(array $array){
        $card_id = json_decode($array['card_id'],true);

        $code = json_decode($array['code'],true);
        $uid = $array['uid'];
        $share_id = $array['share_id'] ?? 0;
        $data = [];
        foreach ($card_id as $k=>$v){
            $data[$k]['share_id'] = $share_id;
            $data[$k]['uid'] = $uid;
            $data[$k]['card_id'] = $v;
            $data[$k]['code'] = $code[$k];
        }
        return $data;
    }
    /** 卡劵核销
     * auth smallzz
     * @param array $param
     * @return array
     */
    public function cardConsume(array $param){
        try{
            $result = $this->wechatcard->checkCode($param['card_id'],$param['code']);
            if(!$result){
                return $this->ajaxError(1701);
            }
            $cardres = $this->wechatcard->consume($param['card_id'],$param['code']);
            if(!$cardres){
                return $this->ajaxError(1701);
            }
            #设置状态为使用
            $this->cardrecevie->save(['status'=>time()],['uid'=>$param['uid'],'card_id'=>$param['card_id'],'code'=>$param['code']]);
        }catch (Exception $exception){
            return $this->ajaxError(110);
        }
        return $this->ajaxSuccess(1702);
    }

    /** 创建分享
     * auth smallzz
     * @param array $param
     * @return bool
     */
    public function cardShare(array $param){
        try{
            $share = new UserCardShare();
            $shaerid = $share->cardShareAdd($param);
        }catch (Exception $exception){
            return false;
        }
        return $shaerid;
    }

    /** 分享领取详情
     * auth smallzz
     * @param array $param
     * @return array
     */
    public function shareReceive(array $param){
        $ucshare = new UserCardShare();
        $list = $this->cardrecevie->getShare($param['share_id']);
        $info = $ucshare->getCardInfo($param['share_id']);
        $lists = ['info'=>$info,'list'=>$list];
        return $this->ajaxSuccess(109,$lists);
    }

}