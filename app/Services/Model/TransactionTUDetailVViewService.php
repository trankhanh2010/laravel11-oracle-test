<?php

namespace App\Services\Model;

use App\DTOs\TransactionTUDetailVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TransactionTUDetailVView\InsertTransactionTUDetailVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TransactionTUDetailVViewRepository;

class TransactionTUDetailVViewService
{
    protected $transactionTUDetailVViewRepository;
    protected $params;
    public function __construct(TransactionTUDetailVViewRepository $transactionTUDetailVViewRepository)
    {
        $this->transactionTUDetailVViewRepository = $transactionTUDetailVViewRepository;
    }
    public function withParams(TransactionTUDetailVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->transactionTUDetailVViewRepository->applyJoins();
            $data = $this->transactionTUDetailVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->transactionTUDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->transactionTUDetailVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->transactionTUDetailVViewRepository->applyDepositIdFilter($data, $this->params->depositId);
            $data = $this->transactionTUDetailVViewRepository->applyDepositCodeFilter($data, $this->params->depositCode);
            $count = $data->count();
            $data = $this->transactionTUDetailVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->transactionTUDetailVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            $data = $this->transactionTUDetailVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_tu_detail_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->transactionTUDetailVViewRepository->applyJoins();
        $data = $this->transactionTUDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->transactionTUDetailVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $this->transactionTUDetailVViewRepository->applyDepositIdFilter($data, $this->params->depositId);
        $data = $this->transactionTUDetailVViewRepository->applyDepositCodeFilter($data, $this->params->depositCode);
        $count = $data->count();
        $data = $this->transactionTUDetailVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->transactionTUDetailVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $data = $this->transactionTUDetailVViewRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->transactionTUDetailVViewRepository->applyJoins()
        ->where('id', $id);
    $data = $this->transactionTUDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->transactionTUDetailVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_tu_detail_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_tu_detail_v_view'], $e);
        }
    }

    // public function createTransactionTUDetailVView($request)
    // {
    //     try {
    //         $data = $this->transactionTUDetailVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->transactionTUDetailVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTransactionTUDetailVViewIndex($data, $this->params->transactionTUDetailVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_bill'], $e);
    //     }
    // }

    // public function updateTransactionTUDetailVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->transactionTUDetailVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->transactionTUDetailVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->transactionTUDetailVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTransactionTUDetailVViewIndex($data, $this->params->transactionTUDetailVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_bill'], $e);
    //     }
    // }

    // public function deleteTransactionTUDetailVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->transactionTUDetailVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->transactionTUDetailVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->transactionTUDetailVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->transactionTUDetailVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_bill'], $e);
    //     }
    // }
}
