<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/12/8
 * Time: 上午10:38
 */

namespace app\backend\controller;

use app\common\controller\BaseController;

use think\Request;
class BaseAdmin extends BaseController
{

    protected $adminId;
    protected $account;
    protected $params;
    protected $request;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->allowWebClient();
        $this->request = $request;
        $this->params = $this->request->param();
        $header = $this->request->header();
        /*
        $tokenId = "SJK3zSvzciwY6MF3jjCIXTduwg3+vcQqhLQrX6L1KdtAPz8=aCRRGiE1oboQYgRSI+zBsEce9XLWbk4qRGjc6949";
        $token = $header['token'];
        if($tokenId!=$token) {
            $result = $this->ajaxError(1004);
            echo json_encode($result);exit;
        }
        */
    }



}