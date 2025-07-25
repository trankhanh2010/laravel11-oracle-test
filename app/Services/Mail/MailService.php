<?php

namespace App\Services\Mail;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Mail\DangKyKhamThanhCongMail;

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
}
