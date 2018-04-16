<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/11/9
 * Time: 下午2:12
 */

namespace app\common\model;


use think\Model;

abstract class BaseModel extends Model
{
    protected $table;

    public function __construct($data = [])
    {
        $this->table = config('database.prefix') . $this->table;

        parent::__construct($data);

    }

}