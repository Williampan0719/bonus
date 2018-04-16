<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/2/10
 * Time: 下午10:11
 * @introduce
 */
namespace app\system\model;

use app\common\model\BaseModel;

class SystemConfigBwheel extends BaseModel
{
    protected $table = 'system_config_bwheel';

    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    /**
     * @Author panhao
     * @DateTime 2018-02-10
     *
     * @description 获取单条
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function getOne(array $where, string $field)
    {
        return $this->where($where)->value($field);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-10
     *
     * @description 获取全部
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAll()
    {
        return $this->order('sequence asc')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-10
     *
     * @description 添加大转盘配置
     * @param array $param
     * @return false|int
     */
    public function addBwheelConfig(array $param)
    {
        $virtual = new SystemConfigBwheel();
        $virtual->allowField(true)->save($param);
        return $virtual->getLastInsID();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-10
     *
     * @description 编辑大转盘配置
     * @param array $param
     * @param int $id
     * @return false|int
     */
    public function editBwheelConfig(array $param, int $id)
    {
        return $this->allowField(true)->save($param,['id'=>$id]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-10
     *
     * @description 删除配置
     * @param int $id
     * @return int
     */
    public function delBwheelConfig(int $id)
    {
        return $this->where('id',$id)->delete();
    }
}