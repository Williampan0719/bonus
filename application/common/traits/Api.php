<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/11/13
 * Time: 上午11:51
 */

namespace app\common\traits;

use think\Validate;

trait Api
{

    /**
     * 返回成功接口
     * @param array $data
     * @param string $message_code
     * @return array
     */
    public function ajaxSuccess($message_code = '', array $data = [], $message = '')
    {
        $data = [
            'status' => 1,
            'message' => $message ?: config('code.' . $message_code),
            'data' => (object)$data,
            'code' => $message_code,
        ];

        return $data;
    }


    /**
     * 返回接口错误
     * @param $error_code
     * @param string $debug_message
     * @param array $data
     * @return array
     */
    public function ajaxError($error_code, array $data = [], $debug_message = '')
    {
        $data = [
            'status' => 0,
            'message' => $debug_message ?: config('code.' . $error_code),
            'data' => (object)$data,
            'code' => $error_code,
        ];

        return $data;
    }


    /**
     * 简单验证参数
     * @param $key
     * @param int $code
     * @param string $pattern
     * @return array|mixed
     */
    public function paramValidate($key, $code = 10, $pattern = '')
    {
        $param = input('?param.' . $key);

        if (!$param) {
            $this->allowWebClient();
            header('Content-Type:application/json; charset=utf-8');
            die(json_encode($this->ajaxError($code)));
        }

        return input('param.' . $key);
    }


    /**
     * 验证器验证参数
     * @param Validate $validate
     * @param $scene
     * @param array $params
     * @param int $code
     * @return bool
     */
    public function paramsValidate(Validate $validate, $scene, array $params, $code = 11)
    {
        $result = $validate->scene($scene)->check($params);
        if (!$result) {
            $this->allowWebClient();
            header('Content-Type:application/json; charset=utf-8');
            die(json_encode($this->ajaxError($code, [], $validate->getError())));
        }
        return $result;
    }

    /**
     * 跨域处理
     */
    protected function allowWebClient()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials:true');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Origin,Authorization,Content-Type,Accept,X-Requested-With,token');
    }
}