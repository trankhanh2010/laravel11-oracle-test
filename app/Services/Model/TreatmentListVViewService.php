<?php

namespace App\Services\Model;

use App\DTOs\TreatmentListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentListVView\InsertTreatmentListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentListVViewRepository;

class TreatmentListVViewService
{
    protected $treatmentListVViewRepository;
    protected $params;
    public function __construct(TreatmentListVViewRepository $treatmentListVViewRepository)
    {
        $this->treatmentListVViewRepository = $treatmentListVViewRepository;
    }
    public function withParams(TreatmentListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->treatmentListVViewName .$this->params->param, 3600, function () {
                $data = $this->treatmentListVViewRepository->applyJoins();
                $data = $this->treatmentListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->treatmentListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
                $data = $this->treatmentListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
                $data = $this->treatmentListVViewRepository->applyTreatmentTypeCodeFilter($data, $this->params->treatmentTypeCode);
                $data = $this->treatmentListVViewRepository->applyInTimeFilter($data, $this->params->inTimeFrom, $this->params->inTimeTo);

                $count = $data->count();
                $data = $this->treatmentListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->treatmentListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                // Group theo field
                $data = $this->treatmentListVViewRepository->applyGroupByField($data, $this->params->groupBy);
                return ['data' => $data, 'count' => $count];
            });

            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->treatmentListVViewRepository->applyJoins()
                ->where('id', $id);
            $data = $this->treatmentListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->treatmentListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_list_v_view'], $e);
        }
    }

    // public function createTreatmentListVView($request)
    // {
    //     try {
    //         $data = $this->treatmentListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentListVViewIndex($data, $this->params->treatmentListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_list_v_view'], $e);
    //     }
    // }

    // public function updateTreatmentListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentListVViewIndex($data, $this->params->treatmentListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_list_v_view'], $e);
    //     }
    // }

    // public function deleteTreatmentListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->treatmentListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_list_v_view'], $e);
    //     }
    // }
}
