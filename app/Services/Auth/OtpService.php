<?php
namespace App\Services\Auth;

use App\Repositories\PatientRepository;
use App\Services\Mail\MailService;
use Illuminate\Support\Facades\Cache;
use App\Services\Sms\TwilioService;

class OtpService
{
    protected $twilioService;
    protected $mailService;
    protected $patientRepository;

    public function __construct(
        TwilioService $twilioService,
        MailService $mailService,
        PatientRepository $patientRepository,
        )
    {
        $this->twilioService = $twilioService;
        $this->mailService = $mailService;
        $this->patientRepository = $patientRepository;
    }

    /**
     * Kiểm tra xem OTP đã được xác thực chưa
     */
    public function isOtpTreatmentFeeVerified($patientCode, $deviceInfo, $ipAddress): bool
    {
        $sanitizedDeviceInfo = preg_replace('/[^a-zA-Z0-9-_]/', '', $deviceInfo);
        $cacheOtpKey = 'OTP_treatment_fee_' . $patientCode . '_' . $sanitizedDeviceInfo . '_' . $ipAddress;

        return Cache::has($cacheOtpKey);
    }

        /**
     * Tạo và gửi OTP nếu chưa có trong cache
     */
    public function createAndSendOtpPhoneTreatmentFee($patientCode)
    {
        $phoneNumber = $this->patientRepository->getByPatientCode($patientCode)->phone ?? null;
        if(!$phoneNumber){
            return false;
        }
        $otpCode = rand(100000, 999999);
        $cacheTTL = 120;
        $cacheKey = 'OTP_treatment_fee_' . $patientCode;

        if (!Cache::has($cacheKey)) {
            try {
                // Test ở local khi bị hạn chế số lượng tin
                // Cache::put($cacheKey, $otpCode, $cacheTTL);
                $this->twilioService->sendOtp($phoneNumber, $otpCode);
            }catch (\Throwable $e){
                return false;
            }
            // Gửi thành công thì mới tạo cache
            Cache::put($cacheKey, $otpCode, $cacheTTL);
        }else return false;
        return true;
    }    
    public function createAndSendOtpMailTreatmentFee($patientCode)
    {
        $email = $this->patientRepository->getByPatientCode($patientCode)->email ?? null;
        if(!$email){
            return false;
        }
        $otpCode = rand(100000, 999999);
        $cacheTTL = 120;
        $cacheKey = 'OTP_treatment_fee_' . $patientCode;

        if (!Cache::has($cacheKey)) {
            try {
                $this->mailService->sendOtp($email, $otpCode);
            }catch (\Throwable $e){
                return false;
            }
            // Gửi thành công thì mới tạo cache
            Cache::put($cacheKey, $otpCode, $cacheTTL);
        }else return false;
        return true;
    }  
    /**
     * Tạo và gửi OTP để thanh toán viện phí
     */
    public function generateAndSendOtpTreatmentFee($patientCode, $deviceInfo, $ipAddress)
    {

        if ($this->isOtpTreatmentFeeVerified($patientCode, $deviceInfo, $ipAddress)) {
            return true; // Nếu OTP đã xác thực, không cần gửi lại
        }

        // Nếu chưa có OTP trong cache thì tạo mới
        // Gọi hàm tạo và gửi OTP
        $this->createAndSendOtpPhoneTreatmentFee($patientCode);

        return false;
    }
}
