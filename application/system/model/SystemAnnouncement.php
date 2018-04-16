<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/19
 * Time: 下午3:39
 */

namespace app\system\model;


use app\common\model\BaseModel;

class SystemAnnouncement extends BaseModel
{
    protected $table = 'system_announcement';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /** 获取最新的公告
     * auth smallzz
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getNewAnn(){
       return $this->where(['status'=>1])->value('content');
    }


}