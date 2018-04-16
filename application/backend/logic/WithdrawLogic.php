<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/2/7
 * Time: 下午5:03
 * @introduce
 */
namespace app\backend\logic;

use app\common\logic\BaseLogic;
use app\payment\logic\PayLogic;
use app\payment\model\Wallet;
use app\payment\model\WithdrawReview;
use app\user\model\User;
use think\Db;
use think\Exception;

class WithdrawLogic extends BaseLogic
{
    protected $withdrawModel;
    protected $user;

    public function __construct()
    {
        $this->withdrawModel = new WithdrawReview();
        $this->user = new User();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-07
     *
     * @description 后台审核列表
     * @param array $param
     * @return array
     */
    public function searchRows(array $param)
    {
        $page = $param['page'] ?? 1;
        $size = $param['size'] ?? 10;
        $where = [];
        if (!empty($param['uid'])) {
            $where['uid'] = $param['uid'];
        }
        if (!empty($param['status'])) {
            $where['status'] = $param['status'];
        }
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $where['updated_at'] = ['between', [$param['start_time'],$param['end_time']]];
        }elseif (!empty($param['start_time'])) {
            $where['updated_at'] = ['gt',$param['start_time']];
        }elseif (!empty($param['end_time'])) {
            $where['updated_at'] = ['lt',$param['end_time']];
        }
        $data = $this->withdrawModel->searchRows($where,$page,$size);
        //获取昵称
        if (!empty($data)) {
            $name_list = array_column($data,'uid');
            $name = $this->user->getNameList($name_list);
            $a = [];
            foreach ($name as $k => $v) {
                $a[$v['openid']] = $v['nickname'];
            }
            foreach ($data as $k => $v) {
                $data[$k]['name'] = $a[$v['uid']] ?? '';
                $data[$k]['status'] = ($v['status'] == 1)?'申请中':($v['status'] == 2 ? '提现成功': '提现失败');
            }
        }
        $total = $this->withdrawModel->getCount($where);
        return $this->ajaxSuccess(202,['total'=>$total,'list'=>$data]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-09
     *
     * @description 申请编辑
     * @param array $param
     * @return array
     */
    public function editReview(array $param)
    {
        try {
            Db::startTrans();
            $money = $this->withdrawModel->getOne(['id'=>$param['id'],'status'=>1]);
            if (!empty($money)) {
                if ($param['status'] == 2) {
                    $condition = [
                        'uid' => $money['uid'],
                        'money' => $money['money'],
                        'enterprise_type' => 4, // 人工提现
                        'check_name' => $param['check_name'] ?? '',//审核员
                    ];
                    $pay = new PayLogic();
                    $b = $pay->EnterprisePay($condition);
                    if ($b['status'] == 0) {
                        Db::rollback();
                        return $this->ajaxError(207);
                    }
                }elseif ($param['status'] == 0) {
                    $wallet = new Wallet();
                    //退回余额
                    $b = $wallet->setBalance($money['uid'],$money['money']);
                    if (!$b) {
                        Db::rollback();
                        return $this->ajaxError(207);
                    }
                }
                $this->withdrawModel->editReview(['id'=>$param['id'],'status'=>$param['status']]);
            }else{
                return $this->ajaxError(205);
            }
            Db::commit();
            return $this->ajaxSuccess(201);
        } catch (Exception $exception) {
            Db::rollback();
            return $this->ajaxError(207);
        }
    }
}