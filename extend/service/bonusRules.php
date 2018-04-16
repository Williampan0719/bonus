<?php
/**
 * Created by PhpStorm.
 * User: smallzz
 * Date: 2018/1/7
 * Time: 下午6:58
 */

namespace extend\service;

#红包分配规则
class bonusRules
{
    private $gtThreshold = 0.1;
    private $amount = 0;
    private $num = 0;
    function __construct(float $amount,int $num)
    {
        $this->amount = $amount;
        $this->num = $num;
    }

    /**
     * auth smallzz
     * @return mixed
     */
    public function distribution(){
        for ($i=1;$i<$this->num;$i++){
            $safe_total = ($this->amount-($this->num-$i)*$this->gtThreshold);    //随机安全上限
            $safe_total = $safe_total/$this->getIntNum(1,$this->num-$i);    //公正随机处理
            $money = $this->getIntNum($this->gtThreshold,$safe_total);
            $this->amount = round($this->amount-$money,2);
            $new[$i] = $money;
        }
        if($this->num==1) {
            $money = $this->amount;
        } else {
            $key = array_rand($new,1);
            $money = $new[$key];
        }
        $new[$this->num] = $this->amount;
        return $new;
    }

    /**
     * auth smallzz
     * @param $min
     * @param $max
     * @return float|int
     */
    private function getIntNum($min,$max){
        return round(mt_rand($min*100,$max*100),2)/100;
    }
}