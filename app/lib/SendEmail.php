<?php


namespace app\index\lib;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
class SendEmail
{
    public $error;
    protected $config=[
        'Host'=>'smtp.qq.com',
        'Username'=>'542920634@qq.com',
        'Password'=>'jkjxefnrukhibbch',
    ];
    public function send($tomail,$name,$subject = '',$body = '',$attachment = null){
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                            // 设定使用SMTP服务
            $mail->Host       = $this->config['Host'];                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = $this->config['Username'];              // SMTP username
            $mail->Password   = $this->config['Password'];                     // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 25;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            //recipients 收件人
            $mail->setFrom($this->config['Username'], 'Mailer');

            $mail->addAddress($tomail, $name);     // Add a recipient// Name is optional
            $mail->addReplyTo($this->config['Username'], 'susan');
            // Content 内容
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    =$body;
            $mail->send();
            return true;
        } catch (Exception $e) {
            $this->error=$mail->ErrorInfo;
            return false;
        }
    }
}