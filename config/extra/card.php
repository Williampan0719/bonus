<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/25
 * Time: 下午2:42
 */
return [
    #类型
    'card_type'=>'CASH',
    #---------------------------base_info
    #卡券的商户logo
    "logo_url"=>'http://mmbiz.qpic.cn/mmbiz_png/JicnmrhdyMFuWXFrG6WLAra2Iwbmiaz0m8aeGAEAA4fP4neSnIzJ2uE3vQE7R7GVOugr0K0DUFzjIl8epiaOl2STA/0',
    #商户名字,字数上限为12个汉字。
    "brand_name"=>'赶紧说代金劵',
    #码型
    "code_type"=>"CODE_TYPE_TEXT",
    #卡券名，字数上限为9个汉字
    "title"=>"满5减0.1元代金劵",
    #券颜色
    "color"=>"Color010",
    #卡券使用提醒，字数上限为16个汉字。
    "notice"=>"支付时选择卡卷使用",
    #卡券使用说明，字数上限为1024个汉字。
    "description"=>"用于支付满减使用",
    #使用时间的类型
    "type"=>"DATE_TYPE_FIX_TIME_RANGE",
    #表示起用时间
    "begin_timestamp"=>time(),
    #表示结束时间
    "end_timestamp"=>time()+86400,
    #卡券库存的数量，上限为100000000。
    "quantity"=> 500000,
    #卡券跳转的小程序的user_name，仅可跳转该 公众号绑定的小程序 。
    'center_app_brand_user_name'=>'gh_3f467cae5c9f@app',
    #卡券跳转的小程序的path
    'center_app_brand_pass'=>'pages/hall/hall',
    #自定义跳转外链的入口名字。
    'custom_url_name'=>'进入小程序',
    #自定义跳转的URL。#小程序外链升级url
    'custom_url'=>'wxapitest.pgyxwd.com/test/Zzhh/cardLog',
    #每人可领券的数量限制,不填写默认为50。
    'get_limit'=>100,
    #每人可核销的数量限制,不填写默认为50。
    'use_limit'=>100,
    #卡券领取页面是否可分享。
    'can_share'=>false,
    #卡券是否可转赠。
    'can_give_friend'=>false,
    #-----------------------------advanced_info
    #指定可用的商品类目
    "accept_category"=> "小程序抵消现金",
    #指定不可用的商品类目
    "reject_category"=> "",
    #不可以与其他类型共享门槛
    "can_use_with_other_discount"=> false,
    #---------------------------------代金劵专用
    #代金券专用，表示起用金额（单位为分）,如果无起用门槛则填0。
    "least_cost"=>500,
    #代金券专用，表示减免金额。（单位为分）
    "reduce_cost"=>10,
];