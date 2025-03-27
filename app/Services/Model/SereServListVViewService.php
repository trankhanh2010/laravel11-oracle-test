<?php

namespace App\Services\Model;

use App\DTOs\SereServListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServListVView\InsertSereServListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServListVViewRepository;

class SereServListVViewService
{
    protected $sereServListVViewRepository;
    protected $params;
    public function __construct(SereServListVViewRepository $sereServListVViewRepository)
    {
        $this->sereServListVViewRepository = $sereServListVViewRepository;
    }
    public function withParams(SereServListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->sereServListVViewRepository->applyJoins();
            $data = $this->sereServListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->sereServListVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->sereServListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->sereServListVViewRepository->applyTrackingIdFilter($data, $this->params->trackingId);
            $data = $this->sereServListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->sereServListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
            $data = $this->sereServListVViewRepository->applyServiceTypeCodesFilter($data, $this->params->serviceTypeCodes);
            $data = $this->sereServListVViewRepository->applyServiceReqIdFilter($data, $this->params->serviceReqId);
            $data = $this->sereServListVViewRepository->applyNotInTrackingFilter($data, $this->params->notInTracking);

            $count = $data->count();
            $data = $this->sereServListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            // Group theo field
            $data = $this->sereServListVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->sereServListVViewRepository->applyJoins();
            $data = $this->sereServListVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->sereServListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->sereServListVViewRepository->applyTrackingIdFilter($data, $this->params->trackingId);
            $data = $this->sereServListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->sereServListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
            $data = $this->sereServListVViewRepository->applyServiceTypeCodesFilter($data, $this->params->serviceTypeCodes);
            $data = $this->sereServListVViewRepository->applyServiceReqIdFilter($data, $this->params->serviceReqId);
            $data = $this->sereServListVViewRepository->applyNotInTrackingFilter($data, $this->params->notInTracking);
            
            $count = $data->count();
            $data = $this->sereServListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            // Group theo field
            $data = $this->sereServListVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->sereServListVViewRepository->applyJoins()
                ->where('id', $id);
            $data = $this->sereServListVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->sereServListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
        }
    }

    // public function createSereServListVView($request)
    // {
    //     try {
    //         $data = $this->sereServListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServListVViewIndex($data, $this->params->sereServListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
    //     }
    // }

    // public function updateSereServListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServListVViewIndex($data, $this->params->sereServListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
    //     }
    // }

    // public function deleteSereServListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->sereServListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
    //     }
    // }
}
