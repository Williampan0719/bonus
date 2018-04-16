<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/6
 * Time: 09:32
 * @introduce
 */
namespace app\backend\controller;

use app\backend\logic\BonusLogic;
use app\payment\model\BonusReceive;
use think\Request;

class Bonus extends BaseAdmin
{
    protected $bonusLogic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->bonusLogic=new BonusLogic();
    }
    /**
     * @api {get} /backend/bonus/list 红包的列表
     * @apiGroup bonus
     * @apiName  list
     * @apiVersion 1.0.0
     * @apiParam {int} page  页数
     * @apiParam {int} size  页码
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/bonus/list
     * @apiSuccessExample {json} Response 200 Example
     * {}
     */
    public function bonusList()
    {
        $params=$this->request->param();
        $result=$this->bonusLogic->bonusList($params);
        return $result;
    }
    /**
     * @api {get} /backend/bonus/detail 红包的详情
     * @apiGroup bonus
     * @apiName  detail
     * @apiVersion 1.0.0
     * @apiParam {int} bonus_id 红包id
     * @apiParam {int} page  页数
     * @apiParam {int} size  页码
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/bonus/detail
     * @apiSuccessExample {json} Response 200 Example
     * {}
     */
    public function bonusDetail()
    {
        $params=$this->request->param();
        $result=$this->bonusLogic->bonusDetail($params);
        return $result;
    }

    /**
     * @api {get} /backend/bonus/adv-search 广告红包搜索
     * @apiGroup bonus
     * @apiName  adv-search
     * @apiVersion 1.0.0
     * @apiParam   {string} name 昵称
     * @apiParam   {string} openid openid
     * @apiParam   {string} start_time 开始时间
     * @apiParam   {string} end_time 结束时间
     * @apiParam {int} page  页数
     * @apiParam {int} size  页码
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/bonus/adv-search
     * @apiSuccessExample {json} Response 200 Example
     * {
     *"status": 1,
     *"message": "获取成功",
     *"data": {
     *  "total": 2,
     *  "list": [
     *         {
     *          "id": 1, // 红包id
     *          "uid": "ocJN_4jN7hgUaIrIkVC-fDhUX_SU", // 用户openid
     *          "type": 1, // 红包类型（0分享红包，1大厅红包）
     *          "bonus_money": "66.00", // 红包金额
     *          "bonus_num": 10, // 红包个数
     *          "bonus_password": "蒲公英", // 广告口号
     *          "created_at": "2018-02-08 22:09:17", // 生成时间
     *          "updated_at": "2018-02-06 22:09:17",
     *          "finish_at": null,
     *          "service_money": "0.00", // 服务费
     *          "refund_service_money": "0.00", // 退款服务费
     *          "is_pay": 1, //是否支付 0否1是
     *          "receive_bonus_num": "0/10", //已领/总数
     *          "form_id": "e4ade2abea3be5bb1ba28e7a6c25907b",
     *          "prepay_id": "",
     *          "class": 2,
     *          "voice_path": "",
     *          "is_done": 0,
     *          "timelength": 0,
     *          "voice_type": 0,
     *          "adv_name": "宁波", //广告名称
     *          "adv_logo": "http://pgy-hongbao.oss-cn-beijing.aliyuncs.com/pic/15179261111141023512.jpg", // 品牌logo
     *          "name": "执迷的鲸鱼", // 昵称
     *          "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/Q0j4wXtsoLCg/0" // 头像
     *          "receive_money": 22.94, //领取金额
     *          "refund_money": "0.00", // 退款金额
     *          "status": "未领取完" // 红包状态
     *         }
     *  ]
     *},
     *"code": 202
     *}
     */
    public function advBonusSearchRows()
    {
        $params=$this->request->param();
        $result=$this->bonusLogic->advBonusSearchRows($params);
        return $result;
    }

    /**
     * @api {get} /backend/bonus/adv-detail 广告红包搜索详情
     * @apiGroup bonus
     * @apiName  adv-detail
     * @apiVersion 1.0.0
     * @apiParam   {int} id 红包id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/bonus/adv-detail
     * @apiSuccessExample {json} Response 200 Example
     * {
     *"status": 1,
     *"message": "获取成功",
     *"data": {
     *  "list": {
     *           "text": "s:726:\"[{\"beij5985.jpg\"}]\";", // json包
     *           "view_num": 26 // 浏览量
     *          }
     *},
     *"code": 202
     *}
     */
    public function advBonusSearchDetail()
    {
        $params=$this->request->param();
        $result=$this->bonusLogic->advBonusSearchDetail($params);
        return $result;
    }

    /**
     * @api {get} /backend/bonus/del-remark 广告红包删除详情
     * @apiGroup bonus
     * @apiName  del-remark
     * @apiVersion 1.0.0
     * @apiParam   {int} bonus_id 红包id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/bonus/del-remark
     * @apiSuccessExample {json} Response 200 Example
     * {
     *"status": 1,
     *"message": "获取成功",
     *"data": {
     *  "list": {
     *           "text": "s:726:\"[{\"beij5985.jpg\"}]\";", // json包
     *           "view_num": 26 // 浏览量
     *          }
     *},
     *"code": 202
     *}
     */
    public function delAdvRemark()
    {
        $params=$this->request->param();
        $result=$this->bonusLogic->delAdvRemark($params);
        return $result;
    }
}