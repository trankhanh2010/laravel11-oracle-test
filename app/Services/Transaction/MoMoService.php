<?php

namespace App\Services\Transaction;

use App\DTOs\MoMoDTO;
use App\Events\Transaction\MoMoNotificationReceived;
use App\Repositories\SereServBillRepository;
use App\Repositories\TestServiceTypeListVViewRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\TreatmentMoMoPaymentsRepository;
use App\Services\Transaction\TreatmentFeePaymentService;

class MoMoService 
{
    protected $treatmentMoMoPaymentsRepository;
    protected $transactionRepository;
    protected $serviceReqPaymentService;
    protected $testServiceTypeListVViewRepository;
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
        TestServiceTypeListVViewRepository $testServiceTypeListVViewRepository,
        SereServBillRepository $sereServBill,
    )
    {
        $this->treatmentMoMoPaymentsRepository = $treatmentMoMoPaymentsRepository;
        $this->transactionRepository = $transactionRepository;
        $this->serviceReqPaymentService = $serviceReqPaymentService;
        $this->testServiceTypeListVViewRepository = $testServiceTypeListVViewRepository;
        $this->sereServBill = $sereServBill;
        $this->partnerCode = config('database')['connections']['momo']['momo_partner_code'];
        $this->accessKey = config('database')['connections']['momo']['momo_access_key'];
        $this->secretKey = config('database')['connections']['momo']['momo_secret_key'];
        $this->endpointCreatePayment = config('database')['connections']['momo']['momo_endpoint_create_payment'];
        $this->endpointCheckTransaction = config('database')['connections']['momo']['momo_endpoint_check_transaction'];
        $this->returnUrl = config('database')['connections']['momo']['momo_return_url'];
        $this->notifyUrl = config('database')['connections']['momo']['momo_notify_url'];
    }
    public function withParams(MoMoDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleNotification()
    {
        // Lấy dữ liệu gửi về từ MoMo hoặc nguồn khác
        $data = $this->params->request->all();
        // Xác minh chữ ký từ MoMo
        // $isVefify = $this->verifyMoMoSignature($data);
        // if (!$isVefify) {
        //     return response()->json([], 204);
        //     // Nếu dữ liệu không khớp
        // }
        $isValid = $this->isValid($data);
        if (!$isValid) {
            return response()->json([], 204);
            // Nếu dữ liệu không khớp
        }
        // Nếu khớp thì cập nhật bên DB
        // Lấy resultCode từ MoMo
        $dataMoMo = $this->serviceReqPaymentService->checkTransactionStatus($data['orderId'])['data'];
        // Cập nhật payment 
        $this->treatmentMoMoPaymentsRepository->update($dataMoMo);
        // và lấy treatmentId, treatmentCode
        $payment = $this->treatmentMoMoPaymentsRepository->getTreatmentByOrderId($dataMoMo['orderId']);
        // Nếu resultCode là 0 hoặc 9000 thì tạo transaction trong DB
        if ($dataMoMo['resultCode'] == 0 || $dataMoMo['resultCode'] == 9000) {
            // Tạo transaction
            $transaction = $this->transactionRepository->createTransactionPaymentMoMo($payment, $dataMoMo, $this->params->appCreator, $this->params->appModifier);
            // sere_serv_bill
            $listServiceType = $this->testServiceTypeListVViewRepository->applyJoins();
            $listServiceType = $this->testServiceTypeListVViewRepository->applyTreatmentIdFilter($listServiceType, $transaction->treatment_id)
            ->where('vir_total_patient_price', '>', 0)
            ->get();
            // Lặp qua từng sere_serv để tạo mới
            foreach($listServiceType as $key => $item){
                $this->sereServBill->create($item, $transaction,  $this->params->appCreator, $this->params->appModifier);
            }
        }
        // Gửi dữ liệu lên WebSocket
        broadcast(new MoMoNotificationReceived($data));
    }
    private function isValid($data)
    {
        // kiểm tra xem dữ liệu có khớp với order_id hay không
        $result = $this->treatmentMoMoPaymentsRepository->checkNofityMoMo($data);
        return $result;
    }
    // private function verifyMoMoSignature($data)
    // {
    // // Bước 1: Tạo chuỗi rawData theo thứ tự quy định
    // $rawData = sprintf(
    //     "partnerCode=%s&orderId=%s&requestId=%s&amount=%s&orderInfo=%s&orderType=%s&transId=%s&resultCode=%s&message=%s&payType=%s&responseTime=%s&extraData=%s",
    //     $data['partnerCode'],
    //     $data['orderId'],
    //     $data['requestId'],
    //     $data['amount'],
    //     $data['orderInfo'],
    //     $data['orderType'],
    //     $data['transId'] ?? "", // Nếu transId là null, dùng chuỗi rỗng
    //     $data['resultCode'],
    //     $data['message'],
    //     $data['payType'] ?? "", // Nếu payType là null, dùng chuỗi rỗng
    //     $data['responseTime'],
    //     $data['extraData'] ?? "" // Nếu extraData là null, dùng chuỗi rỗng
    // );    
    //     // Bước 2: Tạo chữ ký bằng HMAC-SHA256
    // // dd($rawData);
    // $generatedSignature = hash_hmac('sha256', $rawData, $this->secretKey);
    // // dd(hash_equals($generatedSignature, $data['signature']));
    // dd($generatedSignature);
    // // Bước 3: So sánh chữ ký
    // return hash_equals($generatedSignature, $data['signature']);
    // }
}
