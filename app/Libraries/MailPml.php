<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailPml
{
    public function send($settings)
    {
        $data = ['status' => 1, 'error' => ''];

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('email.fromEmail');
            $mail->Password = getenv('email.SMTPPass');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
            $mail->addReplyTo(getenv('email.fromEmail'), getenv('email.fromName'));
            $mail->addAddress($settings['to'], $settings['to']);

            if (isset($settings['bcc'])) {
                $mail->addBCC($settings['bcc']);
            }

            $mail->Subject = $settings['subject'];
            $mail->isHTML(true);
            $mail->Body = $settings['html_message'];

            $mail->send();
        } catch (Exception $e) {
            $data['status'] = 0;
            $data['error'] = $mail->ErrorInfo;
        }

        return $data;
    }
}
