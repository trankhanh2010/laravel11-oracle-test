<?php

namespace App\Services\Sms;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpeedSmsService
{
    protected $apiKey;
    protected $sender;
    protected $otpTTL;

    public function __construct()
    {
        $this->apiKey = config('database.connections.speed_sms.api_key');
        $this->sender = config('database.connections.speed_sms.sender');
        $this->otpTTL = config('database.connections.otp.otp_ttl');
    }

    public function sendOtp($phoneNumber, $otpCode)
    {
        $message = "Ma OTP cua ban: $otpCode. Hieu luc trong " . $this->otpTTL . " phut.";

        $content = [
            'to' => [$phoneNumber], 
            'content' => $message,
            'sms_type' => 6, 
            'sender' => $this->sender, 
        ];

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':x'),
        ])->post('https://api.speedsms.vn/index.php/sms/send', $content);

        $data = $response->json();

        if (!isset($data['status']) || $data['status'] != 'success') {
            Log::error("SpeedSMS gửi OTP thất bại", $data);
            throw new Exception("Gửi tin nhắn SpeedSMS thất bại: " . json_encode($data));
        }

        return true;
    }
}
