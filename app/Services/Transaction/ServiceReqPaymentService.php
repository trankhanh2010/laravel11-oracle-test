<?php

namespace App\Services\Transaction;

use App\DTOs\ServiceReqPaymentDTO;
use App\Repositories\TestServiceReqListVViewRepository;
use App\Repositories\TestServiceTypeListVViewRepository;
use GuzzleHttp\Client;

class ServiceReqPaymentService
{
    protected $testServiceReqListVViewRepository;
    protected $testServiceTypeListVViewRepository;
    protected $params;
    public function __construct(TestServiceReqListVViewRepository $testServiceReqListVViewRepository, TestServiceTypeListVViewRepository $testServiceTypeListVViewRepository)
    {
        $this->testServiceReqListVViewRepository = $testServiceReqListVViewRepository;
        $this->testServiceTypeListVViewRepository = $testServiceTypeListVViewRepository;
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
            // Nếu cần thanh toán thêm
            if ($data->fee_add > 0) {
                $listServiceType = $this->testServiceTypeListVViewRepository->applyJoins();
                $listServiceType = $this->testServiceTypeListVViewRepository->applyTreatmentIdFilter($listServiceType, $data->treatment_id)->get();
                $totalVirPrice =  $listServiceType->sum('vir_total_price');
                $totalHeinPrice =  $listServiceType->sum('vir_total_hein_price');
                $totalPatientPrice =  $listServiceType->sum('vir_total_patient_price');

                // Lấy thông tin từ .env
                $partnerCode = env('MOMO_PARTNER_CODE');
                $accessKey = env('MOMO_ACCESS_KEY');
                $secretKey = env('MOMO_SECRET_KEY');
                $endpoint = env('MOMO_ENDPOINT') . '/v2/gateway/api/create';
                $returnUrl = env('MOMO_RETURN_URL');
                $notifyUrl = env('MOMO_NOTIFY_URL');

                // Thông tin giao dịch
                $orderId = 'order_' . time(); // Mã đơn hàng
                $requestId = 'req_' . time(); // Mã yêu cầu
                $amount = $data->fee_add; // Số tiền (VND)
                $orderInfo = "Tong chi phi: ".$totalVirPrice
                ."; BHYT thanh toan: ".$totalHeinPrice
                ."; BN phai thanh toan: ".$totalPatientPrice
                ."; Da thu: ".$data->total_treatment_bill_amount
                ."; BN can nop them: ".$data->fee_add
                ;
                $extraData = ''; // Thông tin thêm, có thể để trống
                if ($this->params->paymentMethod == 'MoMo') {
                    switch ($this->params->paymentOption) {
                        case 'ThanhToanQRCode':
                            $requestType = 'captureWallet'; //Hình thức thanh toán
                            $rawSignature = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$notifyUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$returnUrl&requestId=$requestId&requestType=$requestType";
                            $signature = hash_hmac('sha256', $rawSignature, $secretKey);
                            break;
                        case 'ThanhToanTheQuocTe':
                            $requestType = 'payWithCC'; //Hình thức thanh toán
                            $rawSignature = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$notifyUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$returnUrl&requestId=$requestId&requestType=$requestType";
                            $signature = hash_hmac('sha256', $rawSignature, $secretKey);
                            break;
                        case 'ThanhToanTheATMNoiDia':
                            $requestType = 'payWithATM'; //Hình thức thanh toán
                            $rawSignature = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$notifyUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$returnUrl&requestId=$requestId&requestType=$requestType";
                            $signature = hash_hmac('sha256', $rawSignature, $secretKey);
                            break;
                        default:
                    }
                }
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
                    'requestType' => $requestType,
                    'signature' => $signature,
                    'lang' => 'vi',
                ];
                // Gửi request đến MoMo
                try {
                    $client = new Client();
                    $response = $client->post($endpoint, ['json' => $dataMoMo]);
                    $body = json_decode($response->getBody(), true); // Chuyển kết quả thành mảng
                    // Kiểm tra nếu có response
                    if ($this->params->paymentMethod == 'MoMo') {
                        if ($this->params->paymentOption == 'ThanhToanQRCode') {
                            if (isset($body['qrCodeUrl'])) {
                                $dataReturn['success'] = true;

                                $qrCodeUrl = $body['qrCodeUrl'] ?? ""; // Lấy URL mã QR
                                $payUrl = $body['payUrl']; // Lấy URL thanh toán
                                $deeplink = $body['deeplink'] ?? ""; // Lấy deeplink

                                $dataReturn['payUrl'] = $payUrl;
                                $dataReturn['qrCodeUrl'] = $qrCodeUrl;
                                $dataReturn['orderId'] = $orderId;
                                $dataReturn['amount'] = $amount;
                                $dataReturn['orderInfo'] = $orderInfo;
                            }
                        }
                        if ($this->params->paymentOption == 'ThanhToanTheQuocTe') {
                            $dataReturn['success'] = true;

                            $payUrl = $body['payUrl']; // Lấy URL thanh toán

                            $dataReturn['payUrl'] = $payUrl;
                            $dataReturn['orderId'] = $orderId;
                            $dataReturn['amount'] = $amount;
                            $dataReturn['orderInfo'] = $orderInfo;
                        }
                        if ($this->params->paymentOption == 'ThanhToanTheATMNoiDia') {
                            $dataReturn['success'] = true;

                            $payUrl = $body['payUrl']; // Lấy URL thanh toán

                            $dataReturn['payUrl'] = $payUrl;
                            $dataReturn['orderId'] = $orderId;
                            $dataReturn['amount'] = $amount;
                            $dataReturn['orderInfo'] = $orderInfo;
                        }
                    }
                } catch (\Exception $e) {
                    $dataReturn['success'] = false;
                    $dataReturn['message'] = 'Lỗi hệ thống';
                    return ['data' => $dataReturn];
                }
            } else {
                $dataReturn['success'] = false;
            }
            return ['data' => $dataReturn];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_req_list_v_view'], $e);
        }
    }
}
