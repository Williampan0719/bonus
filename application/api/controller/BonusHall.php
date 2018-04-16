<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/11
 * Time: 下午4:29
 */

namespace app\api\controller;


use app\payment\logic\BonusHallLogic;
use app\payment\logic\BonusLogic;
use think\Request;

class BonusHall extends BaseApi
{
    protected $bonusHall = null;
    function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->bonusHall = new BonusHallLogic();
    }
    /**
     * @api {get} /api/user/halls 获取大厅记录 内存
     * @apiGroup user
     * @apiName  halls
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/user/halls
     * @apiSuccessExample {json} Response 200 Example
     * {
     *     "status": 1,
     *     "message": "获取成功",
     *     "data": {
     *
     *          },
     *     "code": 202
     * }
     */
    public function getHalls(){
        $param = $this->request->param();
        return $this->bonusHall->getHalls($param);
    }
    /**
     * @api {get} /api/user/allhall 获取所有大厅记录 DB
     * @apiGroup user
     * @apiName  allhall
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/user/allhall
     * @apiSuccessExample {json} Response 200 Example
     * {
     *     "status": 1,
     *     "message": "获取成功",
     *     "data": {
     *
     *          },
     *     "code": 202
     * }
     */
    public function getAllHall(){
        $param = $this->request->param();
        return $this->bonusHall->getAllHall($param);
    }
    /**
     * @api {get} /api/user/daylocalt 获取今日土豪榜
     * @apiGroup user
     * @apiName  daylocalt
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/user/daylocalt
     * @apiSuccessExample {json} Response 200 Example
     * {
     *     "status": 1,
     *     "message": "获取成功",
     *     "data": {
     *
     *          },
     *     "code": 202
     * }
     */
    public function getDayLocalTyrant(){
        return $this->bonusHall->getDayLocalTyrants();
    }
    /**
     * @api {get} /backend/user/daybestl 获取今日手气最佳
     * @apiGroup user
     * @apiName  daybestl
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/user/daybestl
     * @apiSuccessExample {json} Response 200 Example
     * {
     *     "status": 1,
     *     "message": "获取成功",
     *     "data": {
     *
     *          },
     *     "code": 202
     * }
     */
    public function getDayBestLuck(){
        return $this->bonusHall->getDayBestLuck();
    }
    /**
     * @api {get} /backend/user/randex 随机出现一条口令
     * @apiGroup user
     * @apiName  randex
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/user/randex
     * @apiSuccessExample {json} Response 200 Example
     * {
     *     "status": 1,
     *     "message": "获取成功",
     *     "data": {
     *
     *          },
     *     "code": 202
     * }
     */
    public function randExample(){
        $param = $this->request->param();
        return $this->bonusHall->randExample($param['type']);
    }
    /**
     * @api {post} /api/user/hallinfo  获取大厅信息
     * @apiGroup user
     * @apiName  hallinfo
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/user/hallinfo
     * @apiSuccessExample {json} Response 200 Example
     * {
     *     "status": 1,
     *     "message": "获取成功",
     *     "data": {
     *
     *          },
     *     "code": 202
     * }
     */
    public function getHallInfo(){
        $param = $this->request->param();
        return $this->bonusHall->getHallInfo($param);
    }
    /**
     * @api {post} /backend/user/share-hall  获取大厅信息
     * @apiGroup user
     * @apiName  hallinfo
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/user/hallinfo
     * @apiSuccessExample {json} Response 200 Example
     * {
     *     "status": 1,
     *     "message": "获取成功",
     *     "data": {
     *
     *          },
     *     "code": 202
     * }
     */
    public function shareHall(){
        $param = $this->request->param();
        return $this->bonusHall->shareHall($param);
    }

}