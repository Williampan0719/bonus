<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/6
 * Time: 09:13
 * @introduce
 */

namespace app\api\controller;

use app\cron\logic\UserLogic;
use app\payment\logic\PayLogic;
use extend\helper\Files;
use extend\helper\Str;
use extend\helper\Utils;
use think\Request;

class BonusPay extends BaseApi
{
    protected $bonusPayValidate;
    protected $payLogic;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->bonusPayValidate = new \app\api\validate\BonusPay();
        $this->payLogic = new PayLogic();
    }

    /**
     * @api {post} /api/pay/check-word
     * @apiGroup pay
     * @apiName  check-word
     * @apiVersion 1.0.0
     * @apiParam   {string} bonus_password 红包口令
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/pay/check-word
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function checkWord()
    {
        $params = $this->request->param();
        $bool = Str::filters($params['bonus_password']);
        if ($bool) {
            return $this->ajaxError(1308);
        }
        return $this->ajaxSuccess(102);
    }

    /**
     * @api {post} /api/pay/bonus-pay
     * @apiGroup pay
     * @apiName  bonus-pay
     * @apiVersion 1.0.0
     * @apiParam   {string} uid 用户的uid
     * @apiParam   {string} bonus_password 红包的口令
     * @apiParam   {int} bonus_money 红包的金额
     * @apiParam   {string} bonus_num 红包的数量
     * @apiParam   {string} service_money 红包的服务费
     * @apiParam   {string} form_id form_id
     * @apiParam   {int} type 0内部红包 1大厅红包
     * @apiParam   {int} class 0口令红包 1语音红包 2广告红包
     * @apiParam   {file} file 录音文件(语音红包用)
     * @apiParam   {string} voice_path 音乐文件地址（非必传，选择qq音乐歌曲时必传）
     * @apiParam   {int} voice_type 音乐文件类型（红包语音类型 0录音或口令红包 1qq音乐歌曲）
     * @apiParam   {int} timelength 录音时长
     * @apiParam   {string} adv_name  品牌名称(广告用)
     * @apiParam   {string} adv_logo  品牌logo(广告用)
     * @apiParam   {json} adv_remark 品牌详情
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/pay/bonus-pay
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function bonusPay()
    {
        $params = $this->request->param();
        if (isset($params['class']) && $params['class'] == 1) {
            if ((isset($params['voice_type']) && $params['voice_type'] == 1)) {
                // qq音乐歌曲选择，不必上传文件
            } else {
                if (empty($_FILES)) {
                    return $this->ajaxError(106, [], '语音文件不能为空');
                }
            }
            $this->paramsValidate($this->bonusPayValidate, 'voicePay', $params);
        } elseif (isset($params['class']) && $params['class'] == 2) {
            //广告红包
            if ($params['bonus_money'] < 66) {
                return $this->ajaxError(106, [], '广告红包66元起');
            }
        } else {
            $this->paramsValidate($this->bonusPayValidate, 'pay', $params);
            $bool = Str::filters($params['bonus_password']);
            if ($bool) {
                return $this->ajaxError(1308);
            }
        }
        $result = $this->payLogic->pay($params, $_FILES);
        return $result;
    }


    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 微信回调
     */
    public function wxNotify()
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/video/log.txt', 'liyongchuanController' . "\r\n", FILE_APPEND);
        $this->payLogic->wxNotify();
    }

    /**wx-notify_game  微信支付游戏回调
     * auth smallzz
     */
    public function wxNotifyGame(){
        $this->payLogic->wxNotifyGame();
    }
    /**
     * @api {post} /api/pay/withdrawals
     * @apiGroup pay
     * @apiName  withdrawals
     * @apiVersion 1.0.0
     * @apiParam   {string} uid 用户的uid
     * @apiParam   {int} money 提现金额
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/pay/withdrawals
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function wxWithdrawals()
    {
        $params['uid'] = $this->paramValidate('uid');
        $params['money'] = $this->paramValidate('money');
        $params['enterprise_type'] = 2;//手动提现
        $result = $this->payLogic->EnterprisePay($params);
        return $result;
    }

    /**
     * @api {get} /api/pay/close-order
     * @apiGroup pay
     * @apiName  close-order
     * @apiVersion 1.0.0
     * @apiParam   {int} bonus_id 红包id
     * @apiParam   {int} type 红包id类型 1红包  2讨红包 3小游戏
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/pay/close-order
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function closeOrder()
    {
        $params = $this->request->param();
        $result = $this->payLogic->closeOrder($params);
        return $result;
    }

    /**
     * @api {post} /api/pay/upload
     * @apiGroup pay
     * @apiName  upload
     * @apiVersion 1.0.0
     * @apiParam   {file} file 图片文件
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/pay/upload
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function uploadPic()
    {
        $result = $this->payLogic->uploadPic($_FILES);
        return $result;
    }

    /**
     * @api {get} /api/pay/adv-detail
     * @apiGroup pay
     * @apiName  adv-detail
     * @apiVersion 1.0.0
     * @apiParam   {int} bonus_id  广告红包id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/pay/adv-detail
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *      "list": {
     *          "adv_name": "123",
     *          "adv_logo": "",
     *          "adv_remark": "[{\"type\":\"text\",\"text\":\"1111\"},{\"type\":\"img\",\"text\":\"2222\",\"url\":\"http://pgy-hongbao.oss-cn-beijing.aliyuncs.com/pic/15178199694799726193.jpg\"},{\"type\":\"text\",\"text\":\"3333\"}]",
     *          "nickname": "lm",
     *          "password": "打发是发达",
     *          "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83epWNNFDggVZ840zKbribenOCiaVG4ywMmn0SOHNiawWibQOubIiaMNrA7JPv3KWddQ4z57hhWjhBlsu3Iw/0"
     *      }
     * },
     * "code": 102
     * }
     */
    public function advBonusDetail()
    {
        $params = $this->request->param();
        $result = $this->payLogic->advBonusDetail($params);
        return $result;
    }


    /**
     * @api {get} /api/pay/adv_user
     * @apiGroup pay
     * @apiName  adv_user
     * @apiVersion 1.0.0
     * @apiParam   {int} uid  用户uid
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/pay/adv_user
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *      "list": {
     *          "nickname": "执迷的鲸鱼",
     *          "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTLzhOXtsoLCg/0",
     *          "balance": "-1.8000"
     *      }
     * },
     * "code": 102
     * }
     */
    public function getAdvUserInfo()
    {
        $params = $this->request->param();
        $result = $this->payLogic->getAdvUserInfo($params);
        return $result;
    }
}