<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/12/8
 * Time: 下午1:32
 */

namespace app\api\controller;


use app\common\controller\BaseController;
use app\system\logic\SystemImageLogic;
use extend\helper\Files;
use think\Request;

class BaseApi extends BaseController
{
    protected $uuid;
    protected $account;
    protected $params;
    protected $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
        $this->allowWebClient();
        $hash = $this->request->header('hash');
        $this->_validateHash($hash ?? '');
    }

    /**
     * @Author panhao
     * @DateTime 2018-02-05
     *
     * @description 验证版本号
     * @param string $hash
     * @return bool
     */
    private function _validateHash(string $hash)
    {
        if(($this->request->baseUrl() == '/api/pay/wx-notify') || ($this->request->baseUrl() == '/api/pay/wx-notify_game')) {
            return true;
        }
        $system = new SystemImageLogic();
        $data['hash'] = $hash;
        $result = $system->deVersionInfo($data);
        if (!in_array($result['version_hash']['client_version'],config('hash')) || empty($result['version_hash'])) {
            header('Content-Type:application/json; charset=utf-8');
            die(json_encode($this->ajaxError(1007, [])));
        }
    }

}