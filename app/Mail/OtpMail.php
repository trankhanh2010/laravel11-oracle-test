<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $otpTTL;

    public function __construct($otp)
    {
        $this->otp = $otp;
        $this->otpTTL = config('database')['connections']['otp']['otp_ttl'];
    }

    public function build()
    {
        return $this->subject('Mã OTP của bạn')
            ->view('emails.otp')
            ->with([
                'otp' => $this->otp,
                'otp_ttl' => $this->otpTTL,
            ]);
    }
}
