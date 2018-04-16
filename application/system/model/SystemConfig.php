<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/16
 * Time: 上午9:40
 * @introduce
 */
namespace app\system\model;

use traits\model\SoftDelete;
use app\common\model\BaseModel;

class SystemConfig extends BaseModel
{
    protected $table = 'system_config';

    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    /**
     * @Author panhao
     * @DateTime 2018-01-16
     *
     * @description 获取单条
     * @param string $key
     * @param string $prefix
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getOne(string $key, string $prefix)
    {
        return $this->where(['name'=>$key,'prefix'=>$prefix])->find();
    }
}