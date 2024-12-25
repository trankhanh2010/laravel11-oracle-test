<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\DTOs\ServiceReqPaymentDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Services\Transaction\ServiceReqPaymentService;
use Illuminate\Http\Request;


class ServiceReqPayMentController extends BaseApiCacheController
{
    protected $serviceReqPaymentService;
    protected $serviceReqPaymentDTO;
    public function __construct(Request $request, ServiceReqPaymentService $serviceReqPaymentService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->serviceReqPaymentService = $serviceReqPaymentService;
        // Thêm tham số vào service
        $this->serviceReqPaymentDTO = new ServiceReqPaymentDTO(
            $this->paymentMethod,
            $this->paymentOption,
            $this->patientCode,
            $this->treatmentCode,
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
}
