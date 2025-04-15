<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\DTOs\VietinbankDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Services\Transaction\VietinbankService;
use Illuminate\Http\Request;

class VietinbankController extends BaseApiCacheController
{
    protected $vietinBankDTO;
    protected $vietinbankService;
    public function __construct(VietinbankService $vietinbankService, Request $request) {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->vietinbankService = $vietinbankService;

        // Thêm tham số vào service
        $this->vietinBankDTO = new VietinbankDTO(
            $this->appCreator,
            $this->appModifier,
            $request
        );
        $this->vietinbankService->withParams($this->vietinBankDTO);
    }

    public function handleConfirmTransaction(Request $request){
        $data = $this->vietinbankService->handleConfirmTransaction();
        return $data;
    }
    public function handleInqDetailTrans(Request $request){
        $data = $this->vietinbankService->handleInqDetailTrans();
        return $data;
    }
    /**
     * API tạo QR Code giao dịch
     */

}
