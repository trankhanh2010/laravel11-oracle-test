<?php

namespace App\Services\Auth;

use App\Events\Cache\DeleteCache;
use App\Repositories\PatientRepository;
use App\Services\Mail\MailService;
use App\Services\Sms\ESmsService;
use App\Services\Sms\SpeedSmsService;
use Illuminate\Support\Facades\Cache;
// use App\Services\Sms\TwilioService;
use App\Services\Zalo\ZaloService;
use Illuminate\Support\Facades\Redis;

class OtpService
{
    protected $smsSerivce;
    protected $twilioService;
    protected $eSmsService;
    protected $speedSmsService;
    protected $mailService;
    protected $zaloSerivce;
    protected $patientRepository;
    protected $otpTTL;

    public function __construct(
        // TwilioService $twilioService,
        ESmsService $eSmsService,
        SpeedSmsService $speedSmsService,
        MailService $mailService,
        ZaloService $zaloSerivce,
        PatientRepository $patientRepository,
    ) {
        // $this->twilioService = $twilioService;
        $this->eSmsService = $eSmsService;
        $this->speedSmsService = $speedSmsService;
        $this->mailService = $mailService;
        $this->zaloSerivce = $zaloSerivce;
        $this->patientRepository = $patientRepository;

        $this->otpTTL = config('database')['connections']['otp']['otp_ttl'];
        // Chọn loại dịch vụ dùng để gửi sms
        $this->smsSerivce = $this->speedSmsService;
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

    public function createCacheToken($cacheKey, $otpCode)
    {
        // $cacheKeySet = "cache_keys:" . "token"; // Set để lưu danh sách key
        $data = Cache::put($cacheKey, $otpCode, now()->addMinutes($this->otpTTL));
        // Lưu key vào Redis Set để dễ xóa sau này
        // Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $data;
    }
    /**
     * Tạo và gửi OTP nếu chưa có trong cache
     */
    // Gửi qua phone bệnh nhân
    public function createAndSendOtpPhoneTreatmentFee($patientCode)
    {
        $phoneNumber = $this->patientRepository->getByPatientCode($patientCode)->phone ?? null;
        if (!$phoneNumber) {
            return false;
        }
        $otpCode = rand(100000, 999999);
        $cacheTTL = $this->otpTTL;
        $cacheKey = 'OTP_treatment_fee_' . $patientCode;
        if (!Cache::has($cacheKey)) {
            // Nếu có cache thì clear cache đó
            event(new DeleteCache($cacheKey));
        };

        try {
            // Test ở local khi bị hạn chế số lượng tin
            // Cache::put($cacheKey, $otpCode, $cacheTTL);
            $this->smsSerivce->sendOtp($phoneNumber, $otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheToken($cacheKey, $otpCode);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // Gửi qua mobile bệnh nhân
    public function createAndSendOtpMobileTreatmentFee($patientCode)
    {
        $phoneNumber = $this->patientRepository->getByPatientCode($patientCode)->mobile ?? null;
        if (!$phoneNumber) {
            return false;
        }
        $otpCode = rand(100000, 999999);
        $cacheTTL = $this->otpTTL;
        $cacheKey = 'OTP_treatment_fee_' . $patientCode;

        if (!Cache::has($cacheKey)) {
            // Nếu có cache thì clear cache đó
            event(new DeleteCache($cacheKey));
        };
        try {
            // Test ở local khi bị hạn chế số lượng tin
            // Cache::put($cacheKey, $otpCode, $cacheTTL);
            $this->smsSerivce->sendOtp($phoneNumber, $otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheToken($cacheKey, $otpCode);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // Gửi qua phone người thân
    public function createAndSendOtpPatientRelativePhoneTreatmentFee($patientCode)
    {
        $phoneNumber = $this->patientRepository->getByPatientCode($patientCode)->relative_phone ?? null;
        if (!$phoneNumber) {
            return false;
        }
        $otpCode = rand(100000, 999999);
        $cacheTTL = $this->otpTTL;
        $cacheKey = 'OTP_treatment_fee_' . $patientCode;

        if (!Cache::has($cacheKey)) {
            // Nếu có cache thì clear cache đó
            event(new DeleteCache($cacheKey));
        };
        try {
            // Test ở local khi bị hạn chế số lượng tin
            // Cache::put($cacheKey, $otpCode, $cacheTTL);
            $this->smsSerivce->sendOtp($phoneNumber, $otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheToken($cacheKey, $otpCode);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // Gửi qua mobile người thân
    public function createAndSendOtpPatientRelativeMobileTreatmentFee($patientCode)
    {
        $phoneNumber = $this->patientRepository->getByPatientCode($patientCode)->relative_mobile ?? null;

        if (!$phoneNumber) {
            return false;
        }
        $otpCode = rand(100000, 999999);
        $cacheTTL = $this->otpTTL;
        $cacheKey = 'OTP_treatment_fee_' . $patientCode;

        if (!Cache::has($cacheKey)) {
            // Nếu có cache thì clear cache đó
            event(new DeleteCache($cacheKey));
        };
        try {
            // Test ở local khi bị hạn chế số lượng tin
            // Cache::put($cacheKey, $otpCode, $cacheTTL);
            $this->smsSerivce->sendOtp($phoneNumber, $otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheToken($cacheKey, $otpCode);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    public function createAndSendOtpMailTreatmentFee($patientCode)
    {
        $email = $this->patientRepository->getByPatientCode($patientCode)->email ?? null;
        if (!$email) {
            return false;
        }
        $otpCode = rand(100000, 999999);
        $cacheTTL = $this->otpTTL;
        $cacheKey = 'OTP_treatment_fee_' . $patientCode;

        if (!Cache::has($cacheKey)) {
            // Nếu có cache thì clear cache đó
            event(new DeleteCache($cacheKey));
        };
        try {
            $this->mailService->sendOtp($email, $otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheToken($cacheKey, $otpCode);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // Gửi qua zalo phone bệnh nhân
    public function createAndSendOtpZaloPhoneTreatmentFee($patientCode)
    {
        $phoneNumber = $this->patientRepository->getByPatientCode($patientCode)->phone ?? null;
        if (!$phoneNumber) {
            return false;
        }
        $otpCode = rand(100000, 999999);
        $cacheTTL = $this->otpTTL;
        $cacheKey = 'OTP_treatment_fee_' . $patientCode;

        if (!Cache::has($cacheKey)) {
            // Nếu có cache thì clear cache đó
            event(new DeleteCache($cacheKey));
        };
        try {
            // Test ở local khi bị hạn chế số lượng tin
            // Cache::put($cacheKey, $otpCode, $cacheTTL);
            $this->zaloSerivce->sendOtp($phoneNumber, $otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheToken($cacheKey, $otpCode);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // Gửi qua zalo mobile bệnh nhân
    public function createAndSendOtpZaloMobileTreatmentFee($patientCode)
    {
        $phoneNumber = $this->patientRepository->getByPatientCode($patientCode)->mobile ?? null;
        if (!$phoneNumber) {
            return false;
        }
        $otpCode = rand(100000, 999999);
        $cacheTTL = $this->otpTTL;
        $cacheKey = 'OTP_treatment_fee_' . $patientCode;

        if (!Cache::has($cacheKey)) {
            // Nếu có cache thì clear cache đó
            event(new DeleteCache($cacheKey));
        };
        try {
            // Test ở local khi bị hạn chế số lượng tin
            // Cache::put($cacheKey, $otpCode, $cacheTTL);
            $this->zaloSerivce->sendOtp($phoneNumber, $otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheToken($cacheKey, $otpCode);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // Gửi qua zalo phone người thân bệnh nhân
    public function createAndSendOtpZaloPatientRelativePhoneTreatmentFee($patientCode)
    {
        $phoneNumber = $this->patientRepository->getByPatientCode($patientCode)->relative_phone ?? null;
        if (!$phoneNumber) {
            return false;
        }
        $otpCode = rand(100000, 999999);
        $cacheTTL = $this->otpTTL;
        $cacheKey = 'OTP_treatment_fee_' . $patientCode;

        if (!Cache::has($cacheKey)) {
            // Nếu có cache thì clear cache đó
            event(new DeleteCache($cacheKey));
        };
        try {
            // Test ở local khi bị hạn chế số lượng tin
            // Cache::put($cacheKey, $otpCode, $cacheTTL);
            $this->zaloSerivce->sendOtp($phoneNumber, $otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheToken($cacheKey, $otpCode);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // Gửi qua zalo mobile người thân bệnh nhân
    public function createAndSendOtpZaloPatientRelativeMobileTreatmentFee($patientCode)
    {
        $phoneNumber = $this->patientRepository->getByPatientCode($patientCode)->relative_mobile ?? null;
        if (!$phoneNumber) {
            return false;
        }
        $otpCode = rand(100000, 999999);
        $cacheTTL = $this->otpTTL;
        $cacheKey = 'OTP_treatment_fee_' . $patientCode;

        if (!Cache::has($cacheKey)) {
            // Nếu có cache thì clear cache đó
            event(new DeleteCache($cacheKey));
        };

        try {
            // Test ở local khi bị hạn chế số lượng tin
            // Cache::put($cacheKey, $otpCode, $cacheTTL);
            $this->zaloSerivce->sendOtp($phoneNumber, $otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheToken($cacheKey, $otpCode);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
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
