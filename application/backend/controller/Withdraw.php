<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/2/7
 * Time: 下午4:58
 * @introduce
 */
namespace app\backend\controller;

use app\backend\logic\WithdrawLogic;
use app\payment\model\WithdrawReview;
use think\Request;

class Withdraw extends BaseAdmin
{
    protected $review;
    protected $reviewValidate;

    function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->review = new WithdrawLogic();
        $this->reviewValidate = new \app\backend\validate\Withdraw();
    }

    /**
     * @api {get} /backend/withdraw/search-review 后台查询提现订单
     * @apiGroup withdraw
     * @apiName  search-review
     * @apiVersion 1.0.0
     * @apiParam   {int} uid 用户openid
     * @apiParam   {int} status 状态 0失败 1申请中 2成功
     * @apiParam   {string} start_time 开始时间
     * @apiParam   {string} end_time 结束时间
     * @apiParam   {int} page 当前页
     * @apiParam   {int} size 每页数
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/withdraw/search-review
     * @apiSuccessExample {json} Response 200 Example
     * {
     *     "status": 1,
     *     "message": "获取成功",
     *     "data": {
     *         "total": 3
     *         "list": [
     *                  {
     *                      "id": 1, //申请id
     *                      "uid": "ocJN_4sSypPS4OipJWwn-iIq_0Sg", // 申请者
     *                      "money": "58.73", // 申请提现金额
     *                      "status": "申请中", // 状态
     *                      "created_at": "2018-02-07 11:07:10", //申请时间
     *                      "updated_at": "2018-02-07 11:07:10", //审核时间
     *                      "name": "张缘" //昵称
     *                  },
     *                 ]
     * },
     *     "code": 200
     * }
     */
    public function searchReviewRows()
    {
        $param = $this->params;
        $this->paramsValidate($this->reviewValidate, 'search-review', $param);

        $result = $this->review->searchRows($param);

        return $result;
    }

    /**
     * @api {get} /backend/withdraw/edit-review 后台审核提现订单
     * @apiGroup withdraw
     * @apiName  edit-review
     * @apiVersion 1.0.0
     * @apiParam   {int} id 订单id
     * @apiParam   {string} name 审核员
     * @apiParam   {int} status 状态 0失败 1申请中 2成功
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/withdraw/edit-review
     * @apiSuccessExample {json} Response 200 Example
     * {
     *     "status": 1,
     *     "message": "获取成功",
     *     "data": {
     *         "total": 3
     *         "list": [
     *                  {
     *                      "id": 1, //申请id
     *                      "uid": "ocJN_4sSypPS4OipJWwn-iIq_0Sg", // 申请者
     *                      "money": "58.73", // 申请提现金额
     *                      "status": "申请中", // 状态
     *                      "created_at": "2018-02-07 11:07:10", //申请时间
     *                      "updated_at": "2018-02-07 11:07:10", //审核时间
     *                      "name": "张缘" //昵称
     *                  },
     *                 ]
     * },
     *     "code": 200
     * }
     */
    public function editReview()
    {
        $param = $this->params;
        $this->paramsValidate($this->reviewValidate, 'edit-review', $param);
        $result = $this->review->editReview($param);

        return $result;
    }
}