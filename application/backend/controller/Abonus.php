<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/28
 * Time: 下午4:12
 * @introduce
 */
namespace app\backend\controller;

use app\backend\logic\AbonusLogic;
use extend\helper\Utils;
use think\Request;

class Abonus extends BaseAdmin
{
    protected $abonus;
    protected $abonusValidate;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->abonus = new AbonusLogic();
        $this->abonusValidate = new \app\backend\validate\Abonus();
    }

    /**
     * @api {get} /backend/abonus/search 后台讨红包管理
     * @apiGroup abonus
     * @apiName  search
     * @apiVersion 1.0.0
     * @apiParam   {string} openid 用户openid
     * @apiParam   {string} name 昵称
     * @apiParam   {string} start_time 开始时间
     * @apiParam   {string} end_time 结束时间
     * @apiParam   {int} page 当前页
     * @apiParam   {int} size 每页数
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/abonus/search
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *          "list": [
     *                 {
     *                      "id": 1,
     *                      "uid": "ok4Em0YNx6n6hBEI8JHmRMGsUBTc",  用户openid
     *                      "template_id": 1, //模板id
     *                      "remark_type": 0, // 内容类型 0文字 1语音
     *                      "remark_word": "我就是试试看能不能讨个红包", //内容(文字)
     *                      "remark_voice": "", 内容(语音)
     *                      "receive_money": "0.0000", //已收集金额
     *                      "service_money": "0.0000", //服务费
     *                      "status": 0, //
     *                      "num": 0, // 打赏人数
     *                      "created_at": "2018-01-25 15:27:55", //生成时间
     *                      "updated_at": "2018-01-25 15:27:55",
     *                      "name": "张缘", //昵称
     *                      "avatarulr": "https://wx.qlogo.cn/mmope" //头像
     *                 },
     *                  。。。。
     *                 ]
     *  },
     *  "code": 102
     *  }
     */
    public function searchRows()
    {
        $params = $this->request->param();
        $result = $this->abonus->searchRows($params);
        return $result;
    }

    /**
     * @api {get} /backend/abonus/search-detail 后台讨红包管理详情
     * @apiGroup abonus
     * @apiName  search-detail
     * @apiVersion 1.0.0
     * @apiParam   {int} id 红包id
     * @apiParam   {int} page 当前页
     * @apiParam   {int} size 每页数
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/abonus/search-detail
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *          "total": 18, // 总个数
     *          "list": {
     *              "id": 2,
     *              "uid": "ok4Em0WTwfwbbtq6KKi7GgcbgBSA", //用户openid
     *              "template_id": 2, //模板id
     *              "remark_type": 1, // 备注类型
     *              "remark_word": "", // 备注文字
     *              "remark_voice": "www.baidu.com", // 备注语音
     *              "receive_money": "27.2600", // 收到总金额
     *              "service_money": "0.5400", // 服务费
     *              "status": 0, // 红包状态
     *              "num": 7, // 收到个数
     *              "created_at": "2018-01-25 15:39:18", // 创建时间
     *              "updated_at": "2018-01-29 10:53:21",
     *              "nickname": "执迷的鲸鱼", //昵称
     *              "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKz1UM2o6Qdv47aN455L9PhU8PyZuPZBmakLklURz0iadNIktd7wcGCAS4m6dHCVZC44o0vTQgF5Zw/0",
     *              "list": [
     *                  {
     *                      "id": 30,
     *                      "uid": "ok4Em0YNx6n6hBEI8JHmRMGsUBTc", //用户openid
     *                      "abonus_user": "",
     *                      "abonus_id": 2, // 红包id
     *                      "remark_type": 0, //备注类型 0什么也不说 1文字 2语音
     *                      "remark_word": "", //备注文字
     *                      "remark_voice": "", // 备注录音
     *                      "money": "9.00", // 红包金额
     *                      "is_pay": 1,  // 是否支付
     *                      "is_send": 1, // 是否发红包
     *                      "form_id": "",
     *                      "prepay_id": "",
     *                      "created_at": "2018-01-29 10:06:26", // 创建时间
     *                      "updated_at": "2018-01-29 10:06:26",
     *                      "nickname": "张缘", //昵称
     *                      "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTJ7j9DTYteS9Vibr4n5zibs6H927w8ibcUUnTfIAh3XB6oEyibVnv6HrPsicCAxnrD6A7VY3CP921ibnJzA/0",
     *                      "gender": 2 // 性别
     *                  }
     *              ]
     *          }
     *  },
     *  "code": 102
     *  }
     */
    public function searchDetailRows()
    {
        $params = $this->request->param();
        $result = $this->abonus->searchDetailRows($params);
        return $result;
    }

    /**
     * @api {post} /backend/abonus/add 讨红包模板新增
     * @apiGroup abonus
     * @apiName  add
     * @apiVersion 1.0.0
     * @apiParam   {string} img 模板图片
     * @apiParam   {string} word 默认文字
     * @apiParam   {string} class 模板主题 如dog 一个主题对应三张图
     * @apiParam   {string} scenes 场景 ask讨红包用 share分享用 send赏红包用
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/abonus/add
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "添加成功",
     *  "data": {
     *  },
     *  "code": 102
     *  }
     */
    public function addTemplate()
    {
        $params = $this->request->param();
        $result = $this->abonus->addTemplate($params);
        return $result;
    }

    /**
     * @api {get} /backend/abonus/template 讨红包模板展示
     * @apiGroup abonus
     * @apiName  template
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/abonus/template
     * @apiSuccessExample {json} Response 200 Example
     *{
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *        "list": [
     *              {
     *                  "id": 2,
     *                  "url": "http://pgy-hongbao.oss-cn-beijing.aliyuncs.com/template/dog1.png", // 图片
     *                  "word": "没有红包，谈什么新年快乐", //文案
     *                  "class": "dog", //模版类型英文 如dog(一个类型对应三种场景)
     *                  "scenes": "share", //场景英文 ask讨,share分享,send赏三种
     *                  "created_at": "2018-02-05 20:09:18", // 生成时间
     *                  "updated_at": "2018-02-05 20:09:18",
     *                  "status": 1 // 1上架 0下架
     *              },
     *            。。。
     *    ]
     *  },
     *  "code": 102
     *}
     */
    public function getTemplateList()
    {
        $result = $this->abonus->getTemplateList();
        return $result;
    }

    /**
     * @api {post} /backend/abonus/edit 讨红包模板编辑
     * @apiGroup abonus
     * @apiName  edit
     * @apiVersion 1.0.0
     * @apiParam   {int} id id
     * @apiParam   {string} img 模板图片
     * @apiParam   {string} word 默认文字
     * @apiparam   {int} status 模版1上架0下架
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/abonus/edit
     * @apiSuccessExample {json} Response 200 Example
     *{
     *  "status": 1,
     *  "message": "修改成功",
     *  "data": {},
     *  "code": 201
     *}
     */
    public function editTemplate()
    {
        $params = $this->request->param();
        $result = $this->abonus->editTemplate($params);
        return $result;
    }

    /**
     * @api {get} /backend/abonus/del 讨红包模板删除
     * @apiGroup abonus
     * @apiName  del
     * @apiVersion 1.0.0
     * @apiParam   {int} id id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/abonus/del
     * @apiSuccessExample {json} Response 200 Example
     *{
     *  "status": 1,
     *  "message": "删除成功",
     *  "data": {},
     *  "code": 201
     *}
     */
    public function delTemplate()
    {
        $params = $this->request->param();
        $result = $this->abonus->delTemplate($params['id']);
        return $result;
    }
}