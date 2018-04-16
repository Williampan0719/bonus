<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/6
 * Time: 11:17
 * @introduce
 */

namespace app\backend\controller;

use app\backend\logic\OrderLogic;
use think\Request;

class Order extends BaseAdmin
{
    protected $orderLogic;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->orderLogic = new OrderLogic();
    }
    /**
     * @api {get} /backend/order/recharge 充值列表
     * @apiGroup order
     * @apiName  recharge
     * @apiVersion 1.0.0
     * @apiParam {int} page  页数
     * @apiParam {int} size  页码
     * @apiParam {string} name 昵称
     * @apiparam {string} start_time 查询开始时间
     * @apiparam {string} end_time 查询结束时间
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/order/recharge
     * @apiSuccessExample {json} Response 200 Example
     * {
     * "status": 1,
     * "message": "获取成功",
     * "data": {
     *      "list": [
     *          {
     *              "id": 215,
     *              "order_sn": "O15181669912912813",
     *              "uid": "ocJN_4jN7hgUaIrIkVC-fDhUX_SU", // 用户openid
     *              "order_detail": "赶紧说-语音口令支付", // 充值类型
     *              "type": 1,
     *              "bonus_id": 38,
     *              "money": "66.00", //红包金额
     *              "created_at": "2018-02-09 17:03:11", // 时间
     *              "updated_at": "2018-02-09 17:03:11",
     *              "finish_at": "2018-02-09 17:03:11",
     *              "wx_money": "0.00", // 充值金额
     *              "is_close": 0,
     *              "card_money": "0.00",
     *              "trade_no": "", // 交易流水号
     *              "balance_money": "66.00" // 余额抵扣
     *              "nickname": "执迷的鲸鱼" //昵称
     *          }
     *      ],
     *      "total": 44
     *  },
     * "code": 202
     * }
     */
    public function orderRecharge()
    {
        $params = $this->request->param();
        $result = $this->orderLogic->orderRecharge($params);
        return $result;
    }

    /**
     * @api {get} /backend/order/withdraw 提现列表
     * @apiGroup order
     * @apiName  withdraw
     * @apiVersion 1.0.0
     * @apiParam {int} page  页数
     * @apiParam {int} size  页码
     * @apiParam {string} name 昵称
     * @apiparam {string} start_time 查询开始时间
     * @apiparam {string} end_time 查询结束时间
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/order/withdraw
     * @apiSuccessExample {json} Response 200 Example
     * {
     * "status": 1,
     * "message": "获取成功",
     * "data": {
     *      "list": [
     *          {
     *              "id": 45,
     *              "order_sn": "O15180606017055057", //提现单号
     *              "uid": "ocJN_4jN7hgUaIrIkVC-fDhUX_SU", //用户id
     *              "order_detail": "赶紧说-余额提现",
     *              "type": 1,
     *              "bonus_id": 0,
     *              "money": "1.01", // 提现金额
     *              "created_at": "2018-02-08 11:30:01", //申请
     *              "updated_at": "2018-02-08 11:30:01", //通过
     *              "finish_at": "2018-02-06 22:09:17",
     *              "wx_money": "1.01",
     *              "is_close": 0,
     *              "card_money": "0.00",
     *              "check_name": "", //审核人
     *              "trade_no": "",
     *              "nickname": "执迷的鲸鱼", //昵称
     *              "status": "提现成功" //提现状态
     *           }
     *      ],
     *      "total": 44
     *  },
     * "code": 202
     * }
     */
    public function orderWithdraw()
    {
        $params = $this->request->param();
        $result = $this->orderLogic->orderWithdraw($params);
        return $result;
    }
}