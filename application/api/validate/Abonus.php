<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/25
 * Time: 09:20
 * @introduce
 */

namespace app\api\validate;

use think\Validate;

class Abonus extends Validate
{
    protected $rule = [
        'openid' => 'require',
        'template_class' => 'require',
        'remark_type' => 'require|number',
        'id' => 'require|number',
        'money'=>'require',
        'abonus_id' => 'require',
    ];
    protected $message = [
        'template_class.require'=>'模版类型必须填写',
        'openid.require'=>'用户openid必须填写',
        'remark_type.require'=>'备注类型必须填写',
        'remark_type.number'=>'备注类型必须为数字',
        'id.require'=>'红包id必须填写',
        'id.number'=>'红包id必须为数字',
        'money'=>'红包金额必填',
        'abonus_id.require' => '讨红包id必填',

    ];
    protected $scene = [
        'ask'  => ['template_class','openid','remark_type'],
        'share'=> ['id'],
        'index'=> ['openid','id'],
        'show' => ['openid','id'],
        'pay'  => ['openid','id','money'],
        'temp_after' => ['abonus_id'],
    ];
}