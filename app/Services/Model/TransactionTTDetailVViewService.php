<?php

namespace App\Services\Model;

use App\DTOs\TransactionTTDetailVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TransactionTTDetailVView\InsertTransactionTTDetailVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TransactionTTDetailVViewRepository;

class TransactionTTDetailVViewService
{
    protected $transactionTTDetailVViewRepository;
    protected $params;
    public function __construct(TransactionTTDetailVViewRepository $transactionTTDetailVViewRepository)
    {
        $this->transactionTTDetailVViewRepository = $transactionTTDetailVViewRepository;
    }
    public function withParams(TransactionTTDetailVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->transactionTTDetailVViewRepository->applyJoins();
            $data = $this->transactionTTDetailVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->transactionTTDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->transactionTTDetailVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->transactionTTDetailVViewRepository->applyBillIdFilter($data, $this->params->billId);
            $data = $this->transactionTTDetailVViewRepository->applyBillCodeFilter($data, $this->params->billCode);
            $count = $data->count();
            $data = $this->transactionTTDetailVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->transactionTTDetailVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_tt_detail_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->transactionTTDetailVViewRepository->applyJoins();
            $data = $this->transactionTTDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->transactionTTDetailVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->transactionTTDetailVViewRepository->applyBillIdFilter($data, $this->params->billId);
            $data = $this->transactionTTDetailVViewRepository->applyBillCodeFilter($data, $this->params->billCode);
            $count = $data->count();
            $data = $this->transactionTTDetailVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->transactionTTDetailVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_tt_detail_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->transactionTTDetailVViewRepository->applyJoins()
                ->where('id', $id);
            $data = $this->transactionTTDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->transactionTTDetailVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_tt_detail_v_view'], $e);
        }
    }

    // public function createTransactionTTDetailVView($request)
    // {
    //     try {
    //         $data = $this->transactionTTDetailVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->transactionTTDetailVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTransactionTTDetailVViewIndex($data, $this->params->transactionTTDetailVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_bill'], $e);
    //     }
    // }

    // public function updateTransactionTTDetailVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->transactionTTDetailVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->transactionTTDetailVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->transactionTTDetailVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTransactionTTDetailVViewIndex($data, $this->params->transactionTTDetailVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_bill'], $e);
    //     }
    // }

    // public function deleteTransactionTTDetailVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->transactionTTDetailVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->transactionTTDetailVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->transactionTTDetailVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->transactionTTDetailVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_bill'], $e);
    //     }
    // }
}
