<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/22
 * Time: 下午4:25
 */

namespace app\game\model;


use app\common\model\BaseModel;
use app\user\model\User;
use think\db\Query;

class GameSscTo extends BaseModel
{
    protected $table = 'game_ssc_to';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** 添加迎战or畏战
     * auth smallzz
     * @param array $param
     * @return mixed
     */
    public function sscToAdd(array $param){
        $sscTo=new GameSsc($param);
        $sscTo->allowField(true)->save();
        return $sscTo->id;
    }

    /** 获取我参与挑战的
     * auth smallzz
     * @param int $uid
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getMyListTo(int $uid){
        $user = new User();
        $list =  $this->alias('st')
            ->join('wx_game_ssc s','s.id = st.ssid','inner')
            ->where(['st.uid'=>$uid])
            ->field('s.uid,st.uid as myuid,s.title,st.val,st.val,st.result,st.created_at')
            ->select();
        foreach ($list as $k=>$v){
            $list[$k]['create_uid'] = $user->userDetail($v['uid']);
            $list[$k]['myuid'] = $user->userDetail($v['myuid']);
        }
        return $list;
    }
    /** 获取pk项的详细信息 多个（果断迎战 or 押注观战 or 畏战）
     * auth smallzz
     * @param int $ssid
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getSscInfoSelect(int $ssid){
        $list = [];
        $list = $this->alias('st')
            ->join('wx_user u','u.openid = st.uid','inner')
            ->where(['st.ssid'=>$ssid])
            ->field('u.nickname,u.avatarulr,st.coin,st.type,st.result,st.val')
            ->order('st.type desc,st.id asc')
            ->select();
        foreach ($list as $k=>&$v){
            if($v['result'] == 1){
                $v['types'] = '平局';
                $v['coins'] = '+'.$v['coin'];
            }elseif($v['result'] == 2){
                $v['types'] = '赢了';
                $v['coins'] = '+'.$v['coin']*2;
            }elseif($v['result'] == 3){
                $v['types'] = '输了～';
                $v['coins'] = '-'.$v['coin'];
            }else{
                $v['types'] = '';
                $v['coins'] = '';
            }
        }
        return $list;

    }

    /**获取pk项的详细信息 单个（果断迎战）
     * auth smallzz
     * @param int $ssid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getSscInfoFind(int $ssid){
        $list = [];
        $list = $this->alias('st')
            ->join('wx_user u','u.openid = st.uid','inner')
            ->where(['st.ssid'=>$ssid,'st.type'=>1])
            ->field('u.nickname,u.avatarulr,st.coin,st.type,st.result,st.val')
            ->find();
        return $list;
    }

    /** 获取是否参与过
     * auth smallzz
     * @param string $uid
     * @param int $ssid
     * @return bool
     */
    public function getSscTo(string $uid,int $ssid){
        $query = new Query();
        $isgo = $query->table('wx_game_ssc')->where('id = '.$ssid.' and result > 0')->count();
        if(!empty($isgo)){
            return 8;  #游戏完了
        }
        $where = ' uid = "'.$uid.'" and ssid = '.$ssid.' and coin > 0';
        $res = $this->where($where)->field('result')->find();
        if(empty($res)){
            return 0; #未参加
        }
        return 1;  #已经参加过了
    }
}