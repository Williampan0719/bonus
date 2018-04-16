<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/11
 * Time: 下午4:36
 */

namespace app\payment\logic;


use app\common\logic\BaseLogic;
use app\game\model\GameSsc;
use app\index\behavior\Power;
use app\payment\model\Abonus;
use app\payment\model\Bonus;
use app\payment\model\BonusHall;
use app\payment\model\BonusReceive;
use app\payment\model\BonusRemark;
use app\system\model\SystemPower;
use app\user\model\User;
use app\user\model\UserLog;
use app\user\model\UserPower;
use extend\service\RedisService;
use think\Exception;

class BonusHallLogic extends BaseLogic
{
    protected $hall = null;
    protected $redis = null;
    function __construct()
    {
        $this->hall = new BonusHall();
        $this->redis = new RedisService();
    }

    /** 将红包塞入大厅
     * auth smallzz
     * @param $param
     * @return string
     * @throws Exception
     */
    public function addHall($param){
        try{
            #if($param['type'] == 1){      #新增type类型 0内部红包 1大厅红包
                $user = new User();
                #这里存入redis  2018-1-13 smallzz
                $info = $user->where(['openid'=>$param['uid']])->field('nickname,avatarulr,id')->find();
                $data['bonus_id'] = $param['bonus_id']; #红包id
                $data['endtime'] = time()+86400;
                $data['bonus_password'] = $param['bonus_password'];
                $data['uid'] = $info['id'];
                $data['openid'] = $param['uid'];
                $data['nickname'] = $info['nickname'];
                $data['avatarulr'] = $info['avatarulr'];
                $data['total'] = $param['bonus_num'];
                $this->redis->lpush('hallbonus',json_encode($data));
                #$this->redis->expire('hallbonus',86400);
                #加入红包大厅表
                $hallid = $this->hall->addHall(['bonus_id'=>$param['bonus_id'],'uid'=>$param['uid']]);
                #$hallid = $this->hall->addHall($param);
        }catch (Exception $exception){
            var_dump($exception->getMessage());exit;
        }
        return $hallid;
    }

    /** 获取大厅记录 内存
     * auth smallzz
     * @return array
     */
    public function getHalls($param){
        if(empty($param['page'])){
            $page = 0;
            $page_e = 10;
        }else{
            $page = $param['page'] * 10;
            $page_e = $page+10;
        }
        $list = $this->redis->lrange('hallbonus',$page,$page_e);
        /*$lists = [];
        foreach ($list as $k=>$v){
            $res = json_decode($v,true);
            $lists[$k]['uid'] = $res['uid'];
            $lists[$k]['bonus_id'] = $res['bonus_id'];
            $lists[$k]['bonus_password'] = $res['bonus_password'];
            $lists[$k]['nickname'] = $res['nickname'];
            $lists[$k]['avatarulr'] = $res['avatarulr'];
            $lists[$k]['bonus_num'] = $res['total'];
            $num = count($this->redis->lrange('bonus_'.$res['bonus_id'],0,-1));
            if($num <= 0){   #判断是否被领取完
                $lists[$k]['status'] = 0;
                $lists[$k]['yunum'] = $num;
            }else{
                $lists[$k]['status'] = 1;
                $lists[$k]['yunum'] = $num;
            }
        }*/
        $listb = [];
        $lista = [];
        foreach ($list as $k=>$v){
            $res = json_decode($v,true);
            $num = count($this->redis->lrange('bonus_'.$res['bonus_id'],0,-1));
            if($num <= 0){   #判断是否被领取完
                $listb[$k]['status'] = 0;
                $listb[$k]['yunum'] = $num;
                $listb[$k]['uid'] = $res['uid'];
                $listb[$k]['bonus_id'] = $res['bonus_id'];
                $listb[$k]['bonus_password'] = $res['bonus_password'];
                $listb[$k]['nickname'] = $res['nickname'];
                $listb[$k]['avatarulr'] = $res['avatarulr'];
                $listb[$k]['bonus_num'] = $res['total'];
            }else{
                $lista[$k]['status'] = 1;
                $lista[$k]['yunum'] = $num;
                $lista[$k]['uid'] = $res['uid'];
                $lista[$k]['bonus_id'] = $res['bonus_id'];
                $lista[$k]['bonus_password'] = $res['bonus_password'];
                $lista[$k]['nickname'] = $res['nickname'];
                $lista[$k]['avatarulr'] = $res['avatarulr'];
                $lista[$k]['bonus_num'] = $res['total'];
            }
        }
        /*foreach ($lists as $k=>$v){
            if($v['status'] == 0){
                $listb[$k] = $lists[$k];
                unset($lists[$k]);
            }else{
                $lista[$k] = $lists[$k];
            }
        }*/
        $lista = array_values($lista);
        $listb = array_values($listb);
        /*$counta = count($lista);
        $i = $counta;
        foreach ($listb as $k=>$v){
            $lista[$i] = $listb[$k];
            $i+=1;
        }*/
        #return $this->ajaxSuccess(102,$lista);
        return $this->ajaxSuccess(102,['notredone'=>$lista,'redone'=>$listb]);
    }
    /** 获取所有大厅记录 DB
     * auth smallzz
     * @param $param
     */
    public function getAllHall($param){
        $page = $param['page'] ?? config('paginate.default_page');
        $size = 10;#$param['size'] ?? config('paginate.default_size');
        try{
            $ssc = new GameSsc();
            $pk = $ssc->getSscList();
            $list = $this->hall->getAllHall($page,$size);
            $adv = $this->hall->getAdvHall($page,$size);
            if (!empty($adv)) {
                $user = new User();
                $logos = array_column($adv,'uid');
                $logo = $user->getNameList($logos);
                $a = [];
                foreach ($logo as $k => $v) {
                    $a[$v['openid']] = $v['avatarulr'];
                }
                $view = new BonusRemark();
                $views = array_column($adv,'id');
                $num = $view->getList(['bonus_id'=>['in',$views]]);
                $b = [];
                foreach ($num as $k => $v) {
                    $b[$v['bonus_id']] = $v['view_num'];
                }
                foreach ($adv as $k => $v) {
                    $adv[$k]['avatarulr'] = $a[$v['uid']];
                    $adv[$k]['view'] = $b[$v['id']] ?? 0;
                }
            }


        }catch (Exception $exception){
            return $this->ajaxError(105);
        }
        return $this->ajaxSuccess(102,['adv'=>$adv,'list'=>$list,'pk'=>$pk]);
    }

    /**获取今日土豪榜
     * auth smallzz
     */
    public function getDayLocalTyrants(){
        try{
            $list = $this->redis->get('localtyrants');
            if($list){
                return $this->ajaxSuccess(102,json_decode($list,true));
            }
            $list = $this->hall->getLocalTyrants();
        }catch (Exception $exception){
            return $this->ajaxError(105);
        }
        return $this->ajaxSuccess(102,$list);
    }

    /** 获取今日手气最佳
     * auth smallzz
     */
    public function getDayBestLuck(){
        try{
            $list = $this->redis->get('bestluck');
            if($list){
                return $this->ajaxSuccess(102,json_decode($list,true));
            }
            $list = $this->hall->getBestLuck();
        }catch (Exception $exception){
            return $this->ajaxError(105);
        }
        return $this->ajaxSuccess(102,$list);
    }

    /** 随机出现一条口令
     * auth smallzz
     * @return mixed
     */
    public function randExample(int $type){
        switch ($type){
            case 0:
                $example = config('example');
                $rand = rand(0,count($example)-1);
                break;
            case 1:
                $example = config('voice');
                $rand = rand(0,count($example)-1);
                break;
        }
        return $this->ajaxSuccess(102,['rand'=>$example[$rand]]);
    }

    /** 获取大厅详细信息
     * auth smallzz
     * @param $param
     * @return array
     */
    public function getHallInfo($param){

        $receive = new BonusReceive();
        $power = new UserPower();
        $system = new SystemPower();
        $bonushall = new BonusHall();
        $daynum = $this->redis->get('bonus_day_get_num');
        $viewnum = $this->redis->get('bonus_day_view_num');
        $send = (new Bonus())->getUserCount(['uid'=>$param['openid']]);
        $ask = (new Abonus())->getUserCount(['uid'=>$param['openid']]);
        $info = [

            'shuffling'=>$receive->getReceLimit(),
            'localt'=>$bonushall->getLocalTyrants(),
            'bestl'=>$bonushall->getBestLuck(),
            'totalnum'=>$receive->getMeTotal($param['openid']),#我抢到的个数
            'mysend' => $send+$ask,
            'daynum'=>empty($daynum) ? 0 : $daynum,   #今日抢到人数
            'viewnum'=>empty($viewnum) ? 0 : $viewnum, #访问数
            'have'=>$power->getOne($param['openid'])['power'],
            'expend'=>$system->getOne('fetch')['num'] ?? 0
            ];
        #增加浏览
        $userlog = new UserLog();
        $userlog->uLogAdd($param['openid']);
        return $this->ajaxSuccess(202, ['list' => $info]);
    }

    /** 分享红包到大厅
     * auth smallzz
     */
    public function shareHall($param){
        #查询红包信息
        $bonus = new Bonus();
        try{
            #判断是否在大厅
            $isfalse = $this->hall->getHallInfo($param['bonus_id']);
            if(!empty($isfalse)){
                return $this->ajaxError(1601);
            }
            $info = $bonus->bonusDerail($param['bonus_id']);
            #这里存入redis  2018-1-13 smallzz
            $param['bonus_password'] = $info['bonus_password'];
            $param['bonus_num'] = $info['bonus_num'];


            $this->addHall($param);
            return $this->ajaxSuccess(108, ['list' => $info]);
        }catch (Exception $exception){

            return $this->ajaxError(1602);
        }
    }

}