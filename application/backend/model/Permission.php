<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/12/8
 * Time: 上午10:24
 */

namespace app\backend\model;

use app\common\model\BaseModel;

class Permission extends BaseModel
{
    protected $table = 'backend_permissions';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

}