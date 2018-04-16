<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2017/12/9
 * Time: 上午8:50
 * @introduce 验证规范demo参考
 */
namespace app\backend\validate;

use think\Validate;

class Demo extends Validate
{

    protected $rule =   [
        'page'     => 'number',
        'size'     => 'number',
        'title'    => 'require',
        'content'  => 'require',
        'id'       => 'require|number',
    ];

    protected $message  =   [
        'page.number'  => '页码必须为数字',
        'size.number'  => '页码必须为数字',
        'content.require'  => '内容必填',
        'title.require'=> '标题必填',
        'id.require'   => 'id必填',
        'id.number'    => 'id必须为数字',
    ];

    protected $scene = [
        'all'  =>  ['page', 'size'],
        'detail' =>  ['id'],
        'add'  =>  ['title','content'],
        'edit' =>  ['id'],
        'del'  =>  ['id'],
    ];
}