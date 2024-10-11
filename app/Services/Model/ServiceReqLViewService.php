<?php

namespace App\Services\Model;

use App\DTOs\ServiceReqLViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceReq\InsertServiceReqIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceReqLViewRepository;

class ServiceReqLViewService
{
    protected $serviceReqLViewRepository;
    protected $params;
    public function __construct(ServiceReqLViewRepository $serviceReqLViewRepository)
    {
        $this->serviceReqLViewRepository = $serviceReqLViewRepository;
    }
    public function withParams(ServiceReqLViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceReqLViewRepository->applyJoins();
            $data = $this->serviceReqLViewRepository->applyKeywordFilter($data, $this->params->keyword);
            // $data = $this->serviceReqLViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            // $data = $this->serviceReqLViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->serviceReqLViewRepository->applyServiceReqSttIdsFilter($data, $this->params->serviceReqSttIds);
            $data = $this->serviceReqLViewRepository->applyNotInServiceReqTypeIdsFilter($data, $this->params->notInServiceReqTypeIds);
            $data = $this->serviceReqLViewRepository->applyTdlPatientTypeIdsFilter($data, $this->params->tdlPatientTypeIds);
            $data = $this->serviceReqLViewRepository->applyExecuteRoomIdFilter($data, $this->params->executeRoomId);
            $data = $this->serviceReqLViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
            $data = $this->serviceReqLViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
            $data = $this->serviceReqLViewRepository->applyHasExecuteFilter($data, $this->params->hasExecute);
            $data = $this->serviceReqLViewRepository->applyIsNotKskRequriedAprovalOrIsKskApproveFilter($data, $this->params->isNotKskRequriedAprovalOrIsKskApprove);
            $count = $data->count();
            $data = $this->serviceReqLViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceReqLViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_l_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->serviceReqLViewRepository->applyJoins();
            // $data = $this->serviceReqLViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            // $data = $this->serviceReqLViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->serviceReqLViewRepository->applyServiceReqSttIdsFilter($data, $this->params->serviceReqSttIds);
            $data = $this->serviceReqLViewRepository->applyNotInServiceReqTypeIdsFilter($data, $this->params->notInServiceReqTypeIds);
            $data = $this->serviceReqLViewRepository->applyTdlPatientTypeIdsFilter($data, $this->params->tdlPatientTypeIds);
            $data = $this->serviceReqLViewRepository->applyExecuteRoomIdFilter($data, $this->params->executeRoomId);
            $data = $this->serviceReqLViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
            $data = $this->serviceReqLViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
            $data = $this->serviceReqLViewRepository->applyHasExecuteFilter($data, $this->params->hasExecute);
            $data = $this->serviceReqLViewRepository->applyIsNotKskRequriedAprovalOrIsKskApproveFilter($data, $this->params->isNotKskRequriedAprovalOrIsKskApprove);
            $count = $data->count();
            $data = $this->serviceReqLViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceReqLViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_l_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->serviceReqLViewRepository->applyJoins()
                ->where('l_his_service_req.id', $id);
            // $data = $this->serviceReqLViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            // $data = $this->serviceReqLViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_l_view'], $e);
        }
    }
}
