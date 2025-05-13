<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\DTOs\TransactionTamThuDichVuDTO;
use App\DTOs\TreatmentFeePaymentDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Transaction\CreateTransactionTamThuDichVuRequest;
use App\Models\HIS\PayForm;
use App\Services\Transaction\TransactionTamThuDichVuService;
use App\Services\Transaction\TreatmentFeePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Fluent;

class TransactionTamThuDichVuController extends BaseApiCacheController
{
    protected $transactionTamThuDichVuService;
    protected $transactionTamThuDichVuDTO;
    protected $serviceReqPaymentDTO;
    protected $serviceReqPaymentService;
    protected $payForm;
    protected $payFormQrId;


    public function __construct(Request $request, TransactionTamThuDichVuService $transactionTamThuDichVuService,  TreatmentFeePaymentService $serviceReqPaymentService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->transactionTamThuDichVuService = $transactionTamThuDichVuService;
        $this->serviceReqPaymentService = $serviceReqPaymentService;

        // Thêm tham số vào service
        $this->transactionTamThuDichVuDTO = new TransactionTamThuDichVuDTO(
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
        $this->transactionTamThuDichVuService->withParams($this->transactionTamThuDichVuDTO);

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

    public function store(CreateTransactionTamThuDichVuRequest $request)
    {
        // Nếu thanh toán qr 
        if ($request->pay_form_id == $this->payFormQrId) {
            // Thêm tham số vào service
            $this->serviceReqPaymentDTO = new TreatmentFeePaymentDTO(
                $this->appCreator,
                $this->appModifier,
                'VietinBank',
                'ThanhToanQRCode',
                $this->patientCode,
                $this->treatmentCode,
                '',
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
            $dataReturn = $this->serviceReqPaymentService->createTransactionTamThuDichVuVietinBank($request, $data);
            $paramReturn = [];
            return returnDataSuccess($paramReturn, $dataReturn);
        }
        return $this->transactionTamThuDichVuService->createTransactionTamThuDichVu($request);
    }
}
