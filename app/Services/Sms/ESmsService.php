<?php

namespace App\Services\Sms;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ESmsService
{
    protected $apiKey;
    protected $secretKey;
    protected $otpTTL;
    protected $brandName;

    public function __construct()
    {
        $this->apiKey = config('database')['connections']['e_sms']['api_key'];
        $this->secretKey = config('database')['connections']['e_sms']['secret_key'];
        $this->brandName = config('database')['connections']['e_sms']['brand_name'];

        $this->otpTTL = config('database.connections.otp.otp_ttl');

    }

    public function sendOtp($phoneNumber, $otpCode)
    {
        $message = "Ma OTP cua ban: $otpCode. Hieu luc trong " . $this->otpTTL . " phut.";

        $url = 'https://rest.esms.vn/MainService.svc/json/SendMultipleMessage_V4_get?' . http_build_query([
            'ApiKey'    => $this->apiKey,
            'SecretKey' => $this->secretKey,

            // gửi thật
            // 'Phone' => $phoneNumber,
            // 'Content' => $message,
            // 'SmsType' => 8, 
            // 'Brandname' => $this->brandName,

            // test
            'Phone'     => "0388473169",
            'Content'   => $otpCode . " la ma xac minh dang ky Baotrixemay cua ban",
            'SmsType'   => 8, 
            'Brandname' => "Baotrixemay",
            'Sandbox'   => 1, // Test mode
        ]);

        // Gửi GET request
        $response = Http::get($url);
        // Kiểm tra phản hồi từ eSMS
        $data = $response->json();
        if ($data['CodeResult'] !== 100) {
            Log::error($data);
            throw new Exception("Gửi tin nhắn thất bại: " . json_encode($data));
        }

        return $data;


    }
}
