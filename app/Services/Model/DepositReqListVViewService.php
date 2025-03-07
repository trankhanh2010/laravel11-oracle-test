<?php

namespace App\Services\Model;

use App\DTOs\DepositReqListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DepositReqListVView\InsertDepositReqListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DepositReqListVViewRepository;

class DepositReqListVViewService
{
    protected $depositReqListVViewRepository;
    protected $params;
    public function __construct(DepositReqListVViewRepository $depositReqListVViewRepository)
    {
        $this->depositReqListVViewRepository = $depositReqListVViewRepository;
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
            $count = $data->count();
            $data = $this->depositReqListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->depositReqListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['deposit_req_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->depositReqListVViewRepository->applyJoins();
            $data = $this->depositReqListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->depositReqListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->depositReqListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->depositReqListVViewRepository->applyIsDepositFilter($data, $this->params->isDeposit);
            $count = $data->count();
            $data = $this->depositReqListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->depositReqListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['deposit_req_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->depositReqListVViewRepository->applyJoins()
                ->where('id', $id);
            $data = $this->depositReqListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->depositReqListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->depositReqListVViewRepository->applyIsDepositFilter($data, $this->params->isDeposit);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['deposit_req_list_v_view'], $e);
        }
    }

    // public function createDepositReqListVView($request)
    // {
    //     try {
    //         $data = $this->depositReqListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->depositReqListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDepositReqListVViewIndex($data, $this->params->depositReqListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['deposit_req_list_v_view'], $e);
    //     }
    // }

    // public function updateDepositReqListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->depositReqListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->depositReqListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->depositReqListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDepositReqListVViewIndex($data, $this->params->depositReqListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['deposit_req_list_v_view'], $e);
    //     }
    // }

    // public function deleteDepositReqListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->depositReqListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->depositReqListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->depositReqListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->depositReqListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['deposit_req_list_v_view'], $e);
    //     }
    // }
}
