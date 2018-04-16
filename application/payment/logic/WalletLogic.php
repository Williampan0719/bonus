<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/7
 * Time: 11:28
 * @introduce
 */

namespace app\payment\logic;

use app\common\logic\BaseLogic;
use app\payment\model\Wallet;
use app\user\model\User;
use think\Exception;

class WalletLogic extends BaseLogic
{
    protected $walletModel;

    public function __construct()
    {
        $this->walletModel = new Wallet();
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-07
     *
     * @description 提现查询余额(在用)
     * @param array $params
     * @return array
     */
    public function userWallet(array $params)
    {
        try {
            $userModel=new User();
            $userInfo=$userModel->userDetail($params['uid']);
            $walletDetail = $this->walletModel->walletDetail($params['uid']);
            $walletDetail['balance']=sprintf("%01.2f",intval($walletDetail['balance']*100)/100);
            $walletDetail['avatarulr']=$userInfo['avatarulr'];
            $result = $this->ajaxSuccess(102, ['wallet' => $walletDetail]);
        } catch (Exception $exception) {
            $result = $this->ajaxError(105);
        }
        return $result;
    }

    /**
     * @Author liyongchuan
     * @DateTime 2018-01-19
     *
     * @description 首次满1元提现
     * @param array $params
     * @return array|bool
     */
    public function userWalletFirst(array $params)
    {
        try{
            $payLogic=new PayLogic();
            $params['enterprise_type']=1;//自动提现
            $result=$payLogic->EnterprisePay($params);
            if($result['status']==1){
                $result = true;
            }else{
                $result = false;
            }
        }catch (Exception $exception){
            $result = false;
        }
        return $result;
    }
}