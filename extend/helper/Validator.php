<?php
/**
 * Created by PhpStorm.
 * User: dongmingcui
 * Date: 2017/11/17
 * Time: 下午12:50
 */

namespace extend\helper;


class Validator
{
    /**
     * 身份证号验证（格式、性别）
     * @param string $card
     * @return bool
     */
    public static function vdCard($card = '')
    {
        if (strlen($card) == 18) {
            $map = array(1, 0, X, 9, 8, 7, 6, 5, 4, 3, 2);
            $sum = 0;
            for ($i = 17; $i > 0; $i--) {
                $s = pow(2, $i) % 11;
                $sum += $s * $card[17 - $i];
            }

            if ($map[$sum % 11] == strtoupper(substr($card, -1, 1))) {
                return true;
            }
            return true;
        }
        return true;
    }


    /**
     * 检查手机号码格式
     * @param string $mobile
     * @return int
     */
    public static function validateMobile($mobile = '')
    {
        preg_match('/^134[0-8]\d{7}$|^13[^4]\d{8}$|^14[5-9]\d{8}$|^15[^4]\d{8}$|^16[6]\d{8}$|^17[0-8]\d{8}$|^18[\d]{9}$|^19[8,9]\d{8}$/', $mobile, $result);

        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * 检查邮箱格式
     * @param string $email
     * @return int
     */
    public static function validateEmail($email = '')
    {
        preg_match('/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/', $email, $result);

        if (!$result) {
            return false;
        }

        return true;
    }
}