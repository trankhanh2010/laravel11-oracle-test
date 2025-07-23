<?php

namespace App\Services\Auth;

use App\DTOs\OtpDTO;
use App\Events\Cache\DeleteCache;
use App\Models\HIS\Patient;
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
    protected $params;
    protected $smsSerivce;
    protected $twilioService;
    protected $eSmsService;
    protected $speedSmsService;
    protected $mailService;
    protected $zaloSerivce;
    protected $patientRepository;
    protected $patient;
    protected $otpTTL;
    protected $cacheVerifySuccesTTL; // Thời gian lưu trạng thái đã xác thực thành công
    protected $deviceInfo;
    protected $sanitizedDeviceInfo;
    protected $ipAddress;
    protected $dataPatient;
    protected $phone;
    protected $mobile;
    protected $email;
    protected $relativePhone;
    protected $relativeMobile;
    protected $patientCode;
    protected $otpCode;
    protected $cacheKeySaveOtp;
    protected $cacheKeyVerifyPaitent;
    protected $cacheKeyTotalRequestSendOtp;
    protected $cacheKeyTotalRequestVerifyOtp;
    public function __construct(
        // TwilioService $twilioService,
        ESmsService $eSmsService,
        SpeedSmsService $speedSmsService,
        MailService $mailService,
        ZaloService $zaloSerivce,
        PatientRepository $patientRepository,
        Patient $patient,
    ) {
        // $this->twilioService = $twilioService;
        $this->eSmsService = $eSmsService;
        $this->speedSmsService = $speedSmsService;
        $this->mailService = $mailService;
        $this->zaloSerivce = $zaloSerivce;
        $this->patientRepository = $patientRepository;
        $this->patient = $patient;

        $this->otpTTL = config('database')['connections']['otp']['otp_ttl'];
        $this->cacheVerifySuccesTTL = 14400; // 4 tiếng

        $this->deviceInfo = request()->header('User-Agent'); // Lấy thông tin thiết bị từ User-Agent
        $this->sanitizedDeviceInfo = preg_replace('/[^a-zA-Z0-9-_]/', '', $this->deviceInfo); // bỏ cách ký tự đặc biệt
        $this->ipAddress = request()->ip(); // Lấy địa chỉ IP

        // Chọn loại dịch vụ dùng để gửi sms
        $this->smsSerivce = $this->speedSmsService;
    }
    public function withParams(OtpDTO $params)
    {
        $this->params = $params;
        $this->patientCode = $this->params->patientCode;
        $this->dataPatient = $this->getDataPatient($this->patientCode); // Lấy data patient
        $this->phone = $this->dataPatient->phone;
        $this->mobile = $this->dataPatient->mobile;
        $this->email = $this->dataPatient->email;
        $this->relativePhone = $this->dataPatient->relative_phone;
        $this->relativeMobile = $this->dataPatient->relative_mobile;
        $this->otpCode = $this->getRandomNumberOtp(); // Lấy random 
        $this->cacheKeySaveOtp = $this->getCacheKeySaveOtp(); // Lấy key cache sẽ lưu mã otp
        $this->cacheKeyVerifyPaitent = $this->getCacheKeyVerifyPaitent(); // lấy key cache sẽ lưu trạng thái đã xác thực
        $this->cacheKeyTotalRequestSendOtp = $this->getCacheKeyTotalRequestSendOtp(); // lấy key cache sẽ lưu số lần gọi OTP
        $this->cacheKeyTotalRequestVerifyOtp = $this->getCacheKeyTotalRequestVerifyOtp(); // lấy key cache sẽ lưu số lần đã xác thực mã OTP
        return $this;
    }
    public function getDataPatient($patientCode)
    {
        $dataPatient = $this->patient->where('patient_code', $patientCode)->first();
        return $dataPatient;
    }
    // Trả về key lưu mã otp của patient
    public function getCacheKeySaveOtp()
    {
        $key = 'otp_phone_' . $this->phone;
        return $key;
    }
    // Trả về key lưu trạng thái đã xác thực của thiết bị với patient này
    public function getCacheKeyVerifyPaitent()
    {
        $key = 'OTP_verify_' . $this->phone . '_' . $this->sanitizedDeviceInfo . '_' . $this->ipAddress; // thay patientCode = phone
        return $key;
    }
    // Trả về key lưu số lần gọi lấy OTP của thiết bị 
    public function getCacheKeyTotalRequestSendOtp()
    {
        $key = 'total_request_send_otp_' . $this->deviceInfo . '_' . $this->ipAddress; // Tránh key quá dài
        return $key;
    }
    // Trả về key lưu số lần xác thực OTP của thiết bị 
    public function getCacheKeyTotalRequestVerifyOtp()
    {
        $key = 'total_request_verify_otp_' . $this->patientCode; // Tránh key quá dài
        return $key;
    }
    // set lại giá trị mã OTP về null
    public function setNullCacheOtp()
    {
        Cache::put($this->cacheKeySaveOtp, false, $this->otpTTL);
    }
    // Lấy mã OTP từ cache
    public function getDataCacheOtp()
    {
        $otp = Cache::get($this->cacheKeySaveOtp);
        return $otp;
    }
    // Tạo cache lưu trạng thái đã xác thực cho thiết bị với patient này
    public function createCacheVerifySuccess()
    {
        Cache::put($this->cacheKeyVerifyPaitent, 1, $this->cacheVerifySuccesTTL);
    }
    // Lấy data các lần gọi nhận otp của thiết bị
    public function getDataTotalRequestSendOtp()
    {
        $otp = Cache::get($this->cacheKeyTotalRequestSendOtp);
        return $otp;
    }
    // Tạo cache lưu các lần gọi nhận OTP
    public function createCacheTotalRequestSendOtp($dataTotalRequestSendOtp)
    {
        // **Lưu lại vào cache với TTL 
        $cacheKeySet = "cache_keys:" . "device_get_otp"; // Set để lưu danh sách key
        Cache::put($this->cacheKeyTotalRequestSendOtp, $dataTotalRequestSendOtp, now()->addDay());
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$this->cacheKeyTotalRequestSendOtp]);
    }
    public function getTotalRequestSendOtp()
    {
        // Kiểm tra cache hiện tại
        $dataTotalRequestSendOtp = $this->getDataTotalRequestSendOtp();
        // Nếu cache chưa tồn tại, khởi tạo dữ liệu
        if (!$dataTotalRequestSendOtp) {
            $dataTotalRequestSendOtp = [
                'device' => $this->deviceInfo,
                'ip' => $this->ipAddress,
                'total_requests' => 0,  // Số lần gửi OTP
                'first_request_at' => now()->toDateTimeString(), // Thời gian gọi lần đầu
                'last_request_at' => null, // Chưa có lần cuối
                'patient_code_list' => [$this->patientCode => 1] // Thêm patientCode đầu tiên
            ];
        }
        $this->createCacheTotalRequestSendOtp($dataTotalRequestSendOtp);
        return $dataTotalRequestSendOtp['total_requests'];
    }
    public function getDataTotalRequestVerifyOtp()
    {
        $total = Cache::get($this->cacheKeyTotalRequestVerifyOtp) ?? 0;
        return $total;
    }
    // set lại giá trị số lần gọi xác thực OTP
    public function setTotalRequestVerifyOtp($total)
    {
        Cache::put($this->cacheKeyTotalRequestVerifyOtp, $total, now()->addHour());
    }
    // set lại giá trị số lần gọi nhận mã OTP
    public function setTotalRequestSendOtp($total)
    {
        Cache::put($this->cacheKeyTotalRequestSendOtp, $total, 5);
    }
    // Khởi tạo hoặc trả về số lần gọi xác thực OTP
    public function getOrCreateTotalRequestVerifyOtp()
    {
        // Kiểm tra xem cache đã tồn tại chưa
        if (!Cache::has($this->cacheKeyTotalRequestVerifyOtp)) {
            // Nếu chưa có, đặt giá trị là 1 
            $this->setTotalRequestVerifyOtp(1);
        } else {
            // Nếu đã có, tăng giá trị lên 1
            Cache::increment($this->cacheKeyTotalRequestVerifyOtp);
        }
        // Trả về số lần gửi OTP của thiết bị này trong thời gian
        return Cache::get($this->cacheKeyTotalRequestVerifyOtp);
    }
    // Thêm 1 vào số lần yêu cầu nhận mã OTP
    public function addTotalRequestSendOtp()
    {
        // Kiểm tra cache hiện tại
        $dataTotalRequestSendOtp = $this->getDataTotalRequestSendOtp();

        // Nếu cache chưa tồn tại, khởi tạo dữ liệu
        if (!$dataTotalRequestSendOtp) {
            $dataTotalRequestSendOtp = [
                'device' => $this->deviceInfo,
                'ip' => $this->ipAddress,
                'total_requests' => 1,  // Lần gửi đầu tiên
                'first_request_at' => now()->toDateTimeString(), // Thời gian gửi lần đầu
                'last_request_at' => now()->toDateTimeString(), // Cập nhật lần gửi cuối
                'patient_code_list' => [$this->patientCode => 1] // Thêm patientCode đầu tiên
            ];
        } else {
            // Nếu đã tồn tại, tăng số lần gửi OTP
            $dataTotalRequestSendOtp['total_requests'] += 1;
            $dataTotalRequestSendOtp['last_request_at'] = now()->toDateTimeString(); // Cập nhật lần cuối gửi OTP
            // Kiểm tra patientCode đã có chưa
            if (isset($dataTotalRequestSendOtp['patient_code_list'][$this->patientCode])) {
                $dataTotalRequestSendOtp['patient_code_list'][$this->patientCode] += 1; // Tăng số lần gửi cho patientCode
            } else {
                $dataTotalRequestSendOtp['patient_code_list'][$this->patientCode] = 1; // Thêm patientCode mới
            }
        }
        // Lưu lại vào cache 
        $this->createCacheTotalRequestSendOtp($dataTotalRequestSendOtp);

        return $dataTotalRequestSendOtp['total_requests'];
    }
    /**
     * Kiểm tra xem OTP đã được xác thực chưa
     */
    public function isVerified(): bool
    {
        if (!$this->phone) {
            return false;
        }
        // Trả về xem có cache verify cho patient không
        return Cache::has($this->getCacheKeyVerifyPaitent());
    }
    // Lấy số ngẫu nhiên
    public function getRandomNumberOtp()
    {
        $otpCode = rand(100000, 999999);
        return $otpCode;
    }

    public function createCacheSaveOtp()
    {
        // $cacheKeySet = "cache_keys:" . "token"; // Set để lưu danh sách key
        $data = Cache::put($this->cacheKeySaveOtp, $this->otpCode, now()->addMinutes($this->otpTTL));
        // Lưu key vào Redis Set để dễ xóa sau này
        // Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $data;
    }
    public function clearCacheSaveOtp()
    {
        if (Cache::has($this->cacheKeySaveOtp)) {
            // Nếu có cache thì set nó về rỗng
            $this->setNullCacheOtp();
        };
    }
    public function clearCacheTotalRequestVerifyOtp()
    {
        if (Cache::has($this->cacheKeyTotalRequestVerifyOtp)) {
            // Nếu có cache thì đặt nó về 0
            $this->setTotalRequestVerifyOtp(0);
        };
    }
    public function clearCacheTotalRequestSendOtp()
    {
        if (Cache::has($this->cacheKeyTotalRequestSendOtp)) {
            // Nếu có cache thì đặt nó về 0
            $this->setTotalRequestSendOtp(0);
        };
    }
    /**
     * Tạo và gửi OTP nếu chưa có trong cache
     */
    // Gửi qua phone bệnh nhân
    public function createAndSendOtpPhoneTreatmentFee()
    {
        if (!$this->phone) {
            return false;
        }
        // clear OTP cũ trước khi gửi OTP mới
        $this->clearCacheSaveOtp();

        try {
            $this->smsSerivce->sendOtp($this->phone, $this->otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheSaveOtp();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // Gửi qua mobile bệnh nhân
    public function createAndSendOtpMobileTreatmentFee()
    {
        if (!$this->mobile) {
            return false;
        }
        // clear OTP cũ trước khi gửi OTP mới
        $this->clearCacheSaveOtp();

        try {
            $this->smsSerivce->sendOtp($this->mobile, $this->otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheSaveOtp();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // Gửi qua phone người thân
    public function createAndSendOtpPatientRelativePhoneTreatmentFee()
    {
        if (!$this->relativePhone) {
            return false;
        }
        // clear OTP cũ trước khi gửi OTP mới
        $this->clearCacheSaveOtp();

        try {
            $this->smsSerivce->sendOtp($this->relativePhone, $this->otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheSaveOtp();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // Gửi qua mobile người thân
    public function createAndSendOtpPatientRelativeMobileTreatmentFee()
    {
        if (!$this->relativeMobile) {
            return false;
        }
        // clear OTP cũ trước khi gửi OTP mới
        $this->clearCacheSaveOtp();

        try {
            $this->smsSerivce->sendOtp($this->relativeMobile, $this->otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheSaveOtp();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    public function createAndSendOtpMailTreatmentFee()
    {
        if (!$this->email) {
            return false;
        }
        // clear OTP cũ trước khi gửi OTP mới
        $this->clearCacheSaveOtp();

        try {
            $this->mailService->sendOtp($this->email, $this->otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheSaveOtp();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // Gửi qua zalo phone bệnh nhân
    public function createAndSendOtpZaloPhoneTreatmentFee()
    {
        if (!$this->phone) {
            return false;
        }
        // clear OTP cũ trước khi gửi OTP mới
        $this->clearCacheSaveOtp();

        try {
            $this->zaloSerivce->sendOtp($this->phone, $this->otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheSaveOtp();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // Gửi qua zalo mobile bệnh nhân
    public function createAndSendOtpZaloMobileTreatmentFee()
    {
        if (!$this->mobile) {
            return false;
        }
        // clear OTP cũ trước khi gửi OTP mới
        $this->clearCacheSaveOtp();

        try {
            $this->zaloSerivce->sendOtp($this->mobile, $this->otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheSaveOtp();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // Gửi qua zalo phone người thân bệnh nhân
    public function createAndSendOtpZaloPatientRelativePhoneTreatmentFee()
    {
        if (!$this->relativePhone) {
            return false;
        }
        // clear OTP cũ trước khi gửi OTP mới
        $this->clearCacheSaveOtp();

        try {
            $this->zaloSerivce->sendOtp($this->relativePhone, $this->otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheSaveOtp();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // Gửi qua zalo mobile người thân bệnh nhân
    public function createAndSendOtpZaloPatientRelativeMobileTreatmentFee()
    {
        if (!$this->relativeMobile) {
            return false;
        }
        // clear OTP cũ trước khi gửi OTP mới
        $this->clearCacheSaveOtp();

        try {
            $this->zaloSerivce->sendOtp($this->relativeMobile, $this->otpCode);
            // Gửi thành công thì mới tạo cache
            $this->createCacheSaveOtp();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
    /**
     * Tạo và gửi OTP
     */
    public function generateAndSendOtpTreatmentFee($patientCode, $deviceInfo, $ipAddress)
    {

        if ($this->isVerified()) {
            return true; // Nếu OTP đã xác thực, không cần gửi lại
        }

        // Nếu chưa có OTP trong cache thì tạo mới
        // Gọi hàm tạo và gửi OTP
        $this->createAndSendOtpPhoneTreatmentFee();

        return false;
    }
}
