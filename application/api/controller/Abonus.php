<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/25
 * Time: 下午2:11
 * @introduce
 */

namespace app\api\controller;

use app\payment\logic\AbonusLogic;
use think\Request;

class Abonus extends BaseApi
{
    protected $abonusValidate;
    protected $abonus;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->abonusValidate = new \app\api\validate\Abonus();
        $this->abonus = new AbonusLogic();
    }

    /**
     * @api {get} /api/abonus/template 讨红包模板展示
     * @apiGroup abonus
     * @apiName  template
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/api/abonus/template
     * @apiSuccessExample {json} Response 200 Example
     *{
     *"status": 1,
     *"message": "获取成功",
     *"data": {
     *      "list": [
     *          {
     *             "id": 1,
     *             "url": "http://pgy-hongbao.oss-cn-beijing.aliyuncs.com/image/packet_inner1.jpg", // 模板图片
     *             "word": "狗年大吉，大吉大利~", // 模板文字
     *             "class": "dog", // 模板分类
     *             "scenes": "ask", //模板场景
     *             "created_at": "2018-01-29 17:18:18", // 创建时间
     *             "updated_at": "2018-01-29 17:18:18"
     *          },
     *          。。。
     *  ]
     *},
     *"code": 102
     *}
     */
    public function getTemplateList()
    {
        $result = $this->abonus->getTemplateList();
        return $result;
    }

    /**
     * @api {post} /api/abonus/ask 生成讨红包
     * @apiGroup abonus
     * @apiName  ask
     * @apiVersion 1.0.0
     * @apiParam   {string} openid 用户的openid
     * @apiParam   {string} template_class 模版分类
     * @apiParam   {int} remark_type 备注类型 0什么也不说  1文字 2语音
     * @apiParam   {string} remark_word 文字备注
     * @apiParam   {int} timelength 语音时长
     * @apiParam   {file} file 语音文件
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/api/abonus/ask
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function askBonus()
    {
        $params = $this->request->param();
        $this->paramsValidate($this->abonusValidate, 'ask', $params);
        $result = $this->abonus->saveAskingBonus($params,$_FILES);
        return $result;
    }

    /**
     * @api {get} /api/abonus/share 分享讨红包
     * @apiGroup abonus
     * @apiName  share
     * @apiVersion 1.0.0
     * @apiParam   {int} id 红包id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/api/abonus/share
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *        "list": {
     *                   "url": "http://pgy-hongbao.oss-cn-beijing.aliyuncs.com/image/dog1.png", // 模板图片
     *                   "nick_name": "执迷的鲸鱼", // 昵称
     *                   "avatarulr": "https://wx.qlogo.cn/xLrmmbkhQ/0" // 头像
     *                   "bonus_img": "" //二维码
     *                }
     *  },
     *  "code": 102
     *  }
     */
    public function askBonusShare()
    {
        $params = $this->request->param();
        $this->paramsValidate($this->abonusValidate, 'share', $params);
        $result = $this->abonus->abonusShare($params);
        return $result;
    }

    /**
     * @api {get} /api/abonus/index 赏红包进入首页
     * @apiGroup abonus
     * @apiName  index
     * @apiVersion 1.0.0
     * @apiParam   {string} openid 浏览者openid
     * @apiParam   {int} id 红包id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/api/abonus/index
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *          "money": [
     *                     "1.88",
     *                     "5.20",
     *                     "6.66",
     *                     "8.88",
     *                     "66.6",
     *                     "88.8",
     *                     "520",
     *                     "666",
     *                     "888",
     *                     "1314"
     *                    ],
     *            "list": {
     *                    "nick_name": "执迷的鲸鱼",
     *                    "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTIeVA9Vt1Tm2Nqia1uExdoQelto4O42FsWpib0addwVlxCtRe0icy6Iia1ib0Z27xLBxICnlNdLrmmbkhQ/0",
     *                    "url": "",
     *                    "remark_type": 2,
     *                    "remark_word": "",
     *                    "remark_voice": "http://pgy-hongbao.oss-cn-beijing.aliyuncs.com/audio/tape15175771687205381980.mp3"
     *                    }
     *  },
     *  "code": 102
     *  }
     */
    public function askBonusIndex()
    {
        $params = $this->request->param();
        $this->paramsValidate($this->abonusValidate, 'index', $params);
        $result = $this->abonus->abonusIndex($params);
        return $result;
    }

    /**
     * @api {post} /api/abonus/pay 打赏红包
     * @apiGroup abonus
     * @apiName  pay
     * @apiVersion 1.0.0
     * @apiParam   {string} openid 打赏用户的uid
     * @apiparam   {string} form_id 推送id
     * @apiParam   {int} id  红包id
     * @apiParam   {int} money 赏红包金额
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/abonus/pay
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function abonusPay()
    {
        $params = $this->request->param();
        $this->paramsValidate($this->abonusValidate, 'pay', $params);
        $result = $this->abonus->pay($params);
        return $result;
    }

    /**
     * @api {get} /api/abonus/temp-after 打赏红包完填备注时模板读取
     * @apiGroup abonus
     * @apiName  temp-after
     * @apiVersion 1.0.0
     * @apiParam   {int} abonus_id 讨红包id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/abonus/temp-after
     * @apiSuccessExample {json} Response 200 Example
     * {
     *"status": 1,
     *"message": "获取成功",
     *"data": {
     *      "list": {
     *                  "id": 15,
     *                  "uid": "oHPFb5U10bMgyJUGNvEoL-XS--fw",
     *                  "template_class": "dog",
     *                  "remark_type": 2, // 备注类型 0无 1文字 2语音
     *                  "remark_word": "",
     *                  "remark_voice": "http://pgy-hongbao.oss-cn-beijing.aliyuncs.com/audio/tape15175771687205381980.mp3",
     *                  "timelength": 4000,
     *                  "receive_money": "0.0000",
     *                  "service_money": "0.0000",
     *                  "status": 0,
     *                  "num": 0,
     *                  "created_at": "2018-02-02 21:12:49",
     *                  "updated_at": "2018-02-02 21:12:49",
     *                  "nickname": "执迷的鲸鱼", // 昵称
     *                  "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/Q0j4T0Z27xLBxICnlNdLrmmbkhQ/0", // 头像
     *                  "url": "http://pgy-hongbao.oss-cn-beijing.aliyuncs.com/template/reel1.png" // 模板图片
     *               }
     *},
     *"code": 102
     *}
     */
    public function templateAfterSend()
    {
        $params = $this->request->param();
        $this->paramsValidate($this->abonusValidate, 'temp_after', $params);
        $result = $this->abonus->templateAfterSend($params);
        return $result;
    }

    /**
     * @api {post} /api/abonus/pay-plus 打赏红包后的备注添加
     * @apiGroup abonus
     * @apiName  pay-plus
     * @apiVersion 1.0.0
     * @apiParam   {int} id 赏红包的id
     * @apiParam   {int} remark_type 备注类型 0什么也不说 1文字 2语音
     * @apiParam   {string} remark_word 备注文字
     * @apiParam   {int} timelength 录音时长
     * @apiParam   {file} file 录音文件
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/abonus/pay-plus
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function abonusPayPlus()
    {
        $params = $this->request->param();
        $result = $this->abonus->sendAbonusRemark($params, $_FILES);
        return $result;
    }

    /**
     * @api {get} /api/abonus/show 讨红包详情页
     * @apiGroup abonus
     * @apiName  show
     * @apiVersion 1.0.0
     * @apiParam   {string} openid 浏览者openid
     * @apiParam   {int} id 红包id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/abonus/show
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *          "money": "7.40", // 您已赏/您已收到
     *          "nick_name": "执迷的鲸鱼", // 昵称
     *          "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTIV7CyTV3tuvkW41vLPv6g/0", // 头像
     *          "remark_type": 1, // 备注类型 0文字 1语音
     *          "remark_word": "", // 文字备注
     *          "remark_voice": "www.baidu.com", // 语音备注
     *          "gender": 1 //性别 1男 2女
     *          "self": 0他人 1自己
     *          "list": [ // 已打赏
     *                {
     *                  "uid": "okPcX0YqZ4TEg2mXFlRpkx-Muxx8", // 用户openid
     *                  "money": "3.70", //用户赏金
     *                  "remark_type":0 //备注类型 0什么都没有 1文字 2语音
     *                  "remark_word": "必胜客" // 备注文字
     *                  "remark_voice": "", // 备注语音
     *                  "created_at": "2018-01-25 18:55:13", //时间
     *                  "nickname": "Logx", // 昵称
     *                  "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83eoXm6sVdS9JF7iaQ/0",
     *                  "gender": 1, // 性别 1男2女
     *                  "is_max": 1 // 是否最土豪
     *                },
     *              ],
     *          "reading": [ // 已阅
     *              {
     *                  "uid": "okPcX0XbevTGZkvgYoyfnm_STrPM",
     *                  "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/Q0VAWId6yibib4y38FicZdombQ/0"
     *              }
     *          ]
     *  },
     *  "code": 102
     *  }
     */
    public function abonusShow()
    {
        $params = $this->request->param();
        $this->paramsValidate($this->abonusValidate, 'show', $params);
        $result = $this->abonus->abonusShow($params);
        return $result;
    }
}