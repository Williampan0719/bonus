<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/23
 * Time: 19:08
 * @introduce
 */
return [
    #自动退款
    'automatic_refund' => [
        'automatic_time' => 24 * 60 * 60,//退款时间
        'adv_automatic_time' => 48*60*60, // 广告红包退款时间

        'send_time' => 60 * 60,//发送间隔时间

        'email' => [
            'type' => 'email',
            //'toemail'=>'pugongying@pgyxwd.com',//发送到邮箱
            'toemail' => 'yuan.zhang@pgyxwd.com',//张媛的邮箱
            'name' => 'ganjinshuo',
            'subject' => '账户余额不足',
            'content' => '赶紧说,企业账户余额已低于2000,请及时充值!',
        ],

        'send_time_check' => date('Y-m-d', strtotime('+1 day')).' 00:00:00',//判断当天有效时间

        'ding_talk' => [
            'type' => 'dingText',
            'url' => 'https://oapi.dingtalk.com/robot/send?access_token=00b0e5ea895fd4951d111d94d5e7cf36e2a9290ab91c2866ae07dcb649cac032',//todo 钉钉url 钉钉
            'content' => '赶紧说,企业账户余额已低于2000,请及时充值!',
            'atMobiles' => [
                '13361719429',//李义
                '13918027224'//张媛
            ],
            'isAtAll' => false,
        ],
        'enterprise_balance'=>2000,//企业账户余低于
        'enterprise_withdraw_count'=>90,//提现次数限制
    ],
    'wx_notify'=>[
        'bonus_effective_time'=>6 * 60 * 60,//红包在redis的有效时间
    ]
];