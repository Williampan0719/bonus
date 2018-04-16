<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2017/12/7
 * Time: 下午6:19
 */
namespace app\api\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'page'              =>  'number',
        'size'              =>  'number',
        'mobile'            =>  'number|require|checkMobile:',
        'code'              =>  'require',
        'openid'            =>  'require',
        'api_type'          =>  'require|in:1,2,3',
        'client_version'    =>  'require',
        'device'            =>  'require',
        'client_type'       =>  'require',
        'portrait'          =>  'require',
        'birthday'          =>  'require',
        'nick_name'         =>  'require',
        'to_uuid'           =>  'require',
        'cate_id'           =>  'require',
        'true_name'         =>  'require',
        'bonus_id'          =>  'require|number',
    ];
    protected $message = [
        'page.number'       =>  '页码必须为数字',
        'size.number'       =>  '页数必须为数字',
        'mobile.require'    =>  '手机号不能为空',
        'mobile.number'     =>  '手机号必须为数字',
        'mobile.checkMobile'   =>  '手机号格式错误',
        'code.require'      =>  '验证码不能为空',
        'openid.require'    =>  'openid不能为空',
        'api_type.require'  =>  '第三方登录类型必须选择',
        'api_type.in'       =>  '类型错误',
        'client_version'    =>  '客户端版本不能为空',
        'device'            =>  '设备号不能为空',
        'client_type'       =>  '客户端类型不能为空',
        'portrait'          =>  '头像不能为空',
        'nick_name'         =>  '昵称不能为空',
        'birthday'          =>  '出生年月不能为空',
        'to_uuid'           =>  '操作对象不能为空',
        'cate_id'           =>  '请选择举报标题',
        'true_name'         =>  '真实姓名不能为空',
        'bonus_id.require'  =>  '红包id不能为空',
        'bonus_id.number'   =>  '红包id必须为数字',

    ];
    protected $scene = [
        'sendCode'          =>  ['mobile'],
        'login'             =>  ['mobile','client_version','type','code'],
        'thirdLogin'        =>  ['openid','api_type','client_version','type'],
        'perfectInfo'       =>  ['portrait', 'nick_name', 'birthday'],
        'bindThird'         =>  ['openid','api_type'],
        'changeMobile'      =>  ['code','mobile'],
        'followUser'        =>  ['to_uuid'],
        'blackUser'         =>  ['to_uuid'],
        'reportUser'        =>  ['to_uuid','cate_id'],
        'binding'           =>  ['openid'],
        'myPage'            =>  ['openid'],
        'power'             =>  ['openid'],
        'audio'             =>  ['openid'],
        'precondition'      =>  ['mobile','true_name','openid'],
        'listen'            =>  ['openid','bonus_id'],
        'check'             =>  ['openid','bonus_id'],

    ];


    /**
     * 检查手机号码格式
     * @param string $mobile
     * @return int
     */
    public static function checkMobile($mobile = '')
    {
        preg_match('/^134[0-8]\d{7}$|^13[^4]\d{8}$|^14[5-9]\d{8}$|^15[^4]\d{8}$|^16[6]\d{8}$|^17[0-8]\d{8}$|^18[\d]{9}$|^19[8,9]\d{8}$/', $mobile, $result);

        if (!$result) {
            return false;
        }
        return true;
    }


}