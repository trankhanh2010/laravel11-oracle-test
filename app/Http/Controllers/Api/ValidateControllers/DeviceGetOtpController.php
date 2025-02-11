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
        $this->dbCache = config('database')['redis']['cache']['database'];
        $this->maxRequestSendOtpOnday = config('database')['connections']['otp']['otp_max_requests_per_day'];
    }
    public function getDeviceGetOtpTreatmentFeeList()
    {
        Redis::select($this->dbCache); // Chọn DB cache
        $keys = Redis::keys('*total_OTP_treatment_fee*');
        $devices = [];
    
        foreach ($keys as $key) {
            $cacheData = Cache::get($key);
    
            if ($cacheData) {
                if (is_string($cacheData)) {
                    $cacheData = json_decode($cacheData, true);
                }
    
                // **Chỉ lấy nếu total_requests >= maxRequestSendOtpOnday**
                if (($cacheData['total_requests'] ?? 0) >= $this->maxRequestSendOtpOnday) {
                    $devices[] = [
                        'device' => $cacheData['device'] ?? 'Unknown',
                        'ip' => $cacheData['ip'] ?? 'Unknown',
                        'totalRequests' => $cacheData['total_requests'] ?? 0,
                        'firstRequestAt' => $cacheData['first_request_at'] ?? null,
                        'lastRequestAt' => $cacheData['last_request_at'] ?? null,
                    ];
                }
            }
        }
    
        return returnDataSuccess([], $devices);
    }
    
}
