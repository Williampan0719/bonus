<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2018/1/24
 * Time: 上午10:02
 */

namespace app\api\validate;

use think\Validate;

class Qmusic extends Validate
{
    protected $rule = [
        'p' => 'number',
        'number' => 'number',
        'condition' => 'require',
        'guid' => 'number|length:10',
    ];
    protected $message = [
        'p.number' => '页码必须为数字',
        'number.number' => '页数必须为数字',
        'condition.require' => '搜索条件不能为空',
        'guid.number' => 'guid标识必须为数字',
        'guid.length' => 'guid标识长度必须为10',
    ];
    protected $scene = [
        'music-search' => ['p', 'number', 'condition', 'guid'],
    ];
}