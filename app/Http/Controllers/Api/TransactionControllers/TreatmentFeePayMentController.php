<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\DTOs\TreatmentFeePaymentDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Services\Transaction\TreatmentFeePaymentService;
use Illuminate\Http\Request;


class TreatmentFeePayMentController extends BaseApiCacheController
{
    protected $serviceReqPaymentService;
    protected $serviceReqPaymentDTO;
    public function __construct(Request $request, TreatmentFeePaymentService $serviceReqPaymentService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->serviceReqPaymentService = $serviceReqPaymentService;
        // Thêm tham số vào service
        $this->serviceReqPaymentDTO = new TreatmentFeePaymentDTO(
            $this->appCreator,
            $this->appModifier,
            $this->paymentMethod,
            $this->paymentOption,
            $this->patientCode,
            $this->treatmentCode,
            $this->transactionTypeCode,
            $this->depositReqCode,
            $this->param,
            $this->noCache,
        );
        $this->serviceReqPaymentService->withParams($this->serviceReqPaymentDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->serviceReqPaymentService->handleCreatePayment();
        $paramReturn = [];
        return returnDataSuccess($paramReturn, $data['data']);
    }
    public function checkTransactionStatus(Request $request)
    {
        $orderId = $request->orderId;
        if (!$orderId) {
            return 0;
        }
        $data = $this->serviceReqPaymentService->checkTransactionStatus($orderId);
        $paramReturn = [];
        return returnDataSuccess($paramReturn, $data['data']);
    }

    public function createPaymentDepositReq()
    {
        if($this->depositReqCode == null){
            $this->errors[$this->depositReqCode] = 'Thiếu mã yêu cầu tạm ứng!';
        }
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->serviceReqPaymentService->handleCreatePaymentDepositReq();
        $paramReturn = [];
        return returnDataSuccess($paramReturn, $data['data']);
    }
}
