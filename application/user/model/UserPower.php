<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/14
 * Time: 下午2:05
 * @introduce
 */
namespace app\user\model;

use traits\model\SoftDelete;
use app\common\model\BaseModel;

class UserPower extends BaseModel
{
    use SoftDelete;

    protected $table = 'user_power';


    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    /**
     * @Author panhao
     * @DateTime 2018-01-14
     *
     * @description 查询用户体力详情
     * @param string $openid
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getOne(string $openid)
    {
        return $this->field('id,uid,power,login_time,created_at,updated_at')->where(['uid'=>$openid])->find();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-14
     *
     * @description 添加初始账户
     * @param array $param
     * @return false|int
     */
    public function addUserPower(array $param)
    {
        return $this->allowField(true)->save($param);
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-14
     *
     * @description 编辑体力
     * @param array $param
     * @param string $openid
     * @return false|int
     */
    public function editUserPower(array $param,string $openid)
    {
        return $this->allowField(true)->save($param,['uid'=>$openid]);
    }
}