<?php
namespace App\Services\Auth;

use Illuminate\Support\Facades\Cache;
use App\Services\Sms\TwilioService;

class OtpService
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    public function generateAndSendOtpTreatmentFee($phoneNumber, $patientCode, $deviceInfo, $ipAddress)
    {
        $otpCode = rand(100000, 999999);
        $cacheTTL = 120;
        $cacheKey = 'OTP_treatment_fee_' . $phoneNumber;
        $sanitizedDeviceInfo = preg_replace('/[^a-zA-Z0-9-_]/', '', $deviceInfo);
        $cacheOtpKey = $cacheKey . '_' . $patientCode . '_' . $sanitizedDeviceInfo . '_' . $ipAddress;

        // Kiểm tra xem OTP đã được xác thực chưa
        if (!Cache::has($cacheOtpKey)) {
            // Kiểm tra nếu OTP chưa được tạo trong cache
            if (!Cache::has($cacheKey)) {
                // Lưu OTP vào cache và gửi qua Twilio
                Cache::put($cacheKey, $otpCode, $cacheTTL);
                $this->twilioService->sendOtp($phoneNumber, $otpCode);
            }
        } else {
            // Nếu OTP đã được xác thực
            return true;
        }

        return false;
    }
}
