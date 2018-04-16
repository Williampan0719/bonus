<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/26
 * Time: 上午9:58
 */

namespace app\api\controller;


use app\user\logic\CardLogic;
use think\Request;

class Card extends BaseApi
{
    private $card = null;
    function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->card = new CardLogic();
    }
    public function getCardInfo(){

    }
    /**
     * @api {post} /api/card/card-list 小程序端添加卡卷
     * @apiGroup card
     * @apiName  card-list
     * @apiVersion 1.0.0
     * @apiParam   {string} uid 用户的uid
     * @apiParam   {string} card_id 卡劵id
     * @apiParam   {string} share_id 分享id
     * @apiParam   {string} code 卡劵code
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/card/card-list
     * @apiSuccessExample {json} Response 200 Example
     * {}
     */
    public function cardCreate(){
        $result = $this->card->getCardlists();
        return $result;
    }
    /**
     * @api {post} /api/card/card-receive 小程序端领取卡劵
     * @apiGroup card
     * @apiName  card-receive
     * @apiVersion 1.0.0
     * @apiParam   {string} uid 用户的uid
     * @apiParam   {string} card_id 卡劵id
     * @apiParam   {string} share_id 分享id
     * @apiParam   {string} code 卡劵code
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/card/card-receive
     * @apiSuccessExample {json} Response 200 Example
     * {}
     */
    public function cardRevice(){
        $param = $this->request->param();
        return $this->card->getCardRevice($param);

    }
    /**
     * @api {post} /api/card/card-sharein 小程序卡劵领取详情
     * @apiGroup card
     * @apiName  card-sharein
     * @apiVersion 1.0.0
     * @apiParam   {string} share_id 分享id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/card/card-sharein
     * @apiSuccessExample {json} Response 200 Example
     * {}
     */
    public function getShareRe(){
        $param = $this->request->param();
        return $this->card->shareReceive($param);

    }
    /**
     * @api {post} /api/card/card-use 卡劵核销
     * @apiGroup card
     * @apiName  card-use
     * @apiVersion 1.0.0
     * @apiParam   {string} uid 用户的uid
     * @apiParam   {string} card_id 卡劵id
     * @apiParam   {string} code 卡劵code
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/card/card-use
     * @apiSuccessExample {json} Response 200 Example
     * {}
     */
    public function useCardConsume(){
        $param = $this->request->param();
        return $this->card->cardConsume($param);
    }
}