<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/12/8
 * Time: 上午10:28
 */

namespace app\backend\model;

use app\common\model\BaseModel;

class AdminLog extends BaseModel
{
    use SoftDelete;

    protected $table = 'backend_admin_log';

    protected $createTime = 'created_at';
    protected $deleteTime = 'deleted_at';
}