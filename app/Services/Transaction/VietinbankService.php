<?php

namespace App\Services\Transaction;

use App\DTOs\VietinbankDTO;
use Illuminate\Support\Facades\Http;
use App\Classes\Vietinbank\RequestCreateQrcode;
use App\Classes\Vietinbank\QrPack;
use App\Classes\Vietinbank\ReqToSystem;
use App\Classes\Vietinbank\QRBean;
use App\Classes\Vietinbank\QRAddtionalBean;
use App\Classes\Vietinbank\ServiceConfig;
use App\Classes\Vietinbank\QRCode;
use IntlChar;
use Normalizer;

class VietinbankService
{
    protected string $apiUrl;
    protected string $merchantId;
    protected string $secretKey;
    protected $publicKeyVietinbankPath;
    protected $privateKeyPath;
    protected $params;
    private $VND = "VND";
    private $EMPTY = "";

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
    public function createTransactionQrCode($dataTreatment)
    {
        // dd($dataTreatment);
        $data = new RequestCreateQrcode();
        $data->masterMerCode = "970489";
        $data->merchantCode = "VBI";
        $data->merchantType = "01";
        $data->merchantName = "Benh vien Xuyen A";
        $data->terminalId = "0001"; // Mã này cố định
        $data->ccy = "704"; // Mã này cố định
        $data->desc = $dataTreatment['order_info'];
        $data->txnId = $dataTreatment['order_id'];
        $data->amount = $dataTreatment['amount'];
        $data->payType = QRCode::PAY_TYPE_01;
        $data->countryCode = "VN";
        //data.customerID = "Nhập thông tin mã khách hàng của Mobifone tại đây"; // 
      
     
        $data->merchantCity = "VINHLONG";
        $data->terminalName = "Xuyen A Vinh Long";
        $data->merchantCC  = "4814";
        

        $niceAddtionalData = $this->removeDiacritics($data->desc);
        $data->desc = $niceAddtionalData;
        // dd($data);
        $req = $this->makeRequestToSystem($data, true, "");
        $pk = new QrPack();
        $qrData = $pk->pack($req->qrBean, "")->qrData;
        // dd($qrData);
        // $qrImageUrl = "http://chart.apis.google.com/chart?chs=500x500&cht=qr&chl=" . $qrData . "&choe=UTF-8";
        $apiURL = "https://api.qrserver.com/v1/create-qr-code/";
        $size = "300x300"; // Kích thước mã QR code
    
        $qrImageUrl = $apiURL . "?size=" . $size . "&data=" . $qrData;        
        return $qrImageUrl;
    }

    private function makeRequestToSystem(RequestCreateQrcode $req, bool $isInsert, string $tokenKey)
    {
        try {
            $isMobileApp = !$isInsert;
            $qrBean = $this->makeQRBean($req, $isInsert, $tokenKey);

            $reqToSystem = new ReqToSystem();
            $reqToSystem->apiID = "";
            $reqToSystem->urlFolder = "";
            $reqToSystem->tokenKey = $tokenKey;
            $reqToSystem->qrBean = $qrBean;
            $reqToSystem->payType = $req->payType;
            $reqToSystem->imageName = $req->imageName;
            $reqToSystem->productName = $req->productName;
            $reqToSystem->creator = $req->creator;
            $reqToSystem->ismobileApp = $isMobileApp;
            $reqToSystem->sphere = $req->sphere;

            return $reqToSystem;
        } catch (\Exception $ex) {
            return null;
        }
    }

    private function makeQRBean(RequestCreateQrcode $request, bool $isNotMobile, string $tokenKey)
    {
        try {

            $referenceID = "";
            $pointOfMethod = ServiceConfig::POINT_OF_METHOD_TINH;
            $purpose = $request->desc;
            $consumerID = "";
            $consumerAddress = "";
            $consumerMobile = "";
            $consumerEmail = "";

            if (QRCode::PAY_TYPE_01 === $request->payType) {
                if ($this->VND === $request->ccy) {
                    $request->ccy = ServiceConfig::CCY;
                }
                if (!empty($request->desc) && strlen($request->desc) > 44) {
                    $purpose = substr($request->desc, 0, 44);
                }
                $referenceID = QRCode::PAY_TYPE_01 . $request->txnId;
                $pointOfMethod = ServiceConfig::POINT_OF_METHOD_DONG;
            }

            $addinalBean = new QRAddtionalBean();
            $addinalBean->billNumber = $request->billNumber;
            $addinalBean->mobile = $request->mobile;
            $addinalBean->storeID = $request->terminalName;
            $addinalBean->loyaltyNumber = $this->EMPTY;
            $addinalBean->referenceID = $referenceID;
            $addinalBean->customerID = $consumerID;
            $addinalBean->purpose = $this->removeDiacritics($purpose);
            $addinalBean->expDate = $request->expDate;
            $addinalBean->terminalID = $request->terminalId;
            $addinalBean->consumerMobile = $consumerMobile;
            $addinalBean->consumerAddress = $consumerAddress;
            $addinalBean->consumerEmail = $consumerEmail;
            // Kiểm tra $purpose trước khi gọi removeDiacritics()
            if ($purpose !== null) {
                $addinalBean->purpose = $this->removeDiacritics($purpose);
            } else {
                $addinalBean->purpose = ""; // Gán giá trị mặc định nếu $purpose là null
            }

            $bean = new QRBean();
            $bean->payLoad = ServiceConfig::PAYLOAD_FORMAT_INDICATOR;
            $bean->pointOIMethod = $pointOfMethod;
            $bean->merchantCode = $request->merchantCode;
            $bean->masterMerchant = $request->masterMerCode;
            $bean->merchantCC = $request->merchantType;
            $bean->ccy = $request->ccy;
            $bean->amount = $request->amount;
            $bean->tipAndFee = $request->tipAndFee;
            $bean->fixedFee = $request->fixedFee;
            $bean->percentFee = $request->percentageFee;
            $bean->countryCode = $request->countryCode;
            $bean->merchantName = $request->merchantName;
            $bean->merchantCity = $request->merchantCity;
            $bean->pinCode = $request->pinCode;
            $bean->addtionalBean = $addinalBean;
            $bean->term = $request->termBill;

            return $bean;
        } catch (\Exception $ex) {
            return null;
        }
    }

    function removeDiacritics(string $text): string
    {
        if (empty(trim($text))) {
            return $text;
        }
    
        $normalizedString = Normalizer::normalize($text, Normalizer::FORM_D);
        $stringBuilder = '';
    
        for ($i = 0; $i < mb_strlen($normalizedString); $i++) {
            $char = mb_substr($normalizedString, $i, 1);
            $unicodeCategory = IntlChar::charType($char);
    
            if ($unicodeCategory != IntlChar::CHAR_CATEGORY_NON_SPACING_MARK) {
                $stringBuilder .= $char;
            }
        }
    
        return Normalizer::normalize($stringBuilder, Normalizer::FORM_C);
    }

    private function makeTokenKey(RequestCreateQrcode $req): string
    {
        $tokenKey = ServiceConfig::API_ID . $req->merchantCode;
        return $tokenKey;
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
