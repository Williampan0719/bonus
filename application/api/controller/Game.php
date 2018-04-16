<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/27
 * Time: 下午2:16
 */

namespace app\api\controller;


use app\game\logic\GameBigwheelLogic;
use app\game\logic\GameSscLogic;
use app\payment\logic\PayLogic;
use extend\helper\Utils;
use think\Request;

class Game extends BaseApi
{
    protected $bwheel = null;
    protected $ssc = null;
    function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->bwheel = new GameBigwheelLogic();
        $this->ssc = new GameSscLogic();
    }

    /**
     * @api {post} /api/game/bwheel-list 获取奖项
     * @apiGroup game
     * @apiName  bwheel-list
     * @apiVersion 1.0.0
     * @apiParam {string} uid 用户openid
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/game/bwheel-list
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
    public function getBwheel(){
        $param = $this->request->param();
        return $this->bwheel->getList($param['uid']);
    }
    /**
     * @api {post} /api/game/bwheel-draw 抽奖
     * @apiGroup game
     * @apiName  bwheel-draw
     * @apiVersion 1.0.0
     * @apiParam {string} uid 用户openid
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/game/bwheel-draw
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
    public function getBwheelDraw(){
        $param = $this->request->param();
        return $this->bwheel->popRate($param['uid']);
    }
    /**
     * @api {post} /api/game/game-cssc 发起pk
     * @apiGroup game
     * @apiName  game-cssc
     * @apiVersion 1.0.0
     * @apiParam {string} uid  用户的openid
     * @apiParam {string} title  标题
     * @apiParam {string} coin  赌注金额
     * @apiParam {string} val  出拳值  1石头，2剪刀，3布
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/game/game-cssc
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
    public function setGamepk(){
        $param = $this->request->param();
        return $this->ssc->createSsc($param);
    }

    /**
     * @api {post} /api/game/game-csscto 挑战pk
     * @apiGroup game
     * @apiName  game-csscto
     * @apiVersion 1.0.0
     * @apiParam {string} ssid  发起的挑战id
     * @apiParam {string} uid  用户的openid
     * @apiParam {string} coin  赌注金额
     * @apiParam {string} val  出拳值  1石头，2剪刀，3布
     * @apiParam {string} type  挑战类型 （0畏战，1果断应战，2押注观战，）
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/game/game-csscto
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
    public function setGamepkto(){
        $param = $this->request->param();
        return $this->ssc->createSscTo($param);
    }
    /**
     * @api {post} /api/game/game-list 获取未出结果的列表
     * @apiGroup game
     * @apiName  game-list
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/game/game-list
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
    public function getGameList(){
        //$param = $this->request->param();
        return $this->ssc->getList();
    }
    /**
     * @api {post} /api/game/game-signin 签到
     * @apiGroup game
     * @apiName  game-signin
     * @apiVersion 1.0.0
     * @apiParam {string} uid  用户的openid
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/game/game-signin
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
    public function getGameSign(){
        $param = $this->request->param();
        return $this->bwheel->signIn($param['uid']);
    }
    /**
     * @api {post} /api/game/game-detail 明细
     * @apiGroup game
     * @apiName  game-detail
     * @apiVersion 1.0.0
     * @apiParam  {string} uid 用户uid
     * @apiParam  {int} page 当前页
     * @apiParam  {int} size 每页数
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/game/game-detail
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
    public function getGameDetail(){
        $param = $this->request->param();
        return $this->bwheel->detail_list($param);
    }
    /**
     * @api {post} /api/game/game-sscinfo 获取详细信息
     * @apiGroup game
     * @apiName  game-sscinfo
     * @apiVersion 1.0.0
     *
     * @apiParam {string} ssid  发起的挑战id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/game/game-sscinfo
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
    public function getSscInfo(){
        $param = $this->request->param();
        return $this->ssc->getVirtualInfo($param);
    }
    /**
     * @api {post} /api/game/game-yue 获取金币余额
     * @apiGroup game
     * @apiName  game-yue
     * @apiVersion 1.0.0
     * @apiParam {string} uid  用户的openid
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/game/game-yue
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
    public function getYuE(){
        $param = $this->request->param();
        return $this->ssc->getYuE($param['uid']);
    }
    /**
     * @api {post} /api/game/game-into 获取是否参与过
     * @apiGroup game
     * @apiName  game-into
     * @apiVersion 1.0.0
     * @apiParam {string} uid  用户的openid
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/game/game-into
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
    public function getIsToIn(){
        $param = $this->request->param();
        return $this->ssc->getIsToIn($param['uid'],$param['ssid']);
    }
    /**
     * @api {post} /api/game/game-topup 游戏充值
     * @apiGroup game
     * @apiName  game-topup
     * @apiVersion 1.0.0
     * @apiParam {string} uid 用户openid
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/game/game-topup
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
    public function topUp(){
        $paylogic = new PayLogic();
        $param = $this->request->param();
        $result = $paylogic->gpay($param);
        return $result;
    }

    /**
     * @api {get} /api/game/close-order 游戏充值关闭订单
     * @apiGroup game
     * @apiName  close-order
     * @apiVersion 1.0.0
     * @apiParam   {int} order-sn 订单号
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/game/close-order
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function closeOrder()
    {
        $payLogic = new PayLogic();
        $params = $this->request->param();
        $result = $payLogic->closeGameOrder($params);
        return $result;
    }

    /**
     * @api {post} /api/game/game-topup-list 游戏充值列表
     * @apiGroup game
     * @apiName  game-topup-list
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/game/game-topup-list
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
    public function virtualTopup(){
        $virtual = Utils::getVirtualConfig();
        return $this->ajaxSuccess(109,['data'=>$virtual]);
    }
}