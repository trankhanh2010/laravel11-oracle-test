<?php

namespace App\Services\Model;

use App\DTOs\TreatmentFeeDetailVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentFeeDetailVView\InsertTreatmentFeeDetailVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentFeeDetailVViewRepository;

class TreatmentFeeDetailVViewService
{
    protected $treatmentFeeDetailVViewRepository;
    protected $params;
    public function __construct(TreatmentFeeDetailVViewRepository $treatmentFeeDetailVViewRepository)
    {
        $this->treatmentFeeDetailVViewRepository = $treatmentFeeDetailVViewRepository;
    }
    public function withParams(TreatmentFeeDetailVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->treatmentFeeDetailVViewRepository->applyJoins();
            $data = $this->treatmentFeeDetailVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->treatmentFeeDetailVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
            $count = null;
            $data = $data->first();
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_detail_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->treatmentFeeDetailVViewRepository->applyJoins()
                ->where('v_his_account_book.id', $id);
            $data = $this->treatmentFeeDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $data->first();
            return $data;
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
