<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/10
 * Time: 下午10:32
 * @introduce
 */
namespace app\backend\logic;

use app\common\logic\BaseLogic;
use app\payment\model\Distribute;
use app\user\model\User;
use app\user\model\UserRelation;
use think\db;
use think\Exception;

class DistributeLogic extends BaseLogic
{
    protected $distribute;
    protected $user;

    public function __construct()
    {
        $this->distribute = new Distribute();
        $this->user = new User();
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-10
     *
     * @description 后台提成搜索
     * @param array $param
     * @return array
     */
    public function searchRows(array $param)
    {
        try {
            $page = $param['page'] ?? 1;
            $size = $param['size'] ?? 10;
            $where = [];
            //发包人
            if (!empty($param['name'])) {
                $ids = $this->user->getOpenid(['nickname'=>$param['name']]);
                $where['uid'] = ['in',array_column($ids,'openid')];
            }
            //受益人
            if (!empty($param['to_name'])) {
                $ids = $this->user->getOpenid(['nickname'=>$param['to_name']]);
                $where['to_uid'] = ['in',array_column($ids,'openid')];
            }

            if (!empty($param['start_time']) && !empty($param['end_time'])) {
                $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
            }elseif (!empty($param['start_time'])) {
                $where['created_at'] = ['gt',$param['start_time']];
            }elseif (!empty($param['end_time'])) {
                $where['created_at'] = ['lt',$param['end_time']];
            }
            $data = $this->distribute->searchRowsByUid($where,$page,$size);
            //获取昵称
            if (!empty($data)) {
                $name_list = array_merge(array_column($data,'uid'),array_column($data,'to_uid'));
                $name = $this->user->getNameList($name_list);
                $a = [];
                foreach ($name as $k => $v) {
                    $a[$v['openid']] = $v['nickname'];
                }
                foreach ($data as $k => $v) {
                    $data[$k]['name'] = $a[$v['uid']] ?? '';
                    $data[$k]['to_name'] = $a[$v['to_uid']] ?? '';
                }
            }

            $total = $this->distribute->getCount($where);

            $result = $this->ajaxSuccess(202, ['total'=>$total,'list' => $data]);

        } catch (Exception $exception) {

            $result = $this->ajaxError(205);
        }

        return $result;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-23
     *
     * @description 搜索分销明细列表
     * @param array $param
     * @return array
     */
    public function searchDetailRows(array $param)
    {
        try {
            //获取昵称头像
            $one_nick = $this->user->userDetailAll($param['openid'],'nickname,avatarulr');
            //获取时间
            $relation = new UserRelation();
            $one_time = $relation->getOne($param['openid']);
            $one_count = $relation->getGroupCount(['depth'=>['gt',$one_time['depth']],'path'=>['like',$one_time['path'].'%']]);
            $one_money = $this->distribute->getLastOne(['to_uid'=>$param['openid']]);
            $pid_nick = $this->user->userDetailAll($one_time['pid'],'nickname,avatarulr');
            $one = [
                'nickname'=>$one_nick['nickname'],
                'avatarulr'=>$one_nick['avatarulr'],
                'count'=>($one_count[0]['count(*)']??0) + ($one_count[1]['count(*)']??0),
                'count_one'=>$one_count[0]['count(*)'] ?? 0,
                'count_second'=>$one_count[1]['count(*)'] ?? 0,
                'binding_time'=>substr($one_time['created_at'],0,10),
                'money'=>sprintf("%01.2f",$one_money['all_commission']) ?? 0.00,
                'pid'=>$one_time['pid'],
                'pid_nickname'=>$pid_nick['nickname'],
                'pid_avatarulr'=>$pid_nick['avatarulr'],
            ];

            //获取列表
            $where = [];
            if (!empty($param['start_time']) && !empty($param['end_time'])) {
                $where['created_at'] = ['between', [$param['start_time'],$param['end_time']]];
            }elseif (!empty($param['start_time'])) {
                $where['created_at'] = ['gt',$param['start_time']];
            }elseif (!empty($param['end_time'])) {
                $where['created_at'] = ['lt',$param['end_time']];
            }
            $page = $param['page'] ?? 1;
            $size = $param['size'] ?? 10;

            $dis_where = ['to_uid'=>$param['openid']];
            if (!empty($param['depth'])) {
                $dis_where = ['to_uid'=>$param['openid'],'level'=>$param['depth']];
            }
            $data = $this->distribute->searchMyByUid($dis_where,'time desc');
            $new = [];
            //把产生提成用户信息的先取出来并组合
            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    $new[$value['uid']]['num'] = 1+($new[$value['uid']]['num'] ?? 0);
                    $new[$value['uid']]['money'] = $value['commission']+($new[$value['uid']]['money'] ?? 0);
                    $new[$value['uid']]['uid'] = $value['uid'];
                    $new[$value['uid']]['time'] = $value['time'];
                }
            }
            if (!empty($param['uid'])) {
                $list = $relation->getUidList(['uid'=>$param['uid']]);
            } else {
                if (empty($param['depth'])) {
                    $where['depth'] = ['gt',$one_time['depth']];
                    $where['path'] = ['like',$one_time['path'].'%'];
                    $list = $relation->searchRowsByUid($where,$page,$size);
                }else {
                    $where['depth'] = $one_time['depth']+$param['depth'];
                    $where['path'] = ['like',$one_time['path'].'%'];
                    $list = $relation->searchRowsByUid($where,$page,$size);
                }
            }
            if (!empty($list)) {
                foreach ($list as $index => $item) {
                    $list[$index]['time'] = substr($item['created_at'],0,10);
                    $list[$index]['num'] = $new[$item['uid']]['num'] ?? 0;
                    $list[$index]['money'] = !empty($new[$item['uid']]['money']) ? sprintf("%01.2f",$new[$item['uid']]['money']) : '0.00';
                }
                //取昵称，头像，性别
                $uids = array_column($list,'uid');
                $name = $this->user->getNameList($uids);
                $names = [];
                foreach ($name as $k => $v) {
                    $names[$v['openid']] = $v;
                }
                foreach ($list as $key => $value) {
                    $list[$key]['nickname'] = $names[$value['uid']]['nickname'] ?? '';
                    $list[$key]['avatarulr'] = $names[$value['uid']]['avatarulr'] ?? '';
                    $list[$key]['gender'] = $names[$value['uid']]['gender'] ?? 0;
                    $list[$key]['depth'] = $value['depth']-$one_time['depth'];
                }
            }

            $total = $relation->searchRowsCount($where);

            $result = $this->ajaxSuccess(202, ['total'=>$total,'info'=>$one,'list' => $list]);

        } catch (Exception $exception) {

            $result = $this->ajaxError(205);
        }

        return $result;
    }
}