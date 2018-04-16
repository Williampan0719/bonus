<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/25
 * Time: 下午2:20
 * @introduce 讨红包邀请表
 */
namespace app\payment\model;

use app\common\model\BaseModel;

class Abonus extends BaseModel
{

    protected $table = 'payment_abonus';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = false;

    /**
     * @Author panhao
     * @DateTime 2018-01-25
     *
     * @description 生成讨红包
     * @param array $params
     * @return mixed
     */
    public function saveAskingBonus(array $params)
    {
        $bonusModel = new Abonus($params);
        $bonusModel->allowField(true)->save();
        return $bonusModel->id;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-25
     *
     * @description 获取讨红包详情
     * @param int $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getDetail(int $id)
    {
        return $this->field('*')->where(['id'=>$id])->find();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-26
     *
     * @description 编辑讨红包
     * @param array $param
     * @param $id
     * @return false|int
     */
    public function editAbonus(array $param, $id)
    {
        return $this->allowField('receive_money,num,service_money')->save($param,['id'=>$id]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-29
     *
     * @description 后台搜索
     * @param array $param
     * @param int $page
     * @param int $size
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function searchRows(array $param,int $page,int $size)
    {
        return $this->where($param)->field('*')->page($page,$size)->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-30
     *
     * @description 获取列表
     * @param array $param
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList(array $param,string $field)
    {
        return $this->field($field)->where($param)->select();
    }

    /**
     * @Author panhao
     * @DateTime
     *
     * @description
     * @param array $param
     * @return int|string
     */
    public function getUserCount(array $param)
    {
        return $this->where($param)->count();
    }
}