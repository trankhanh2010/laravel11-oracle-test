<?php

namespace App\Services\Model;

use App\DTOs\ServiceReqListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceReqListVView\InsertServiceReqListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceReqListVViewRepository;

class ServiceReqListVViewService
{
    protected $serviceReqListVViewRepository;
    protected $params;
    public function __construct(ServiceReqListVViewRepository $serviceReqListVViewRepository)
    {
        $this->serviceReqListVViewRepository = $serviceReqListVViewRepository;
    }
    public function withParams(ServiceReqListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceReqListVViewRepository->applyJoins();
            $data = $this->serviceReqListVViewRepository->applyWithParam($data);
            $data = $this->serviceReqListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceReqListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->serviceReqListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->serviceReqListVViewRepository->applyTrackingIdFilter($data, $this->params->trackingId);
            $data = $this->serviceReqListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $count = $data->count();
            $data = $this->serviceReqListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceReqListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            // Group theo field
            $data = $this->serviceReqListVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_list_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->serviceReqListVViewRepository->applyJoins();
        $data = $this->serviceReqListVViewRepository->applyWithParam($data);
        $data = $this->serviceReqListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->serviceReqListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $this->serviceReqListVViewRepository->applyTrackingIdFilter($data, $this->params->trackingId);
        $data = $this->serviceReqListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $count = $data->count();
        $data = $this->serviceReqListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->serviceReqListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        // Group theo field
        $data = $this->serviceReqListVViewRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->serviceReqListVViewRepository->applyJoins()
        ->where('id', $id);
    $data = $this->serviceReqListVViewRepository->applyWithParam($data);
    $data = $this->serviceReqListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->serviceReqListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_list_v_view'], $e);
        }
    }

    // public function createServiceReqListVView($request)
    // {
    //     try {
    //         $data = $this->serviceReqListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->serviceReqListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertServiceReqListVViewIndex($data, $this->params->serviceReqListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['service_req_list_v_view'], $e);
    //     }
    // }

    // public function updateServiceReqListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->serviceReqListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->serviceReqListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->serviceReqListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertServiceReqListVViewIndex($data, $this->params->serviceReqListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['service_req_list_v_view'], $e);
    //     }
    // }

    // public function deleteServiceReqListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->serviceReqListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->serviceReqListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->serviceReqListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->serviceReqListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['service_req_list_v_view'], $e);
    //     }
    // }
}
