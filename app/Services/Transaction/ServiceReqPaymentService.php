<?php

namespace App\Services\Transaction;

use App\DTOs\ServiceReqPaymentDTO;
use App\Repositories\TestServiceReqListVViewRepository;
use GuzzleHttp\Client;

class ServiceReqPaymentService
{
    protected $testServiceReqListVViewRepository;
    protected $params;
    public function __construct(TestServiceReqListVViewRepository $testServiceReqListVViewRepository)
    {
        $this->testServiceReqListVViewRepository = $testServiceReqListVViewRepository;
    }
    public function withParams(ServiceReqPaymentDTO $params)
    {
        $this->params = $params;
        return $this;
    }

    public function handleCreatePayment()
    {
        try {
            $data = $this->testServiceReqListVViewRepository->applyJoins();
            // $data = $this->testServiceReqListVViewRepository->applyWith($data);
            if ($this->params->treatmentCode || $this->params->patientCode) {
                if ($this->params->treatmentCode) {
                    $data = $this->testServiceReqListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
                }
                if ($this->params->patientCode) {
                    $data = $this->testServiceReqListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
                }
            }
            $data = $data->first();
            if ($data->fee_add > 0) {
                // Lấy thông tin từ .env
                $partnerCode = env('MOMO_PARTNER_CODE');
                $accessKey = env('MOMO_ACCESS_KEY');
                $secretKey = env('MOMO_SECRET_KEY');
                $endpoint = env('MOMO_ENDPOINT');
                $returnUrl = env('MOMO_RETURN_URL');
                $notifyUrl = env('MOMO_NOTIFY_URL');

                // Thông tin giao dịch
                $orderId = 'order_' . time(); // Mã đơn hàng
                $requestId = 'req_' . time(); // Mã yêu cầu
                $amount = $data->fee_add; // Số tiền (VND)
                $orderInfo = "Bệnh nhân: ".$data->patient_name ." Mã bệnh nhân: ". $data->patient_code . " Mã điều trị: ".$data->treatment_code. " Số tiền: ".$data->fee_add;
                $extraData = ''; // Thông tin thêm, có thể để trống

                // Tạo chữ ký (signature)
                $rawSignature = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$notifyUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$returnUrl&requestId=$requestId&requestType=captureWallet";
                $signature = hash_hmac('sha256', $rawSignature, $secretKey);

                // Tạo dữ liệu gửi đến API MoMo
                $dataMoMo = [
                    'partnerCode' => $partnerCode,
                    'accessKey' => $accessKey,
                    'requestId' => $requestId,
                    'amount' => $amount,
                    'orderId' => $orderId,
                    'orderInfo' => $orderInfo,
                    'redirectUrl' => $returnUrl,
                    'ipnUrl' => $notifyUrl,
                    'extraData' => $extraData,
                    'requestType' => 'captureWallet',
                    'signature' => $signature,
                ];

                // Gửi request đến MoMo
                try {
                    $client = new Client();
                    $response = $client->post($endpoint, ['json' => $dataMoMo]);
                    $body = json_decode($response->getBody(), true); // Chuyển kết quả thành mảng
                    // Kiểm tra nếu có URL QR code
                    if (isset($body['qrCodeUrl'])) {
                        $qrCodeUrl = $body['qrCodeUrl']; // Lấy URL mã QR
                        $payUrl = $body['payUrl']; // Lấy URL thanh toán
                        $deeplink = $body['deeplink']; // Lấy deeplink

                        $dataReturn['payUrl'] = $payUrl;
                        $dataReturn['qrCodeUrl'] = $qrCodeUrl;
                        $dataReturn['orderId'] = $orderId;
                        $dataReturn['amount'] = $amount;
                        $dataReturn['orderInfo'] = $orderInfo;

                    }
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Lỗi hệ thống'], 500);
                }
            }
            return ['data' => $dataReturn, 'count' => null];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_req_list_v_view'], $e);
        }
    }
}
