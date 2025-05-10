<?php

namespace App\Services\Model;

use App\DTOs\TreatmentFeeDetailVViewDTO;
use App\DTOs\TreatmentFeePaymentDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentFeeDetailVView\InsertTreatmentFeeDetailVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentFeeDetailVViewRepository;
use App\Repositories\TreatmentMoMoPaymentsRepository;
use App\Services\Transaction\TreatmentFeePaymentService;
use Illuminate\Support\Facades\Log;

class TreatmentFeeDetailVViewService
{
    protected $treatmentFeeDetailVViewRepository;
    protected $params;
    protected $treatmentFeePaymentService;
    protected $treatmentMomoPaymentsRepository;
    protected $treatmentFeePaymentDTO;
    public function __construct(
        TreatmentFeeDetailVViewRepository $treatmentFeeDetailVViewRepository,
        TreatmentFeePaymentService $treatmentFeePaymentService,
        TreatmentMoMoPaymentsRepository $treatmentMomoPaymentsRepository,
        )
    {
        $this->treatmentFeeDetailVViewRepository = $treatmentFeeDetailVViewRepository;
        $this->treatmentFeePaymentService = $treatmentFeePaymentService;
        $this->treatmentMomoPaymentsRepository = $treatmentMomoPaymentsRepository;
    }
    public function withParams(TreatmentFeeDetailVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->treatmentFeeDetailVViewRepository->applyJoins();
        $data = $this->treatmentFeeDetailVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->treatmentFeeDetailVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
        $count = null;
        $data = $data->first();
        // nếu có dữ liệu, kiểm tra xem có khóa viện phí không
        if($data){
            // nếu có khóa viện phí thì kiểm tra xem có giao dịch nào có mã 1000 không
            if($data->fee_lock_time != null){
                $listPayment = $this->treatmentMomoPaymentsRepository->getAllPayment1000($data->id);
                // nếu có mã 1000 thì gọi lại việc lấy link để tạo lại, cập nhật bản ghi, => nếu đã khóa viện phí thì link sẽ k được trả về
                // lặp qua từng bản ghi để kiểm tra
                if($listPayment->isNotEmpty()){
                    foreach ($listPayment as $key => $item){
                        switch ($item->request_type) {
                            case 'captureWallet':
                                $requestType = 'ThanhToanQRCode';
                                break;
                            case 'payWithCC':
                                $requestType = 'ThanhToanTheQuocTe';
                                break;
                            case 'payWithATM':
                                $requestType = 'ThanhToanTheATMNoiDia';
                                break;
                            default:
                                $requestType = '';
                        }

                        $this->treatmentFeePaymentDTO = new TreatmentFeePaymentDTO(
                            'MOS_v2',
                            'MOS_v2',
                            'MoMo',
                            $requestType,
                            null,
                            $data->treatment_code,
                            $item->transaction_type_code,
                            $item->deposit_req_code,
                            $this->params->param,
                            false,
                            "MOS_v2"
                        );
                        $this->treatmentFeePaymentService->withParams($this->treatmentFeePaymentDTO);

                        // nếu là link thanh toán viện phí còn thiếu => gọi handleCreatePayment
                        if($item->deposit_req_code == null){
                            $this->treatmentFeePaymentService->handleCreatePayment();
                        }

                        // nếu là link thanh toán yêu cầu tạm ứng => gọi handleCreatePaymentDepositReq
                        if($item->deposit_req_code != null){
                            $this->treatmentFeePaymentService->handleCreatePaymentDepositReq();
                        }
                    }
                }
            }
        }
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->treatmentFeeDetailVViewRepository->applyJoins()
        ->where('id', $id);
    $data = $this->treatmentFeeDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $data->first();
    return $data; 
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_detail_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_detail_v_view'], $e);
        }
    }

    // public function createTreatmentFeeDetailVView($request)
    // {
    //     try {
    //         $data = $this->treatmentFeeDetailVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentFeeDetailVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentFeeDetailVViewIndex($data, $this->params->treatmentFeeDetailVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_detail_v_view'], $e);
    //     }
    // }

    // public function updateTreatmentFeeDetailVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentFeeDetailVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentFeeDetailVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentFeeDetailVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentFeeDetailVViewIndex($data, $this->params->treatmentFeeDetailVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_detail_v_view'], $e);
    //     }
    // }

    // public function deleteTreatmentFeeDetailVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentFeeDetailVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentFeeDetailVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentFeeDetailVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->treatmentFeeDetailVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_detail_v_view'], $e);
    //     }
    // }
}
