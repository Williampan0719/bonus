<?php
/**
 * 热门语音推荐
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/19
 * Time: 下午2:43
 */

namespace app\user\model;


use app\common\model\BaseModel;
use think\db\Query;

class UserReceiveHot extends BaseModel
{
    protected $table = 'user_receive_hot';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /**
     * auth smallzz
     * @param array $param
     */
    public function hotAdd(array $param){
        $data['uid'] = $param['uid']??0;
        $data['type'] = $param['type'];
        $data['content'] = $param['content'];
        $this->save($data);
    }

    /** 删除指定用户的推荐
     * auth smallzz
     * @param array $param
     * @return int
     */
    public function hotDel(array $param){
        return $this->where(['uid'=>$param['uid']])->delete();
    }

    /** 删除所有推荐类型的数据
     * auth smallzz
     * @return int
     */
    public function hotAllHot(){
        return $this->where(['type'=>0])->delete();
    }
    /** 获取热门推荐
     * auth smallzz
     * @param int $uid
     * @return array
     */
    public function getReceiveHot(string $uid){
        $query = new Query();
        $all = $this->where(['type'=>0])->field('content as bonus_password')->select();

        $send = $query->table('wx_payment_bonus')
            ->where(['uid'=>$uid,'class'=>0])
            ->group('bonus_password')
            ->order('count(bonus_password) desc')
            ->field('bonus_password')
            ->limit(5)
            ->select();
        #echo $query->getLastSql();
        $get = $query->table('wx_payment_bonus_receive')->alias('br')
            ->join('wx_payment_bonus b','b.id=br.bonus_id','inner')
            ->where(['br.receive_uid'=>$uid,'b.class'=>0])
            ->group('b.bonus_password')
            ->order('count(br.bonus_id) desc')
            ->field('b.bonus_password as bonus_password')
            ->limit(5)
            ->select();
       # echo '<br/>';
        #echo $query->getLastSql();
        #exit;
        #$send = $this->where(['uid'=>$uid,'type'=>1])->select();
        #$get = $this->where(['uid'=>$uid,'type'=>2])->select();
        return ['all'=>$all,'send'=>$send,'get'=>$get];
    }
}