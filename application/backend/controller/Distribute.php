<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/10
 * Time: 下午10:32
 * @introduce
 */
namespace app\backend\controller;

use app\backend\logic\DistributeLogic;
use think\Request;

class Distribute extends BaseAdmin
{
    protected $distribute;
    protected $disValidate;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->distribute = new DistributeLogic();
        $this->disValidate = new \app\backend\validate\Distribute();
    }

    /**
     * @api {get} /backend/distribute/list 条件搜索分销收益列表
     * @apiGroup distribute
     * @apiName  list
     * @apiVersion 1.0.0
     * @apiParam   {string} name 发包者昵称
     * @apiParam   {string} to_name 受益人昵称
     * @apiParam   {string} start_time 开始时间
     * @apiParam   {string} end_time 结束时间
     * @apiParam   {int} page 当前页
     * @apiParam   {int} size 每页数
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/distribute/list
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *      "total": 1,
     *      "list": [
     *               {
     *                 "id": 2, // 提成id
     *                 "bonus_id": 348, // 红包id
     *                 "uid": "okPcX0R75LjFNEPEzUOEsYWGD7Vw", //发包人openid
     *                 "bonus_money": "5.00", // 发红包金额
     *                 "payable_money": "5.00", // 被领取金额
     *                 "to_uid": "okPcX0boRIt0ecKoNvVFTRQ5VJYQ", // 受益人openid
     *                 "commission": "0.0500", // 昵称金额
     *                 "created_at": "2018-01-10 20:48:45", //提成时间
     *                 "updated_at": "2018-01-10 20:48:45",
     *                 "name": "A.老罗来了", // 发包人昵称
     *                 "to_name": "执迷的鲸鱼" // 受益人昵称
     *                },
     *            ...
     *      ]
     *  },
     *  "code": 0
     * }
     */
    public function searchRows()
    {
        $param = $this->params;
        $this->paramsValidate($this->disValidate, 'list', $param);

        $result = $this->distribute->searchRows($param);

        return $result;
    }

    /**
     * @api {get} /backend/distribute/detail 条件搜索分销收益明细列表
     * @apiGroup distribute
     * @apiName  detail
     * @apiVersion 1.0.0
     * @apiParam   {string} openid 选中者openid
     * @apiParam   {string} uid openid搜索框
     * @apiParam   {string} start_time 开始时间
     * @apiParam   {string} end_time 结束时间
     * @apiParam   {int} depth 层级
     * @apiParam   {int} page 当前页
     * @apiParam   {int} size 每页数
     * @apiSuccess {int} status 调用状态 1-调用成功 0-调用失败
     * @apiSuccess {int} code   状态响应码 为0时表示无错误发生，大于0时表示发生了特定错误
     * @apiSuccess {string} message 提示消息
     * @apiSuccess {Object} data 数据部分,忽略
     * @apiSampleRequest http://apitest.jkxxkj.com/backend/distribute/detail
     * @apiSuccessExample {json} Response 200 Example
     * {
     *  "status": 1,
     *  "message": "获取成功",
     *  "data": {
     *      "total": 12,
     *      "info": {
     *                  "nickname": "ZzHh",
     *                  "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKibIe3jyIyFF0VYRTXDQyia9UFTChfIj2Yic9gmcpAkn6O0AGOtOjg1gQKDhKZLQPvukblJ8FPHoVDg/0",
     *                  "count": 2,
     *                  "count_one": 1,
     *                  "count_second": 1,
     *                  "binding_time": "2018-01-13",
     *                  "money": "1.10",
     *                  "pid": "okPcX0boRIt0ecKoNvVFTRQ5VJYQ",
     *                  "pid_nickname": "执迷的鲸鱼",
     *                  "pid_avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTIV2ia1uJTNnw3MBWtqWZjOhovnrf5xoknVPocqacBibmwvUpmhVRvKzR8bF7CyTV3tuvkW41vLPv6g/0"
     *          },
     *      "list": [
     *                {
     *                  "id": 42,
     *                  "uid": "okPcX0e5JYKQnNMfiwfKG_hRGbQY",
     *                  "pid": "okPcX0YqZ4TEg2mXFlRpkx-Muxx8",
     *                  "depth": 2,
     *                  "path": "||okPcX0boRIt0ecKoNvVFTRQ5VJYQ||okPcX0QZTrJpOtnER2ZDOncg5SVU||okPcX0YqZ4TEg2mXFlRpkx-Muxx8||okPcX0e5JYKQnNMfiwfKG_hRGbQY",
     *                  "created_at": "2018-01-16 15:11:01",
     *                  "time": "2018-01-16",
     *                  "num": 2,
     *                  "money": "0.44",
     *                  "nickname": "童泽平",
     *                  "avatarulr": "https://wx.qlogo.cn/mmopen/vi_32/MJqp9UoIISX2icCibx11BGpDiahhaRvPvW4G3pV6Aic07EgCkL5W6NkcR6ibAiaOXbdLgoYnh9aDMge1o6cpfq3aAATQ/0",
     *                  "gender": 1
     *          }
     *      ]
     *  },
     *  "code": 202
     * }
     */
    public function searchDetailRows()
    {
        $param = $this->params;
        $this->paramsValidate($this->disValidate, 'detail', $param);

        $result = $this->distribute->searchDetailRows($param);

        return $result;
    }
}