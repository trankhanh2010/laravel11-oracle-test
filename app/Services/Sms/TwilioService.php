<?php

namespace App\Services\Sms;

use Twilio\Rest\Client;

class TwilioService
{
    protected $twilio;
    protected $otpTTL;
    public function __construct()
    {
        $this->twilio = new Client(
            config('database')['connections']['twilio']['sid'],
            config('database')['connections']['twilio']['auth_token'],
        );
        $this->otpTTL = config('database')['connections']['otp']['otp_ttl'];

    }

    public function sendOtp($phoneNumber, $otpCode)
    {
        $message = "Ma OTP cua ban: $otpCode. Hieu luc trong ".$this->otpTTL." phut.";

        $this->twilio->messages->create(
            $phoneNumber, // Số điện thoại nhận
            [
                'from' => config('database')['connections']['twilio']['phone_number'],// Số Twilio
                'body' => $message, // Nội dung tin nhắn
            ]
        );

        return true;
    }
}
