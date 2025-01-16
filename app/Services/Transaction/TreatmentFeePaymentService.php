<?php

namespace App\Services\Transaction;

use App\DTOs\TreatmentFeePaymentDTO;
use App\Repositories\SereServMomoPaymentsRepository;
use App\Repositories\TreatmentFeeListVViewRepository;
use App\Repositories\TestServiceTypeListVViewRepository;
use App\Repositories\TreatmentFeeDetailVViewRepository;
use App\Repositories\TreatmentMoMoPaymentsRepository;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class TreatmentFeePaymentService
{
    protected $treatmentFeeListVViewRepository;
    protected $testServiceTypeListVViewRepository;
    protected $treatmentFeeDetailVViewRepository;
    protected $treatmentMoMoPaymentsRepository;
    protected $sereServMomoPayments;
    protected $params;
    protected $unit = ' VNĐ';
    protected $partnerCode;
    protected $accessKey;
    protected $secretKey;
    protected $endpointCreatePayment;
    protected $endpointCheckTransaction;
    protected $returnUrl;
    protected $notifyUrl;
    public function __construct(
        TreatmentFeeListVViewRepository $treatmentFeeListVViewRepository,
        TestServiceTypeListVViewRepository $testServiceTypeListVViewRepository,
        TreatmentFeeDetailVViewRepository $treatmentFeeDetailVViewRepository,
        TreatmentMoMoPaymentsRepository $treatmentMoMoPaymentsRepository,
        SereServMomoPaymentsRepository $sereServMomoPayments,
    ) {
        $this->treatmentFeeListVViewRepository = $treatmentFeeListVViewRepository;
        $this->testServiceTypeListVViewRepository = $testServiceTypeListVViewRepository;
        $this->treatmentFeeDetailVViewRepository = $treatmentFeeDetailVViewRepository;
        $this->treatmentMoMoPaymentsRepository = $treatmentMoMoPaymentsRepository;
        $this->sereServMomoPayments = $sereServMomoPayments;

        $this->partnerCode = config('database')['connections']['momo']['momo_partner_code'];
        $this->accessKey = config('database')['connections']['momo']['momo_access_key'];
        $this->secretKey = config('database')['connections']['momo']['momo_secret_key'];
        $this->endpointCreatePayment = config('database')['connections']['momo']['momo_endpoint_create_payment'];
        $this->endpointCheckTransaction = config('database')['connections']['momo']['momo_endpoint_check_transaction'];

    }
    public function withParams(TreatmentFeePaymentDTO $params)
    {
        $this->params = $params;
        // Lấy URL IPN tương ứng với loại giao dịch
        if($this->params->transactionTypeCode == 'TT'){
            $this->returnUrl = config('database')['connections']['momo']['momo_return_url_thanh_toan'];
            $this->notifyUrl = config('database')['connections']['momo']['momo_notify_url_thanh_toan'];
        }
        return $this;
    }
    protected function getTreatmentFeeData()
    {
        $data = $this->treatmentFeeDetailVViewRepository->applyJoins();
        if ($this->params->treatmentCode) {
            $data = $this->treatmentFeeDetailVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
        }
        return $data->first();
    }

    // protected function calculateCosts($treatmentId)
    // {
    //     $listServiceType = $this->testServiceTypeListVViewRepository->applyJoins();
    //     $listServiceType = $this->testServiceTypeListVViewRepository->applyTreatmentIdFilter($listServiceType, $treatmentId)->get();

    //     return [
    //         'totalVirPrice' => $listServiceType->sum('vir_total_price'),
    //         'totalHeinPrice' => $listServiceType->sum('vir_total_hein_price'),
    //         'totalPatientPrice' => $listServiceType->sum('vir_total_patient_price'),
    //     ];
    // }

    protected function generateTransactionInfo($data, $costs)
    {
        $orderId = Str::uuid();
        $requestId = Str::uuid();
        $orderInfo = "Tong chi phi: " . $data->total_price . $this->unit
            . "; BHYT thanh toan: " . $data->total_hein_price . $this->unit
            . "; BN phai thanh toan: " . $data->total_patient_price . $this->unit
            . "; Da thu: " . $data->da_thu . $this->unit
            . "; BN can nop them: " . $data->fee . $this->unit;

        return [
            'orderId' => $orderId,
            'requestId' => $requestId,
            'amount' => $data->fee,
            'orderInfo' => $orderInfo,
            'extraData' => '',
        ];
    }

    protected function generateSignature($paymentOption, $transactionInfo)
    {
        switch ($paymentOption) {
            case 'ThanhToanQRCode':
                $requestType = 'captureWallet';
                break;
            case 'ThanhToanTheQuocTe':
                $requestType = 'payWithCC';
                break;
            case 'ThanhToanTheATMNoiDia':
                $requestType = 'payWithATM';
                break;
            default:
                throw new \Exception('Invalid payment option');
        }

        $rawSignature = "accessKey={$this->accessKey}&amount={$transactionInfo['amount']}&extraData={$transactionInfo['extraData']}&ipnUrl={$this->notifyUrl}&orderId={$transactionInfo['orderId']}&orderInfo={$transactionInfo['orderInfo']}&partnerCode={$this->partnerCode}&redirectUrl={$this->returnUrl}&requestId={$transactionInfo['requestId']}&requestType=$requestType";
        $signature = hash_hmac('sha256', $rawSignature, $this->secretKey);

        return [$requestType, $signature];
    }

    protected function sendPaymentRequest($dataMoMo)
    {
        try {
            $client = new Client();
            $response = $client->post($this->endpointCreatePayment, ['json' => $dataMoMo]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            throw new \Exception('Error sending payment request to MoMo');
        }
    }

    protected function formatResponseFromRepository($check, $amount, $orderInfo)
    {
        return [
            'success' => true,
            'deeplink' => $check->deep_link ?? '',
            'qrCodeUrl' => $check->qr_code_url ?? '',
            'payUrl' => $check->pay_url ?? '',
            'orderId' => $check->order_id,
            'amount' => $amount,
            'orderInfo' => $orderInfo,
            'requestId' => $check->request_id,
        ];
    }

    protected function formatResponseFromMoMo(array $response, array $transactionInfo)
    {
        $dataReturn = [
            'success' => true,
            'orderId' => $transactionInfo['orderId'],
            'amount' => $transactionInfo['amount'],
            'orderInfo' => $transactionInfo['orderInfo'],
            'requestId' => $transactionInfo['requestId'],
        ];
        if (isset($response['qrCodeUrl'])) {
            $dataReturn['qrCodeUrl'] = $response['qrCodeUrl'];
        }
        if (isset($response['payUrl'])) {
            $dataReturn['payUrl'] = $response['payUrl'];
        }
        if (isset($response['deeplink'])) {
            $dataReturn['deeplink'] = $response['deeplink'];
        }
        return $dataReturn;
    }

    protected function checkTimeLiveLinkPaymentMoMo($treatment_code, $requestType, $fee){
        $dataReturn = null;
        // Nếu là giao dịch thanh toán
        if($this->params->transactionTypeCode = 'TT'){
            $dataDB = $this->treatmentMoMoPaymentsRepository->checkTT($treatment_code, $requestType, $fee);
        }
        // Nếu có tồn tại trong DB và check bên MoMo ra mã 1000 thì trả về, k thì trả về null
        if($dataDB){
            $dataMoMo = $this->checkTransactionStatus($dataDB->order_id, $dataDB->request_id);
            if($dataMoMo['data']['resultCode'] != 1000){
                $dataReturn = null;
                $this->treatmentMoMoPaymentsRepository->update($dataMoMo['data']);
            }else{
                $dataReturn = $dataDB;
            }
        }
        return $dataReturn;
    }
    protected function getListSereServ($treatmentId){
        $data = $this->testServiceTypeListVViewRepository->applyJoins();
        $data = $this->testServiceTypeListVViewRepository->applyTreatmentIdFilter($data, $treatmentId);
        $data = $this->testServiceTypeListVViewRepository->applyChuaThanhToanFilter($data);
        $data = $this->testServiceTypeListVViewRepository->applyCoPhiFilter($data);
        $data = $data->get();
        return $data;
    }
    public function handleCreatePayment()
    {
        try {
            $data = $this->getTreatmentFeeData();
            if (!$data || $data->fee <= 0) {
                return ['data' => ['success' => false]];
            }

            // $costs = $this->calculateCosts($data->treatment_id);
            $costs = null;
            $transactionInfo = $this->generateTransactionInfo($data, $costs);

            if ($this->params->paymentMethod == 'MoMo') {
                [$requestType, $signature] = $this->generateSignature($this->params->paymentOption, $transactionInfo);
                $check = $this->checkTimeLiveLinkPaymentMoMo($data->treatment_code, $requestType, $data->fee);
                if ($check) {
                    return ['data' => $this->formatResponseFromRepository($check, $transactionInfo['amount'], $transactionInfo['orderInfo'])];
                }

                $dataMoMo = array_merge($transactionInfo, [
                    'partnerCode' => $this->partnerCode,
                    'accessKey' => $this->accessKey,
                    'redirectUrl' => $this->returnUrl,
                    'ipnUrl' => $this->notifyUrl,
                    'requestType' => $requestType,
                    'signature' => $signature,
                    'lang' => 'vi',
                ]);

                $response = $this->sendPaymentRequest($dataMoMo);
                $dataReturn = $this->formatResponseFromMoMo($response, $transactionInfo);
                // Lưu thông tin vào database
                $dataCreate =                     
                [
                    'treatmentCode' => $data->treatment_code,
                    'treatmentId' => $data->id,
                    'orderId' => $dataReturn['orderId'],
                    'requestId' => $dataReturn['requestId'],
                    'amount' => $dataReturn['amount'],
                    'resultCode' => 1000,
                    'deeplink' => $dataReturn['deeplink'] ?? '',
                    'payUrl' => $dataReturn['payUrl'] ?? '',
                    'requestType' => $requestType,
                    'qrCodeUrl' => $dataReturn['qrCodeUrl'] ?? '',
                    'transactionTypeCode' => $this->params->transactionTypeCode ?? '',
                ];
                // Tạo payment momo
                $treatmentMomoPayments = $this->treatmentMoMoPaymentsRepository->create($dataCreate, $this->params->appCreator, $this->params->appModifier);

                // Nếu là giao dịch thanh toán
                if($this->params->transactionTypeCode == 'TT'){
                    // Tạo danh sách dịch vụ cho payment
                    $listSereServ = $this->getListSereServ($data->id);
                    foreach($listSereServ as $key => $item){
                        $dataSereServCreate = [
                            'sere_serv_id' => $item->id,
                            'treatment_momo_payments_id' => $treatmentMomoPayments->id,
                            'transaction_type_code' => $this->params->transactionTypeCode,
                        ];
                        $this->sereServMomoPayments->create($dataSereServCreate, $this->params->appCreator, $this->params->appModifier);
                    }
                }
                return ['data' => $dataReturn];
            }

            return ['data' => ['success' => false]];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_req_list_v_view'], $e);
        }
    }

    public function checkTransactionStatus($orderId, $requestId = 0)
    {
        if(!$requestId){
            $requestId = $this->treatmentMoMoPaymentsRepository->getByOrderId($orderId)->request_id ?? '0';
        }    
        // Tạo chữ ký (signature)
        $rawSignature = "accessKey=$this->accessKey&orderId=$orderId&partnerCode=$this->partnerCode&requestId=$requestId";
        $signature = hash_hmac('sha256', $rawSignature, $this->secretKey);

        // Tạo dữ liệu gửi đến API MoMo
        $data = [
            'partnerCode' => $this->partnerCode,
            'accessKey' => $this->accessKey,
            'requestId' => $requestId,
            'orderId' => $orderId,
            'signature' => $signature,
            'lang' => 'vi',
        ];
        // Gửi request đến API MoMo
        try {
            $client = new Client();
            $response = $client->post($this->endpointCheckTransaction, ['json' => $data]);
            $body = json_decode($response->getBody(), true); // Chuyển kết quả thành mảng
            return ['data' => $body];
        } catch (\Exception $e) {
            return response()->json([
                'status' => '500',
                'message' => 'Lỗi hệ thống: ',
            ], 500);
        }
    }
}
