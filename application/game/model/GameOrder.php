<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/30
 * Time: 下午3:19
 */

namespace app\game\model;


use app\common\model\BaseModel;

class GameOrder extends BaseModel
{
    protected $table = 'game_order';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /** 订单添加
     * auth smallzz
     * @param array $param
     * @return string
     */
    public function orderAdd(array $param){
        $model = new GameOrder($param);
        $model->allowField(true)->save();
        return $model->getLastInsID();
    }

    /**
     * auth smallzz
     * @param string $ordersn
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function orderInfo(string $ordersn){
        return $this->where(['order_sn'=>$ordersn])->find();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-09
     *
     * @description 根据订单id获取单条
     * @param int $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getOneById(int $id){
        return $this->where(['id'=>$id])->find();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-09
     *
     * @description 改订单状态
     * @param array $params
     * @return false|int
     */
    public function orderEdit(array $params)
    {
        return $this->allowField(['finish_at','is_close'])
            ->save($params, ['order_sn' => $params['order_sn']]);
    }

}