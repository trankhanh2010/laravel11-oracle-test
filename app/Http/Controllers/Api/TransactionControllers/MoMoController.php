<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\Events\Transaction\MoMoNotificationReceived;
use App\Http\Controllers\Controller;
use App\Repositories\TreatmentMoMoPaymentsRepository;
use App\Services\Transaction\ServiceReqPaymentService;
use App\Services\TreatmentMoMoPaymentsService;
use Illuminate\Http\Request;

class MoMoController extends Controller
{
    protected $treatmentMoMoPaymentsRepository;
    protected $serviceReqPaymentService;
    public function __construct(
        TreatmentMoMoPaymentsRepository $treatmentMoMoPaymentsRepository,
        ServiceReqPaymentService $serviceReqPaymentService
        )
    {
        $this->treatmentMoMoPaymentsRepository = $treatmentMoMoPaymentsRepository;
        $this->serviceReqPaymentService = $serviceReqPaymentService;
    }
    public function handleNotification(Request $request)
    {
        // Lấy dữ liệu gửi về từ MoMo
        $data = $request->all();
        // (Tùy chọn) Xác minh chữ ký từ MoMo
        $isValid = $this->isValid($data);
        if (!$isValid) {
            return response()->json([],204);
            // Nếu dữ liệu không khớp
        }
        // Nếu khớp thì cập nhật bên DB
        // Lấy resultCode từ MoMo
        $resultCode = $this->serviceReqPaymentService->checkTransactionStatus($data['orderId'])['data']['resultCode'];
        $this->treatmentMoMoPaymentsRepository->update($data['orderId'], $resultCode);
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
