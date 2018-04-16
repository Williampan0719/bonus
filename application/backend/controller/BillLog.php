<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/7
 * Time: 08:59
 * @introduce
 */
namespace app\backend\controller;

use app\backend\logic\BillLogLogic;
use think\Request;

class BillLog extends BaseAdmin
{
    protected $billLogLogic;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->billLogLogic= new BillLogLogic();
    }
    /**
     * @api {get} /backend/bill/list 资金流水列表
     * @apiGroup bill
     * @apiName  list
     * @apiVersion 1.0.0
     * @apiParam {int} page  页数
     * @apiParam {int} size  页码
     * @apiParam   {string} start_time 开始时间
     * @apiParam   {string} end_time 结束时间
     * @apiParam {string} keyword  关键词(用户昵称)
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/bonus/list
     * @apiSuccessExample {json} Response 200 Example
     * {}
     */
    public function billLogList()
    {
        $params=$this->request->param();
        $result=$this->billLogLogic->billLogList($params);
        return $result;
    }

    /**
     * @api {get} /backend/bill/stats 每日各项统计
     * @apiGroup bill
     * @apiName  stats
     * @apiVersion 1.0.0
     * @apiParam   {string} start_time 开始时间
     * @apiParam   {string} end_time 结束时间
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/bonus/stats
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *      "list": {
     *          "wx_recharge": "4.28元",  // 红包充值
     *          "coin_recharge": "0.86元", // 金币充值
     *          "all_recharge": "5.14元", // 全部收入
     *          "withdraw": "0.00元", // 当日提现
     *          "change_balance": "0.00元", // 余额变化
     *          "all_balance": "0.00元", // 当前总余额
     *          "bonus_fees": "0.00元", // 红包毛利
     *          "refund": "0.00元", // 退款
     *          "profit": "0.00元", // 纯利润
     *          "wx_fees": "0.00元" // 微信手续费
     *      }
     *  },
     *  "code": 202
     *  }
     */
    public function billLogStats()
    {
        $params=$this->request->param();
        $result=$this->billLogLogic->billLogStats($params);
        return $result;
    }
}