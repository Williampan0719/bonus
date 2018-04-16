<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/25
 * Time: 下午2:20
 * @introduce 讨红包赏金表
 */
namespace app\payment\model;

use app\common\model\BaseModel;

class AbonusSend extends BaseModel
{

    protected $table = 'payment_abonus_send';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = false;

    /**
     * @Author panhao
     * @DateTime 2018-01-25
     *
     * @description 获取单条记录
     * @param array $param
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getOne(array $param)
    {
        return $this->where($param)->order('created_at desc')->find();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-25
     *
     * @description 获取单条记录
     * @param array $param
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getSum(array $param)
    {
        return $this->where($param)->sum('money');
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-01
     *
     * @description 获取总数
     * @param array $param
     * @return int|string
     */
    public function getCount(array $param)
    {
        return $this->where($param)->count();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-25
     *
     * @description 生成初始数据
     * @param array $param
     * @return mixed
     */
    public function initOne(array $param)
    {
        $bonusModel = new AbonusSend($param);
        $bonusModel->allowField(true)->save();
        return $bonusModel->id;
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-26
     *
     * @description 编辑打赏状态
     * @param array $param
     * @param int $id
     * @return false|int
     */
    public function editPay(array $param,int $id)
    {
        return $this->allowField(['money','is_pay','is_send','updated_at','prepay_id'])->save($param,['id'=>$id]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-27
     *
     * @description 编辑支付状态
     * @param array $param
     * @return false|int
     */
    public function editWxPay(array $param)
    {
        return $this->allowField(['money','is_pay','is_send','updated_at','prepay_id'])->save($param,['uid'=>$param['uid'],'abonus_id'=>$param['abonus_id']]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-03
     *
     * @description 编辑赏红包信息
     * @param array $param
     * @param int $id
     * @return false|int
     */
    public function editBonus(array $param,int $id)
    {
        return $this->allowField(['remark_type','remark_word','remark_voice','timelength'])->save($param,['id'=>$id]);
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-26
     *
     * @description 获取列表
     * @param array $param
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList(array $param, string $field)
    {
        return $this->where($param)->field($field)->order('created_at desc')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-01
     *
     * @description 搜索
     * @param array $param
     * @param int $page
     * @param int $size
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function searchRowById(array $param,int $page,int $size)
    {
        return $this->where($param)->field('*')->page($page,$size)->order('created_at desc')->select();
    }
}