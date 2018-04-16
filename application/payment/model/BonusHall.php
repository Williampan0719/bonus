<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/11
 * Time: 上午11:02
 */

namespace app\payment\model;


use app\common\model\BaseModel;
use extend\service\RedisService;
use think\Db;
use think\db\Query;

class BonusHall extends BaseModel
{
    protected $table = 'payment_bonus_hall';

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    /** 将红包大厅的红包添加到此表
     * auth smallzz
     * @param $param
     * @return string
     */
    public function addHall($param){
        #添加红包到大厅
        $hall = new BonusHall($param);
        $hall->allowField(true)->save();
        return $hall->getLastInsID();
    }

    /** 获取大厅所有红包
     * auth smallzz
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAllHall($page,$size){
        $redis = new RedisService();
        $list = $this->alias('ah')
            ->join('wx_payment_bonus pb','pb.id = ah.bonus_id','inner')
            ->join('wx_user u','u.openid = ah.uid','inner')
            ->where('pb.is_pay = 1')
            ->where('pb.class != 2')
            ->field('ah.bonus_id,u.nickname,u.avatarulr,pb.bonus_num,pb.bonus_password,ah.uid')

            ->order('pb.is_done asc,pb.id desc')
            ->page($page,$size)
            ->select();
        foreach ($list as $k=>$v){
            $num = count($redis->lrange('bonus_'.$v['bonus_id'],0,-1));
            if($num <= 0){   #判断是否被领取完
                $list[$k]['status'] = 0;
                $list[$k]['yunum'] = $num;
            }else{
                $list[$k]['status'] = 1;
                $list[$k]['yunum'] = $num;
            }
        }
        return $list;
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-05
     *
     * @description
     * @param $page
     * @param $size
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAdvHall($page,$size){
        $redis = new RedisService();
        $bonus = new Bonus();
        $where = ['class'=>2,'type'=>1,'is_pay'=>1,'finish_at'=>null];
        $list = $bonus->getAdvHall($where,$page,$size);
        foreach ($list as $k=>$v){
            $num = count($redis->lrange('bonus_'.$v['id'],0,-1));
            if($num <= 0){   #判断是否被领取完
                $list[$k]['status'] = 0;
                $list[$k]['yunum'] = $num;
            }else{
                $list[$k]['status'] = 1;
                $list[$k]['yunum'] = $num;
            }
        }
        return $list;
    }

    /** 获取今日土豪榜
     * auth smallzz
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getLocalTyrants1(){
        $list = $this->alias('bh')
            ->join('wx_payment_bonus pb','pb.id=bh.bonus_id','inner')
            ->join('wx_user u','u.openid = bh.uid','inner')
            ->where('bh.created_at >= "'.date('Y-m-d').' 00:00:00" and bh.created_at <= "'.date('Y-m-d').' 23:59:59" and pb.is_pay = 1')
            ->field('sum(pb.bonus_money) as total_amount,count(bh.id) as total_num,u.nickname,u.avatarulr')
            ->group('u.nickname')
            ->order('total_amount desc')
            ->limit(10)
            ->select();
        return $list;
    }
    public function getLocalTyrants(){
        $list = Db::query('SELECT sum(pb.bonus_money) as total_amount,count(pb.id) as total_num,u.nickname,u.avatarulr  from wx_payment_bonus as pb
left join `wx_user` `u` ON `u`.`openid`=`pb`.`uid` 
where (pb.created_at >= "'.date("Y-m-d").' 00:00:00" and pb.created_at <= "'.date("Y-m-d").' 23:59:59" and pb.is_pay = 1)
group by u.nickname ORDER BY total_amount desc limit 10');

        return $list;
    }
    /** 获取今日手气最佳
     * auth smallzz
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getBestLuck(){

        $list = Db::query('select * from (SELECT `u`.`nickname`,`bd`.`receive_money` as total_amount,`u`.`avatarulr` 
FROM `wx_payment_bonus_detail` `bd` 
INNER JOIN `wx_payment_bonus_receive` `br` ON `br`.`detail_id`=`bd`.`id` 
INNER JOIN `wx_user` `u` ON `u`.`openid`=`br`.`receive_uid` 
WHERE  (  br.created_at >= "'.date('Y-m-d').' 00:00:00" and br.created_at <= "'.date('Y-m-d').' 23:59:59" ) 
ORDER BY total_amount desc LIMIT 10) AS mm GROUP BY nickname order by total_amount desc');

        return $list;
    }

    /** 判断红包是否存在大厅
     * auth smallzz
     * @param int $bonus_id
     * @return int|string
     */
    public function getHallInfo(int $bonus_id){
        return $this->where(['bonus_id'=>$bonus_id])->count();
    }
}