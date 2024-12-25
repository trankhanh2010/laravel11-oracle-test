<?php

namespace App\Services\Transaction;

use App\DTOs\ServiceReqPaymentDTO;
use App\Repositories\TestServiceReqListVViewRepository;
use App\Repositories\TestServiceTypeListVViewRepository;
use App\Repositories\TreatmentMoMoPaymentsRepository;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class ServiceReqPaymentService
{
    protected $testServiceReqListVViewRepository;
    protected $testServiceTypeListVViewRepository;
    protected $treatmentMoMoPaymentsRepository;
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
        TestServiceReqListVViewRepository $testServiceReqListVViewRepository,
        TestServiceTypeListVViewRepository $testServiceTypeListVViewRepository,
        TreatmentMoMoPaymentsRepository $treatmentMoMoPaymentsRepository,
    ) {
        $this->testServiceReqListVViewRepository = $testServiceReqListVViewRepository;
        $this->testServiceTypeListVViewRepository = $testServiceTypeListVViewRepository;
        $this->treatmentMoMoPaymentsRepository = $treatmentMoMoPaymentsRepository;

        $this->partnerCode = config('database')['connections']['momo']['momo_partner_code'];
        $this->accessKey = config('database')['connections']['momo']['momo_access_key'];
        $this->secretKey = config('database')['connections']['momo']['momo_secret_key'];
        $this->endpointCreatePayment = config('database')['connections']['momo']['momo_endpoint_create_payment'];
        $this->endpointCheckTransaction = config('database')['connections']['momo']['momo_endpoint_check_transaction'];
        $this->returnUrl = config('database')['connections']['momo']['momo_return_url'];
        $this->notifyUrl = config('database')['connections']['momo']['momo_notify_url'];
    }
    public function withParams(ServiceReqPaymentDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    protected function getTreatmentData()
    {
        $data = $this->testServiceReqListVViewRepository->applyJoins();
        if ($this->params->treatmentCode) {
            $data = $this->testServiceReqListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
        }
        return $data->first();
    }

    protected function calculateCosts($treatmentId)
    {
        $listServiceType = $this->testServiceTypeListVViewRepository->applyJoins();
        $listServiceType = $this->testServiceTypeListVViewRepository->applyTreatmentIdFilter($listServiceType, $treatmentId)->get();

        return [
            'totalVirPrice' => $listServiceType->sum('vir_total_price'),
            'totalHeinPrice' => $listServiceType->sum('vir_total_hein_price'),
            'totalPatientPrice' => $listServiceType->sum('vir_total_patient_price'),
        ];
    }

    protected function generateTransactionInfo($data, $costs)
    {
        $orderId = Str::uuid();
        $requestId = Str::uuid();
        $orderInfo = "Tong chi phi: " . $costs['totalVirPrice'] . $this->unit
            . "; BHYT thanh toan: " . $costs['totalHeinPrice'] . $this->unit
            . "; BN phai thanh toan: " . $costs['totalPatientPrice'] . $this->unit
            . "; Da thu: " . $data->total_treatment_bill_amount . $this->unit
            . "; BN can nop them: " . $data->fee_add . $this->unit;

        return [
            'orderId' => $orderId,
            'requestId' => $requestId,
            'amount' => $data->fee_add,
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

    public function handleCreatePayment()
    {
        try {
            $data = $this->getTreatmentData();
            if (!$data || $data->fee_add <= 0) {
                return ['data' => ['success' => false]];
            }

            $costs = $this->calculateCosts($data->treatment_id);
            $transactionInfo = $this->generateTransactionInfo($data, $costs);

            if ($this->params->paymentMethod == 'MoMo') {
                [$requestType, $signature] = $this->generateSignature($this->params->paymentOption, $transactionInfo);
                $check = $this->treatmentMoMoPaymentsRepository->check($data->treatment_code, $requestType);

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
                $this->treatmentMoMoPaymentsRepository->create(
                    $data->treatment_code,
                    $dataReturn['orderId'],
                    $dataReturn['requestId'],
                    $dataReturn['amount'],
                    '1000',
                    $dataReturn['deeplink'] ?? '',
                    $dataReturn['payUrl'] ?? '',
                    $requestType,
                    $dataReturn['qrCodeUrl'] ?? ''
                );
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
