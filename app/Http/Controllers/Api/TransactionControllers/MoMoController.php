<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\Events\Transaction\MoMoNotificationReceived;
use App\Http\Controllers\Controller;
use App\Repositories\SereServBillRepository;
use App\Repositories\TestServiceTypeListVViewRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\TreatmentMoMoPaymentsRepository;
use App\Services\Transaction\TreatmentFeePaymentService;
use App\Services\TreatmentMoMoPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MoMoController extends Controller
{
    protected $treatmentMoMoPaymentsRepository;
    protected $transactionRepository;
    protected $serviceReqPaymentService;
    protected $testServiceTypeListVViewRepository;
    protected $sereServBill;
    protected $appModifier = 'MOS_v2';
    protected $appCreator = 'MOS_v2';
    public function __construct(
        TreatmentMoMoPaymentsRepository $treatmentMoMoPaymentsRepository,
        TransactionRepository $transactionRepository,
        TreatmentFeePaymentService $serviceReqPaymentService,
        TestServiceTypeListVViewRepository $testServiceTypeListVViewRepository,
        SereServBillRepository $sereServBill
        )
    {
        $this->treatmentMoMoPaymentsRepository = $treatmentMoMoPaymentsRepository;
        $this->transactionRepository = $transactionRepository;
        $this->serviceReqPaymentService = $serviceReqPaymentService;
        $this->testServiceTypeListVViewRepository = $testServiceTypeListVViewRepository;
        $this->sereServBill = $sereServBill;
    }
    public function handleNotification(Request $request)
    {
        // Lấy dữ liệu gửi về từ MoMo hoặc nguồn khác
        $data = $request->all();
        // (Tùy chọn) Xác minh chữ ký từ MoMo
        $isValid = $this->isValid($data);
        if (!$isValid) {
            return response()->json([],204);
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
        if($dataMoMo['resultCode'] == 0 || $dataMoMo['resultCode'] == 9000){
            // Tạo transaction
            $transaction = $this->transactionRepository->createTransactionPaymentMoMo($payment, $dataMoMo, $this->appCreator, $this->appModifier);
            // // sere_serv_bill
            // $listServiceType = $this->testServiceTypeListVViewRepository->applyJoins();
            // $listServiceType = $this->testServiceTypeListVViewRepository->applyTreatmentIdFilter($listServiceType, $transaction->treatment_id)->get();
            // // Lặp qua từng sere_serv để tạo mới
            // $appCreator = 'MOS_v2';
            // $appModifier = 'MOS_v2';
            // foreach($listServiceType as $key => $item){
            //     $this->sereServBill->create($item, $transaction,  $appCreator, $appModifier);
            // }
        }
        // Gửi dữ liệu lên WebSocket
        broadcast(new MoMoNotificationReceived($data));

        // Trả về phản hồi cho MoMo
        return response()->json([],204);
    }
    
    private function isValid($data)
    {
        // kiểm tra xem dữ liệu có khớp với order_id hay không
        $result = $this->treatmentMoMoPaymentsRepository->checkNofityMoMo($data);
        return $result;
    }
    
}
