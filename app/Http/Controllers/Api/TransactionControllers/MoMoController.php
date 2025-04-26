<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\DTOs\MoMoDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Services\Transaction\MoMoService;

class MoMoController extends BaseApiCacheController
{
    protected $moMoDTO;
    protected $moMoService;
    public function __construct(MoMoService $moMoService, Request $request) {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->moMoService = $moMoService;

        // Thêm tham số vào service
        $this->moMoDTO = new MoMoDTO(
            $this->appCreator,
            $this->appModifier,
            $request
        );
        $this->moMoService->withParams($this->moMoDTO);
    }
    // public function handleNotificationThanhToan()
    // {
    //     $this->moMoService->handleNotificationThanhToan();
    //     // Trả về phản hồi cho MoMo
    //     return response()->json([], 204);
    // }
    public function handleNotificationTamUng()
    {
        $this->moMoService->handleNotificationTamUng();
        // Trả về phản hồi cho MoMo
        return response()->json([], 204);
    }
}
