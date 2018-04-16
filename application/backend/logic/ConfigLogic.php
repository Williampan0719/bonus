<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/16
 * Time: 上午9:40
 * @introduce
 */
namespace app\backend\logic;

use app\common\logic\BaseLogic;
use app\system\model\SystemConfig;

class ConfigLogic extends BaseLogic
{
    protected $config;
    public function __construct()
    {
        $this->config = new SystemConfig();
    }

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
        return $this->config->getOne($key,$prefix);
    }
}