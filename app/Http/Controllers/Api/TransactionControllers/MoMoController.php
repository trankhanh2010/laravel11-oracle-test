<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\Events\Transaction\MoMoNotificationReceived;
use App\Http\Controllers\Controller;
use App\Repositories\TreatmentMoMoPaymentsRepository;
use Illuminate\Http\Request;

class MoMoController extends Controller
{
    protected $treatmentMoMoPaymentsRepository;
    public function __construct(TreatmentMoMoPaymentsRepository $treatmentMoMoPaymentsRepository)
    {
        $this->treatmentMoMoPaymentsRepository = $treatmentMoMoPaymentsRepository;
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
        $this->treatmentMoMoPaymentsRepository->update($data['orderId'], $data['resultCode']);
        // Gửi dữ liệu lên WebSocket
        broadcast(new MoMoNotificationReceived($data));

        // Trả về phản hồi cho MoMo
        return response()->json([],204);
    }
    
    private function isValid($data)
    {
        // kiểm tra xem dữ liệu có khớp với order_id hay không
        $result = true;
        $result = $this->treatmentMoMoPaymentsRepository->checkNofityMoMo($data);
        return $result;
    }
    
}
