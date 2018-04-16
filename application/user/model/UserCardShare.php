<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/26
 * Time: 下午2:29
 */

namespace app\user\model;


use app\common\model\BaseModel;

class UserCardShare extends BaseModel
{
    protected $table = 'user_card_share';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /** 创建卡劵分享
     * auth smallzz
     * @param array $param
     * @return mixed
     */
    public function cardShareAdd(array $param){
        $cardModel=new UserCardReceive($param);
        $cardModel->allowField(true)->save();
        return $cardModel->id;
    }

    /** ß
     * auth smallzz
     * @param int $share_id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCardInfo(int $share_id){
        $list = $this->alias('cs')
            ->join('wx_card c','c.id = cs.card_id','inner')
            ->join('wx_user u','u.openid = cs.uid','inner')
            ->where(['cs.id'=>$share_id])
            ->field('c.brand_name,c.title,u.nickname,u.avatarulr')
            ->find();

        return $list;
    }

}