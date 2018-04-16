<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/12
 * Time: 上午11:31
 * @introduce 后台体力管理配置
 */
namespace app\backend\controller;

use app\backend\logic\PowerLogic;
use think\Request;

class Power extends BaseAdmin
{
    protected $powerValidate;
    protected $power;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->powerValidate = new \app\backend\validate\Power();
        $this->power = new PowerLogic();
    }

    /**
     * @api {get} /backend/power/all 后台体力列表
     * @apiGroup power
     * @apiName  all
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/power/all
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *      "total": 1,
     *      "list": [
     *               {
     *                  "id": 1, // 体力id
     *                  "title": "everyday" //体力配置唯一标志
     *                  "name": "每日送体力值", // 配置体力内容
     *                  "num": 5, // 体力值
     *                  "created_at": "2018-01-12 11:50:06",
     *                  "updated_at": "2018-01-12 11:50:06"
     *                },
     *                {
     *                  "id": 2,
     *                  "title": "new" //体力配置唯一标志
     *                  "name": "新用户默认赠送体力值",
     *                  "num": 10,
     *                  "created_at": "2018-01-12 11:50:25",
     *                  "updated_at": "2018-01-12 11:50:25"
     *                },
     *            ...
     *      ]
     *  },
     *  "code": 0
     * }
     */
    public function getAll()
    {
        return $this->power->getAll();
    }

    /**
     * @api {post} /backend/power/add 后台添加体力配置
     * @apiGroup power
     * @apiName  add
     * @apiVersion 1.0.0
     * @apiParam {string} title 英文简写
     * @apiParam {string} name 体力名称介绍
     * @apiParam {int} num 体力值额
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/power/add
     * @apiSuccessExample {json} Response 200 Example
     * {
     *     "status": 1,
     *     "message": "添加成功",
     *     "data": {},
     *     "code": 200
     * }
     */
    public function addPower()
    {
        $param = $this->params;
        $this->paramsValidate($this->powerValidate, 'add', $param);
        $result = $this->power->addPower($param);

        return $result;
    }

    /**
     * @api {post} /backend/power/edit 后台编辑体力配置
     * @apiGroup power
     * @apiName  edit
     * @apiVersion 1.0.0
     * @apiParam {int} id 配置id
     * @apiParam {string} name 体力名称介绍
     * @apiParam {int} num 体力值额
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/power/edit
     * @apiSuccessExample {json} Response 200 Example
     * {
     *     "status": 1,
     *     "message": "编辑成功",
     *     "data": {},
     *     "code": 200
     * }
     */
    public function editPower()
    {
        $param = $this->params;
        $this->paramsValidate($this->powerValidate, 'edit', $param);
        $result = $this->power->editPower($param);

        return $result;
    }
}