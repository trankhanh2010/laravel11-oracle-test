<?php

namespace App\Services\Transaction;

use App\DTOs\MoMoDTO;
use App\Events\Transaction\MoMoNotificationThanhToanReceived;
use App\Repositories\SereServBillRepository;
use App\Repositories\SereServMomoPaymentsRepository;
use App\Repositories\TestServiceTypeListVViewRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\TreatmentMoMoPaymentsRepository;
use App\Services\Transaction\TreatmentFeePaymentService;

class MoMoService
{
    protected $treatmentMoMoPaymentsRepository;
    protected $transactionRepository;
    protected $serviceReqPaymentService;
    protected $sereServMomoPaymentsRepository;
    protected $sereServBill;
    protected $params;
    protected $partnerCode;
    protected $accessKey;
    protected $secretKey;
    protected $endpointCreatePayment;
    protected $endpointCheckTransaction;
    protected $returnUrl;
    protected $notifyUrl;
    public function __construct(
        TreatmentMoMoPaymentsRepository $treatmentMoMoPaymentsRepository,
        TransactionRepository $transactionRepository,
        TreatmentFeePaymentService $serviceReqPaymentService,
        SereServMomoPaymentsRepository $sereServMomoPaymentsRepository,
        SereServBillRepository $sereServBill,
    ) {
        $this->treatmentMoMoPaymentsRepository = $treatmentMoMoPaymentsRepository;
        $this->transactionRepository = $transactionRepository;
        $this->serviceReqPaymentService = $serviceReqPaymentService;
        $this->sereServMomoPaymentsRepository = $sereServMomoPaymentsRepository;
        $this->sereServBill = $sereServBill;
        $this->partnerCode = config('database')['connections']['momo']['momo_partner_code'];
        $this->accessKey = config('database')['connections']['momo']['momo_access_key'];
        $this->secretKey = config('database')['connections']['momo']['momo_secret_key'];
        $this->endpointCreatePayment = config('database')['connections']['momo']['momo_endpoint_create_payment'];
        $this->endpointCheckTransaction = config('database')['connections']['momo']['momo_endpoint_check_transaction'];
        $this->returnUrl = config('database')['connections']['momo']['momo_return_url_thanh_toan'];
        $this->notifyUrl = config('database')['connections']['momo']['momo_notify_url_thanh_toan'];
    }
    public function withParams(MoMoDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    // Nhận ipn thanh toán
    public function handleNotificationThanhToan()
    {
        // Lấy param từ request
        $data = $this->getParamRequest();
        //Xác minh chữ ký từ MoMo
        $isVefify = $this->verifyMoMoSignature($data);
        if (!$isVefify) {
            // Nếu dữ liệu không khớp thì bỏ qua
            return response()->json([], 204);
        }
        // Check trong DB xem có tạo giao dịch cho payment này chưa
        $isValid = $this->isValid($data);
        if (!$isValid) {
            // Nếu dữ liệu không khớp hoặc đã có rồi thì bỏ qua
            return response()->json([], 204);
        }
        // Nếu khớp thì cập nhật bên DB
        // Lấy resultCode từ MoMo
        $dataMoMo = $this->serviceReqPaymentService->checkTransactionStatus($data['orderId'])['data'];
        // Cập nhật payment 
        $this->treatmentMoMoPaymentsRepository->update($dataMoMo);
        // Nếu resultCode là 0 hoặc 9000 thì tạo transaction trong DB
        if ($dataMoMo['resultCode'] == 0 || $dataMoMo['resultCode'] == 9000) {
            // và lấy treatmentId, treatmentCode
            $payment = $this->treatmentMoMoPaymentsRepository->getTreatmentByOrderId($dataMoMo['orderId']);
            // Tạo transaction
            $transaction = $this->transactionRepository->createTransactionPaymentMoMoThanhToan($payment, $dataMoMo, $this->params->appCreator, $this->params->appModifier);
            // Cập nhật bill cho treatmentMomoPayments
            $this->treatmentMoMoPaymentsRepository->updateBill($payment, $transaction->id);
            // sere_serv_bill
            $listSereServ = $this->sereServMomoPaymentsRepository->getByTreatmentMomoPaymentsId($payment->id);
            // Lặp qua từng sere_serv để tạo mới
            foreach ($listSereServ as $key => $item) {
                $this->sereServBill->create($item->sere_serv_id, $transaction,  $this->params->appCreator, $this->params->appModifier);
            }
            // Vô hiệu hóa các link thanh toán đã có trước khi thanh toán
            $this->treatmentMoMoPaymentsRepository->setResultCode1005($payment->treatment_code);
        }
        // Gửi dữ liệu lên WebSocket
        broadcast(new MoMoNotificationThanhToanReceived($data));
    }
    // Nhận ipn tạm ứng
    // public function handleNotificationTamUng()
    // {
    //     // Lấy param từ request
    //     $data = $this->getParamRequest();
    //     //Xác minh chữ ký từ MoMo
    //     $isVefify = $this->verifyMoMoSignature($data);
    //     if (!$isVefify) {
    //         // Nếu dữ liệu không khớp thì bỏ qua
    //         return response()->json([], 204);
    //     }
    //     // Check trong DB xem có tạo giao dịch cho payment này chưa
    //     $isValid = $this->isValid($data);
    //     if (!$isValid) {
    //         // Nếu dữ liệu không khớp hoặc đã có rồi thì bỏ qua
    //         return response()->json([], 204);
    //     }
    //     // Nếu khớp thì cập nhật bên DB
    //     // Lấy resultCode từ MoMo
    //     $dataMoMo = $this->serviceReqPaymentService->checkTransactionStatus($data['orderId'])['data'];
    //     // Cập nhật payment 
    //     $this->treatmentMoMoPaymentsRepository->update($dataMoMo);
    //     // Nếu resultCode là 0 hoặc 9000 thì tạo transaction trong DB
    //     if ($dataMoMo['resultCode'] == 0 || $dataMoMo['resultCode'] == 9000) {
    //         // và lấy treatmentId, treatmentCode
    //         $payment = $this->treatmentMoMoPaymentsRepository->getTreatmentByOrderId($dataMoMo['orderId']);
    //         // Tạo transaction
    //         $transaction = $this->transactionRepository->createTransactionPaymentMoMoThanhToan($payment, $dataMoMo, $this->params->appCreator, $this->params->appModifier);
    //         // Cập nhật bill cho treatmentMomoPayments
    //         $this->treatmentMoMoPaymentsRepository->updateBill($payment, $transaction->id);
    //         // Vô hiệu hóa các link thanh toán đã có trước khi thanh toán
    //         $this->treatmentMoMoPaymentsRepository->setResultCode1005($payment->treatment_code);
    //     }
    //     // Gửi dữ liệu lên WebSocket
    //     broadcast(new MoMoNotificationTamUngReceived($data));
    // }
    private function isValid($data)
    {
        // kiểm tra xem dữ liệu có khớp với order_id hay không
        $result = $this->treatmentMoMoPaymentsRepository->checkNotifyMoMo($data);
        return $result;
    }

    private function verifyMoMoSignature($data)
    {
        // Kiểm tra `accessKey` trong `$data`, nếu không có thì dùng `$this->accessKey`
        $accessKey = $data['accessKey'] ?? $this->accessKey;
    
        // Bước 1: Sắp xếp mảng `$data` theo thứ tự bảng chữ cái của key
        ksort($data);
    
        // Bước 2: Tạo chuỗi `rawData` từ mảng đã sắp xếp, bỏ qua `signature`
        $rawData = ["accessKey={$accessKey}"];
        foreach ($data as $key => $value) {
            if ($key === 'signature' || $key === 'accessKey') {
                continue; // Bỏ qua trường `signature`
            }
            // Nối chuỗi
            $rawData[] = "{$key}={$value}";
        }
        $rawData = implode('&', $rawData);

        // Bước 3: Tạo chữ ký bằng HMAC-SHA256
        $generatedSignature = hash_hmac('sha256', $rawData, $this->secretKey);

        // Bước 4: So sánh chữ ký
        return hash_equals($generatedSignature, $data['signature']);
    }

    private function getParamRequest(){
        // Lấy dữ liệu gửi về từ MoMo hoặc nguồn khác
        $data = $this->params->request->all();
        return $data;
    }
    
}
