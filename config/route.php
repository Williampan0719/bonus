<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

/**
 * 微信端
 */

Route::group('api', function () {
    Route::group('user', [
        'user' => ['api/User/userLogin', ['method' => 'post']],
        'mobile' => ['api/User/getMobile', ['method' => 'post']],
        'qrcode' => ['api/User/createQrcode', ['method' => 'post']],
        'run' => ['api/User/getAudio', ['method' => 'post']],
        'bonus' => ['api/User/bonusRecord', ['method' => 'get']],
        'withdrawals'=>['api/User/userWithdrawals',['method' =>'get']],
        'index'=>['api/User/bonusIndex',['method'=>'get']],
        'share'=>['api/User/bonusShare',['method'=>'get']],
        'check'=>['api/User/checkVoiceBonus',['method'=>'get']],
        'binding'=>['api/User/bindingUser',['method'=>'get']],
        'capital'=>['api/User/userCapital',['method'=>'get']],
        'my'=>['api/User/myPage',['method'=>'get']],
        'form-id'=>['api/User/formIdAdd',['method'=>'post']],
        'complaints'=>['api/User/complaints',['method'=>'get']],
        'halls'=>['api/BonusHall/getHalls', ['method' => 'get']],
        'allhall'=>['api/BonusHall/getAllHall', ['method' => 'get']],
        'hallinfo'=>['api/BonusHall/getHallInfo', ['method' => 'post']],
        'daylocalt'=>['api/BonusHall/getDayLocalTyrant', ['method' => 'get']],
        'daybestl'=>['api/BonusHall/getDayBestLuck', ['method' => 'get']],
        'randex'=>['api/BonusHall/randExample', ['method' => 'get']],
        'user-power'=>['api/User/hallUserPower',['method'=>'get']],
        'ctcode'=>['api/User/getImg',['method'=>'post']],
        'upexp'=>['api/User/upexample',['method'=>'post']],
        'precondition'=>['api/User/savePreconditionInfo',['method'=>'post']],
        'share-hall'=>['api/BonusHall/shareHall',['method'=>'post']],
        'user-hot'=>['api/User/getHot',['method'=>'get']],
        'ann'=>['api/User/announcement',['method'=>'get']],
        'version-config'=>['api/User/versionConfig',['method'=>'get']],
        'version-info'=>['api/User/versionInfo',['method'=>'post']],

        'qq-music' => ['api/User/getQqMusic', ['method' => 'get']]
    ]);
    Route::group('pay',[
        'check-word'=>['api/BonusPay/checkWord',['method'=>'post']],
        'bonus-pay'=>['api/BonusPay/bonusPay',['method'=>'post']],
        'wx-notify'=>['api/BonusPay/wxNotify'],
        'wx-notify_game'=>['api/BonusPay/wxNotifyGame'],
        'withdrawals'=>['api/BonusPay/wxWithdrawals',['method'=>'post']],
        'close-order'=>['api/BonusPay/closeOrder',['method'=>'get']],
        'upload'=>['api/BonusPay/uploadPic',['method'=>'post']],
        'adv-detail'=>['api/BonusPay/advBonusDetail',['method'=>'get']],
        'adv_user'=>['api/BonusPay/getAdvUserInfo',['method'=>'get']],
    ]);
    Route::group('abonus',[
        'template'=>['api/Abonus/getTemplateList',['method'=>'get']],
        'ask'=>['api/Abonus/askBonus',['method'=>'post']],
        'share'=>['api/Abonus/askBonusShare',['method'=>'get']],
        'index'=>['api/Abonus/askBonusIndex',['method'=>'get']],
        'pay'=>['api/Abonus/aBonusPay',['method'=>'post']],
        'temp-after'=>['api/Abonus/templateAfterSend',['method'=>'get']],
        'pay-plus'=>['api/Abonus/abonusPayPlus',['method'=>'post']],
        'show'=>['api/Abonus/aBonusShow',['method'=>'get']],
    ]);
    Route::group('card',[
        'card-list'=>['api/card/cardCreate',['method'=>'post']],
        'card-receive'=>['api/card/cardRevice',['method'=>'post']],
        'card-sharein'=>['api/card/getShareRe',['method'=>'post']],
        'card-use'=>['api/card/useCardConsume',['method'=>'post']],
    ]);
    Route::group('game',[
        'bwheel-list'=>['api/Game/getBwheel',['method'=>'post']],
        'bwheel-draw'=>['api/Game/getBwheelDraw',['method'=>'post']],
        'game-cssc'=>['api/Game/setGamepk',['method'=>'post']],
        'game-csscto'=>['api/Game/setGamepkto',['method'=>'post']],
        'game-signin'=>['api/Game/getGameSign',['method'=>'post']],
        'game-detail'=>['api/Game/getGameDetail',['method'=>'post']],
        'game-sscinfo'=>['api/Game/getSscInfo',['method'=>'post']],
        'game-yue'=>['api/Game/getYuE',['method'=>'post']],
        'game-into'=>['api/Game/getIsToIn',['method'=>'post']],
        'game-topup'=>['api/Game/topUp',['method'=>'post']],
        'close-order'=>['api/Game/closeOrder',['method'=>'get']],
        'game-topup-list'=>['api/Game/virtualTopup',['method'=>'post']],
    ]);
});

/**
 * 后台
 */
Route::group('backend', function () {
    //红包
    Route::group('bonus', [
        'list' => ['backend/Bonus/bonusList', ['method' => 'get']],
        'detail' => ['backend/Bonus/bonusDetail', ['method' => 'get']],
        'adv-search' => ['backend/Bonus/advBonusSearchRows', ['method' => 'get']],
        'adv-detail' => ['backend/Bonus/advBonusSearchDetail', ['method' => 'get']],
        'del-remark' => ['backend/Bonus/delAdvRemark', ['method' => 'get']],
    ]);
    Route::group('order',[
        'recharge'=>['backend/Order/orderRecharge',['method'=>'get']],
        'withdraw'=>['backend/Order/orderWithdraw',['method'=>'get']],
    ]);
    Route::group('bill',[
        'list'=>['backend/BillLog/billLogList',['method'=>'get']],
        'stats'=>['backend/BillLog/billLogStats',['method'=>'get']],
    ]);
    Route::group('user',[
        'list'=>['backend/User/userList',['method'=>'get']],
        'panel'=>['backend/User/userPanel',['method'=>'get']],
        'forbid'=>['backend/User/forbidUser',['method'=>'post']],
    ]);
    Route::group('distribute',[
        'list'=>['backend/Distribute/searchRows',['method'=>'get']],
        'detail'=>['backend/Distribute/searchDetailRows',['method'=>'get']],
    ]);
    Route::group('power',[
        'all'=>['backend/Power/getAll',['method'=>'get']],
        'add'=>['backend/Power/addPower',['method'=>'post']],
        'edit'=>['backend/Power/editPower',['method'=>'post']],
    ]);
    Route::group('enterprise',[
        'list'=>['backend/Enterprise/enterpriseList',['method'=>'get']],
        'add'=>['backend/Enterprise/enterpriseAdd',['method'=>'post']],
    ]);
    Route::group('abonus',[
        'search'=>['backend/Abonus/searchRows',['method'=>'get']],
        'search-detail'=>['backend/Abonus/searchDetailRows',['method'=>'get']],
        'template'=>['backend/Abonus/getTemplateList',['method'=>'get']],
        'add'=>['backend/Abonus/addTemplate',['method'=>'post']],
        'edit'=>['backend/Abonus/editTemplate',['method'=>'post']],
        'del'=>['backend/Abonus/delTemplate',['method'=>'get']],
    ]);
    Route::group('game',[
        'search'=>['backend/Game/searchDetailRows',['method'=>'get']],
        'recharge-list'=>['backend/Game/getRechargeList',['method'=>'get']],
        'virtual-list'=>['backend/Game/virtualConfigList',['method'=>'get']],
        'add-virtual'=>['backend/Game/addVirtualConfig',['method'=>'post']],
        'edit-virtual'=>['backend/Game/editVirtualConfig',['method'=>'post']],
        'del-virtual'=>['backend/Game/delVirtualConfig',['method'=>'get']],
        'bwheel-list'=>['backend/Game/bwheelConfigList',['method'=>'get']],
        'add-bwheel'=>['backend/Game/addBwheelConfig',['method'=>'post']],
        'edit-bwheel'=>['backend/Game/editBwheelConfig',['method'=>'post']],
        'del-bwheel'=>['backend/Game/delBwheelConfig',['method'=>'get']],
    ]);
    Route::group('withdraw',[
        'search-review'=>['backend/withdraw/searchReviewRows',['method'=>'get']],
        'edit-review'=>['backend/withdraw/editReview',['method'=>'get']],
    ]);

});

Route::group('test', function () {
    Route::group('lyc',[
        'test'=>['test/Lyc/test'],
        'ess'=>['test/Lyc/ess']
    ]);
});