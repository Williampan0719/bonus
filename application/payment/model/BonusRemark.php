<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/2/4
 * Time: 下午10:33
 * @introduce
 */
namespace app\payment\model;

use app\common\model\BaseModel;

class BonusRemark extends BaseModel
{
    protected $table = 'payment_bonus_remark';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /**
     * @Author panhao
     * @DateTime 2018-02-04
     *
     * @description 连续单条新增
     * @param array $param
     * @return false|int
     */
    public function addRemarkByBonus(array $param)
    {
        $re = new BonusRemark($param);
        return $re->allowField(true)->save();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-05
     *
     * @description 批量保存广告详情
     * @param array $param
     * @return array|false
     */
    public function addSave(array $param)
    {
        return $this->allowField(true)->saveAll($param);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-05
     *
     * @description 获取单条
     * @param array $param
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getOne(array $param)
    {
        return $this->field('text,view_num')->where($param)->find();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-05
     *
     * @description 浏览量
     * @param array $param
     * @return int|true
     */
    public function addViewNum(array $param)
    {
        return $this->where($param)->setInc('view_num',1);
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-05
     *
     * @description 获取列表
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList(array $param)
    {
        return $this->where($param)->order('created_at desc')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-13
     *
     * @description 将广告详情清空
     * @param array $param
     * @return false|int
     */
    public function initRemark(array $param)
    {
        return $this->allowField(true)->save(['text'=>''],['bonus_id'=>$param['bonus_id']]);
    }
}