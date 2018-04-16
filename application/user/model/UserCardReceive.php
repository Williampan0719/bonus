<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/26
 * Time: 下午1:28
 */

namespace app\user\model;


use app\common\model\BaseModel;

class UserCardReceive extends BaseModel
{
    protected $table = 'user_card_reveive';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /** 领取添加
     * auth smallzz
     * @param array $param
     * @return mixed
     */
    public function cardReceiveAdd(array $param){
        $cardModel=new UserCardReceive($param);
        $cardModel->allowField(true)->save();
        return $cardModel->id;
    }

    /** 获取分享卡劵的领取信息
     * auth smallzz
     * @param int $share_id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getShare(int $share_id){
        $list = $this->alias('cr')
            ->join('wx_user u','u.openid = cr.uid','inner')
            ->where(['cr.share_id'=>$share_id])
            ->field('u.nickname,u.avatarulr,cr.created_at')
            ->select();
        $lists['data'] = $list;
        return $list;
    }


}