<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/22
 * Time: 11:41
 * @introduce
 */

namespace app\backend\controller;

use app\backend\logic\EnterpriseLogic;
use think\Request;

class Enterprise extends BaseAdmin
{

    protected $enterpriseLogic;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->enterpriseLogic = new EnterpriseLogic();
    }
    /**
     * @api {get} /backend/enterprise/list 资金流水列表
     * @apiGroup enterprise
     * @apiName  list
     * @apiVersion 1.0.0
     * @apiParam {int} page  页数
     * @apiParam {int} size  页码
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/enterprise/list
     * @apiSuccessExample {json} Response 200 Example
     * {}
     */
    public function enterpriseList()
    {
        $result=$this->enterpriseLogic->enterpriseList();
        return $result;
    }
    /**
     * @api {get} /backend/enterprise/add 资金流水列表
     * @apiGroup enterprise
     * @apiName  add
     * @apiVersion 1.0.0
     * @apiParam {float} money 充值金额
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/enterprise/add
     * @apiSuccessExample {json} Response 200 Example
     * {}
     */
    public function enterpriseAdd()
    {
        $money=$this->paramValidate('money');
        $result=$this->enterpriseLogic->enterpriseAdd($money);
        return $result;
    }
}