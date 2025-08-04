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
                return $this->sendOtpPhoneTreatmentFee();
            case 'patient-mobile-sms':
                return $this->sendOtpMobileTreatmentFee();
            case 'patient-mail':
                return $this->sendOtpMailTreatmentFee();
            case 'patient-phone-zalo':
                return $this->sendOtpZaloPhoneTreatmentFee();
            case 'patient-mobile-zalo':
                return $this->sendOtpZaloMobileTreatmentFee();
            case 'patient-relative-phone-sms':
                return $this->sendOtpPatientRelativePhoneTreatmentFee();
            case 'patient-relative-mobile-sms':
                return $this->sendOtpPatientRelativeMobileTreatmentFee();
            case 'patient-relative-phone-zalo':
                return $this->sendOtpZaloPatientRelativePhoneTreatmentFee();
            case 'patient-relative-mobile-zalo':
                return $this->sendOtpZaloPatientRelativeMobileTreatmentFee();
    
            // Theo số điện thoại đăng ký mới
            case 'register-phone-zalo':
                return $this->sendOtpZaloRegisterPhone();
            default:
                // Theo số điện thoại / cccd lúc tìm kiếm thông tin => tìm ds bệnh nhân => có => lấy sđt của phần tử đầu tiên
                if(!empty($this->paramTimKiemThongTin)){
                    return $this->sendOtpZaloPhoneTimKiemBenhNhan();
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

        // Gọi function trong OtpService thì phải gọi sau khi truyền params
        $this->isLimitTotalRequestSendOtp = $this->checkLimitTotalRequestSendOtp($this->otpService->getTotalRequestSendOtp());
        $this->isLimitTotalRequestVerifyOtp = $this->checkLimitTotalRequestVerifyOtp();
    }
    public function checkLimitTotalRequestVerifyOtp()
    {
        return $this->otpService->getOrCreateTotalRequestVerifyOtp() > $this->otpMaxRequestsVerifyPerOtp;
    }
    public function getTotalRetryVerifyOtp()
    {
        return max(0, $this->otpMaxRequestsVerifyPerOtp - $this->otpService->getDataTotalRequestVerifyOtp()); // bé hơn 0 thì trả 0 
    }
    public function handleVerifySuccess()
    {
        $this->otpService->clearCacheSaveOtp(); // Xóa mã OTP sau khi sử dụng
        $this->otpService->clearCacheTotalRequestVerifyOtp(); // Nếu xác minh thành công thì xóa cache limitRequestVerifyOtp
        $this->otpService->clearCacheTotalRequestSendOtp(); // Nếu xác minh thành công thì xóa cache limitRequestSendOtp
        // Tạo cache lưu trạng thái
        $this->otpService->createCacheVerifySuccess();
    }
    public function verifyOtp()
    {
        return $this->otpService->getDataCacheOtp() && $this->otpService->getDataCacheOtp() == $this->inputOtp;
    }
    public function getDataInfoResponse()
    {
        return [
            'totalRetryVerify' => $this->getTotalRetryVerifyOtp(),
            'totalRequestPerDay' => $this->otpService->getTotalRequestSendOtp(),
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'otpTTL' => $this->otpTTL,
        ];
    }
    public function verifyOtpTreatmentFee()
    {
        $response = array_merge(['limitRequest' => $this->isLimitTotalRequestVerifyOtp], $this->getDataInfoResponse());
        // Nếu hết lần thử xác thực OTP
        if ($this->isLimitTotalRequestVerifyOtp) {
            return returnDataSuccess([], array_merge(['success' => false], $response));
        }
        $isVerified = $this->verifyOtp();
        if ($isVerified) {
            $this->handleVerifySuccess();
        }
        return returnDataSuccess([], array_merge(['success' => $isVerified], $response));
    }

    public function checkLimitTotalRequestSendOtp($total)
    {
        return $total >= $this->maxRequestSendOtpOnday;
    }
    public function sendOtpPhoneTreatmentFee()
    {
        // Đạt giới hạn thì k gửi otp
        if ($this->isLimitTotalRequestSendOtp) {
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpPhoneTreatmentFee();
            if ($data) {
                $this->otpService->clearCacheTotalRequestVerifyOtp(); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->otpService->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess(
            [],
            array_merge(
                [
                    'success' => $data,
                    'limitRequest' => $this->isLimitTotalRequestSendOtp,
                ],
                $this->getDataInfoResponse()
            )
        );
    }
    public function sendOtpMobileTreatmentFee()
    {
        // Đạt giới hạn thì k gửi otp
        if ($this->isLimitTotalRequestSendOtp) {
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpMobileTreatmentFee();
            if ($data) {
                $this->otpService->clearCacheTotalRequestVerifyOtp(); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->otpService->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess(
            [],
            array_merge(
                [
                    'success' => $data,
                    'limitRequest' => $this->isLimitTotalRequestSendOtp,
                ],
                $this->getDataInfoResponse()
            )
        );
    }
    public function sendOtpPatientRelativePhoneTreatmentFee()
    {
        // Đạt giới hạn thì k gửi otp
        if ($this->isLimitTotalRequestSendOtp) {
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpPatientRelativePhoneTreatmentFee();
            if ($data) {
                $this->otpService->clearCacheTotalRequestVerifyOtp(); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->otpService->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess(
            [],
            array_merge(
                [
                    'success' => $data,
                    'limitRequest' => $this->isLimitTotalRequestSendOtp,
                ],
                $this->getDataInfoResponse()
            )
        );
    }
    public function sendOtpPatientRelativeMobileTreatmentFee()
    {
        // Đạt giới hạn thì k gửi otp
        if ($this->isLimitTotalRequestSendOtp) {
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpPatientRelativeMobileTreatmentFee();
            if ($data) {
                $this->otpService->clearCacheTotalRequestVerifyOtp(); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->otpService->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess(
            [],
            array_merge(
                [
                    'success' => $data,
                    'limitRequest' => $this->isLimitTotalRequestSendOtp,
                ],
                $this->getDataInfoResponse()
            )
        );
    }
    public function sendOtpMailTreatmentFee()
    {
        // Đạt giới hạn thì k gửi otp
        if ($this->isLimitTotalRequestSendOtp) {
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpMailTreatmentFee();
            if ($data) {
                $this->otpService->clearCacheTotalRequestVerifyOtp(); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->otpService->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess(
            [],
            array_merge(
                [
                    'success' => $data,
                    'limitRequest' => $this->isLimitTotalRequestSendOtp,
                ],
                $this->getDataInfoResponse()
            )
        );
    }
    public function sendOtpZaloPhoneTreatmentFee()
    {
        // Đạt giới hạn thì k gửi otp
        if ($this->isLimitTotalRequestSendOtp) {
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpZaloPhoneTreatmentFee();
            if ($data) {
                $this->otpService->clearCacheTotalRequestVerifyOtp(); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->otpService->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess(
            [],
            array_merge(
                [
                    'success' => $data,
                    'limitRequest' => $this->isLimitTotalRequestSendOtp,
                ],
                $this->getDataInfoResponse()
            )
        );
    }

    public function sendOtpZaloMobileTreatmentFee()
    {
        // Đạt giới hạn thì k gửi otp
        if ($this->isLimitTotalRequestSendOtp) {
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpZaloMobileTreatmentFee();
            if ($data) {
                $this->otpService->clearCacheTotalRequestVerifyOtp(); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->otpService->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess(
            [],
            array_merge(
                [
                    'success' => $data,
                    'limitRequest' => $this->isLimitTotalRequestSendOtp,
                ],
                $this->getDataInfoResponse()
            )
        );
    }
    public function sendOtpZaloPatientRelativeMobileTreatmentFee()
    {
        // Đạt giới hạn thì k gửi otp
        if ($this->isLimitTotalRequestSendOtp) {
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpZaloPatientRelativeMobileTreatmentFee();
            if ($data) {
                $this->otpService->clearCacheTotalRequestVerifyOtp(); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->otpService->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess(
            [],
            array_merge(
                [
                    'success' => $data,
                    'limitRequest' => $this->isLimitTotalRequestSendOtp,
                ],
                $this->getDataInfoResponse()
            )
        );
    }
    public function sendOtpZaloPatientRelativePhoneTreatmentFee()
    {
        // Đạt giới hạn thì k gửi otp
        if ($this->isLimitTotalRequestSendOtp) {
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpZaloPatientRelativePhoneTreatmentFee();
            if ($data) {
                $this->otpService->clearCacheTotalRequestVerifyOtp(); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->otpService->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess(
            [],
            array_merge(
                [
                    'success' => $data,
                    'limitRequest' => $this->isLimitTotalRequestSendOtp,
                ],
                $this->getDataInfoResponse()
            )
        );
    }

    public function sendOtpZaloRegisterPhone()
    {
        // Đạt giới hạn thì k gửi otp
        if ($this->isLimitTotalRequestSendOtp) {
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpZaloRegisterPhone();
            if ($data) {
                $this->otpService->clearCacheTotalRequestVerifyOtp(); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->otpService->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess(
            [],
            array_merge(
                [
                    'success' => $data,
                    'limitRequest' => $this->isLimitTotalRequestSendOtp,
                ],
                $this->getDataInfoResponse()
            )
        );
    }

    public function sendOtpZaloPhoneTimKiemBenhNhan()
    {
        // Đạt giới hạn thì k gửi otp
        if ($this->isLimitTotalRequestSendOtp) {
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpZaloPhoneTimKiemBenhNhan();
            if ($data) {
                $this->otpService->clearCacheTotalRequestVerifyOtp(); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->otpService->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess(
            [],
            array_merge(
                [
                    'success' => $data,
                    'limitRequest' => $this->isLimitTotalRequestSendOtp,
                ],
                $this->getDataInfoResponse()
            )
        );
    }
}
