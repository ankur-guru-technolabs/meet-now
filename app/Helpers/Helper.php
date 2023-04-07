<?php
  
namespace App\Helpers;
use Mail;
use App\Mail\EmailVerificationMail;

class Helper {

    /**
     * Write code on Method
     *
     * @return response()
     */
    public static function sendMail($view = '', $data = [], $to = '', $from = '', $attechMent = '')
    {
        if(empty($view) || empty($to)) {
            return false;
        }

        $subject = isset($data['subject']) ? $data['subject'] : '';
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <info@meet-now.com>' . "\r\n";

        // For sending otp to mail
        
        if(isset($data['otp'])){
            $otp = $data['otp'] ;
            Mail::to($data['email'])->send(new EmailVerificationMail($otp));
        }
        
        return true;

    }
}