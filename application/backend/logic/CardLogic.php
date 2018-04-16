<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/24
 * Time: 下午1:18
 */

namespace app\backend\logic;


use app\backend\model\Card;
use app\common\logic\BaseLogic;
use extend\service\WechatCard;
use think\Exception;

class CardLogic extends BaseLogic
{
    protected $card = null;
    function __construct()
    {
        $this->card = new Card();

    }

    /** 创建卡卷
     * auth smallzz
     * @param array $param
     * @return bool
     */
    public function createCard(){
        #调用卡卷接口
        $cardSer = new WechatCard();
        $id = 0;
        try{
            $res = $cardSer->createCard();
            if($res){
                $data = $res['data'];
                $data['card_id'] = $res['result']['card_id'];
                $id = $this->card->cardAdd($data);
            }
        }catch (Exception $exception){
            return false;
        }
        return $id;
    }

    /** 获取卡卷列表
     * auth smallzz
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCardList(){
        return $this->card->getCardSelect();
    }
}