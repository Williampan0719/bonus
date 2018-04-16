<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/6
 * Time: 09:20
 * @introduce
 */

namespace app\api\validate;

use think\Validate;

class BonusPay extends Validate
{
    protected $rule = [
        'bonus_num' => 'require|number',
        'bonus_money' => 'require',
        'bonus_password'=>'require|max:20',
        'uid'=>'require',
        'service_money'=>'require',
        'voice_path'   =>'require',
    ];
    protected $message = [
        'bonus_num.require'=>'红包个数必须填写',
        'bonus_num.number'=>'红包个数必须为数字',
        'bonus_money.require'=>'红包金额必须填写',
        'bonus_password.require'=>'红包口令必须填写',
        'bonus_password.max'=>'红包口令不能超过20个字',
        'uid.require'=>'用户uid不能为空',
        'service_money.require'=>'服务费不能为空',
        'voice_path'=>'红包语音必须填写',

    ];
    protected $scene = [
        'pay'     =>['bonus_num','bonus_password','bonus_money','uid','service_money'],
        'voicePay'=>['bonus_num','bonus_money','uid','service_money'],
    ];
}