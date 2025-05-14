<?php

namespace App\Services\Model;

use App\DTOs\DepositReqListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DepositReqListVView\InsertDepositReqListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use App\Models\HIS\DepositReq;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DepositReqListVViewRepository;

class DepositReqListVViewService
{
    protected $depositReqListVViewRepository;
    protected $depositReq;
    protected $params;
    public function __construct(
        DepositReqListVViewRepository $depositReqListVViewRepository,
        DepositReq $depositReq,
        )
    {
        $this->depositReqListVViewRepository = $depositReqListVViewRepository;
        $this->depositReq = $depositReq;
    }
    public function withParams(DepositReqListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->depositReqListVViewRepository->applyJoins();
            $data = $this->depositReqListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->depositReqListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->depositReqListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->depositReqListVViewRepository->applyIsDepositFilter($data, $this->params->isDeposit);
            $data = $this->depositReqListVViewRepository->applyDepositReqCodeFilter($data, $this->params->depositReqCode);
            $count = $data->count();
            $data = $this->depositReqListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->depositReqListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['deposit_req_list_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->depositReqListVViewRepository->applyJoins();
        $data = $this->depositReqListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->depositReqListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $this->depositReqListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->depositReqListVViewRepository->applyIsDepositFilter($data, $this->params->isDeposit);
        $data = $this->depositReqListVViewRepository->applyDepositReqCodeFilter($data, $this->params->depositReqCode);
        $count = $data->count();
        $data = $this->depositReqListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->depositReqListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->depositReqListVViewRepository->applyJoins()
        ->where('id', $id);
    $data = $this->depositReqListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->depositReqListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $this->depositReqListVViewRepository->applyIsDepositFilter($data, $this->params->isDeposit);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['deposit_req_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['deposit_req_list_v_view'], $e);
        }
    }

    public function createDepositReq($request)
    {
        try {
            $data = $this->depositReqListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['deposit_req'], $e);
        }
    }

    public function updateDepositReq($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->depositReq->find($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->depositReqListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['deposit_req'], $e);
        }
    }

    public function deleteDepositReq($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->depositReq->find($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->depositReqListVViewRepository->delete($data);
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['deposit_req'], $e);
        }
    }
}
