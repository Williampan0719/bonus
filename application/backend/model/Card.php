<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/24
 * Time: 下午1:18
 */

namespace app\backend\model;


use app\common\model\BaseModel;

class Card extends BaseModel
{
    protected $table = 'card';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /** 添加卡卷
     * auth smallzz
     * @param array $param
     * @return mixed
     */
    public function cardAdd(array $param){
        $cardModel=new Card($param);
        $cardModel->allowField(true)->save();
        return $cardModel->id;
    }
    /** 获取卡卷
     * auth smallzz
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCardSelect(string $field){
        $list = $this->field($field)->select();
        return $list;
    }


}