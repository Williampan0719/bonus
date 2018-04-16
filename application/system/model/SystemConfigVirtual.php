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

class SystemConfigVirtual extends BaseModel
{
    protected $table = 'system_config_virtual';

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
        return $this->order('money asc')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-10
     *
     * @description 添加虚拟币配置
     * @param array $param
     * @return false|int
     */
    public function addVirtualConfig(array $param)
    {
        $virtual = new SystemConfigVirtual();
        $virtual->allowField(true)->save($param);
        return $virtual->getLastInsID();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-10
     *
     * @description 编辑虚拟币配置
     * @param array $param
     * @param int $id
     * @return false|int
     */
    public function editVirtualConfig(array $param, int $id)
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
    public function delVirtualConfig(int $id)
    {
        return $this->where('id',$id)->delete();
    }
}