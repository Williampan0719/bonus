<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/6
 * Time: 09:34
 * @introduce
 */

namespace app\backend\logic;

use app\common\logic\BaseLogic;
use app\payment\model\Bonus;
use app\payment\model\BonusDetail;
use app\payment\model\BonusReceive;
use app\payment\model\BonusRemark;
use app\user\model\User;
use think\Exception;

class BonusLogic extends BaseLogic
{
    protected $bonusModel;
    protected $user;

    public function __construct()
    {
        $this->bonusModel = new Bonus();
        $this->user = new User();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 红包列表
     * @param array $params
     * @return array
     */
    public function bonusList(array $params)
    {
        try {
            $page = $params['page'] ?? config('paginate.default_page');
            $size = $params['size'] ?? config('paginate.default_size');
            $total = $this->bonusModel->bonusCount();
            if ($total > 0) {
                $bonusList = $this->bonusModel->bonusList($page, $size);
                if ($bonusList != false) {
                    $userModel=new User();
                    $bonusDetailModel=new BonusDetail();
                    foreach ($bonusList as $key=>$vo){
                        $bonusList[$key]['is_pay'] = (($vo['is_pay'] == 0) ? '未支付' : '已支付');
                        $receive_money=$bonusDetailModel->getReceiveMoney($vo['id']);
                        //$num=$bonusDetailModel->checkReceDone($vo['id']);
                        $bonusList[$key]['receive_num']=$vo['receive_bonus_num'];
                        $bonusList[$key]['receive_money']=''.$receive_money;
                        //$bonusList[$key]['refund_money']=''.($vo['bonus_money']-$receive_money);
                        $userInfo=$userModel->userDetail($vo['uid']);
                        $bonusList[$key]['nickname']=$userInfo->nickname;
                        $bonusList[$key]['avatarulr']=$userInfo->avatarulr;
//                        if($vo['finish_at']==null){
//                            $bonusList[$key]['status']=1;//未完成
//                        }elseif ($vo['finish_at']!=null && $num=0){
//                            $bonusList[$key]['status']=2;//完成
//                        }else{
//                            $bonusList[$key]['status']=3;//过期
//                        }
                        $bonusList[$key]['status'] = $vo['is_done'] == 1 ? '已领取完' : (!$vo['finish_at'] ? '未领取完' : '已过期-未领取完');
                    }
                    $result = $this->ajaxSuccess(202, ['list' => $bonusList, 'total' => $total]);
                } else {
                    $result = $this->ajaxSuccess(202, ['list' => [], 'total' => $total]);
                }
            } else {
                $result = $this->ajaxSuccess(202, ['list' => [], 'total' => $total]);
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(205);
        }
        return $result;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-06
     *
     * @description 领取者详情
     * @param array $params
     * @return array
     */
    public function bonusDetail(array $params)
    {
        try {
            $bonusReceiveModel=new BonusReceive();
            $list=$bonusReceiveModel->bonusReceiveList($params['bonus_id']);
            if($list!=false){
                $userModel=new User();
                $bonusDetail=new BonusDetail();
                foreach ($list as $key=>$vo){
                    if($vo['detail_id']!=0){
                        $bonusDet=$bonusDetail->bonusReceiveDetail($vo['detail_id']);
                        $list[$key]['receive_money']=$bonusDet['receive_money'];
                    }else{
                        $list[$key]['receive_money']=0;
                    }
                    $userInfo=$userModel->userDetail($vo['receive_uid']);
                    $list[$key]['nickname']=$userInfo->nickname;
                    $list[$key]['avatarulr']=$userInfo->avatarulr;
                }
                $result = $this->ajaxSuccess(202, ['list' => $list]);
            }else{
                $result = $this->ajaxSuccess(202, ['list' => []]);
            }
        } catch (Exception $exception) {
            $result = $this->ajaxError(205);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-09
     *
     * @description 广告红包搜索列表
     * @param array $param
     * @return array
     */
    public function advBonusSearchRows(array $param)
    {
        try {
            $page = $param['page'] ?? 1;
            $size = $param['size'] ?? 10;
            $where = ['class'=>2];
            //发包人
            if (!empty($param['name'])) {
                $ids = $this->user->getOpenid(['nickname'=>$param['name']]);
                $where['uid'] = ['in',array_column($ids,'openid')];
            }
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
            $data = $this->bonusModel->searchBonusList($where,$page,$size);
            //获取昵称
            if (!empty($data)) {
                $name_list = array_column($data,'uid');
                $name = $this->user->getNameList($name_list);
                $a = [];
                foreach ($name as $k => $v) {
                    $a[$v['openid']]['nickname'] = $v['nickname'];
                    $a[$v['openid']]['avatarulr'] =$v['avatarulr'];
                }
                $bonusDetailModel=new BonusDetail();
                foreach ($data as $k => $v) {
                    $data[$k]['name'] = $a[$v['uid']]['nickname'] ?? '';
                    $data[$k]['avatarulr'] = $a[$v['uid']]['avatarulr'] ?? '';
                    $data[$k]['receive_bonus_num'] = $v['receive_bonus_num'].'/'.$v['bonus_num'];
                    $receive_money=$bonusDetailModel->getReceiveMoney($v['id']);
                    $data[$k]['receive_money'] = $receive_money;
                    $data[$k]['refund_money'] = ($v['finish_at'] ? ($v['bonus_money']-$receive_money) : '0.00');
                    $data[$k]['status'] = $v['is_done'] == 1 ? '已领取完' : (!$v['finish_at'] ? '未领取完' : '已过期-未领取完');
                }
            }
            $total = $this->bonusModel->getUserCount($where);

            $result = $this->ajaxSuccess(202, ['total'=>$total,'list' => $data]);
        } catch (Exception $exception) {
            $result = $this->ajaxError(205);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-09
     *
     * @description 获取广告详情
     * @param array $param
     * @return array
     */
    public function advBonusSearchDetail(array $param)
    {
        try {
            $where = ['bonus_id'=>$param['id']];
            $remark = new BonusRemark();
            $data = $remark->getOne($where);

            $result = $this->ajaxSuccess(202, ['list' => $data]);
        } catch (Exception $exception) {
            $result = $this->ajaxError(205);
        }
        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-13
     *
     * @description 删除广告图片
     * @param array $param
     * @return array
     */
    public function delAdvRemark(array $param)
    {
        try {
            $where = ['bonus_id'=>$param['bonus_id']];
            $remark = new BonusRemark();
            $data = $remark->initRemark($where);

            $result = $this->ajaxSuccess(203, ['list' => $data]);
        } catch (Exception $exception) {
            $result = $this->ajaxError(204);
        }
        return $result;
    }
}