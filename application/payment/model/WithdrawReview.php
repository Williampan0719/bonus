<?php
/**
 * Created by PhpStorm.
 * User: panhao
 * Date: 2018/2/7
 * Time: 上午10:32
 * @introduce
 */
namespace app\payment\model;

use app\common\model\BaseModel;

class WithdrawReview extends BaseModel
{
    protected $table = 'payment_withdraw_review';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /**
     * @Author panhao
     * @DateTime 2018-02-07
     *
     * @description 生成初始记录
     * @param array $param
     * @return string
     */
    public function initOne(array $param)
    {
        $view = new WithdrawReview($param);
        $view->allowField(true)->save();
        return $view->getLastInsID();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-07
     *
     * @description 后台搜索列表
     * @param array $param
     * @param int $page
     * @param int $size
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function searchRows(array $param, int $page, int $size)
    {
        return $this->field('id,uid,money,status,created_at,updated_at')->where($param)->order('updated_at')->page($page,$size)->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-07
     *
     * @description 获取个数
     * @param array $param
     * @return int|string
     */
    public function getCount(array $param)
    {
        return $this->where($param)->count();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-07
     *
     * @description 获取单条
     * @param array $param
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getOne(array $param)
    {
        return $this->where($param)->find();
    }

    public function editReview(array $param)
    {
        return $this->allowField(true)->save($param,['id'=>$param['id']]);
    }
}