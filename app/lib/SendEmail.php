<?php


namespace app\lib;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
class SendEmail
{
    public $error;
    public function send($config,$tomail,$name,$subject = '',$body = '',$attachment = null){
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                            // 设定使用SMTP服务
            $mail->Host       = $config['EMAIL_HOST'];                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = $config['EMAIL_USER'];              // SMTP username
            $mail->Password   = $config['EMAIL_PASSWORD'];                     // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = $config['EMAIL_PORT'];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            //recipients 收件人
            $mail->setFrom($config['EMAIL_USER'], 'Mailer');

            $mail->addAddress($tomail, $name);     // Add a recipient// Name is optional
            $mail->addReplyTo($config['EMAIL_USER'], 'susan');
            // Content 内容
            $mail->isHTML(true);

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