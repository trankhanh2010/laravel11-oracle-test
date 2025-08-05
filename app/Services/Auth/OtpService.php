<?php

namespace App\Services\Auth;

use App\DTOs\OtpDTO;
use App\DTOs\PatientDTO;
use App\Events\Cache\DeleteCache;
use App\Models\HIS\Patient;
use App\Repositories\PatientRepository;
use App\Services\Mail\MailService;
use App\Services\Model\PatientService;
use App\Services\Sms\ESmsService;
use App\Services\Sms\SpeedSmsService;
use Illuminate\Support\Facades\Cache;
// use App\Services\Sms\TwilioService;
use App\Services\Zalo\ZaloService;
use Exception;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;

class OtpService
{
    protected $urlOtpService;
    protected $registerPhone;
    protected $params;
    protected $smsSerivce;
    protected $twilioService;
    protected $eSmsService;
    protected $speedSmsService;
    protected $mailService;
    protected $zaloSerivce;
    protected $patientService;
    protected $patientDTO;
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
    protected $patientName;
    protected $patientCode;
    protected $phoneTimKiemThongTin;
    protected $otpCode;
    public function __construct(
        // TwilioService $twilioService,
        ESmsService $eSmsService,
        SpeedSmsService $speedSmsService,
        MailService $mailService,
        ZaloService $zaloSerivce,
        PatientService $patientService,
        PatientRepository $patientRepository,
        Patient $patient,
    ) {
        $this->urlOtpService = config('database')['connections']['otp_service']['url'];
        // $this->twilioService = $twilioService;
        $this->eSmsService = $eSmsService;
        $this->speedSmsService = $speedSmsService;
        $this->mailService = $mailService;
        $this->zaloSerivce = $zaloSerivce;
        $this->patientService = $patientService;
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
        $this->setParamsPatient();
        $this->otpCode = $this->getRandomNumberOtp(); // Lấy random 
        return $this;
    }
    public function setParamsPatient()
    {
        if (!empty($this->patientCode)) {
            // lúc xác thực khi tìm dữ liệu cũ
            $this->dataPatient = $this->getDataPatient($this->patientCode); // Lấy data patient
            $this->validatePatientCode();
            $this->phone = convertPhoneTo84Format($this->dataPatient->phone ?? null); // chuyển về dạng 84 để xử lý
            $this->mobile = convertPhoneTo84Format($this->dataPatient->mobile ?? null); // chuyển về dạng 84 để xử lý
            $this->email = $this->dataPatient->email ?? null;
            $this->relativePhone = convertPhoneTo84Format($this->dataPatient->relative_phone ?? null); // chuyển về dạng 84 để xử lý 
            $this->relativeMobile = convertPhoneTo84Format($this->dataPatient->relative_mobile ?? null); // chuyển về dạng 84 để xử lý
            $this->patientName = $this->dataPatient->vir_patient_name ?? '';
        } else {
            // lúc đăng ký mới
            $this->registerPhone = convertPhoneTo84Format($this->params->registerPhone);
            // lúc tìm kiếm theo sđt/cccd
            if (empty($this->registerPhone)) {
                $this->getPhoneTimKiemThongTin();
            }
        }
    }
    public function validatePatientCode()
    {
        if (empty($this->dataPatient)) {
            throw new \Exception('Không tìm thấy thông tin bệnh nhân.');
        }
    }
    public function getPhoneTimKiemThongTin()
    {
        $this->phoneTimKiemThongTin = null;
        if (empty(request()->input('paramTimKiemThongTin'))) {
            return;
        } // không có thông tin tìm kiếm => ngắt luôn

        $paramTimKiemThongTin = $this->getParamTimKiemThongTin();
        if (empty($paramTimKiemThongTin['phone']) && empty($paramTimKiemThongTin['cccdNumber'])) {
            return;
        }

        $this->patientDTO = new PatientDTO(
            'patient',
            '',
            '',
            '',
            '',
            '',
            '',
            true,
            0,
            20,
            '',
            '',
            '',
            '',
            request()->input('paramTimKiemThongTin'),
            '',
            $paramTimKiemThongTin['phone'],
            $paramTimKiemThongTin['cccdNumber'],
            '',
        );
        $this->patientService->withParams($this->patientDTO);
        $dataTimKiemThongTinList = $this->patientService->handleDataBaseGetAllTimThongTinBenhNhan();
        $dataTimKiemThongTin = $dataTimKiemThongTinList['data'];
        if (empty($dataTimKiemThongTin)) {
            return;
        } // list trống => ngắt
        $data = $dataTimKiemThongTin[0] ?? [];
        $phone = convertPhoneTo84Format($data->phone ?? null);
        if (empty($phone)) {
            return;
        }
        $this->phoneTimKiemThongTin = $phone;
    }
    public function getParamTimKiemThongTin()
    {
        $data = [
            'phone' => null,
            'cccdNumber' => null,
        ];
        // Thay thế dấu + và / nếu bị thay đổi thành khoảng trắng hoặc các ký tự khác
        $encodedParam  = str_replace([' ', '+', '/'], ['+', '+', '/'], request()->input('paramTimKiemThongTin'));
        // Lọc bỏ các đoạn comment kiểu /* ... */
        $cleanBase64Decode = preg_replace('#/\*.*?\*/#s', '', base64_decode($encodedParam));

        $paramRequest = json_decode($cleanBase64Decode, true) ?? null;
        if (empty($paramRequest)) {
            return $data;
        }

        $phone = $paramRequest['ApiData']['Phone'] ?? null;
        $cccdNumber = $paramRequest['ApiData']['CccdNumber'] ?? null;
        if ($phone != null) {
            if (!is_string($phone) || mb_strlen($phone) > 20) {
                throw new Exception("SĐT không hợp lệ");
            }
        }
        if ($cccdNumber != null) {
            if (!is_string($cccdNumber) || !preg_match('/^\d{12}$/', $cccdNumber)) {
                throw new Exception("Số CCCD không hợp lệ");
            }
        }
        return [
            'phone' => $phone,
            'cccdNumber' => $cccdNumber,
        ];
    }
    public function getDataPatient($patientCode)
    {
        $dataPatient = $this->patient->where('patient_code', $patientCode)->first();
        return $dataPatient;
    }
    // Lấy số ngẫu nhiên
    public function getRandomNumberOtp()
    {
        $otpCode = rand(100000, 999999);
        return $otpCode;
    }
    /**
     * Kiểm tra xem OTP đã được xác thực chưa
     */
    public function isVerified(): bool
    {
        $phone = $this->phone ?? $this->registerPhone ?? $this->phoneTimKiemThongTin;
        // Trả về xem có cache verify cho patient không
        return $this->callApiCheckStatusVerifyOtp($phone);
    }
    /**
     * Tạo và gửi OTP nếu chưa có trong cache
     */
    // Gửi qua phone bệnh nhân
    public function createAndSendOtpPhoneTreatmentFee()
    {
        return $this->callApiSendOtp($this->phone, null, 'sms');
    }
    // Gửi qua mobile bệnh nhân
    public function createAndSendOtpMobileTreatmentFee()
    {
        return $this->callApiSendOtp($this->mobile, null, 'sms');
    }
    // Gửi qua phone người thân
    public function createAndSendOtpPatientRelativePhoneTreatmentFee()
    {
        return $this->callApiSendOtp($this->relativePhone, null, 'sms');
    }
    // Gửi qua mobile người thân
    public function createAndSendOtpPatientRelativeMobileTreatmentFee()
    {
        return $this->callApiSendOtp($this->relativeMobile, null, 'sms');
    }
    public function createAndSendOtpMailTreatmentFee()
    {
        return $this->callApiSendOtp($this->phone, $this->email, 'mail');
    }

    // Gửi qua zalo phone bệnh nhân
    public function createAndSendOtpZaloPhoneTreatmentFee()
    {
        return $this->callApiSendOtp($this->phone, 'ndai6618@gmail.com', 'mail');
        return $this->callApiSendOtp($this->phone, null, 'zalo');
    }
    // Gửi qua zalo mobile bệnh nhân
    public function createAndSendOtpZaloMobileTreatmentFee()
    {
        return $this->callApiSendOtp($this->mobile, null, 'zalo');
    }
    // Gửi qua zalo phone người thân bệnh nhân
    public function createAndSendOtpZaloPatientRelativePhoneTreatmentFee()
    {
        return $this->callApiSendOtp($this->relativePhone, null, 'zalo');
    }
    // Gửi qua zalo mobile người thân bệnh nhân
    public function createAndSendOtpZaloPatientRelativeMobileTreatmentFee()
    {
        return $this->callApiSendOtp($this->relativeMobile, null, 'zalo');    
    }
    // Gửi qua zalo số điện thoại đăng ký mới
    public function createAndSendOtpZaloRegisterPhone()
    {
        return $this->callApiSendOtp($this->registerPhone, 'ndai6618@gmail.com', 'mail');
        return $this->callApiSendOtp($this->registerPhone, null, 'zalo');
    }
    // Gửi qua zalo số điện thoại đầu tiên trong khi tìm kiếm thông tin
    public function createAndSendOtpZaloPhoneTimKiemBenhNhan()
    {
        return $this->callApiSendOtp($this->phoneTimKiemThongTin, 'ndai6618@gmail.com', 'mail');
        return $this->callApiSendOtp($this->phoneTimKiemThongTin, null, 'zalo');
    }
    /**
     * Gọi gửi OTP bằng backend otp-service
     */
    public function callApiSendOtp($phone, $email, $method)
    {
        $data = [
            "CommonParam" => [], 
            "ApiData" => [
                "Method" => $method,
                "Phone" => $phone,
                "Email" => $email,
                "SanitizedDeviceInfo" => $this->sanitizedDeviceInfo,
                "IpAddress" => $this->ipAddress, 
            ],
        ];

        // Convert to JSON then base64 encode
        $jsonString = json_encode($data, JSON_UNESCAPED_UNICODE);
        $paramBase64 = base64_encode($jsonString);

        // Gọi API OTP service
        $url = $this->urlOtpService."/api/v1/send-otp?param=" . urlencode($paramBase64);

        try {
            $response = Http::timeout(25)->get($url);
            if (!$response->successful()) {
                throw new Exception("Không thể call api otp-service!");
            }
            return $response->json();
        } catch (Exception $e) {
            return [
                'success' => false,
            ];
        }
    }
    public function callApiVerifyOtp($inputOtp)
    {
        $data = [
            "CommonParam" => [], 
            "ApiData" => [
                "InputOtp" => $inputOtp,
                "Phone" => $this->phone ?? $this->registerPhone ?? $this->phoneTimKiemThongTin,
                "SanitizedDeviceInfo" => $this->sanitizedDeviceInfo,
                "IpAddress" => $this->ipAddress, 
            ],
        ];

        // Convert to JSON then base64 encode
        $jsonString = json_encode($data, JSON_UNESCAPED_UNICODE);
        $paramBase64 = base64_encode($jsonString);

        // Gọi API OTP service
        $url = $this->urlOtpService."/api/v1/verify-otp?param=" . urlencode($paramBase64);

        try {
            $response = Http::timeout(25)->get($url);
            if (!$response->successful()) {
                throw new Exception("Không thể call api otp-service!");
            }
            return $response->json();
        } catch (Exception $e) {
            return [
                'success' => false,
            ];
        }
    }
    public function callApiCheckStatusVerifyOtp($phone)
    {
        $data = [
            "CommonParam" => [], 
            "ApiData" => [
                "Phone" => $phone,
                "SanitizedDeviceInfo" => $this->sanitizedDeviceInfo,
                "IpAddress" => $this->ipAddress, 
            ],
        ];

        // Convert to JSON then base64 encode
        $jsonString = json_encode($data, JSON_UNESCAPED_UNICODE);
        $paramBase64 = base64_encode($jsonString);

        // Gọi API OTP service
        $url = $this->urlOtpService."/api/v1/check-status-verify-otp?param=" . urlencode($paramBase64);

        try {
            $response = Http::timeout(25)->get($url);
            if (!$response->successful()) {
                throw new Exception("Không thể call api otp-service!");
            }
            return $response->json()['data']['success'] ?? false;
        } catch (Exception $e) {
            return false;
        }
    }
}
