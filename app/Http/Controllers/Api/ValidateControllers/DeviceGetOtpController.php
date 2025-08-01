<?php

namespace App\Http\Controllers\Api\ValidateControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class DeviceGetOtpController extends Controller
{
    protected $dbCache;
    protected $maxRequestSendOtpOnday;
    public function __construct(){
        // $this->dbCache = config('database')['redis']['cache']['database'];
        $this->maxRequestSendOtpOnday = config('database')['connections']['otp']['otp_max_requests_per_day'];
    }
    public function getDeviceGetOtpTreatmentFeeList()
    {
        // Redis::select($this->dbCache); // Chọn DB cache
        $cacheKeySet = "cache_keys:" . "device_get_otp";
        // $keys = Redis::keys('*total_OTP_treatment_fee*');
        $keys = Redis::connection('cache')->smembers($cacheKeySet);
        $devices = [];
    
        foreach ($keys as $key) {
            $cacheData = Cache::get($key);
            if ($cacheData) {
                if (is_string($cacheData)) {
                    $cacheData = json_decode($cacheData, true);
                }
    
                // **Chỉ lấy nếu total_requests >= maxRequestSendOtpOnday**
                if (($cacheData['total_requests'] ?? 0) >= $this->maxRequestSendOtpOnday) {
                    $ttl = Redis::connection('cache')->ttl($key); // Lấy TTL của cache (nếu dùng Redis)
                    $devices[] = [
                        'key' => $key,
                        'device' => $cacheData['device'] ?? 'Unknown',
                        'ip' => $cacheData['ip'] ?? 'Unknown',
                        'totalRequests' => $cacheData['total_requests'] ?? 0,
                        'firstRequestAt' => $cacheData['first_request_at'] ?? null,
                        'lastRequestAt' => $cacheData['last_request_at'] ?? null,
                        'lastPatientCodeRequestOtp' => $cacheData['last_patient_code_request_otp'] ?? null,
                        'patientCodeList' => $cacheData['patient_code_list'] ?? [],
                        'ttl' => $ttl ?? 0, // Thêm TTL vào kết quả
                    ];
                }
            }
        }
        return returnDataSuccess([], $devices);
    }

    public function unlockDeviceLimitTotalRequestSendOtp(Request $request)
    {
        $key = $request->key;
    
        if (!$key) {
            return returnDataSuccess([], ['success' => false]);
        }
        
        // Lấy dữ liệu từ cache
        $cachedData = Cache::get($key);
        if ($cachedData && is_array($cachedData)) {
            // Cập nhật giá trị total_requests
            $cachedData['total_requests'] = $this->maxRequestSendOtpOnday - 5;
    
            // Ghi đè lại dữ liệu vào cache với thời gian lưu không đổi
            Cache::put($key, $cachedData, now()->addHours(24)); // Giữ thời gian cache theo nhu cầu
        } else {
            return returnDataSuccess([], ['success' => false, 'message' => 'Không tìm thấy dữ liệu cache']);
        }
    
        return returnDataSuccess([], ['success' => true, 'totalRequests' => $cachedData['total_requests']]);
    }
    
    
    
}
