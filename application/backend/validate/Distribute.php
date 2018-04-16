<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/10
 * Time: 下午10:37
 * @introduce 后台分销提成管理验证
 */
namespace app\backend\validate;

use think\Validate;

class Distribute extends Validate
{

    protected $rule =   [
        'page'     => 'number',
        'size'     => 'number',
        'openid'   => 'require',
    ];

    protected $message  =   [
        'page.number'   => '页码必须为数字',
        'size.number'   => '页码必须为数字',
        'openid.require'=> '用户id不能为空',
    ];

    protected $scene = [
        'list' => ['page','size'],
        'detail'=>['page','size','openid'],
    ];
}