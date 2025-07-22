<?php

namespace App\Http\Controllers\Api\ValidateControllers;

use App\Http\Controllers\Controller;
use App\Services\Auth\OtpService;
use App\Services\Zalo\ZaloService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class OtpController extends Controller
{
    protected $otpService;
    protected $zaloSerivce;
    protected $maxRequestSendOtpOnday;
    protected $otpMaxRequestsVerifyPerOtp;
    protected $otpTTL;
    protected $otpMaxRequestsPerDay;
    public function __construct(
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
        
    }
    public function sendOtpTreatmentFee(Request $request)
    {
        $method = $request->query('method'); // Nhận phương thức gửi OTP từ tham số

        switch ($method) {
            case 'patient-phone-sms':
                return $this->sendOtpPhoneTreatmentFee($request);
            case 'patient-mobile-sms':
                return $this->sendOtpMobileTreatmentFee($request);
            case 'patient-mail':
                return $this->sendOtpMailTreatmentFee($request);
            case 'patient-phone-zalo':
                return $this->sendOtpZaloPhoneTreatmentFee($request);
            case 'patient-mobile-zalo':
                return $this->sendOtpZaloMobileTreatmentFee($request);
            case 'patient-relative-phone-sms':
                return $this->sendOtpPatientRelativePhoneTreatmentFee($request);
            case 'patient-relative-mobile-sms':
                return $this->sendOtpPatientRelativeMobileTreatmentFee($request);
            case 'patient-relative-phone-zalo':
                return $this->sendOtpZaloPatientRelativePhoneTreatmentFee($request);
            case 'patient-relative-mobile-zalo':
                return $this->sendOtpZaloPatientRelativeMobileTreatmentFee($request);
            default:
                return returnDataSuccess([], [
                    'success' => false,
                ]);
        }
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
        $cacheKey = 'total_verify_OTP_treatment_fee_' . $patientCode; // Tránh key quá dài

        Cache::forget($cacheKey); // Xóa cache với key tương ứng
    }
    public function deleteCacheLimitTotalRequestSendOtp()
    {
        $deviceInfo = request()->header('User-Agent'); // Lấy thông tin thiết bị từ User-Agent
        $ipAddress = request()->ip(); // Lấy địa chỉ IP
        $cacheKey = 'total_OTP_treatment_fee_' . $deviceInfo . '_' . $ipAddress; // Tránh key quá dài

        Cache::forget($cacheKey); // Xóa cache với key tương ứng
    }
    public function getTotalRetryVerifyOtp($patientCode)
    {
        $cacheKey = 'total_verify_OTP_treatment_fee_' . $patientCode; // Tránh key quá dài

        $totalRequestVerify = Cache::get($cacheKey) ?? 0;
        return max(0,$this->otpMaxRequestsVerifyPerOtp - $totalRequestVerify); // bé hơn 0 thì trả 0 
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
    public function getTotalRequestSendOtp($patientCode)
    {
        $deviceInfo = request()->header('User-Agent'); // Lấy thông tin thiết bị từ User-Agent
        $ipAddress = request()->ip(); // Lấy địa chỉ IP
        $cacheKey = 'total_OTP_treatment_fee_' . $deviceInfo . '_' . $ipAddress; // Tránh key quá dài

        // Kiểm tra cache hiện tại
        $cacheData = Cache::get($cacheKey);

        // Nếu cache chưa tồn tại, khởi tạo dữ liệu
        if (!$cacheData) {
            $cacheData = [
                'device' => $deviceInfo,
                'ip' => $ipAddress,
                'total_requests' => 0,  // Số lần gửi OTP
                'first_request_at' => now()->toDateTimeString(), // Thời gian gọi lần đầu
                'last_request_at' => null, // Chưa có lần cuối
                'patient_code_list' => [$patientCode => 1] // Thêm patientCode đầu tiên
            ];
        }

        // **Lưu lại vào cache với TTL 
        $cacheKeySet = "cache_keys:" . "device_get_otp"; // Set để lưu danh sách key
        Cache::put($cacheKey, $cacheData, now()->addDay());

        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $cacheData['total_requests'];
    }
    public function addTotalRequestSendOtp($patientCode)
    {
        $deviceInfo = request()->header('User-Agent'); // Lấy thông tin thiết bị từ User-Agent
        $ipAddress = request()->ip(); // Lấy địa chỉ IP
        $cacheKey = 'total_OTP_treatment_fee_' . $deviceInfo . '_' . $ipAddress; // Tránh key quá dài
        // Kiểm tra cache hiện tại
        $cacheData = Cache::get($cacheKey);

        // Nếu cache chưa tồn tại, khởi tạo dữ liệu
        if (!$cacheData) {
            $cacheData = [
                'device' => $deviceInfo,
                'ip' => $ipAddress,
                'total_requests' => 1,  // Lần gửi đầu tiên
                'first_request_at' => now()->toDateTimeString(), // Thời gian gửi lần đầu
                'last_request_at' => now()->toDateTimeString(), // Cập nhật lần gửi cuối
                'patient_code_list' => [$patientCode => 1] // Thêm patientCode đầu tiên
            ];
        } else {
            // Nếu đã tồn tại, tăng số lần gửi OTP
            $cacheData['total_requests'] += 1;
            $cacheData['last_request_at'] = now()->toDateTimeString(); // Cập nhật lần cuối gửi OTP
            // Kiểm tra patientCode đã có chưa
            if (isset($cacheData['patient_code_list'][$patientCode])) {
                $cacheData['patient_code_list'][$patientCode] += 1; // Tăng số lần gửi cho patientCode
            } else {
                $cacheData['patient_code_list'][$patientCode] = 1; // Thêm patientCode mới
            }
        }

        // Lưu lại vào cache với TTL 1 ngày
        // **Lưu lại vào cache với TTL 
        $cacheKeySet = "cache_keys:" . "device_get_otp"; // Set để lưu danh sách key
        Cache::put($cacheKey, $cacheData, now()->addDay());

        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $cacheData['total_requests'];
    }
    public function checkLimitTotalRequestSendOtp($total)
    {
        return $total >= $this->maxRequestSendOtpOnday;
    }
    public function sendOtpPhoneTreatmentFee(Request $request)
    {
        $patientCode = $request->input('patientCode');
        $checkTotalRequest = $this->checkLimitTotalRequestSendOtp($this->getTotalRequestSendOtp($patientCode));
        $limitRequest = false;
        // Đạt giới hạn thì k gửi otp
        if ($checkTotalRequest) {
            $limitRequest = true;
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpPhoneTreatmentFee($patientCode);
            if ($data) {
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->addTotalRequestSendOtp($patientCode); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess([], [
            'success' => $data,
            'limitRequest' => $limitRequest,
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'totalRequestPerDay' => $this->getTotalRequestSendOtp($patientCode),
            'otpTTL' => $this->otpTTL,
        ]);
    }
    public function sendOtpMobileTreatmentFee(Request $request)
    {
        $patientCode = $request->input('patientCode');
        $checkTotalRequest = $this->checkLimitTotalRequestSendOtp($this->getTotalRequestSendOtp($patientCode));
        $limitRequest = false;
        // Đạt giới hạn thì k gửi otp
        if ($checkTotalRequest) {
            $limitRequest = true;
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpMobileTreatmentFee($patientCode);
            if ($data) {
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->addTotalRequestSendOtp($patientCode); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess([], [
            'success' => $data,
            'limitRequest' => $limitRequest,
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'totalRequestPerDay' => $this->getTotalRequestSendOtp($patientCode),
            'otpTTL' => $this->otpTTL,
        ]);
    }
    public function sendOtpPatientRelativePhoneTreatmentFee(Request $request)
    {
        $patientCode = $request->input('patientCode');
        $checkTotalRequest = $this->checkLimitTotalRequestSendOtp($this->getTotalRequestSendOtp($patientCode));
        $limitRequest = false;
        // Đạt giới hạn thì k gửi otp
        if ($checkTotalRequest) {
            $limitRequest = true;
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpPatientRelativePhoneTreatmentFee($patientCode);
            if ($data) {
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->addTotalRequestSendOtp($patientCode); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess([], [
            'success' => $data,
            'limitRequest' => $limitRequest,
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'totalRequestPerDay' => $this->getTotalRequestSendOtp($patientCode),
            'otpTTL' => $this->otpTTL,
        ]);
    }
    public function sendOtpPatientRelativeMobileTreatmentFee(Request $request)
    {
        $patientCode = $request->input('patientCode');
        $checkTotalRequest = $this->checkLimitTotalRequestSendOtp($this->getTotalRequestSendOtp($patientCode));
        $limitRequest = false;
        // Đạt giới hạn thì k gửi otp
        if ($checkTotalRequest) {
            $limitRequest = true;
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpPatientRelativeMobileTreatmentFee($patientCode);
            if ($data) {
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->addTotalRequestSendOtp($patientCode); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess([], [
            'success' => $data,
            'limitRequest' => $limitRequest,
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'totalRequestPerDay' => $this->getTotalRequestSendOtp($patientCode),
            'otpTTL' => $this->otpTTL,
        ]);
    }
    public function sendOtpMailTreatmentFee(Request $request)
    {
        $patientCode = $request->input('patientCode');
        $checkTotalRequest = $this->checkLimitTotalRequestSendOtp($this->getTotalRequestSendOtp($patientCode));
        $limitRequest = false;
        // Đạt giới hạn thì k gửi otp
        if ($checkTotalRequest) {
            $limitRequest = true;
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpMailTreatmentFee($patientCode);
            if ($data) {
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->addTotalRequestSendOtp($patientCode); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess([], [
            'success' => $data,
            'limitRequest' => $limitRequest,
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'totalRequestPerDay' => $this->getTotalRequestSendOtp($patientCode),
            'otpTTL' => $this->otpTTL,
        ]);
    }
    public function sendOtpZaloPhoneTreatmentFee(Request $request)
    {
        $patientCode = $request->input('patientCode');
        $checkTotalRequest = $this->checkLimitTotalRequestSendOtp($this->getTotalRequestSendOtp($patientCode));
        $limitRequest = false;
        // Đạt giới hạn thì k gửi otp
        if ($checkTotalRequest) {
            $limitRequest = true;
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpZaloPhoneTreatmentFee($patientCode);
            if ($data) {
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->addTotalRequestSendOtp($patientCode); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess([], [
            'success' => $data,
            'limitRequest' => $limitRequest,
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'totalRequestPerDay' => $this->getTotalRequestSendOtp($patientCode),
            'otpTTL' => $this->otpTTL,
        ]);
    }

    public function sendOtpZaloMobileTreatmentFee(Request $request)
    {
        $patientCode = $request->input('patientCode');
        $checkTotalRequest = $this->checkLimitTotalRequestSendOtp($this->getTotalRequestSendOtp($patientCode));
        $limitRequest = false;
        // Đạt giới hạn thì k gửi otp
        if ($checkTotalRequest) {
            $limitRequest = true;
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpZaloMobileTreatmentFee($patientCode);
            if ($data) {
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->addTotalRequestSendOtp($patientCode); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess([], [
            'success' => $data,
            'limitRequest' => $limitRequest,
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'totalRequestPerDay' => $this->getTotalRequestSendOtp($patientCode),
            'otpTTL' => $this->otpTTL,
        ]);
    }
    public function sendOtpZaloPatientRelativeMobileTreatmentFee(Request $request)
    {
        $patientCode = $request->input('patientCode');
        $checkTotalRequest = $this->checkLimitTotalRequestSendOtp($this->getTotalRequestSendOtp($patientCode));
        $limitRequest = false;
        // Đạt giới hạn thì k gửi otp
        if ($checkTotalRequest) {
            $limitRequest = true;
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpZaloPatientRelativeMobileTreatmentFee($patientCode);
            if ($data) {
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->addTotalRequestSendOtp($patientCode); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess([], [
            'success' => $data,
            'limitRequest' => $limitRequest,
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'totalRequestPerDay' => $this->getTotalRequestSendOtp($patientCode),
            'otpTTL' => $this->otpTTL,
        ]);
    }
    public function sendOtpZaloPatientRelativePhoneTreatmentFee(Request $request)
    {
        $patientCode = $request->input('patientCode');
        $checkTotalRequest = $this->checkLimitTotalRequestSendOtp($this->getTotalRequestSendOtp($patientCode));
        $limitRequest = false;
        // Đạt giới hạn thì k gửi otp
        if ($checkTotalRequest) {
            $limitRequest = true;
            $data = false;
        } else {
            $data = $this->otpService->createAndSendOtpZaloPatientRelativePhoneTreatmentFee($patientCode);
            if ($data) {
                $this->deleteCacheLimitTotalRequestVerifyOtp($patientCode); // Nếu gửi mã OTP mới thì xóa cache limitRequestVerifyOtp
                $this->addTotalRequestSendOtp($patientCode); // Nếu gửi mã OTP thì tăng tổng lên 1
            }
        }
        return returnDataSuccess([], [
            'success' => $data,
            'limitRequest' => $limitRequest,
            'otpMaxRequestsPerDay' => $this->otpMaxRequestsPerDay,
            'otpMaxRequestsVerifyPerOtp' => $this->otpMaxRequestsVerifyPerOtp,
            'totalRequestPerDay' => $this->getTotalRequestSendOtp($patientCode),
            'otpTTL' => $this->otpTTL,
        ]);
    }
    public function refreshAccessTokenOtpZalo()
    {
        $data = $this->zaloSerivce->refreshAccessToken();
        return returnDataSuccess([], $data);
    }

    // public function getAccessAndRefreshToken(){
    //     $data = $this->zaloSerivce->getAccessAndRefreshToken();
    //     return returnDataSuccess([], $data);
    // }
    public function setTokenOtpZalo(Request $request)
    {
        $accessToken = $request->input('access_token');
        $refreshToken = $request->input('refresh_token');
        $this->zaloSerivce->setTokenOtpZalo([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ]);
        // Xóa cache
        Cache::forget('zalo_config');
    }
}
