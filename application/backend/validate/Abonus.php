<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/28
 * Time: 下午4:18
 * @introduce
 */
namespace app\backend\validate;

use think\Validate;

class Abonus extends Validate
{
    protected $rule = [
        'openid' => 'require',
        'template_id' => 'require|number',
        'remark_type' => 'require|number',
        'id' => 'require|number',
        'money'=>'require',
    ];
    protected $message = [
        'template_id.require'=>'模版id必须填写',
        'template_id.number'=>'模版id必须为数字',
        'openid.require'=>'用户openid必须填写',
        'remark_type.require'=>'备注类型必须填写',
        'remark_type.number'=>'备注类型必须为数字',
        'id.require'=>'红包id必须填写',
        'id.number'=>'红包id必须为数字',
        'money'=>'红包金额必填',

    ];
    protected $scene = [
        'ask'  => ['template_id','openid','remark_type'],
        'share'=> ['id'],
        'index'=> ['openid','id'],
        'show' => ['openid','id'],
        'pay'  => ['openid','id','money'],
    ];
}