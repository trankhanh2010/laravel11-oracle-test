<?php

namespace App\Services\Mail;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class MailService
{

    public function sendOtp($email, $otpCode)
    {
        $message = new OtpMail($otpCode);
        Mail::to($email)->send($message);
        return true;
    }

}
