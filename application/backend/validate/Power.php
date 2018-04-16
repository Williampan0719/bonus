<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/12
 * Time: 下午1:52
 * @introduce
 */
namespace app\backend\validate;

use think\Validate;

class Power extends Validate
{

    protected $rule =   [
        'name'    => 'require',
        'num'     => 'number',
        'id'      => 'require|number',
    ];

    protected $message  =   [
        'name.require' => '体力名称必填',
        'num.number'   => '体力值额必须为数字',
        'id.require' => '体力配置id必填',
        'id.number'   => '体力配置id必须为数字',
    ];

    protected $scene = [
        'add' => ['name','num'],
        'edit'=> ['id','name','num'],
    ];
}