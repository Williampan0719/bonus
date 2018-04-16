<?php
/**
 * Created by PhpStorm.
 * User: liyongchuan
 * Date: 2018/1/22
 * Time: 09:32
 * @introduce
 */

namespace app\message\logic;

use app\common\logic\BaseLogic;
use PHPMailer\PHPMailer\PHPMailer;

class EmailLogic extends BaseLogic
{
    protected $config;

    public function __construct()
    {
        $this->config = config('email');
    }

    /**
     * 系统邮件发送函数
     * @param string $tomail 接收邮件者邮箱
     * @param string $name 接收邮件者名称
     * @param string $subject 邮件主题
     * @param string $body 邮件内容
     * @param string $attachment 附件列表
     * @return boolean
     * @author static7 <static7@qq.com>
     */
    public function sendEmail($tomail, $name, $subject = '', $body = '', $attachment = null)
    {
        $mail = new PHPMailer();           //实例化PHPMailer对象
        $mail->CharSet = 'UTF-8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP();                    // 设定使用SMTP服务
        $mail->SMTPDebug = 0;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
        $mail->SMTPAuth = true;             // 启用 SMTP 验证功能
        $mail->SMTPSecure = 'ssl';          // 使用安全协议
        $mail->Host = $this->config['host']; // SMTP 服务器
        $mail->Port = $this->config['port']; // SMTP服务器的端口号
        $mail->Username = $this->config['username'];    // SMTP服务器用户名
        $mail->Password = $this->config['password'];     // SMTP服务器密码
        $mail->SetFrom($this->config['username'], 'pgyxwd');
        $replyEmail = '';                   //留空则为发件人EMAIL
        $replyName = '';                    //回复名称（留空则为发件人名称）
        $mail->AddReplyTo($replyEmail, $replyName);
        $mail->Subject = $subject;
        $mail->MsgHTML($body);
        $mail->AddAddress($tomail, $name);
        if (is_array($attachment)) { // 添加附件
            foreach ($attachment as $file) {
                is_file($file) && $mail->AddAttachment($file);
            }
        }
        return $mail->Send() ? true : $mail->ErrorInfo;
    }
}