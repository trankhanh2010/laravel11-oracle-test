<?php

namespace App\Services\Model;

use App\DTOs\ServiceReqDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceReq\InsertServiceReqIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceReqRepository;

class ServiceReqService
{
    protected $serviceReqRepository;
    protected $params;
    public function __construct(ServiceReqRepository $serviceReqRepository)
    {
        $this->serviceReqRepository = $serviceReqRepository;
    }
    public function withParams(ServiceReqDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceReqRepository->lView();
            $data = $this->serviceReqRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceReqRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->serviceReqRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->serviceReqRepository->applyServiceReqSttIdsFilter($data, $this->params->serviceReqSttIds);
            $data = $this->serviceReqRepository->applyNotInServiceReqTypeIdsFilter($data, $this->params->notInServiceReqTypeIds);
            $data = $this->serviceReqRepository->applyTdlPatientTypeIdsFilter($data, $this->params->tdlPatientTypeIds);
            $data = $this->serviceReqRepository->applyExecuteRoomIdFilter($data, $this->params->executeRoomId);
            $data = $this->serviceReqRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
            $data = $this->serviceReqRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
            $data = $this->serviceReqRepository->applyHasExecuteFilter($data, $this->params->hasExecute);
            $data = $this->serviceReqRepository->applyIsNotKskRequriedAprovalOrIsKskApproveFilter($data, $this->params->isNotKskRequriedAprovalOrIsKskApprove);
            $count = $data->count();
            $data = $this->serviceReqRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceReqRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->serviceReqRepository->lView();
            $data = $this->serviceReqRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->serviceReqRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->serviceReqRepository->applyServiceReqSttIdsFilter($data, $this->params->serviceReqSttIds);
            $data = $this->serviceReqRepository->applyNotInServiceReqTypeIdsFilter($data, $this->params->notInServiceReqTypeIds);
            $data = $this->serviceReqRepository->applyTdlPatientTypeIdsFilter($data, $this->params->tdlPatientTypeIds);
            $data = $this->serviceReqRepository->applyExecuteRoomIdFilter($data, $this->params->executeRoomId);
            $data = $this->serviceReqRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
            $data = $this->serviceReqRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
            $data = $this->serviceReqRepository->applyHasExecuteFilter($data, $this->params->hasExecute);
            $data = $this->serviceReqRepository->applyIsNotKskRequriedAprovalOrIsKskApproveFilter($data, $this->params->isNotKskRequriedAprovalOrIsKskApprove);
            $count = $data->count();
            $data = $this->serviceReqRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceReqRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->serviceReqRepository->lView()
                ->where('his_service_req.id', $id);
            $data = $this->serviceReqRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->serviceReqRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req'], $e);
        }
    }

    // public function createServiceReq($request)
    // {
    //     try {
    //         $data = $this->serviceReqRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->serviceReqName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertServiceReqIndex($data, $this->params->serviceReqName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['service_req'], $e);
    //     }
    // }

    // public function updateServiceReq($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->serviceReqRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->serviceReqRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->serviceReqName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertServiceReqIndex($data, $this->params->serviceReqName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['service_req'], $e);
    //     }
    // }

    // public function deleteServiceReq($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->serviceReqRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->serviceReqRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->serviceReqName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->serviceReqName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['service_req'], $e);
    //     }
    // }
}
