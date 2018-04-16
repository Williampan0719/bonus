<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/11/10
 * Time: 下午5:46
 */

namespace app\cms\logic;


use app\cms\model\Demo;
use app\common\logic\BaseLogic;

class DemoLogic extends BaseLogic
{
    /**
     * 获取第一个记录
     * @return null|static
     */
    public function getFirstRow()
    {
        $result = Demo::get(1);

        return $result;
    }
}