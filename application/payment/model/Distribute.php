<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/1/10
 * Time: 下午12:27
 * @introduce 提成日志
 */
namespace app\payment\model;

use app\common\model\BaseModel;
use traits\model\SoftDelete;

class Distribute extends BaseModel
{
    use SoftDelete;

    protected $table = 'payment_distribute';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    /**
     * @Author panhao
     * @DateTime 2018-1-10
     *
     * @description 新增记录
     * @param array $param
     * @return false|int
     */
    public function addLog(array $param)
    {
        $disModel=new Distribute($param);
        $disModel->allowField(true)->save();
        return $disModel->id;
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-10
     *
     * @description 条件搜索列表
     * @param array $where
     * @param int $page
     * @param int $size
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function searchRowsByUid(array $where, int $page, int $size) {
        return $this->field('id,bonus_id,time,uid,bonus_money,payable_money,to_uid,commission,created_at,updated_at')->order('created_at desc')->where($where)->page($page,$size)->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-10
     *
     * @description 我的页面搜索列表
     * @param array $where
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function searchMyByUid(array $where, string $order) {
        return $this->field('id,bonus_id,time,uid,bonus_money,payable_money,to_uid,commission,created_at,updated_at')->where($where)->order($order)->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-1-10
     *
     * @description 获取某状态个数
     * @param array $where
     * @return int|string
     */
    public function getCount(array $where)
    {
        return $this->where($where)->count();
    }

    /**
     * @Author panhao
     * @DateTime 2018-01-18
     *
     * @description 获取最新一条提成
     * @param array $where
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getLastOne(array $where)
    {
        return $this->where($where)->field('all_commission')->order('created_at desc')->find();
    }

}