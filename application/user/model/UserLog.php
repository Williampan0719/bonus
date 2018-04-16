<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/15
 * Time: 下午2:57
 */

namespace app\user\model;


use app\common\model\BaseModel;

class UserLog extends BaseModel
{
    protected $table = 'user_log';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /** 添加登陆log
     * auth smallzz
     * @param $param
     */
    public function uLogAdd($uid){
        $this->uid = $uid;
        $this->save();
    }

    /** 获取今日浏览量
     * auth smallzz
     * @return int|string
     */
    public function getDayViewNum(){
        $stime = date('Y-m-d 00:00:00');
        $etime = date('Y-m-d 23:59:59');
        return $this->where('created_at >= "'.$stime.'" and created_at <= "'.$etime.'"')->count();
    }
}