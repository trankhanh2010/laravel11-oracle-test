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
use App\Repositories\ConfigRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\TransReqRepository;
use Exception;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use IntlChar;
use Normalizer;

use function Laravel\Prompts\error;

class VietinbankService
{
    protected string $apiUrl;
    protected string $merchantId;
    protected string $secretKey;
    protected string $clientId;
    protected $publicKeyVietinbankConfirmPath;
    protected $publicKeyVietinbankInqDetailPath;
    protected $privateKeyPath;
    protected $urlInqDetailTrans;
    protected $transactionRepository;
    protected $transReqRepository;
    protected $configRepository;
    protected $params;
    private $VND = "VND";
    private $EMPTY = "";
    protected $merchantCode;
    protected $merchantCC;
    protected $merchantName;
    protected $terminalId;
    protected $storeId;
    protected $providerId;
    protected $expTimeQrVtb;

    public function __construct(
        TransactionRepository $transactionRepository,
        TransReqRepository $transReqRepository,
        ConfigRepository $configRepository,
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->transReqRepository = $transReqRepository;
        $this->configRepository = $configRepository;

        $this->apiUrl = config('database')['connections']['vietinbank']['vietinbank_api_url'];
        $this->merchantId = config('database')['connections']['vietinbank']['vietinbank_merchant_id'];
        $this->secretKey = config('database')['connections']['vietinbank']['vietinbank_secret_key'];
        $this->clientId = config('database')['connections']['vietinbank']['vietinbank_client_id'];
        $this->publicKeyVietinbankConfirmPath = config('database')['connections']['vietinbank']['public_key_vietinbank_confirm_path'];
        $this->publicKeyVietinbankInqDetailPath = config('database')['connections']['vietinbank']['public_key_vietinbank_inq_detail_path'];
        $this->privateKeyPath = config('database')['connections']['vietinbank']['private_key_bvxa_path'];
        $this->urlInqDetailTrans = config('database')['connections']['vietinbank']['vietinbank_api_url_inq_detail_trans'];
        $this->merchantCode = config('database')['connections']['vietinbank']['merchant_code'];
        $this->merchantCC = config('database')['connections']['vietinbank']['merchant_code'];
        $this->merchantName = config('database')['connections']['vietinbank']['merchant_name'];
        $this->terminalId = config('database')['connections']['vietinbank']['terminal_id'];
        $this->storeId = config('database')['connections']['vietinbank']['store_id'];
        $this->providerId = config('database')['connections']['vietinbank']['provider_id'];
        $this->expTimeQrVtb = (int) $this->configRepository->getHisTranReqExpiredTimeOption();
    }
    public function withParams(VietinbankDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    /**
     * Tạo giao dịch QR Code
     */
    public function createTransactionQrCode($dataTreatment, $dataTransHis, $currentLoginname = '')
    {
        // $dataTreatment['orderId'] = 'baotri4';
        // $dataTreatment['orderInfo'] = 'bao tri';
        // dd($dataTreatment);
        $data = new RequestCreateQrcode();
        $data->masterMerCode = "970489";
        $data->merchantCode = $this->merchantCode;
        $data->merchantType = "01";
        $data->merchantName = $this->merchantName;
        $data->terminalId = $this->terminalId; // Mã này cố định
        $data->ccy = "704"; // Mã này cố định
        $data->desc = $dataTreatment['orderInfo'];
        $data->txnId = $dataTreatment['orderId'];
        $data->amount = $dataTreatment['amount'];
        $data->payType = QRCode::PAY_TYPE_01;
        $data->countryCode = "VN";
        //data.customerID = "Nhập thông tin mã khách hàng của Mobifone tại đây"; // 


        $data->merchantCity = "VINHLONG";
        $data->terminalName = $this->storeId;
        $data->merchantCC  = $this->merchantCC;


        $niceAddtionalData = $this->removeDiacritics($data->desc);
        $data->desc = $niceAddtionalData;
        // Thời gian hết hạn giao dịch  phút
        $timeTransaction = Carbon::now();
        $timeTransaction2 = $timeTransaction->copy();
        $timeTransaction3 = $timeTransaction->copy()->format('YmdHis');
        $data->expDate = $timeTransaction->addMinutes($this->expTimeQrVtb)->format('ymdHi');
        $expDate = $timeTransaction2->addMinutes($this->expTimeQrVtb)->format('YmdHis');
        // dd($data);
        $req = $this->makeRequestToSystem($data, true, "");
        $pk = new QrPack();
        $qrData = $pk->pack($req->qrBean, "")->qrData;
        // check và thêm mới thông tin trans_req cho his_trans
        $qrData = $this->checkAndAddInfoTransReq($data, $timeTransaction3, $expDate, $qrData, $dataTransHis, $currentLoginname);
        // dd($qrData);
        // $qrImageUrl = "http://chart.apis.google.com/chart?chs=500x500&cht=qr&chl=" . $qrData . "&choe=UTF-8";
        // $apiURL = "https://api.qrserver.com/v1/create-qr-code/";
        // $size = "300x300"; // Kích thước mã QR code

        // $qrImageUrl = $apiURL . "?size=" . $size . "&data=" . $qrData;        
        return base64_encode($qrData);
    }
    public function checkAndAddInfoTransReq($dataCreateQr, $timeTransaction, $expDate, $qrData, $dataTransHis, $currentLoginname)
    {
        // check xem có trans_req không, nếu không thì tạo trans_req mới và add vào his_trans
        if (!$dataTransHis['trans_req_id']) {
            $dataTransReq = $this->createTransReqVtb($dataCreateQr, $timeTransaction, $expDate, $qrData, $dataTransHis, $currentLoginname);
            $dataTransHis->update([
                'trans_req_id' => $dataTransReq['id'],
            ]);
            return $qrData;
        }

        // nếu có => trans_req của his_tran này, check xem có còn hạn k, nếu còn => dùng dataQr của trans_req, nếu không thì tạo trans_req mới và add vào his_trans
        $dataTransReq = $this->transReqRepository->getById($dataTransHis['trans_req_id']);

        // Nếu k khớp tiền
        if ($dataTransReq['amount'] != $dataTransHis['amount']) {
            $dataTransReq = $this->createTransReqVtb($dataCreateQr, $timeTransaction, $expDate, $qrData, $dataTransHis, $currentLoginname);
            $dataTransHis->update([
                'trans_req_id' => $dataTransReq['id'],
            ]);
            return $qrData;
        }

        // Nếu k có hạn
        if (empty($dataTransReq['expired_time'])) {
            $dataTransReq = $this->createTransReqVtb($dataCreateQr, $timeTransaction, $expDate, $qrData, $dataTransHis, $currentLoginname);
            $dataTransHis->update([
                'trans_req_id' => $dataTransReq['id'],
            ]);
            return $qrData;
        }

        $expiredAt = Carbon::createFromFormat('YmdHis', $dataTransReq['expired_time']);
        if (now()->lt($expiredAt)) {
            // Còn hạn
            return $dataTransReq['qr_text'];
        }
        // Hết hạn
        $dataTransReq = $this->createTransReqVtb($dataCreateQr, $timeTransaction, $expDate, $qrData, $dataTransHis, $currentLoginname);
        $dataTransHis->update([
            'trans_req_id' => $dataTransReq['id'],
        ]);
        return $qrData;
    }

    public function createTransReqVtb($dataCreateQr, $timeTransaction, $expDate, $qrData, $dataTransHis, $currentLoginname)
    {
        $request = (object) [
            'amount' => $dataTransHis->amount,
            'treatment_id' => $dataTransHis->treatment_id,
            'bank_json_data' => '',
            'trans_req_code' => $dataTransHis->transaction_code,
            'bank_message'  => '',
            'tdl_treatment_code' => $dataTransHis->tdl_treatment_code,
            'tdl_patient_code' => $dataTransHis->tdl_patient_code,
            'tdl_patient_name' => $dataTransHis->tdl_patient_name,
            'expired_time' => $expDate,
            'bank'  => '',
            'request_id'  => '',
            'qr_text'  => $qrData,
            'qr_time'  => $timeTransaction,
            'bank_trans_code'  => '',
            'merchant_code'  => $this->merchantCode,
            'terminal_id'  => $this->terminalId,
        ];
        if($currentLoginname == null){
            return $this->transReqRepository->createTransReqQrVtbByNguoiDung($request, 'MOS_v2', 'MOS_v2');
        }else{
            return $this->transReqRepository->createTransReqQrVtbByThuNgan($request, 'MOS_v2', 'MOS_v2', $currentLoginname);
        }
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
            // Thời gian hết hạn giao dịch  phút
            // $timeTransaction = Carbon::now();
            // $expDate = $timeTransaction->addMinutes($this->expTimeQrVtb)->format('ymdHi');
            $expDate = $request->expDate;
            // $expDate = "";

            if (QRCode::PAY_TYPE_01 === $request->payType) {
                if ($this->VND === $request->ccy) {
                    $request->ccy = ServiceConfig::CCY;
                }
                if (!empty($request->desc) && strlen($request->desc) > 19) {
                    $purpose = substr($request->desc, 0, length: 19);
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
            $addinalBean->expDate = $expDate;
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
    public function generateParam($requestId, $paymentStatus)
    {
        return [
            'requestId' => $requestId,
            'paymentStatus' => $paymentStatus,
            'signature' => $this->SignData([
                'requestId' => $requestId,
                'paymentStatus' => $paymentStatus,
            ]),
        ];
    }

    public function handleConfirmTransaction()
    {
        try {
            $data = $this->getParamRequest();
            // Log::error($data);
            //test
            if ($data['orderId'] == 'datt') {
                return $this->generateParam($data['requestId'], '01');
            }
            if ($data['orderId'] == 'khople') {
                return $this->generateParam($data['requestId'], '02');
            }
            if ($data['orderId'] == 'ktimthay') {
                return $this->generateParam($data['requestId'], '03');
            }
            if ($data['orderId'] == 'saitien') {
                return $this->generateParam($data['requestId'], '04');
            }
            if ($data['orderId'] == 'hethan') {
                return $this->generateParam($data['requestId'], '05');
            }
            if (($data['orderId'] == 'baotri') || ($data['orderId'] == 'baotri2') || ($data['orderId'] == 'baotri3') || ($data['orderId'] == 'baotri4') || ($data['orderId'] == 'baotri5') || ($data['orderId'] == 'baotri6') || ($data['orderId'] == 'baotri7')) {
                return $this->generateParam($data['requestId'], '09');
            }
            // Xác minh chữ ký 
            $isVerify = $this->verifyVietinbankSignature($data);
            // Nếu không đúng chữ ký
            if (!$isVerify) {
                return $this->generateParam($data['requestId'], '02');
            }
            // Nếu đúng và mã khác 00
            if ($data['statusCode'] !== '00') {
                return $this->generateParam($data['requestId'], '02');
            }
            // Nếu đúng và mã = 00 và có transVietinbank(có is_cancel =1) thì cập nhật is_cancel = 0 cho transaction trong DB
            $dataTransactionVietinbank = $this->transactionRepository->getTransactionVietinBank($data);
            // Nếu k tìm thấy giao dịch
            if (!$dataTransactionVietinbank) {
                return $this->generateParam($data['requestId'], '03');
            }
            // Nếu số tiền giao dịch không khớp
            if ($dataTransactionVietinbank['amount'] != $data['amount']) {
                return $this->generateParam($data['requestId'], '04');
            }
            // Nếu hết hạn giao dịch
            $transactionTime = Carbon::createFromFormat('YmdHis', $dataTransactionVietinbank['transaction_time']);
            $expireTime = $transactionTime->copy()->addMinutes($this->expTimeQrVtb);
            $requestTime = Carbon::createFromFormat('YmdHis', $data['transactionDate']);
            // So sánh
            if ($expireTime->lessThanOrEqualTo($requestTime)) {
                return $this->generateParam($data['requestId'], '05');
            }
            // Nếu đã thanh toán cho giao dịch này
            if (($dataTransactionVietinbank['is_cancel'] == 0) || (!$dataTransactionVietinbank['is_cancel'])) {
                return $this->generateParam($data['requestId'], '01');
            }

            // Cập nhật trans_req và transaction
            DB::connection('oracle_his')->transaction(function () use ($data, $dataTransactionVietinbank) {
                $sttUpdate = $this->transactionRepository->updateTransactionVietinBank($dataTransactionVietinbank);
                // Nếu cập nhật không thành công
                if (!$sttUpdate) {
                    return $this->generateParam($data['requestId'], '02');
                }

                // Cập nhật message và stt cho trans_req
                $dataTransReq = $this->transReqRepository->getById($dataTransactionVietinbank['trans_req_id']);
                $dataTransReq->update([
                    'trans_req_stt_id' => 2,
                    'bank_message' => $data['statusMessage'],
                    'bank_json_data' => json_encode($data),
                ]);
                // test lỗi
                // throw new Exception("Lỗi cập nhật db");
            });
            // Cập nhật thành công
            return $this->generateParam($data['requestId'], '00');
        } catch (Exception $e) {
            return $this->generateParam($data['requestId'], '09');
        }
    }
    public function handleInqDetailTrans()
    {
        $param = $this->getParamRequest();

        $requestId = $param['requestId'] ?? Carbon::now()->timestamp;
        $providerId = $param['providerId'] ?? $this->providerId;
        $merchantId = $param['merchantId'] ?? $this->merchantId;
        $terminalId = $param['terminalId'] ?? $this->terminalId;
        $payDate =  $param['payDate'] ?? '';
        $orderId = $param['orderId'] ?? '';
        $hostrefno = $param['hostrefno'] ?? ''; // Để rỗng
        $addInfor1 = $param['addInfor1'] ?? '';
        $addInfor2 = $param['addInfor2'] ?? '';
        $addInfor3 = $param['addInfor3'] ?? '';
        $clientIP = $param['clientIP'] ?? '';
        $channel = $param['channel'] ?? 'MOBILE';
        $version = $param['version'] ?? '1.0';
        $language = $param['language'] ?? 'vi';

        $paramRequest = [
            'requestId' => $requestId,
            'providerId' => $providerId,
            'merchantId' => $merchantId,
            'terminalId' => $terminalId,
            'payDate' => $payDate,
            'orderId' => $orderId,
            'hostrefno' => $hostrefno,
            'addInfor1' => $addInfor1,
            'addInfor2' => $addInfor2,
            'addInfor3' => $addInfor3,
            'clientIP' => $clientIP,
            'channel' => $channel,
            'version' => $version,
            'language' => $language,
            'signature' => $this->SignDataInqDetailTrans([
                'requestId' => $requestId,
                'providerId' => $providerId,
                'merchantId' => $merchantId,
                'terminalId' => $terminalId,
                'payDate' => $payDate,
                'transTime' => '',
                'channel' => $channel,
                'version' => $version,
                'clientIP' => $clientIP,
                'language' => $language,
            ])
        ];

        try {
            $client = new Client();
            $response = $client->post(
                $this->urlInqDetailTrans,
                [
                    'headers' => [
                        'x-ibm-client-id' => $this->clientId,
                        'x-ibm-client-secret' => $this->secretKey,
                    ],
                    'json' => $paramRequest
                ]
            );

            $data = json_decode($response->getBody(), true);
            // Log::error($data);
        } catch (\Exception $e) {
            // Xử lý lỗi nếu gọi API thất bại
            throw new \Exception('Lỗi khi gọi api vấn tin Vietinbank ');
        }
        // dd($data);

        // Nếu mã khác 00
        if ($data['status']['code'] !== '00') {
            return $data;
        } else {
            // Xác minh chữ ký 
            $isVerify = $this->verifyVietinbankSignatureInqDetailTrans($data);
            if ($isVerify) {
                return $data;
            } else {
                // Nếu không đúng chữ ký
                throw new \Exception('Lỗi khi gọi api vấn tin Vietinbank: Sai chữ ký ');
            }
        }
    }

    private function verifyVietinbankSignature($data)
    {
        $publicKeyVietinbank = openssl_pkey_get_public(file_get_contents($this->publicKeyVietinbankConfirmPath));
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
        // $rawData = $data['requestId'] . $data['merchantId'] . $data['orderId'] . $data['productId'];
        // $signature = '';
        // $success = openssl_sign($rawData,$signature, $privateKeyVietinbank, OPENSSL_ALGO_SHA256);
        // $signatureBase64 = base64_encode($signature);
        // dd($signatureBase64);
        return $verify === 1;
    }
    private function verifyVietinbankSignatureInqDetailTrans($data)
    {
        $publicKeyVietinbank = openssl_pkey_get_public(file_get_contents($this->publicKeyVietinbankInqDetailPath));
        if (!$publicKeyVietinbank) {
            throw new \Exception("Không thể đọc public key VietinBank");
        }
        $signatureDecode = base64_decode($data['signature'] ?? '') ?? '';
        // Tạo chuỗi rawData theo yêu cầu
        $rawData = $data['requestId'] . $data['providerId'] . $data['merchantId'] . $data['status']['code'];

        // Tạo chữ ký bằng HMAC-SHA256
        $verify = openssl_verify($rawData, $signatureDecode, $publicKeyVietinbank, OPENSSL_ALGO_SHA256);
        //  So sánh chữ ký
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
        $success = openssl_sign($rawData, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        // Base 64
        $signatureBase64 = base64_encode($signature);
        return $signatureBase64;
    }
    private function SignDataInqDetailTrans($data)
    {

        $privateKey = openssl_pkey_get_private(file_get_contents($this->privateKeyPath));
        if (!$privateKey) {
            throw new \Exception("Không thể đọc private key của viện");
        }

        // Tạo chuỗi rawData theo yêu cầu
        $rawData = $data['requestId']
            . $data['providerId']
            . $data['merchantId']
            . $data['terminalId']
            . $data['payDate']
            . $data['transTime']
            . $data['channel']
            . $data['version']
            . $data['clientIP']
            . $data['language'];
        // Tạo chữ ký bằng HMAC-SHA256
        $signature = '';
        $success = openssl_sign($rawData, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        // Base 64
        $signatureBase64 = base64_encode($signature);
        return $signatureBase64;
    }
}
