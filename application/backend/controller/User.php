<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/5
 * Time: 上午11:44
 */

namespace app\backend\controller;


use app\backend\logic\UserLogic;
use think\Request;

class User extends BaseAdmin
{
    protected $user = null;

    function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->user = new UserLogic();

    }

    /**
     * @api {get} /backend/user/list 用户列表
     * @apiGroup user
     * @apiName  list
     * @apiVersion 1.0.0
     * @apiParam {string} keyword 关键词
     * @apiParam {int} sex 性别
     * @apiParam {string} start_time 开始时间
     * @apiParam {string} end_time 结束时间
     * @apiParam {int} page  页数
     * @apiParam {int} size  页码
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/user/list
     * @apiSuccessExample {json} Response 200 Example
     * {
     * "status": 1,
     * "message": "获取成功",
     * "data": {
         "0": {
         "id": 1,
         "nickname": "我是谁",
         "avatarulr": "",
         "openid": "oq_Lbw05XTL08d2VGCFRQuFQXbPs",
         "mobile": "",
         "gender": 0,
         "city": null,
         "province": null,
         "country": null,
         "language": null,
         "created_at": "1970-01-01 08:00:00",
         "updated_at": "1970-01-01 08:00:00",
         "deleted_at": null,
         "login_at": null,
         "status": 1
         }
     * }
     * "code": 202
     * }
     */
    public function userList()
    {
        $param = $this->request->param();
        return $this->user->userList($param);
    }

    /**
     * @api {get} /backend/user/panel 用户面板
     * @apiGroup user
     * @apiName  panel
     * @apiVersion 1.0.0
     * @apiParam {string} openid 用户openid
     * @apiParam   {string} start_time 开始时间
     * @apiParam   {string} end_time 结束时间
     * @apiParam   {int} page 当前页
     * @apiParam   {int} size 每页数
     * @apiParam {int} type  0发口令 1发语音 2抢口令 3抢语音 4资金变动 5金币记录
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/user/panel
     * @apiSuccessExample {json} Response 200 Example
     * {
     * "status": 1,
     * "message": "获取成功",
     * "data": {
     *          "list": {
     *                     "nickname": "执迷的鲸鱼", //昵称
     *                     "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/Q0jYtsoLCg/0", //头像
     *                     "created_at": "2018-02-06 21:25:49", // 首次进入时间
     *                     "distribute_time": "2018-02-11 10:10:10", // 分销时间
     *                     "status": 1, //1正常 0封号
     *                     "truename": "潘浩",
     *                     "mobile": "15700082829",
     *                     "is_distribute": 1, //是否分销状态 1是 0否
     *                     "balance": "27.08", 余额
     *                     "virtual": 480, //金币余额
     *                     "list":{'total'=>0,'list'=>[..]} // 列表
     *                  }
     * }
     * "code": 202
     * }
     */
    public function userPanel()
    {
        $param = $this->request->param();
        return $this->user->userPanel($param);
    }

    /**
     * @api {post} /backend/user/forbid 封号/解封
     * @apiGroup user
     * @apiName  forbid
     * @apiVersion 1.0.0
     * @apiParam {string} openid 用户openid
     * @apiParam {int} status 0封号 1解封
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/backend/user/forbid
     * @apiSuccessExample {json} Response 200 Example
     * {
     * "status": 1,
     * "message": "编辑成功",
     * "data": {
     * }
     * "code": 201
     * }
     */
    public function forbidUser()
    {
        $param = $this->request->param();
        return $this->user->forbidUser($param);
    }

}