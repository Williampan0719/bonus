<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/6
 * Time: 11:21
 * @introduce
 */

namespace app\backend\logic;

use app\common\logic\BaseLogic;
use app\payment\model\BillInfo;
use app\payment\model\Order;
use app\user\model\User;
use think\Exception;

class OrderLogic extends BaseLogic
{
    protected $orderModel;
    protected $user;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->user = new User();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-27
     *
     * @description 充值记录表
     * @param array $param
     * @return array
     */
    public function orderRecharge(array $param)
    {
        try {
            $page = $param['page'] ?? 1;
            $size = $param['size'] ?? 10;
            $where = ['bonus_id'=>['gt',0],'wx_money'=>['gt',0],'finish_at'=>['<>','']];
            //发包人
            if (!empty($param['name'])) {
                $ids = $this->user->getOpenid(['nickname'=>$param['name']]);
                $where['uid'] = ['in',array_column($ids,'openid')];
            }

            if (!empty($param['start_time']) && !empty($param['end_time'])) {
                $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
            }elseif (!empty($param['start_time'])) {
                $where['created_at'] = ['gt',$param['start_time']];
            }elseif (!empty($param['end_time'])) {
                $where['created_at'] = ['lt',$param['end_time']];
            }
            $total = $this->orderModel->orderCount($where);
            if ($total > 0) {
                $billInfoModel=new BillInfo();
                $orderList = $this->orderModel->orderList($page, $size,$where);
                $name_list = array_column($orderList,'uid');
                $name = $this->user->getNameList($name_list);
                $a = [];
                foreach ($name as $k => $v) {
                    $a[$v['openid']] = $v['nickname'];
                }
                foreach ($orderList as $key => $value) {
                    //微信流水号
                    $billInfo = $billInfoModel->billInfoDetail($value['order_sn']);
                    $orderList[$key]['trade_no']=$billInfo['trade_no'] ?? '';
                    $orderList[$key]['balance_money'] = sprintf("%01.2f",$value['money'] - $value['wx_money']);
                    $orderList[$key]['nickname'] = $a[$value['uid']];
                }
            }
            $result = $this->ajaxSuccess(202, ['list' => $orderList ?? [], 'total' => $total]);
        } catch (Exception $exception) {
            $result=$this->ajaxError(205);
        }
        return $result;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-27
     *
     * @description 提现记录表
     * @param array $param
     * @return array
     */
    public function orderWithdraw(array $param)
    {
        try {
            $page = $param['page'] ?? 1;
            $size = $param['size'] ?? 10;
            $where = ['bonus_id'=>0];
            //发包人
            if (!empty($param['name'])) {
                $ids = $this->user->getOpenid(['nickname'=>$param['name']]);
                $where['uid'] = ['in',array_column($ids,'openid')];
            }

            if (!empty($param['start_time']) && !empty($param['end_time'])) {
                $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
            }elseif (!empty($param['start_time'])) {
                $where['created_at'] = ['gt',$param['start_time']];
            }elseif (!empty($param['end_time'])) {
                $where['created_at'] = ['lt',$param['end_time']];
            }
            $total = $this->orderModel->orderCount($where);
            if ($total > 0) {
                $billInfoModel=new BillInfo();
                $orderList = $this->orderModel->orderList($page, $size,$where);
                $name_list = array_column($orderList,'uid');
                $name = $this->user->getNameList($name_list);
                $a = [];
                foreach ($name as $k => $v) {
                    $a[$v['openid']] = $v['nickname'];
                }
                foreach ($orderList as $key => $value) {
                    //微信流水号
                    $billInfo = $billInfoModel->billInfoDetail($value['order_sn']);
                    $orderList[$key]['trade_no']=$billInfo['trade_no'] ?? '';
                    $orderList[$key]['nickname'] = $a[$value['uid']];
                    $orderList[$key]['status'] = $value['finish_at'] == null ? '提现失败' : '提现成功';
                }
            }
            $result = $this->ajaxSuccess(202, ['list' => $orderList ?? [], 'total' => $total]);
        } catch (Exception $exception) {
            $result=$this->ajaxError(205);
        }
        return $result;
    }
}