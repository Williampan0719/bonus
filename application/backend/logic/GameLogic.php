<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/29
 * Time: 下午4:06
 */

namespace app\backend\logic;


use app\common\logic\BaseLogic;
use app\game\model\GameCoinLog;
use app\system\model\SystemConfig;
use app\system\model\SystemConfigBwheel;
use app\system\model\SystemConfigVirtual;
use app\user\model\User;
use extend\helper\Utils;
use think\Cache;

class GameLogic extends BaseLogic
{
    protected $log;
    protected $user;
    private $type;

    function __construct()
    {
        $this->type = [
            '0' => '发起挑战',
            '1' => '押注',
            '2' => '押注奖励',
            '3' => '应战',
            '4' => '应战奖励',
            '5' => '欢乐大转盘',
            '6' => '欢乐大转盘奖励',
            '7' => '充值',
            '8' => '签到',
            '9' => '退款',
            '10'=> '挑战奖励',
            '11'=> '平局',
        ];
        $this->log = new GameCoinLog();
        $this->user = new User();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-01
     *
     * @description 后台明细搜索
     * @param array $param
     * @return array
     */
    public function searchDetailRows(array $param)
    {
        $page = $param['page'] ?? 1;
        $size = $param['size'] ?? 10;
        $where = [];
        if (!empty($param['openid'])) {
            $where['uid'] = $param['openid'];
        }
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
        }elseif (!empty($param['start_time'])) {
            $where['created_at'] = ['gt',$param['start_time']];
        }elseif (!empty($param['end_time'])) {
            $where['created_at'] = ['lt',$param['end_time']];
        }
        //类别筛选
        if (!empty($param['type'])) {
            if (isset($param['type'][1])) {
                $where['type'] = $param['type'][1];
                if ($param['type'][1] == 10) {
                    $where['type'] = ['in',[4,10]];
                }
            }elseif ($param['type'][0] == 1) {
                $where['type'] = ['in',[0,1,3,5]];
            }else{
                $where['type'] = ['in',[8,6,2,4,10]];
            }
        }
        $data = $this->log->searchDetailRows($where,$page,$size);
        $a = [];
        //获取昵称
        if (!empty($data)) {
            $name_list = array_column($data,'uid');
            $name = $this->user->getNameList($name_list);
            foreach ($name as $k => $v) {
                $a[$v['openid']] = $v['nickname'];
            }
        }
        foreach ($data as $k => $v) {
            $data[$k]['symbol'] = ($v['symbol'] == 0) ? '支出': '收入';
            $data[$k]['nickname'] = $a[$v['uid']] ?? '';
            $data[$k]['type'] = $this->type[$v['type']];
        }
        $total = $this->log->getCount($where);
        return $this->ajaxSuccess(202,['total'=>$total,'list'=>$data]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-01
     *
     * @description 后台充值搜索
     * @param array $param
     * @return array
     */
    public function getRechargeList(array $param)
    {
        $page = $param['page'] ?? 1;
        $size = $param['size'] ?? 10;
        $where = [];
        if (!empty($param['openid'])) {
            $where['uid'] = $param['openid'];
        }
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
        }elseif (!empty($param['start_time'])) {
            $where['created_at'] = ['gt',$param['start_time']];
        }elseif (!empty($param['end_time'])) {
            $where['created_at'] = ['lt',$param['end_time']];
        }
        $where['type'] = 7;
        $data = $this->log->searchDetailRows($where,$page,$size);
        $a = [];
        //获取昵称
        if (!empty($data)) {
            $name_list = array_column($data,'uid');
            $name = $this->user->getNameList($name_list);
            foreach ($name as $k => $v) {
                $a[$v['openid']] = $v['nickname'];
            }
        }
        $virtual = Utils::getVirtualConfig();
        $money = array_flip($virtual);

        foreach ($data as $k => $v) {
            $data[$k]['symbol'] = ($v['symbol'] == 0) ? '支出': '收入';
            $data[$k]['nickname'] = $a[$v['uid']] ?? '';
            $data[$k]['money'] = $money[$v['coin']];
        }

        $total = $this->log->getCount($where);
        return $this->ajaxSuccess(202,['total'=>$total,'list'=>$data]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-11
     *
     * @description 虚拟币配置列表
     * @return array
     */
    public function virtualConfigList()
    {
        $virtual = new SystemConfigVirtual();
        $list = $virtual->getAll();
        return $this->ajaxSuccess(202,['list'=>$list]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-10
     *
     * @description 新增配置
     * @param array $param
     * @return array
     */
    public function addVirtualConfig(array $param)
    {
        $virtual = new SystemConfigVirtual();
        $data = $virtual->addVirtualConfig(['money'=>$param['money'],'coin'=>$param['coin']]);
        Cache::rm('virtual');
        if ($data) {
            return $this->ajaxSuccess(200);
        }
        return $this->ajaxError(206);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-10
     *
     * @description 编辑配置
     * @param array $param
     * @return array
     */
    public function editVirtualConfig(array $param)
    {
        $virtual = new SystemConfigVirtual();
        $data = $virtual->editVirtualConfig(['money'=>$param['money'],'coin'=>$param['coin']],$param['id']);
        Cache::rm('virtual');
        if ($data) {
            return $this->ajaxSuccess(201);
        }
        return $this->ajaxError(207);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-10
     *
     * @description 删除配置
     * @param array $param
     * @return array
     */
    public function delVirtualConfig(array $param)
    {
        $virtual = new SystemConfigVirtual();
        $data = $virtual->delVirtualConfig($param['id']);
        Cache::rm('virtual');
        if ($data) {
            return $this->ajaxSuccess(203);
        }
        return $this->ajaxError(204);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-11
     *
     * @description 大转盘配置列表
     * @return array
     */
    public function bwheelConfigList()
    {
        $virtual = new SystemConfigBwheel();
        $list = $virtual->getAll();
        return $this->ajaxSuccess(202,['list'=>$list]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-10
     *
     * @description 新增配置
     * @param array $param
     * @return array
     */
    public function addBwheelConfig(array $param)
    {
        $virtual = new SystemConfigBwheel();
        $data = $virtual->addBwheelConfig(['sequence'=>$param['sequence'],'prize'=>$param['prize'],'reward'=>$param['reward'],'rate'=>$param['rate'],'type'=>$param['type']]);
        Cache::rm('bwheel');
        if ($data) {
            return $this->ajaxSuccess(200);
        }
        return $this->ajaxError(206);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-10
     *
     * @description 编辑配置
     * @param array $param
     * @return array
     */
    public function editBwheelConfig(array $param)
    {
        $virtual = new SystemConfigBwheel();
        $data = $virtual->editBwheelConfig(['sequence'=>$param['sequence'],'prize'=>$param['prize'],'reward'=>$param['reward'],'rate'=>$param['rate']],$param['id']);
        Cache::rm('bwheel');
        if ($data) {
            return $this->ajaxSuccess(201);
        }
        return $this->ajaxError(207);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-10
     *
     * @description 删除配置
     * @param array $param
     * @return array
     */
    public function delBwheelConfig(array $param)
    {
        $virtual = new SystemConfigBwheel();
        $data = $virtual->delBwheelConfig($param['id']);
        Cache::rm('bwheel');
        if ($data) {
            return $this->ajaxSuccess(203);
        }
        return $this->ajaxError(204);
    }
}