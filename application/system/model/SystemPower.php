<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/12
 * Time: 上午11:34
 * @introduce 后台体力管理
 */
namespace app\system\model;

use traits\model\SoftDelete;
use app\common\model\BaseModel;

class SystemPower extends BaseModel
{
    use SoftDelete;

    protected $table = 'system_power';


    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    /**
     * @Author panhao
     * @DateTime 2018-01-12
     *
     * @description 获取所有数据
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAll()
    {
        return $this->field('id,name,title,num,created_at,updated_at')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-12
     *
     * @description 体力配置添加
     * @param array $where
     * @return false|int
     */
    public function addPower(array $where)
    {
        return $this->allowField(true)->save($where);
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-12
     *
     * @description 体力配置编辑
     * @param array $where
     * @param int $id
     * @return $this
     */
    public function editPower(array $where, int $id)
    {
        return $this->allowField(true)->where('id',$id)->update($where);
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-14
     *
     * @description 根据title获取体力配置信息
     * @param string $title
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getOne(string $title)
    {
        return $this->field('id,name,title,num,created_at,updated_at')->where(['title'=>$title])->find();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-19
     *
     * @description 判断是否重复
     * @param array $param
     * @return int|string
     */
    public function getExist(array $param)
    {
        return $this->field('*')->where($param)->count();
    }
}