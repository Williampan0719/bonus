<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/22
 * Time: 11:45
 * @introduce
 */
namespace app\backend\logic;

use app\common\logic\BaseLogic;
use app\payment\model\Enterprise;
use think\Exception;

class EnterpriseLogic extends BaseLogic
{
    protected $enterpriseModel;

    public function __construct()
    {
        $this->enterpriseModel=new Enterprise();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-22
     *
     * @description 企业账户金额列表
     * @return array
     */
    public function enterpriseList()
    {
        try{
            $page = $params['page'] ?? config('paginate.default_page');
            $size = $params['size'] ?? config('paginate.default_size');
            $total =  $this->enterpriseModel->enterpriseRechargeCount();
            if ($total > 0) {
                $bonusList =  $this->enterpriseModel->enterpriseRecharge($page, $size);
                if ($bonusList != false) {
                    $result = $this->ajaxSuccess(202, ['list' => $bonusList, 'total' => $total]);
                } else {
                    $result = $this->ajaxSuccess(202, ['list' => [], 'total' => $total]);
                }
            } else {
                $result = $this->ajaxSuccess(202, ['list' => [], 'total' => $total]);
            }
        }catch (Exception $exception){
            $result=$this->ajaxError(205);
        }
        return $result;
    }
    /**
     * @Author liyongchuan
     * @DateTime 2018-01-22
     *
     * @description 企业账户的充值
     * @param float $money
     * @return array
     */
    public function enterpriseAdd(float $money)
    {
        try{
            $enterpriseInfo=$this->enterpriseModel->enterpriseLast();
            if($enterpriseInfo){
                $enterprise_balance=$money+$enterpriseInfo['enterprise_balance'];
            }else{
                $enterprise_balance=$money;
            }
            $data=[
                'type'=>0,
                'money'=>$money,
                'enterprise_balance'=>$enterprise_balance
            ];
            $id=$this->enterpriseModel->enterpriseAdd($data);
            if($id>0){
                $result=$this->ajaxSuccess(200);
            }else{
                $result=$this->ajaxError(206);
            }
        }catch (Exception $exception){
            $result=$this->ajaxError(206);
        }
        return $result;
    }
}