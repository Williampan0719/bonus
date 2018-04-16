<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/10
 * Time: 下午1:49
 */

namespace app\user\model;


use app\common\model\BaseModel;

class UserLevel extends BaseModel
{
    protected $table = 'user_level';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** 获取等级列表
     * auth smallzz
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAllLevel(){
        return $this->field('level,number')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-15
     *
     * @description 获取详情
     * @param string $openid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getExist(string $openid)
    {
        return $this->field('id,uid,level,number')->where(['uid'=>$openid])->find();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-15
     *
     * @description 新增初始用户等级
     * @param string $openid
     * @return false|int
     */
    public function initUserLevel(string $openid)
    {
        return $this->allowField(true)->save(['uid'=>$openid,'level'=>'暂无等级']);
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-15
     *
     * @description 编辑用户等级
     * @param array $param
     * @return false|int
     */
    public function editUserLevel(array $param)
    {
        return $this->allowField(true)->save($param,['uid'=>$param['uid']]);
    }
}