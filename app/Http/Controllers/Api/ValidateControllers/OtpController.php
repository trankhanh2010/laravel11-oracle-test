<?php

namespace App\Http\Controllers\Api\ValidateControllers;

use App\DTOs\OtpDTO;
use App\Http\Controllers\Controller;
use App\Services\Auth\OtpService;
use App\Services\Zalo\ZaloService;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    protected $otpDTO;
    protected $otpService;
    protected $zaloSerivce;
    protected $maxRequestSendOtpOnday;
    protected $otpMaxRequestsVerifyPerOtp;
    protected $otpTTL;
    protected $otpMaxRequestsPerDay;
    protected $patientCode;
    protected $method;
    protected $deviceInfo;
    protected $sanitizedDeviceInfo;
    protected $ipAddress;
    protected $inputOtp;
    protected $registerPhone;
    protected $paramTimKiemThongTin;
    protected $isLimitTotalRequestSendOtp; // Nhớ gọi sau khi đã truyền parasm vào OtpService
    protected $isLimitTotalRequestVerifyOtp; // Nhớ gọi sau khi đã truyền parasm vào OtpService
    public function __construct(
        Request $request,
        OtpService $otpService,
        ZaloService $zaloSerivce,
    ) {
        $this->otpService = $otpService;
        $this->zaloSerivce = $zaloSerivce;
        $this->maxRequestSendOtpOnday = config('database')['connections']['otp']['otp_max_requests_per_day'];
        $this->otpMaxRequestsVerifyPerOtp = config('database')['connections']['otp']['otp_max_requests_verify_per_otp'];
        $this->otpTTL = config('database')['connections']['otp']['otp_ttl'];
        $this->otpMaxRequestsVerifyPerOtp = config('database')['connections']['otp']['otp_max_requests_verify_per_otp'];
        $this->otpMaxRequestsPerDay = config('database')['connections']['otp']['otp_max_requests_per_day'];
        $this->patientCode = $request->input('patientCode');
        $this->method = $request->query('method'); // Nhận phương thức gửi OTP từ tham số
        $this->deviceInfo = request()->header('User-Agent'); // Lấy thông tin thiết bị từ User-Agent
        $this->sanitizedDeviceInfo = preg_replace('/[^a-zA-Z0-9-_]/', '', $this->deviceInfo); // bỏ cách ký tự đặc biệt
        $this->ipAddress = request()->ip(); // Lấy địa chỉ IP
        $this->inputOtp = $request->input('otp'); // Lấy mã OTP sẽ xác thực từ request
        $this->registerPhone = $request->input('registerPhone'); // Lấy mã OTP sẽ xác thực từ request
        $this->paramTimKiemThongTin = request()->input('paramTimKiemThongTin');

        $this->setParam();
    }
    public function sendOtp()
    {
        // Nếu thiết bị đã xác thực rồi mà yêu cầu OTP tiếp => return false
        if ($this->otpService->isVerified()) {
            return returnDataSuccess([], [
                'success' => false,
            ]);
        }

        switch ($this->method) {
            // Theo mã bệnh nhân
            case 'patient-phone-sms':
                return $this->otpService->createAndSendOtpPhoneTreatmentFee();
            case 'patient-mobile-sms':
                return $this->otpService->createAndSendOtpMobileTreatmentFee();
            case 'patient-mail':
                return $this->otpService->createAndSendOtpMailTreatmentFee();
            case 'patient-phone-zalo':
                return $this->otpService->createAndSendOtpZaloPhoneTreatmentFee();
            case 'patient-mobile-zalo':
                return $this->otpService->createAndSendOtpZaloMobileTreatmentFee();
            case 'patient-relative-phone-sms':
                return $this->otpService->createAndSendOtpPatientRelativePhoneTreatmentFee();
            case 'patient-relative-mobile-sms':
                return $this->otpService->createAndSendOtpPatientRelativeMobileTreatmentFee();
            case 'patient-relative-phone-zalo':
                return $this->otpService->createAndSendOtpZaloPatientRelativePhoneTreatmentFee();
            case 'patient-relative-mobile-zalo':
                return $this->otpService->createAndSendOtpZaloPatientRelativeMobileTreatmentFee();
    
            // Theo số điện thoại đăng ký mới
            case 'register-phone-zalo':
                return $this->otpService->createAndSendOtpZaloRegisterPhone();
            default:
                // Theo số điện thoại / cccd lúc tìm kiếm thông tin => tìm ds bệnh nhân => có => lấy sđt của phần tử đầu tiên
                if(!empty($this->paramTimKiemThongTin)){
                    return $this->otpService->createAndSendOtpZaloPhoneTimKiemBenhNhan();
                }
                return returnDataSuccess([], [
                    'success' => false,
                ]);
        }
    }
    public function setParam(){
        // Thêm tham số vào service
        $this->otpDTO = new OtpDTO(
            $this->patientCode,
            $this->method,
            $this->registerPhone,
        );
        $this->otpService->withParams($this->otpDTO);
    }
    public function verifyOtpTreatmentFee()
    {
        return $this->otpService->callApiVerifyOtp($this->inputOtp);
    }

    public function checkLimitTotalRequestSendOtp($total)
    {
        return $total >= $this->maxRequestSendOtpOnday;
    }
}
