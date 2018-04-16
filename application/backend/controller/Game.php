<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/29
 * Time: 下午4:00
 */

namespace app\backend\controller;

use app\backend\logic\GameLogic;
use think\Request;

class Game extends BaseAdmin
{
    private $game = null;
    function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->game = new GameLogic();
    }

    /**
     * @api {get} /backend/game/search 明细搜索
     * @apiGroup game
     * @apiName  search
     * @apiVersion 1.0.0
     * @apiParam   {string} openid 用户openid
     * @apiParam   {string} start_time 开始时间
     * @apiParam   {string} end_time 结束时间
     * @apiParam   {array} type 行为类型[1,2]/[1]
     * @apiParam   {int} page 当前页
     * @apiParam   {int} size 每页数
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/game/search
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *      "total": 33,
     *      "list": [
     *               {
     *                 "id": 198,
     *                 "uid": "oHPFb5dMQCy44RKhtYJ-d7ZfaeUk", // 用户openid
     *                 "type": "押注", // 行为
     *                 "coin": "100", //变化金额
     *                 "balance": 0, // 余额
     *                 "symbol": "支出", // 类型
     *                 "created_at": "2018-01-30 15:06:27", // 创建时间
     *                 "updated_at": "2018-01-30 15:06:27",
     *                 "nickname": "lm" // 昵称
     *               },
     *            ...
     *      ]
     *  },
     *  "code": 0
     * }
     */
    public function searchDetailRows(){
        $param = $this->request->param();
        return $this->game->searchDetailRows($param);
    }

    /**
     * @api {get} /backend/game/recharge-list 充值搜索
     * @apiGroup game
     * @apiName  recharge-list
     * @apiVersion 1.0.0
     * @apiParam   {string} openid 用户openid
     * @apiParam   {string} start_time 开始时间
     * @apiParam   {string} end_time 结束时间
     * @apiParam   {int} page 当前页
     * @apiParam   {int} size 每页数
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/game/recharge-list
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *      "total": 33,
     *      "list": [
     *               {
     *                  "id": 288,
     *                  "uid": "oHPFb5RR4E0lfvDnY9lSZPanJiMs", //openid
     *                  "type": 7,
     *                  "coin": "100", //充值金币
     *                  "balance": 0, //余额
     *                  "symbol": "收入",
     *                  "created_at": "2018-01-31 14:05:45", // 充值时间
     *                  "updated_at": "2018-01-31 14:05:45",
     *                  "nickname": "new了个t", //昵称
     *                  "money": "1.00" // 充值金额
     *                },
     *            ...
     *      ]
     *  },
     *  "code": 0
     * }
     */
    public function getRechargeList(){
        $param = $this->request->param();
        return $this->game->getRechargeList($param);
    }

    /**
     * @api {get} /backend/game/virtual-list 虚拟币列表
     * @apiGroup game
     * @apiName  virtual-list
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/game/virtual-list
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {},
     *  "code": 0
     * }
     */
    public function virtualConfigList()
    {
        return $this->game->virtualConfigList();
    }

    /**
     * @api {post} /backend/game/add-virtual 添加虚拟币配置
     * @apiGroup game
     * @apiName  add-virtual
     * @apiVersion 1.0.0
     * @apiParam   {int} coin 虚拟币
     * @apiParam   {int} money 金钱
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/game/add-virtual
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {},
     *  "code": 0
     * }
     */
    public function addVirtualConfig()
    {
        $param = $this->request->param();
        return $this->game->addVirtualConfig($param);
    }

    /**
     * @api {post} /backend/game/edit-virtual 编辑虚拟币配置
     * @apiGroup game
     * @apiName  edit-virtual
     * @apiVersion 1.0.0
     * @apiParam   {int} coin 虚拟币
     * @apiParam   {int} money 金钱
     * @apiParam   {int} id 编辑id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/game/edit-virtual
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {},
     *  "code": 0
     * }
     */
    public function editVirtualConfig()
    {
        $param = $this->request->param();
        return $this->game->editVirtualConfig($param);
    }

    /**
     * @api {get} /backend/game/del-virtual 删除虚拟币配置
     * @apiGroup game
     * @apiName  del-virtual
     * @apiVersion 1.0.0
     * @apiParam   {int} id 删除id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/game/del-virtual
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {},
     *  "code": 0
     * }
     */
    public function delVirtualConfig()
    {
        $param = $this->request->param();
        return $this->game->delVirtualConfig($param);
    }

    /**
     * @api {get} /backend/game/bwheel-list 大转盘列表
     * @apiGroup game
     * @apiName  bwheel-list
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/game/bwheel-list
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {},
     *  "code": 0
     * }
     */
    public function bwheelConfigList()
    {
        return $this->game->bwheelConfigList();
    }

    /**
     * @api {post} /backend/game/add-bwheel 添加大转盘配置
     * @apiGroup game
     * @apiName  add-bwheel
     * @apiVersion 1.0.0
     * @apiParam   {int} sequence 排序(位置)
     * @apiParam   {string} prize 奖项
     * @apiParam   {string} reward 奖励
     * @apiParam   {int} rate 几率
     * @apiParam   {int} type  奖励单位 0金币 1元
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/game/add-bwheel
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {},
     *  "code": 0
     * }
     */
    public function addBwheelConfig()
    {
        $param = $this->request->param();
        if (!is_numeric($param['reward'])) {
            return $this->ajaxError(206,[],'奖励必须为数字');
        }
        return $this->game->addBwheelConfig($param);
    }

    /**
     * @api {post} /backend/game/edit-bwheel 编辑大转盘配置
     * @apiGroup game
     * @apiName  edit-bwheel
     * @apiVersion 1.0.0
     * @apiParam   {int} sequence 排序(位置)
     * @apiParam   {string} prize 奖项
     * @apiParam   {string} reward 奖励
     * @apiParam   {int} rate 几率
     * @apiParam   {int} id 编辑id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/game/edit-bwheel
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {},
     *  "code": 0
     * }
     */
    public function editBwheelConfig()
    {
        $param = $this->request->param();
        return $this->game->editBwheelConfig($param);
    }

    /**
     * @api {get} /backend/game/del-bwheel 删除大转盘配置
     * @apiGroup game
     * @apiName  del-bwheel
     * @apiVersion 1.0.0
     * @apiParam   {int} id 删除id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/game/del-bwheel
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {},
     *  "code": 0
     * }
     */
    public function delBwheelConfig()
    {
        $param = $this->request->param();
        return $this->game->delBwheelConfig($param);
    }
}