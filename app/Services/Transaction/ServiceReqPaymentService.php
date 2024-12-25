<?php

namespace App\Services\Transaction;

use App\DTOs\ServiceReqPaymentDTO;
use App\Repositories\TestServiceReqListVViewRepository;
use App\Repositories\TestServiceTypeListVViewRepository;
use App\Repositories\TreatmentMoMoPaymentsRepository;
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
        )
    {
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

    public function handleCreatePayment()
    {
        try {
            $data = $this->testServiceReqListVViewRepository->applyJoins();
            if ($this->params->treatmentCode) {
                if ($this->params->treatmentCode) {
                    $data = $this->testServiceReqListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
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

                // Thông tin giao dịch
                $orderId = 'order_' . time(); // Mã đơn hàng
                $requestId = $data->treatment_code; // Mã yêu cầu /// dùng treatment_code để thực hiện API Idempotency
                $amount = $data->fee_add; // Số tiền (VND)
                $orderInfo = "Tong chi phi: ".$totalVirPrice . $this->unit
                ."; BHYT thanh toan: ".$totalHeinPrice . $this->unit
                ."; BN phai thanh toan: ".$totalPatientPrice . $this->unit
                ."; Da thu: ".$data->total_treatment_bill_amount . $this->unit
                ."; BN can nop them: ".$data->fee_add . $this->unit
                ;
                $extraData = ''; // Thông tin thêm, có thể để trống
                if ($this->params->paymentMethod == 'MoMo') {
                    switch ($this->params->paymentOption) {
                        case 'ThanhToanQRCode':
                            $requestType = 'captureWallet'; //Hình thức thanh toán
                            $rawSignature = "accessKey=$this->accessKey&amount=$amount&extraData=$extraData&ipnUrl=$this->notifyUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$this->partnerCode&redirectUrl=$this->returnUrl&requestId=$requestId&requestType=$requestType";
                            $signature = hash_hmac('sha256', $rawSignature, $this->secretKey);
                            break;
                        case 'ThanhToanTheQuocTe':
                            $requestType = 'payWithCC'; //Hình thức thanh toán
                            $rawSignature = "accessKey=$this->accessKey&amount=$amount&extraData=$extraData&ipnUrl=$this->notifyUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$this->partnerCode&redirectUrl=$this->returnUrl&requestId=$requestId&requestType=$requestType";
                            $signature = hash_hmac('sha256', $rawSignature, $this->secretKey);
                            break;
                        case 'ThanhToanTheATMNoiDia':
                            $requestType = 'payWithATM'; //Hình thức thanh toán
                            $rawSignature = "accessKey=$this->accessKey&amount=$amount&extraData=$extraData&ipnUrl=$this->notifyUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$this->partnerCode&redirectUrl=$this->returnUrl&requestId=$requestId&requestType=$requestType";
                            $signature = hash_hmac('sha256', $rawSignature, $this->secretKey);
                            break;
                        default:
                    }
                }
                $check = $this->treatmentMoMoPaymentsRepository->check($data->treatment_code, $requestType);
                if($check){
                    $dataReturn['success'] = true;
                    $dataReturn['deeplink'] = $check->deep_link;
                    $dataReturn['qrCodeUrl'] = $check->qr_code_url;
                    $dataReturn['orderId'] = $check->order_id;
                    $dataReturn['amount'] = $amount;
                    $dataReturn['orderInfo'] = $orderInfo;
                    $dataReturn['payUrl'] = $check->pay_url;
                    $dataReturn['requestId'] = $check->request_id;
                    return ['data' => $dataReturn];
                }
                // Tạo dữ liệu gửi đến API MoMo
                $dataMoMo = [
                    'partnerCode' => $this->partnerCode,
                    'accessKey' => $this->accessKey,
                    'requestId' => $requestId,
                    'amount' => $amount,
                    'orderId' => $orderId,
                    'orderInfo' => $orderInfo,
                    'redirectUrl' => $this->returnUrl,
                    'ipnUrl' => $this->notifyUrl,
                    'extraData' => $extraData,
                    'requestType' => $requestType,
                    'signature' => $signature,
                    'lang' => 'vi',
                ];
                // Gửi request đến MoMo
                try {
                    $client = new Client();
                    $response = $client->post($this->endpointCreatePayment, ['json' => $dataMoMo]);
                    $body = json_decode($response->getBody(), true); // Chuyển kết quả thành mảng
                    // Kiểm tra nếu có response
                    if ($this->params->paymentMethod == 'MoMo') {
                        if ($this->params->paymentOption == 'ThanhToanQRCode') {
                            if (isset($body['qrCodeUrl'])) {
                                $dataReturn['success'] = true;
                                $qrCodeUrl = $body['qrCodeUrl'] ?? ""; // Lấy URL mã QR
                                $payUrl = $body['payUrl']; // Lấy URL thanh toán
                                $deeplink = $body['deeplink'] ?? ""; // Lấy deeplink
                                $dataReturn['deeplink'] = $deeplink;
                                $dataReturn['qrCodeUrl'] = $qrCodeUrl;
                                $dataReturn['orderId'] = $orderId;
                                $dataReturn['amount'] = $amount;
                                $dataReturn['orderInfo'] = $orderInfo;
                            }
                        }
                        if ($this->params->paymentOption == 'ThanhToanTheQuocTe') {
                            $dataReturn['success'] = true;
                            $payUrl = $body['payUrl']; // Lấy URL thanh toán
                            $dataReturn['orderId'] = $orderId;
                            $dataReturn['amount'] = $amount;
                            $dataReturn['orderInfo'] = $orderInfo;
                        }
                        if ($this->params->paymentOption == 'ThanhToanTheATMNoiDia') {
                            $dataReturn['success'] = true;
                            $payUrl = $body['payUrl']; // Lấy URL thanh toán
                            $dataReturn['orderId'] = $orderId;
                            $dataReturn['amount'] = $amount;
                            $dataReturn['orderInfo'] = $orderInfo;
                        }
                        $dataReturn['payUrl'] = $payUrl;
                        $dataReturn['requestId'] = $requestId;
                    }
                } catch (\Exception $e) {
                    $dataReturn['success'] = false;
                    $dataReturn['message'] = 'Lỗi hệ thống';
                    return ['data' => $dataReturn];
                }
            } else {
                $dataReturn['success'] = false;
            }
            $this->treatmentMoMoPaymentsRepository->create($data->treatment_code, $orderId, $requestId, $amount, '1000', $deeplink ?? '', $payUrl, $requestType, $qrCodeUrl ?? '');
            return ['data' => $dataReturn];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_req_list_v_view'], $e);
        }
    }
}
