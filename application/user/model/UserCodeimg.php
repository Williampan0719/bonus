<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/16
 * Time: 上午11:35
 */

namespace app\user\model;


use app\common\model\BaseModel;

class UserCodeimg extends BaseModel
{
    protected $table = 'user_codeimg';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /** 检查分享二维码是否存在
     * auth smallzz
     * @param int $bonus_id
     * @return int|string
     */
    public function isCheckCode(int $bonus_id,int $type = 0, int $class = 1){
        return $this->where(['bonus_id'=>$bonus_id,'type'=>$type,'class'=>$class])->value('imgurl');
    }

    /** 检查邀请码是否存在
     * auth smallzz
     * @param int $uid
     * @return int|string
     */
    public function isCheckInviteCode(string $uid,int $type, int $class = 1){
        return $this->where(['uid'=>$uid,'type'=>$type, 'class'=>$class])->value('imgurl');
    }

    /** h获取红包二维码
     * auth smallzz
     * @param int $bonus_id
     * @param int $type
     * @return mixed
     */
    public function getBonusCode(int $bonus_id,int $type, int $class = 1){
        return $this->where(['bonus_id'=>$bonus_id,'type'=>$type, 'class'=>$class])->value('imgurl');
    }
}