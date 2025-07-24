<?php

namespace App\Http\Controllers\Api\ValidateControllers;

use App\DTOs\OtpDTO;
use App\Http\Controllers\Controller;
use App\Services\Auth\OtpService;
use App\Services\Zalo\ZaloService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ZaloController extends Controller
{
    protected $zaloSerivce;
   
    public function __construct(
        Request $request,
        ZaloService $zaloSerivce,
    ) {
        $this->zaloSerivce = $zaloSerivce;
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
        $refreshToken = $request->input('refresh_token');
        // lấy refreshToken từ param => gọi api => nhận về 1 cặp AT, RT mới => lưu db
        $this->zaloSerivce->refreshAccessToken($refreshToken);
    }
}
