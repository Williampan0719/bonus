<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/2/7
 * Time: 下午5:06
 * @introduce
 */
namespace app\backend\validate;

use think\Validate;

class Withdraw extends Validate
{

    protected $rule =   [
        'page'    => 'require|number',
        'size'    => 'require|number',
        'id'      => 'require|number',
        'openid'  => 'require',
        'status'  => 'require|number',
    ];

    protected $message  =   [
        'page.require' => '当前页必填',
        'page.number'   => '当前页必须为数字',
        'size.require' => '每页数必填',
        'size.number'   => '每页数必须为数字',
        'openid.require' => '用户uid必填',
        'id.require' => '申请id必填',
        'id.number'   => '申请id必须为数字',
        'status.require' => '审核状态必填',
        'status.number'   => '审核状态必须为数字',
    ];

    protected $scene = [
        'search-review' => ['page','size'],
        'edit-review'=> ['id','status'],
    ];
}