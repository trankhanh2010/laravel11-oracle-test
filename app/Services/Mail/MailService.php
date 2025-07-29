<?php

namespace App\Services\Mail;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Mail\DangKyKhamThanhCongMail;
use App\Mail\SendInvalidRefreshTokenTokenZaloNotification;
use App\Mail\SentAccessTokenRefreshTokenZalo;

class MailService
{

    public function sendOtp($email, $otpCode)
    {
        $message = new OtpMail($otpCode);
        Mail::to($email)->send($message);
        return true;
    }
    public function sendThongBaoDangKyKhamThanhCong($email, $responeMos)
    {
        $message = new DangKyKhamThanhCongMail($responeMos);
        Mail::to($email)->send($message);
        return true;
    }
    public function sendTokenZalo($AT, $RT)
    {
        $email = 'tranlenguyenkhanh20102001@gmail.com';
        $message = new SentAccessTokenRefreshTokenZalo($AT, $RT);
        Mail::to($email)->send($message);
        return true;
    }
    public function sendInvalidRefreshTokenTokenZaloNotification($AT, $RT)
    {
        $email = 'tranlenguyenkhanh20102001@gmail.com';
        $message = new SendInvalidRefreshTokenTokenZaloNotification($AT, $RT);
        Mail::to($email)->send($message);
        return true;
    }
}
