<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/29
 * Time: 下午2:34
 */

namespace app\user\model;


use app\common\model\BaseModel;

class UserSignin extends BaseModel
{
    protected $table = 'user_signin';
    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /**
     * @Author panhao
     * @DateTime 2018-02-01
     *
     * @description 获取单条
     * @param array $param
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getOne(array $param)
    {
        return $this->where($param)->find();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-01
     *
     * @description 新增初始账户
     * @param array $param
     * @return string
     */
    public function initOne(array $param)
    {
        $a = new UserSignin($param);
        $a->allowField(true)->save();
        return $a->getLastInsID();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-01
     *
     * @description 编辑签到
     * @param array $param
     * @return false|int
     */
    public function editOne(array $param)
    {
        return $this->allowField(['con_num','con_time','updated_at'])->save($param,['uid'=>$param['uid']]);
    }

}