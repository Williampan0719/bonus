<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/22
 * Time: 下午3:43
 */

namespace app\game\model;


use app\common\model\BaseModel;
use app\user\model\User;
use extend\helper\Time;

class GameSsc extends BaseModel
{
    protected $table = 'game_ssc';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /** 发起石头剪刀布
     * auth smallzz
     * @param array $param
     * @return mixed
     */
    public function sscAdd(array $param){
        $ssc=new GameSsc($param);
        $ssc->allowField(true)->save();
        return $ssc->id;
    }

    /** 获取信息
     * auth smallzz
     * @param int $ssid
     * @param string $field
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getInfo(int $ssid,string $field){
        return $this->where(['id'=>$ssid])->field($field)->find();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-07
     *
     * @description 编辑状态
     * @param array $param
     * @return $this
     */
    public function editInfo(array $param){
        return $this->where('id',$param['id'])->update($param);
    }

    /** 获取自己发起的挑战
     * auth smallzz
     * @param string $uid
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getMyList(string $uid)
    {
        $user = new User();
        $list = $this->alias('sc')
            ->where(['sc.uid'=>$uid])
            ->field('title,val,coin,result,yuid,created_at')
            ->select();
        foreach ($list as $k=>$v){
            $list[$k]['yuid'] = $user->userDetail($v['yuid']);
            $list[$k]['uid'] = $user->userDetail($uid);
        }
        return $list;
    }

    /**获取pk进行中页
     * auth smallzz
     * @param int $ssid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getVirtualNotDone(int $ssid){
        $sscto = new GameSscTo();
        $list = $this->alias('s')
            ->join('wx_user u','u.openid = s.uid','inner')
            ->where(['s.id'=>$ssid])
            ->field('u.nickname,u.avatarulr,s.val,s.result')
            ->find();
        $list['object'] = $sscto->getSscInfoFind($ssid);
        $list['list'] = $sscto->getSscInfoSelect($ssid);
        return $list;
    }
    /** 获取pk完成页
     * auth smallzz
     * @param int $ssid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getVirtualDone(int $ssid,string $uid){
        $sscto = new GameSscTo();
        $list = $this->alias('s')
            ->join('wx_user u','u.openid = s.uid','left')
            ->where(['s.id'=>$ssid])
            ->field('u.nickname,u.avatarulr,s.val,s.result,s.uid,s.coin,s.yuid,s.updated_at,s.created_at')
            ->find();
        if(empty($list)){
            return false;
        }
        if($list['result'] == 1){
            $list['types'] = '平局';
            $list['coins'] = '+'.$list['coin'];
        }elseif($list['result'] == 2){
            $list['types'] = '赢了';
            $list['coins'] = '+'.$list['coin']*2;
        }elseif($list['result'] == 3){
            $list['types'] = '输了～';
            $list['coins'] = '-'.$list['coin'];
        }else{
            $list['types'] = '';
            $list['coins'] = '';
        }
        if($list['result'] == 2){
            $list['results'] = 1;
        }elseif ($list['result'] == 3){
            $list['results'] = 2;
        }else{
            $list['results'] = 0;
        }
        if($list['uid'] == $uid){
            $list['from'] = 1; #发起
        }elseif($list['yuid'] == $uid){
            $infos = $sscto->where(['ssid'=>$ssid,'uid'=>$uid])->field('result')->find();
            $list['from'] = 2; #挑战
            $list['result'] = $infos['result'];
        }else{
            #查询观战
            $gzinfo = $sscto->where(['ssid'=>$ssid,'uid'=>$uid])->find();
            if(!empty($gzinfo)){
                $list['from'] = 3; #观战
            }else{
                $list['from'] = 4; #游客
            }
            #0,weizhi,1平局，2胜利，3失败
            if(!empty($list['result'])){
                if($list['result'] == 3){
                    $user = new User();
                    $list['nickname'] = $user->userDetail($list['yuid'])['nickname'];
                }
            }
        }

        $lists['info'] = $list;
        $lists['object'] = $sscto->getSscInfoFind($ssid);
        $lists['times'] = Time::secsToStr(strtotime($list['updated_at']) - strtotime($list['created_at']));
        $lists['count'] = count($sscto->getSscInfoSelect($ssid));
        $lists['list'] = $sscto->getSscInfoSelect($ssid);
        return $lists;
    }

    /** 获取未出结果的记录
     * auth smallzz
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getSscList(){
        $list = $this->alias('gs')
            ->join('wx_user u','u.openid = gs.uid','inner')
            #->where(['gs.result'=>0])
            ->where('gs.updated_at >= "'.date('Y-m-d H:i:s',time()-600).'"')
            ->field('gs.id,gs.title,gs.coin,u.nickname,u.avatarulr,gs.result')
            ->order('gs.id desc')
            ->select();
        if(!empty($list)){
            foreach ($list as $k=>$v){
                if(empty($v['result'])){
                    $list[$k]['result'] = 2;
                }else{
                    $list[$k]['result'] = 3;
                }
            }
        }
        return $list;
    }

}