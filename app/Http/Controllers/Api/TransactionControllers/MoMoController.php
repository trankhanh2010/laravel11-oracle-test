<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\Events\Transaction\MoMoNotificationReceived;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MoMoController extends Controller
{
    public function handleNotification(Request $request)
    {
        // Lấy dữ liệu gửi về từ MoMo
        $data = $request->all();
        // (Tùy chọn) Xác minh chữ ký từ MoMo
        $isValid = $this->isValid($data);
        if (!$isValid) {
            // Nếu dữ liệu không khớp
        }
        // Gửi dữ liệu lên WebSocket
        broadcast(new MoMoNotificationReceived($data));

        // Trả về phản hồi cho MoMo
        return response()->json([],204);
    }
    
    private function isValid($data)
    {
        // kiểm tra xem dữ liệu có khớp với order_id hay không
        $result = true;
        return $result;
    }
    
}
