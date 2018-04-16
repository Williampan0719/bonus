<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/6
 * Time: 上午9:31
 */

namespace app\backend\model;


use app\common\model\BaseModel;
use think\Exception;

class User extends BaseModel
{
    protected $table = 'user';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /**
     * auth smallzz
     * @param $where
     * @param $field
     * @return bool|false|\PDOStatement|string|\think\Collection
     */
    public function UListS($where = '',$page,$size){
        try{
            $count = $this->where($where)->count();
            $list = $this->where($where)->order('created_at desc')->page($page,$size)->select();
        }catch (Exception $exception){
            return false;
        }
        return ['list'=>$list,'total'=>$count];
    }
}