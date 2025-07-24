<?php

namespace App\Services\Zalo;

use App\Repositories\ZaloConfigRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class ZaloService
{
    protected $client;
    protected $zaloConfig;
    protected $zaloConfigRepository;
    protected $appId;
    protected $secretKey;

    // protected $authorizationCode;
    // protected $codeVerifier;

    protected $accessToken;
    protected $refreshToken;

    public function __construct(
        ZaloConfigRepository $zaloConfigRepository,
    ) {
        $this->client = new Client();
        $this->zaloConfigRepository = $zaloConfigRepository;

        $this->appId = config('database')['connections']['zalo']['zalo_app_id'];
        $this->secretKey = config('database')['connections']['zalo']['zalo_app_secret_key'];

        // Lấy data từ  cache
        $this->zaloConfig =  Cache::remember('zalo_config', now()->addHours(25), function () {
            return $this->zaloConfigRepository->getToken();
        });
        $this->accessToken =  $this->zaloConfig->access_token;
        $this->refreshToken =  $this->zaloConfig->refresh_token;
        // $this->authorizationCode = config('database')['connections']['zalo']['authorization_code'];
        // $this->codeVerifier = config('database')['connections']['zalo']['code_verifier'];
    }

    public function callApiSendOtp($phoneNumber, $otpCode){
        $url = 'https://business.openapi.zalo.me/message/template';

        $data = [
            'phone' => $phoneNumber, // 84772064649
            'template_id' => 408549, // Thay bằng ID của template ZNS đã được phê duyệt
            'template_data' => [
                'otp' => $otpCode
            ],
        ];

        $response = $this->client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'access_token' => $this->accessToken,
            ],
            'json' => $data,
        ]);
        return $response;
    }
    public function sendOtp($phoneNumber, $otpCode)
    {
        $response = $this->callApiSendOtp($phoneNumber, $otpCode);

        $responseBody = json_decode($response->getBody(), true);
        // Kiểm tra mã lỗi trả về từ Zalo
        // Nếu mã = 0 thì thành công
        if (isset($responseBody['error']) && $responseBody['error'] == 0) {
            return $responseBody;
        } else {
            // Nếu mã liên quan đến accessToken thì thử refresh
            if (isset($responseBody['error']) && $responseBody['error'] == -124) {
                $result = $this->refreshAccessToken();
                // Dừng lại
                throw new \Exception('Error: Retry send otp');
            }
            // Còn lại ném ra lỗi
            throw new \Exception('Error from Zalo API: ' . $responseBody['message']);
        }
    }
    public function refreshAccessToken($refreshToken = '') // Nhận vào refreshToken để check lúc gọi api setDBTokenOtpZalo => nếu check đúng mới cập nhật   // lấy refreshToken từ param => gọi api => nhận về 1 cặp AT, RT mới => lưu db
    {
        $url = 'https://oauth.zaloapp.com/v4/oa/access_token';

        $data = [
            'app_id' => $this->appId,
            'grant_type' => 'refresh_token',
            'refresh_token' => !empty($refreshToken) ? $refreshToken : $this->refreshToken, // Nhận vào refreshToken từ param request lúc setDB hoặc lấy từ cache khi bình thường
        ];
        // dump($data);
        $response = $this->client->post($url, [
            'form_params' => $data,
            'headers' => [
                'secret_key' => $this->secretKey,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);
        $responseBody = json_decode($response->getBody(), true);
        // dump($responseBody);
        // Nếu thành công, cập nhật db
        if (isset($responseBody['access_token']) && isset($responseBody['refresh_token'])) {
            $this->accessToken = $responseBody['access_token'];
            $this->refreshToken = $responseBody['refresh_token'];

            $this->setTokenOtpZalo([
                'access_token' => $this->accessToken,
                'refresh_token' => $this->refreshToken,
            ]);
            // Xóa cache
            Cache::forget('zalo_config');
            return true;
        }
        // Còn lại ném ra lỗi
        throw new \Exception('Error from Refresh token Zalo API: ' . $responseBody['error_name']);
    }

    public function setTokenOtpZalo($data){
        $this->zaloConfigRepository->updateToken($data);
    }
    public function getInfoTemplate($id){
        $url = 'https://business.openapi.zalo.me/template/info/v2'. '?template_id='.$id;

    
        $response = $this->client->get($url, [
            'headers' => [
                'access_token' => $this->accessToken,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);
        $responseBody = json_decode($response->getBody(), true);
        return $responseBody;
    }
    // public function getAccessAndRefreshToken(){
    //     $url = 'https://oauth.zaloapp.com/v4/oa/access_token';

    //     $data = [
    //         'code' => $this->authorizationCode,
    //         'app_id' => $this->appId, 
    //         'grant_type' => 'authorization_code',
    //         'code_verifier' => $this->codeVerifier,
    //     ];
    //     $response = $this->client->post($url, [
    //         'form_params' => $data,
    //         'headers' => [
    //             'secret_key' => $this->secretKey,
    //             'Content-Type' => 'application/x-www-form-urlencoded'
    //         ]
    //     ]);

    //     $responseBody = json_decode($response->getBody(), true);
    //     // Nếu thành công, tạo lại cache
    //     if(isset($responseBody['error']) && $responseBody['error'] == 0){
    //         Cache::put('zalo_config', $responseBody, now()->addHours(25));
    //     }
    //     dd($responseBody);
    //     // Còn lại ném ra lỗi
    //     throw new \Exception('Error from Get token Zalo API: ' . $responseBody['error_name']);
    // }
}
