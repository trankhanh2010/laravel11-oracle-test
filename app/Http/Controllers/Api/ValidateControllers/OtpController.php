<?php

namespace App\Http\Controllers\Api\ValidateControllers;

use App\Http\Controllers\Controller;
use App\Services\Auth\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OtpController extends Controller
{
    protected $otpService;
    protected $maxRequestSendOtpOnday;
    protected $otpMaxRequestsVerifyPerOtp;
    protected $otpTTL;
    protected $otpMaxRequestsPerDay;
    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
        $this->maxRequestSendOtpOnday = config('database')['connections']['otp']['otp_max_requests_per_day'];
        $this->otpMaxRequestsVerifyPerOtp = config('database')['connections']['otp']['otp_max_requests_verify_per_otp'];
        $this->otpTTL = config('database')['connections']['otp']['otp_ttl'];
        $this->otpMaxRequestsVerifyPerOtp = config('database')['connections']['otp']['otp_max_requests_verify_per_otp'];
        $this->otpMaxRequestsPerDay = config('database')['connections']['otp']['otp_max_requests_per_day'];
    }
    public function getTotalRequestVerifyOtp($patientCode)
    {
        $cacheKey = 'total_verify_OTP_treatment_fee_' . $patientCode; // Tránh key quá dài
        // Kiểm tra xem cache đã tồn tại chưa
        if (!Cache::has($cacheKey)) {
            // Nếu chưa có, đặt giá trị là 1 
            Cache::put($cacheKey, 1, now()->addHour());
        } else {
            // Nếu đã có, tăng giá trị lên 1
            Cache::increment($cacheKey);
        }

        // Trả về số lần gửi OTP của thiết bị này trong ngày
        return Cache::get($cacheKey);
    }
    public function checkLimitTotalRequestVerifyOtp($total)
    {
        return $total > $this->otpMaxRequestsVerifyPerOtp;
    }
    public function deleteCacheLimitTotalRequestVerifyOtp($patientCode)
    {
        $cacheKey = 'total_verify_OTP_treatment_fee_' .$patientCode; // Tránh key quá dài

        Cache::forget($cacheKey); // Xóa cache với key tương ứng
    }
    public function deleteCacheLimitTotalRequestSendOtp()
    {
        $deviceInfo = request()->header('User-Agent'); // Lấy thông tin thiết bị từ User-Agent
        $ipAddress = request()->ip(); // Lấy địa chỉ IP
        $cacheKey = 'total_OTP_treatment_fee_' . md5($deviceInfo . '_' . $ipAddress); // Tránh key quá dài

        Cache::forget($cacheKey); // Xóa cache với key tương ứng
    }
    public function getTotalRetryVerifyOtp($patientCode){
        $cacheKey = 'total_verify_OTP_treatment_fee_' . $patientCode; // Tránh key quá dài
        
        $totalRequestVerify = Cache::get($cacheKey) ?? 0;
        return $this->otpMaxRequestsVerifyPerOtp - $totalRequestVerify;
    }
    public function verifyOtpTreatmentFee(Request $request)
    {
        // Lấy data từ request
        $inputOtp = $request->input('otp');
        $name = 'OTP_treatment_fee';

        $patientCode = $request->input('patientCode');
        $deviceInfo = request()->header('User-Agent'); // Lấy thông tin thiết bị từ User-Agent
        // Loại bỏ các ký tự đặc biệt, chỉ giữ lại chữ cái, chữ số, gạch dưới và gạch nối
        $sanitizedDeviceInfo = preg_replace('/[^a-zA-Z0-9-_]/', '', $deviceInfo);
        $ipAddress = request()->ip(); // Lấy địa chỉ IP

        $cacheKey = $name . '_' . $patientCode;
        $cacheTTL = 14400;
        // Check xem đã xác thực bao lần 
        $checkLimitVerify = $this->checkLimitTotalRequestVerifyOtp($this->getTotalRequestVerifyOtp($patientCode));
        $limitRequest = false;
        if ($checkLimitVerify) {
            $limitRequest = true;
            return returnDataSuccess([], [
                'success' => false,
                'limitRequest' => $limitRequest,
                'totalRetryVerify' => $this->getTotalRetryVerifyOtp($patientCode),
                'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
                'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
                'otpTTL' => $this->otpTTL,
            ]);
        } else {
            // Kiểm tra mã OTP trong cache
            $cachedOtp = Cache::get($cacheKey);
            if ($cachedOtp && $cachedOtp == $inputOtp) {
                // Xác minh thành công
                Cache::forget($cacheKey); // Xóa mã OTP sau khi sử dụng
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu xác minh thành công thì xóa cache limitRequestVerifyOtp
                $this->deleteCacheLimitTotalRequestSendOtp(); // Nếu xác minh thành công thì xóa cache limitRequestSendOtp
                // Tạo cache lưu trạng thái
                Cache::put($name . '_' . $patientCode . '_' . $sanitizedDeviceInfo . '_' . $ipAddress, 1, $cacheTTL);

                return returnDataSuccess([], [
                    'success' => true,
                    'limitRequest' => $limitRequest,
                    'totalRetryVerify' => $this->getTotalRetryVerifyOtp($patientCode),
                    'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
                    'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
                    'otpTTL' => $this->otpTTL,
                ]);
            } else {
                // Xác minh thất bại
                return returnDataSuccess([], [
                    'success' => false,
                    'limitRequest' => $limitRequest,
                    'totalRetryVerify' => $this->getTotalRetryVerifyOtp($patientCode),
                    'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
                    'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
                    'otpTTL' => $this->otpTTL,
                ]);
            }
        }
    }
    public function getTotalRequestSendOtp()
    {
        $deviceInfo = request()->header('User-Agent'); // Lấy thông tin thiết bị từ User-Agent
        $ipAddress = request()->ip(); // Lấy địa chỉ IP
        $cacheKey = 'total_OTP_treatment_fee_' . md5($deviceInfo . '_' . $ipAddress); // Tránh key quá dài
        // Kiểm tra xem cache đã tồn tại chưa
        if (!Cache::has($cacheKey)) {
            // Nếu chưa có, đặt giá trị là 0 và hết hạn sau 1 ngày
            Cache::put($cacheKey, 0, now()->addDay());
        } 

        // Trả về số lần gửi OTP của thiết bị này trong ngày
        return Cache::get($cacheKey);
    }
    public function addTotalRequestSendOtp()
    {
        $deviceInfo = request()->header('User-Agent'); // Lấy thông tin thiết bị từ User-Agent
        $ipAddress = request()->ip(); // Lấy địa chỉ IP
        $cacheKey = 'total_OTP_treatment_fee_' . md5($deviceInfo . '_' . $ipAddress); // Tránh key quá dài
        // Kiểm tra xem cache đã tồn tại chưa
        if (!Cache::has($cacheKey)) {
            // Nếu chưa có, đặt giá trị là 1 và hết hạn sau 1 ngày
            Cache::put($cacheKey, 1, now()->addDay());
        } else {
            // Nếu đã có, tăng giá trị lên 1
            Cache::increment($cacheKey);
        }

        // Trả về số lần gửi OTP của thiết bị này trong ngày
        return Cache::get($cacheKey);
    }
    public function checkLimitTotalRequestSendOtp($total)
    {
        return $total >= $this->maxRequestSendOtpOnday;
    }
    public function sendOtpPhoneTreatmentFee(Request $request)
    {
        $patientCode = $request->input('patientCode');
        $checkTotalRequest = $this->checkLimitTotalRequestSendOtp($this->getTotalRequestSendOtp());
        $limitRequest = false;
        // Đạt giới hạn thì k gửi otp
        if ($checkTotalRequest) {
            $limitRequest = true;
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpPhoneTreatmentFee($patientCode);
            if($data) {
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            } 

        }
        return returnDataSuccess([], [
            'success' => $data,
            'limitRequest' => $limitRequest,
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'otpTTL' => $this->otpTTL,
        ]);
    }
    public function sendOtpPatientRelativePhoneTreatmentFee(Request $request)
    {
        $patientCode = $request->input('patientCode');
        $checkTotalRequest = $this->checkLimitTotalRequestSendOtp($this->getTotalRequestSendOtp());
        $limitRequest = false;
        // Đạt giới hạn thì k gửi otp
        if ($checkTotalRequest) {
            $limitRequest = true;
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpPatientRelativePhoneTreatmentFee($patientCode);
            if($data) {
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            } 
        }
        return returnDataSuccess([], [
            'success' => $data,
            'limitRequest' => $limitRequest,
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'otpTTL' => $this->otpTTL,
        ]);
    }
    public function sendOtpPatientRelativeMobileTreatmentFee(Request $request)
    {
        $patientCode = $request->input('patientCode');
        $checkTotalRequest = $this->checkLimitTotalRequestSendOtp($this->getTotalRequestSendOtp());
        $limitRequest = false;
        // Đạt giới hạn thì k gửi otp
        if ($checkTotalRequest) {
            $limitRequest = true;
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpPatientRelativeMobileTreatmentFee($patientCode);
            if($data) {
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            } 
        }
        return returnDataSuccess([], [
            'success' => $data,
            'limitRequest' => $limitRequest,
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'otpTTL' => $this->otpTTL,
        ]);
    }
    public function sendOtpMailTreatmentFee(Request $request)
    {
        $patientCode = $request->input('patientCode');
        $checkTotalRequest = $this->checkLimitTotalRequestSendOtp($this->getTotalRequestSendOtp());
        $limitRequest = false;
        // Đạt giới hạn thì k gửi otp
        if ($checkTotalRequest) {
            $limitRequest = true;
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpMailTreatmentFee($patientCode);
            if($data) {
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->addTotalRequestSendOtp(); // Nếu gửi mã OTP thì tăng tổng lên 1
            } 
        }
        return returnDataSuccess([], [
            'success' => $data,
            'limitRequest' => $limitRequest,
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'otpTTL' => $this->otpTTL,
        ]);
    }
}
