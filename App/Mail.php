<?php

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Mail
 *
 * PHP version 7.0
 */
class Mail
{

    /**
     * Send a message
     *
     * @param string $to Recipient
     * @param string $subject Subject
     * @param string $text Text-only content of the message
     * @param string $html HTML content of the message
     *
     * @return mixed
     */
    public static function send($to, $subject, $text, $html)
    {
        $mail = new PHPMailer(true);
        //Send mail using gmail

        //$mail->SMTPDebug = 3;
        $mail->isSMTP(); // telling the class to use SMTP
        $mail->SMTPAuth = true; // enable SMTP authentication
        $mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server
        $mail->SMTPSecure = "ssl"; // sets the prefix to the servicer
        $mail->Port = 465; // set the SMTP port for the GMAIL server
        //$mail->SMTPSecure = "tls";
        //$mail->Port = 587;
        $mail->Username = Config::EMAIL_ADDR; // GMAIL username
        $mail->Password = Config::EMAIL_PWD; // GMAIL password
        $mail->isHtml(true);

        //Typical mail data
        $mail->AddAddress($to);
        $mail->SetFrom(Config::EMAIL_ADDR, 'SantaParavia.com');
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = $text;

        try {
            $mail->Send();
            //$mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str";};
        } catch (Exception $e) {
            //Something went bad
            echo "Fail - " . $mail->ErrorInfo;
        }

//        $mg = new Mailgun(Config::MAILGUN_API_KEY);
//        $domain = Config::MAILGUN_DOMAIN;
//
//        $mg->sendMessage($domain, ['from'    => 'your-sender@your-domain.com',
//                                   'to'      => $to,
//                                   'subject' => $subject,
//                                   'text'    => $text,
//                                   'html'    => $html]);
    }
}
