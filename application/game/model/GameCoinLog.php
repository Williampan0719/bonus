<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/27
 * Time: 下午2:57
 */

namespace app\game\model;


use app\common\model\BaseModel;

class GameCoinLog extends BaseModel
{
    protected $table = 'game_coin_log';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /** 添加记录
     * auth smallzz
     * @param string $uid
     * @param int $type
     * @param string $coin
     * @param $sym
     * @param int $balance
     * @return string
     */
    public function addLog(string $uid,int $type,string $coin,int $sym, int $balance = 0, int $ssid = 0){
        $param = ['uid'=>$uid,'type'=>$type,'coin'=>$coin,'symbol'=>$sym,'balance'=>$balance];
        $log = new GameCoinLog($param);
        $log->allowField(true)->save();
        return $log->getLastInsID();
    }

    /** 查看记录
     * auth smallzz
     * @param string $uid
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function listLog(string $uid,int $page, int $size){

        $list = $this->where(['uid'=>$uid])->order('created_at desc, symbol desc')->page($page, $size)->select();
        foreach ($list as $k=>$v){
            switch ($v['type']){
                #0,发起挑战，10挑战奖励，1，押注，2押注奖励，3应战，4应战奖励，5欢乐大转盘，6欢乐大转盘奖励
                case 0:
                    $list[$k]['type'] = '发起挑战';
                    break;
                case 10:
                    $list[$k]['type'] = '挑战奖励';
                    break;
                case 1:
                    $list[$k]['type'] = '押注';
                    break;
                case 2:
                    $list[$k]['type'] = '押注奖励';
                    break;
                case 3:
                    $list[$k]['type'] = '应战';
                    break;
                case 4:
                    $list[$k]['type'] = '应战奖励';
                    break;
                case 5:
                    $list[$k]['type'] = '欢乐大转盘';
                    break;
                case 6:
                    $list[$k]['type'] = '欢乐大转盘奖励';
                    break;
                case 7:
                    $list[$k]['type'] = '金币充值';
                    break;
                case 8:
                    $list[$k]['type'] = '签到';
                    break;
                case 9:
                    $list[$k]['type'] = '退款';
                    break;
                case 11:
                    $list[$k]['type'] = '平局';
            }
        }
        return $list;

    }

    /**
     * @Author panhao
     * @DateTime 2018-02-01
     *
     * @description 明细搜索
     * @param array $param
     * @param int $page
     * @param int $size
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function searchDetailRows(array $param, int $page, int $size)
    {
        return $this->where($param)->field('*')->page($page,$size)->order('created_at desc')->select();
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-01
     *
     * @description 总数
     * @param array $param
     * @return int|string
     */
    public function getCount(array $param)
    {
        return $this->where($param)->count();
    }
}