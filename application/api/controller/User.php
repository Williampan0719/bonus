<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/5
 * Time: 下午2:13
 */

namespace app\api\controller;


use app\payment\logic\BonusLogic;
use app\payment\logic\DistributeLogic;
use app\payment\logic\WalletLogic;
use app\system\logic\SystemImageLogic;
use app\user\logic\UserLogic;
use app\user\logic\UserPowerLogic;
use extend\service\RedisService;
use extend\service\WechatService;
use think\Hook;
use think\Request;


class User extends BaseApi
{
    protected $user = null;
    protected $wechat = null;
    protected $bonusLogic;
    protected $userValidate;

    function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->user = new UserLogic();
        $this->wechat = new WechatService();
        $this->bonusLogic = new BonusLogic();
        $this->userValidate = new \app\api\validate\User();
    }

    /**
     * @api {post} /api/user/user 用户登陆
     * @apiGroup user
     * @apiName  user
     * @apiVersion 1.0.0
     * @apiParam {string} code  小程序code
     * @apiParam {string} encryptedData  加密数据
     * @apiParam {string} iv  解密密数据
     * @apiParam {string} channel  渠道（1赶紧说，2赶快说）
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/user/user
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
    public function userLogin()
    {
        $param = $this->request->param();
        $info = $this->user->getSessionKey($param);
        return $info;
    }

    /**
     * @api {post} /api/user/mobile 获取手机号
     * @apiGroup user
     * @apiName  mobile
     * @apiVersion 1.0.0
     * @apiParam {string} openid  微信openid
     * @apiParam {string} code  小程序code
     * @apiParam {string} encryptedData  加密数据
     * @apiParam {string} iv  解密密数据
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/user/mobile
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
    public function getMobile()
    {
        $param = $this->request->param();
        $info = $this->user->getMobile($param);
        return $info;
    }

    /**
     * @api {post} /api/user/run 语音抢红包
     * @apiGroup user
     * @apiName  run
     * @apiVersion 1.0.0
     * @apiParam {string} bonus_id 红包id
     * @apiParam {string} openid   唯一
     * @apiParam {int} timelength  语音时长
     * @apiParam {string} form_id  分享id
     * @apiParam {string} page  路径
     * @apiParam {int} type 0分享红包，1大厅红包
     * @apiParam {int} class 红包种类 0口令红包 1语音红包
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/user/run
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
    public function getAudio()
    {
        $param = $this->request->param();
        if (isset($param['class']) && $param['class'] == 1) {
            $this->paramsValidate($this->userValidate, 'listen', $param);
            return $this->user->listenBonus($param);
        }
        #$param['file'] = $this->request->file();
        return $this->user->audioExec($param, $_FILES);
    }

    /**
     * @api {post} /api/user/qrcode 生成二维码
     * @apiGroup user
     * @apiName  qrcode
     * @apiVersion 1.0.0
     * @apiParam {string} path 二维码路径
     * @apiParam {int} width  二维码宽度（默认430)
     * @apiparam {int} class 1红包id 2讨红包id 默认1
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest https://miyin.my/api/user/qrcode
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
    public function createQrcode()
    {
        $param = $this->request->param();
        $res = $this->user->createQrcode($param['scene'], $param['path'], $param['width'] ?? 430, $param['bonus_id'] ?? 0, $param['uid'] ?? 0, $param['class'] ?? 1);
        return $res;
    }

    /**
     * @api {get} /api/user/bonus 红包记录(在用)
     * @apiGroup user
     * @apiName  bonus
     * @apiVersion 1.0.0
     * @apiParam   {string} uid 用户的uid
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/bonus
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function bonusRecord()
    {
        $params = $this->request->param();
        $this->paramValidate('uid');
        $result = $this->bonusLogic->bonusRecord($params);
        return $result;
    }

    /**
     * @api {get} /api/user/index 红包首页
     * @apiGroup user
     * @apiName  index
     * @apiVersion 1.0.0
     * @apiParam   {int} bonus_id 红包id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/index
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function bonusIndex()
    {
        $params = $this->request->param();
        $this->paramValidate('bonus_id');
        $result = $this->bonusLogic->bonusIndex($params);
        return $result;
    }

    /**
     * @api {get} /api/user/share 红包分享页面接口
     * @apiGroup user
     * @apiName  share
     * @apiVersion 1.0.0
     * @apiParam   {int} bonus_id 红包id
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/share
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function bonusShare()
    {
        $params = $this->request->param();
        $this->paramValidate('bonus_id');
        $result = $this->bonusLogic->bonusShare($params);
        return $result;
    }

    /**
     * @api {get} /api/user/check 判断红包状态
     * @apiGroup user
     * @apiName  check
     * @apiVersion 1.0.0
     * @apiParam   {int} bonus_id 红包id
     * @apiParam   {string} openid 用户openid
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/check
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function checkVoiceBonus()
    {
        $params = $this->request->param();
        $this->paramsValidate($this->userValidate, 'check', $params);
        $result = $this->user->checkVoiceBonus($params);
        return $result;
    }

    /**
     * @api {get} /api/user/withdrawals 提现展示(在用)
     * @apiGroup user
     * @apiName  withdrawals
     * @apiVersion 1.0.0
     * @apiParam   {string} uid 用户的uid
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} order_info 支付参数
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/withdrawals
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function userWithdrawals()
    {
        $params = $this->request->param();
        $this->paramValidate('uid');
        $walletLogic = new WalletLogic();
        $result = $walletLogic->userWallet($params);
        return $result;
    }

    /**
     * @api {post} /api/user/precondition 分享前提保存信息
     * @apiGroup user
     * @apiName  binding
     * @apiVersion 1.0.0
     * @apiParam {string} openid openid
     * @apiParam {int} mobile  手机号
     * @apiParam {string} true_name 真实姓名
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   仅供参考
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/binding
     * @apiSuccessExample {json} Response 200 Example
     * {
     *   "status": 1,
     *   "message": "添加成功",
     *   "data": [],
     *   "code": 0
     * }
     */
    public function savePreconditionInfo()
    {
        $params = $this->request->param();
        $this->paramsValidate($this->userValidate, 'precondition', $params);
        $a = $this->user->savePreconditionInfo($params);
        return $a;
    }

    /**
     * @api {get} /api/user/binding 绑定用户关系
     * @apiGroup user
     * @apiName  binding
     * @apiVersion 1.0.0
     * @apiParam {string} openid 点击进入者openid
     * @apiParam {string} from_uid  邀请来源uid(父id)
     * @apiParam {string} form_id 红包相关标志id(推送用)
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   仅供参考
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/binding
     * @apiSuccessExample {json} Response 200 Example
     * {
     *   "status": 1,
     *   "message": "添加成功",
     *   "data": [],
     *   "code": 0
     * }
     */
    public function bindingUser()
    {
        $params = $this->request->param();
        $this->paramsValidate($this->userValidate, 'binding', $params);
        $a = $this->user->bindingUser($params);
        return $a;
    }


    /**
     * @api {get} /api/user/my 我的页面
     * @apiGroup user
     * @apiName  my
     * @apiVersion 1.0.0
     * @apiParam {string} openid 用户openid
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   仅供参考
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/my
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *      "data": {
     *          "level": "学前班", //等级
     *          "logo": ""  //用户头像
     *          "invite_logo": "", // 邀请码
     *          "total": 4, // 推荐的好友个数
     *          "has_info": 1 //1表示已填认证 0表示未填
     *          "list": {
     *              "2018-01-12": [
     *                        {
     *                          "id":1
     *                          "num": 1, // 红包数
     *                          "money": "0.00", //贡献金额
     *                          "uid": "okPcX0WBM_b1yllNqAG2dCNiuFYM", // 贡献者
     *                          "time": "2018-01-12", //邀请时间
     *                          "nickname": "日暮", // 贡献者昵称
     *                          "gender": 1 // 性别
     *                          "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKlUrGOYdvIYiaf48EeQ4zg8ic97lGSYzDljr9fbJXNIGnsopNjb6AR6VnlO8n3k7m5RX3IwwY1LdTQ/0" // 头像
     *                        },
     *                        {
     *                          "id":1
     *                          "num": 1,
     *                          "money": "0.05",
     *                          "uid": "okPcX0e5JYKQnNMfiwfKG_hRGbQY",
     *                          "time": "2018-01-12",
     *                          "nickname": "童泽平",
     *                          "gender": 1
     *                          "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/MJqp9UoIISX2icCibx11BGpDiahhaRvPvW4G3pV6Aic07EgCkL5W6NkcR6ibAiaOXbdLgoYnh9aDMge1o6cpfq3aAATQ/0"
     *                        }
     *              ],
     *          }
     *          "list2": {
     *             "2018-01-13": [
     *                        {
     *                          "id": 35,
     *                          "uid": "okPcX0YqZ4TEg2mXFlRpkx-Muxx8",
     *                          "pid": "okPcX0QZTrJpOtnER2ZDOncg5SVU",
     *                          "created_at": "2018-01-13 13:11:20",
     *                          "time": "2018-01-13",
     *                          "num": 3,
     *                          "money": "0.66",
     *                          "nickname": "Logx",
     *                          "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83eow4tiaNxALtvxv2fZAJZ7vZ6FMJwbx8rT5mvTqa81QMibyVlHcl4Amv3Rq1VkrTrXm6sJVdS9JF7iaQ/0",
     *                          "gender": 1,
     *                          "from_name": "ZzHh" //由某某推荐
     *                         }
     *                  ],
     *             }
     *      },
     *  "code": 102
     *  }
     */
    public function myPage()
    {

        $params = $this->request->param();
        $this->paramsValidate($this->userValidate, 'myPage', $params);
        $dis = new DistributeLogic();
        return $dis->myPage($params);
    }

    /**
     * @api {get} /api/user/capital 个人资金明细
     * @apiGroup user
     * @apiName  capital
     * @apiVersion 1.0.0
     * @apiParam {string} uid 发红包者openid
     * @apiParam {string} from_openid  邀请来源openid(父id)
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/capital
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function userCapital()
    {
        $params = $this->request->param();
        $this->paramValidate('uid');
        $result = $this->user->userCapital($params);
        return $result;
    }

    /**
     * @api {post} /api/user/form-id form_id存redis
     * @apiGroup user
     * @apiName  form-id
     * @apiVersion 1.0.0
     * @apiParam {string} uid 登录者openid   不能和bonus_id 共存
     * @apiParam {int} bonus_id 红包id    不能和uid 共存
     * @apiParam {file} form-data 文件数据流
     *
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   仅供参考
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/form-id
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function formIdAdd()
    {
        $params = $this->request->param();
        $data = json_decode($params['json']);
        $redisService = new RedisService();
        foreach ($data->form_id as $key => $vo) {
            $redisService->rpush($data->openid, $vo);
        }
        return $this->ajaxSuccess(200);
    }

    /** 投诉
     * auth smallzz
     * @return array
     */
    public function complaints()
    {
        return $this->ajaxSuccess(108);
    }

    /**
     * @api {post} /api/user/ctcode 创建小程序二维码
     * @apiGroup user
     * @apiName  ctcode
     * @apiVersion 1.0.0
     * @apiParam {string} uid 登录者openid   不能和bonus_id 共存
     * @apiParam {int} bonus_id 红包id    不能和uid 共存
     * @apiParam {file} form-data 文件数据流
     *
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   仅供参考
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/ctcode
     * @apiSuccessExample {json} Response 200 Example
     * {
     * }
     */
    public function getImg()
    {
        $param = $this->request->param();
        return $this->user->createCodeImg($param, $_FILES);
    }

    /**
     * @api {get} /api/user/user-power 用户体力展示
     * @apiGroup user
     * @apiName user-power
     * @apiVersion 1.0.0
     * @apiParam {string} openid 登录者openid
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   仅供参考
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/user-power
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *  "list": {
     *      "have": 15, // 用户当前体力值
     *      "expend": -2 // 须消耗值
     *      }
     *  },
     *  "code": 202
     *  }
     */
    public function hallUserPower()
    {
        $params = $this->request->param();
        $this->paramsValidate($this->userValidate, 'power', $params);
        $power = new UserPowerLogic();
        $result = $power->getUserPower($params);
        return $result;
    }

    /**
     * @api {get} /api/user/user-hot 获取热门推荐
     * @apiGroup user
     * @apiName user-hot
     * @apiVersion 1.0.0
     * @apiParam {string} openid 登录者openid
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   仅供参考
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/user-hot
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *  "list": {
     *      "have": 15, // 用户当前体力值
     *      "expend": -2 // 须消耗值
     *      }
     *  },
     *  "code": 202
     *  }
     */
    public function getHot()
    {
        $param = $this->request->param();
        return $this->user->getHot($param);
    }

    /**
     * @api {get} /api/user/ann 公告
     * @apiGroup user
     * @apiName ann
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   仅供参考
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/ann
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *
     *  "code": 202
     *  }
     */
    public function announcement()
    {
        return config('system')['ann'];
    }

    /**
     * @api {post} /api/user/qq-music qq音乐列表获取
     * @apiGroup user
     * @apiName  getQqMusic
     * @apiVersion 1.0.0
     * @apiParam {int} number 查询数量
     * @apiParam {int} p 查询页
     * @apiParam {string} condition 查询条件（歌手或歌曲）
     * @apiParam {int} guid  guid标识（非必传）
     *
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   仅供参考
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/api/user/qq-music
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "音乐列表获取成功",
     *  "data": {
     *      "0": {
     *          "id": "00378Vgm4TjsTs",
     *          "fsong": "Beggar",
     *          "fsinger": "黄子韬",
     *          "img_url": "http://imgcache.qq.com/music/photo/mid_album_90/O/Y/004NgqLV36tMOY.jpg",
     *          "player_url": "http://dl.stream.qqmusic.qq.com/C40000378Vgm4TjsTs.m4a?vkey=DC641B23C014E3520CD8FD1EFDCD8934A407FB3BBE5AFADDF56C865F723268A1D16F5407FF784FE02C9BA083C3AF03745DA97451CAF839B4&guid=2650353915&uin=0&fromtag=66"
     *      },
     *      "1": {
     *          "id": "001HXF9M3VhfVu",
     *          "fsong": "舍不得",
     *          "fsinger": "黄子韬",
     *          "img_url": "http://imgcache.qq.com/music/photo/mid_album_90/x/R/0016UZTE45uGxR.jpg",
     *          "player_url": "http://dl.stream.qqmusic.qq.com/C400001HXF9M3VhfVu.m4a?vkey=9C3FF007D6BA60E06FCC9B4EC6FD156E50CDF5D469016BEDA06226BEE23BFFB6A41FFA824942ADF7657C1648FBA2BBF38D3F6C2D113603F1&guid=2650353915&uin=0&fromtag=66"
     *      }
     *  },
     *  "code": 1400
     * }
     */
    public function getQqMusic()
    {
        $params = $this->request->param();
        $music = new \app\api\validate\Qmusic();
        $this->paramsValidate($music, 'music-search', $params);
        return $this->user->getQqMusic($params);
    }

    /**
     * @api {get} /api/user/version-config 版本控制
     * @apiGroup user
     * @apiName  version-config
     * @apiVersion 1.0.0
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {int} open_audit 1:开启 0:关闭
     * @apiSampleRequest https://apitest.jkxxkj.com/api/user/version-config
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *      "open_audit": 1
     *  },
     *  "code": 200
     *}
     */
    public function versionConfig()
    {
        $system = new SystemImageLogic();
        $result = $system->versionConfig();
        return $result;
    }

    /**
     * @api {post} /api/user/version-info 生成版本信息密钥
     * @apiGroup user
     * @apiName  version-info
     * @apiVersion 1.0.0
     * @apiHeader {string} client_version 客户端版本
     * @apiHeader {int} client_type 客户端类型 1
     * @apiParam {string} client_version 客户端版本
     * @apiParam {int} client_type 客户端类型 1
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} version_hash hash值
     * @apiSampleRequest https://apitest.jkxxkj.com/api/user/version-info
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *      "version_hash": ""
     *  },
     *  "code": 200
     *}
     */
    public function versionInfo()
    {
        $params = $this->request->param();
        $system = new SystemImageLogic();
        $result = $system->versionInfo($params);
        return $result;
    }

    /**
     * @api {post} /api/user/deVersion-info 解析版本信息密钥
     * @apiGroup user
     * @apiName  version-info
     * @apiVersion 1.0.0
     * @apiHeader {string} client_version 客户端版本
     * @apiHeader {int} client_type 客户端类型 1
     * @apiParam {string} hash 解密原本
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSuccess {string} version_hash hash值
     * @apiSampleRequest https://apitest.jkxxkj.com/api/user/version-info
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *      "version_hash": {
     *                     "client_version": "1.0.4",
     *                     "client_type": "1"
     *                     }
     *  },
     *  "code": 200
     *}
     */
    public function deVersionInfo()
    {
        $params = $this->request->param();
        $system = new SystemImageLogic();
        $result = $system->deVersionInfo($params);
        return $result;
    }
}