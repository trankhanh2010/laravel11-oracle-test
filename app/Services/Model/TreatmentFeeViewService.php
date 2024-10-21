<?php

namespace App\Services\Model;

use App\DTOs\TreatmentFeeViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentFeeView\InsertTreatmentFeeViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentFeeViewRepository;

class TreatmentFeeViewService
{
    protected $treatmentFeeViewRepository;
    protected $params;
    public function __construct(TreatmentFeeViewRepository $treatmentFeeViewRepository)
    {
        $this->treatmentFeeViewRepository = $treatmentFeeViewRepository;
    }
    public function withParams(TreatmentFeeViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->treatmentFeeViewRepository->applyJoins();
            $data = $this->treatmentFeeViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->treatmentFeeViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->treatmentFeeViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->treatmentFeeViewRepository->applyTdlTreatmentTypeIdsFilter($data, $this->params->tdlTreatmentTypeIds);
            $data = $this->treatmentFeeViewRepository->applyTdlPatientTypeIdsFilter($data, $this->params->tdlPatientTypeIds);
            $data = $this->treatmentFeeViewRepository->applyBranchIdFilter($data, $this->params->branchId);
            $data = $this->treatmentFeeViewRepository->applyInDateFromFilter($data, $this->params->inDateFrom);
            $data = $this->treatmentFeeViewRepository->applyInDateToFilter($data, $this->params->inDateTo);
            $data = $this->treatmentFeeViewRepository->applyIsApproveStoreFilter($data, $this->params->isApproveStore);
            $count = $data->count();
            $data = $this->treatmentFeeViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentFeeViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->treatmentFeeViewRepository->applyJoins();
            $data = $this->treatmentFeeViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->treatmentFeeViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->treatmentFeeViewRepository->applyTdlTreatmentTypeIdsFilter($data, $this->params->tdlTreatmentTypeIds);
            $data = $this->treatmentFeeViewRepository->applyTdlPatientTypeIdsFilter($data, $this->params->tdlPatientTypeIds);
            $data = $this->treatmentFeeViewRepository->applyBranchIdFilter($data, $this->params->branchId);
            $data = $this->treatmentFeeViewRepository->applyInDateFromFilter($data, $this->params->inDateFrom);
            $data = $this->treatmentFeeViewRepository->applyInDateToFilter($data, $this->params->inDateTo);
            $data = $this->treatmentFeeViewRepository->applyIsApproveStoreFilter($data, $this->params->isApproveStore);
            $count = $data->count();
            $data = $this->treatmentFeeViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentFeeViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->treatmentFeeViewRepository->applyJoins()
                ->where('v_his_treatment_fee.id', $id);
            $data = $this->treatmentFeeViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->treatmentFeeViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_view'], $e);
        }
    }

    // public function createTreatmentFeeView($request)
    // {
    //     try {
    //         $data = $this->treatmentFeeViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentFeeViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentFeeViewIndex($data, $this->params->treatmentFeeViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_view'], $e);
    //     }
    // }

    // public function updateTreatmentFeeView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentFeeViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentFeeViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentFeeViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentFeeViewIndex($data, $this->params->treatmentFeeViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_view'], $e);
    //     }
    // }

    // public function deleteTreatmentFeeView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentFeeViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentFeeViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentFeeViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->treatmentFeeViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_fee_view'], $e);
    //     }
    // }
}
