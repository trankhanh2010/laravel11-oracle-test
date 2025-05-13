<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\DTOs\TransactionTamUngDTO;
use App\DTOs\TreatmentFeePaymentDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Transaction\CreateTransactionTamUngRequest;
use App\Models\HIS\DepositReq;
use App\Models\HIS\PayForm;
use App\Services\Transaction\TransactionTamUngService;
use App\Services\Transaction\TreatmentFeePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Fluent;

class TransactionTamUngController extends BaseApiCacheController
{
    protected $transactionTamUngService;
    protected $transactionTamUngDTO;
    protected $serviceReqPaymentService;
    protected $serviceReqPaymentDTO;
    protected $payForm;
    protected $depositReq;
    protected $payFormQrId;
    public function __construct(Request $request, TransactionTamUngService $transactionTamUngService, TreatmentFeePaymentService $serviceReqPaymentService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->transactionTamUngService = $transactionTamUngService;
        $this->serviceReqPaymentService = $serviceReqPaymentService;
        // Thêm tham số vào service
        $this->transactionTamUngDTO = new TransactionTamUngDTO(
            $this->transactionName,
            $this->keyword,
            $this->isActive,
            $this->orderBy,
            $this->orderByJoin,
            $this->orderByString,
            $this->getAll,
            $this->start,
            $this->limit,
            $request,
            $this->appCreator, 
            $this->appModifier, 
            $this->time,
            $this->param,
            $this->noCache,
        );
        $this->transactionTamUngService->withParams($this->transactionTamUngDTO);

        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $cacheKey = 'pay_form_qr_id';
        $this->payForm = new PayForm();
        $this->payFormQrId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '08')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
    }

    public function store(CreateTransactionTamUngRequest $request)
    {
        // Nếu thanh toán qr 
        if($request->pay_form_id == $this->payFormQrId){
            // Nếu k theo yêu cầu tạm ứng
            if($request->deposit_req_id == null){
                // Thêm tham số vào service
                $this->serviceReqPaymentDTO = new TreatmentFeePaymentDTO(
                    $this->appCreator,
                    $this->appModifier,
                    'VietinBank',
                    'ThanhToanQRCode',
                    $this->patientCode,
                    $this->treatmentCode,
                    'TU',
                    null,
                    $this->param,
                    $this->noCache,
                    $this->currentLoginname,
                );
                $this->serviceReqPaymentService->withParams($this->serviceReqPaymentDTO);
                $data = new Fluent([
                    'id'  => $request->treatment_id,
                    'fee' => $request->amount,
                ]);
                $dataReturn = $this->serviceReqPaymentService->createTransactionTamUngVietinBank($data, 0, get_loginname_with_token($request->bearerToken(), 14400), get_username_with_token($request->bearerToken(), 14400));
                $paramReturn = [];
                return returnDataSuccess($paramReturn, $dataReturn);
            }

            
            // Nếu theo yêu cầu tạm ứng
            if($request->deposit_req_id != null){
                $this->depositReq = new DepositReq();
                $dataDepositReq = $this->depositReq->find($request->deposit_req_id);
                // Thêm tham số vào service
                $this->serviceReqPaymentDTO = new TreatmentFeePaymentDTO(
                    $this->appCreator,
                    $this->appModifier,
                    'VietinBank',
                    'ThanhToanQRCode',
                    $this->patientCode,
                    $this->treatmentCode,
                    'TU',
                    $dataDepositReq->deposit_req_code??null,
                    $this->param,
                    $this->noCache,
                    $this->currentLoginname,
                );
                $this->serviceReqPaymentService->withParams($this->serviceReqPaymentDTO);

                $data = new Fluent([
                    'treatment_id'  => $dataDepositReq->treatment_id,
                    'fee' => $dataDepositReq->amount,
                    'amount' => $dataDepositReq->amount,
                    'deposit_req_code' => $dataDepositReq->deposit_req_code,
                ]);
                $dataReturn = $this->serviceReqPaymentService->createTransactionDepositReqVietinBank($data, 0, get_loginname_with_token($request->bearerToken(), 14400), get_username_with_token($request->bearerToken(), 14400));
                $paramReturn = [];
                return returnDataSuccess($paramReturn, $dataReturn);
            }
        }

        return $this->transactionTamUngService->createTransactionTamUng($request);
    }

}
