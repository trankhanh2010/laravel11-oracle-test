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

    /**
     * Kiểm tra xem OTP đã được xác thực chưa
     */
    public function isOtpVerified($patientCode, $deviceInfo, $ipAddress): bool
    {
        $sanitizedDeviceInfo = preg_replace('/[^a-zA-Z0-9-_]/', '', $deviceInfo);
        $cacheOtpKey = 'OTP_treatment_fee_' . $patientCode . '_' . $sanitizedDeviceInfo . '_' . $ipAddress;

        return Cache::has($cacheOtpKey);
    }

    /**
     * Tạo và gửi OTP để thanh toán viện phí
     */
    public function generateAndSendOtpTreatmentFee($phoneNumber, $patientCode, $deviceInfo, $ipAddress)
    {
        if ($this->isOtpVerified($patientCode, $deviceInfo, $ipAddress)) {
            return true; // Nếu OTP đã xác thực, không cần gửi lại
        }

        $otpCode = rand(100000, 999999);
        $cacheTTL = 120;
        $cacheKey = 'OTP_treatment_fee_' . $phoneNumber;

        // Nếu chưa có OTP trong cache thì tạo mới
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, $otpCode, $cacheTTL);
            $this->twilioService->sendOtp($phoneNumber, $otpCode);
        }

        return false;
    }
}
