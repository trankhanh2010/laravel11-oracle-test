<?php

namespace App\Services\Transaction;

use App\DTOs\VietinbankDTO;
use Illuminate\Support\Facades\Http;

class VietinbankService
{
    protected string $apiUrl;
    protected string $merchantId;
    protected string $secretKey;
    protected $publicKeyVietinbankPath;
    protected $privateKeyPath;
    protected $params;
    public function __construct()
    {
        $this->apiUrl = config('database')['connections']['vietinbank']['vietinbank_api_url'];
        $this->merchantId = config('database')['connections']['vietinbank']['vietinbank_merchant_id'];
        $this->secretKey = config('database')['connections']['vietinbank']['vietinbank_secret_key'];
        $this->publicKeyVietinbankPath = config('database')['connections']['vietinbank']['public_key_vietinbank_path'];
        $this->privateKeyPath = config('database')['connections']['vietinbank']['private_key_bvxa_path'];
    }
    public function withParams(VietinbankDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    /**
     * Tạo giao dịch QR Code
     */
    public function createTransactionQrCode($amount, $orderId, $callbackUrl)
    {
        $payload = [
            "merchant_id" => $this->merchantId,
            "order_id" => $orderId,
            "amount" => $amount,
            "callback_url" => $callbackUrl,
            "description" => "Thanh toán đơn hàng #{$orderId}",
        ];
        dd($payload);
        // Ký dữ liệu trước ghi gửi 
        

        // Gửi request đến API VietinBank
        $response = Http::post("{$this->apiUrl}", $payload);
        dump($response->json());
        return $response->json();
    }
    public function handleConfirmTransaction()
    {
        $data = $this->getParamRequest();
        // Xác minh chữ ký 
        $isVerify = $this->verifyVietinbankSignature($data);
        // Nếu đúng thì tạo transaction trong DB
        if($isVerify){
            $param = [
                'requestId' => $data['requestId'],
                'paymentStatus' => '00',
                'signature' => $this->SignData(['requestId' => $data['requestId'], 'paymentStatus' => '00',])
            ];
        } else {
            $param = [
                'requestId' => $data['requestId'],
                'paymentStatus' => 'ZZ',
                'signature' => $this->SignData(['requestId' => $data['requestId'], 'paymentStatus' => 'ZZ',])
            ];
        }
        return $param;
    }

    private function verifyVietinbankSignature($data)
    {
        $publicKeyVietinbank = openssl_pkey_get_public(file_get_contents($this->publicKeyVietinbankPath));
        if (!$publicKeyVietinbank) {
            throw new \Exception("Không thể đọc public key VietinBank");
        }
        $signatureDecode = base64_decode($data['signature']);

        // Tạo chuỗi rawData theo yêu cầu
        $rawData = $data['requestId'] . $data['merchantId'] . $data['orderId'] . $data['productId'];

        // Tạo chữ ký bằng HMAC-SHA256
        $verify = openssl_verify($rawData, $signatureDecode, $publicKeyVietinbank, OPENSSL_ALGO_SHA256);
        //  So sánh chữ ký

        // // Tạo chữ ký test
        // $privateKeyVietinbankPath = "D:/vietinbank/vtb_private_key.pem";
        // $privateKeyVietinbank = openssl_pkey_get_private(file_get_contents($privateKeyVietinbankPath));
        // $rawData = '000002235MERCHANTĐỐI TÁC KẾT NỐI QR20330123';
        // $signature = '';
        // $success = openssl_sign($rawData,$signature, $privateKeyVietinbank, OPENSSL_ALGO_SHA256);
        // $signatureBase64 = base64_encode($signature);
        // dd($signatureBase64);

        return $verify === 1;
    }

    private function getParamRequest()
    {
        // Lấy dữ liệu gửi về từ MoMo hoặc nguồn khác
        $data = $this->params->request->all();
        return $data;
    }
    private function SignData($data)
    {

        $privateKey = openssl_pkey_get_private(file_get_contents($this->privateKeyPath));
        if (!$privateKey) {
            throw new \Exception("Không thể đọc private key của viện");
        }

        // Tạo chuỗi rawData theo yêu cầu
        $rawData = $data['requestId'] . $data['paymentStatus'];

        // Tạo chữ ký bằng HMAC-SHA256
        $signature = '';
        $success = openssl_sign($rawData,$signature, $privateKey, OPENSSL_ALGO_SHA256);

        // Base 64
        $signatureBase64 = base64_encode($signature);
        return $signatureBase64;
    }
}
