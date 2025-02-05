<?php

namespace App\Http\Controllers\Api\ValidateControllers;

use App\Http\Controllers\Controller;
use App\Services\Auth\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OtpController extends Controller
{
    protected $OtpService;
    public function __construct(OtpService $OtpService){
        $this->OtpService = $OtpService;
    }
    public function verifyOtpTreatmentFee(Request $request){
        // Lấy data từ request
        $inputOtp = $request->input('otp');
        $name = 'OTP_treatment_fee';

        $patientCode = $request->input('patientCode');
        $deviceInfo = request()->header('User-Agent'); // Lấy thông tin thiết bị từ User-Agent
        // Loại bỏ các ký tự đặc biệt, chỉ giữ lại chữ cái, chữ số, gạch dưới và gạch nối
        $sanitizedDeviceInfo = preg_replace('/[^a-zA-Z0-9-_]/', '', $deviceInfo);
        $ipAddress = request()->ip(); // Lấy địa chỉ IP

        $cacheKey = $name.'_'. $patientCode;
        $cacheTTL = 14400;
        // Kiểm tra mã OTP trong cache
        $cachedOtp = Cache::get($cacheKey);
        if ($cachedOtp && $cachedOtp == $inputOtp) {
            // Xác minh thành công
            Cache::forget($cacheKey); // Xóa mã OTP sau khi sử dụng
            // Tạo cache lưu trạng thái
            Cache::put($name.'_'.$patientCode.'_'.$sanitizedDeviceInfo.'_'.$ipAddress, 1, $cacheTTL);

            return returnDataSuccess([], ['success' => true]);
        } else {
            // Xác minh thất bại
            return returnDataSuccess([], ['success' => false]);
        }
    }

    public function sendOtpPhoneTreatmentFee(Request $request){
        $patientCode = $request->input('patientCode');
        $data = $this->OtpService->createAndSendOtpPhoneTreatmentFee($patientCode);
        return returnDataSuccess([], ['success' => $data]);
    }
    public function sendOtpMailTreatmentFee(Request $request){
        $patientCode = $request->input('patientCode');
        $data = $this->OtpService->createAndSendOtpMailTreatmentFee($patientCode);
        return returnDataSuccess([], ['success' => $data]);
    }
}
