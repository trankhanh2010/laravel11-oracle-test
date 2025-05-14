<?php

namespace App\Services\Model;

use App\DTOs\TransactionListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TransactionListVView\InsertTransactionListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use App\Models\HIS\Transaction;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TransactionListVViewRepository;
use App\Repositories\TransactionRepository;

class TransactionListVViewService
{
    protected $transactionListVViewRepository;
    protected $transaction;
    protected $transactionRepository;
    protected $params;
    public function __construct(
        TransactionListVViewRepository $transactionListVViewRepository,
        Transaction $transaction,
        TransactionRepository $transactionRepository,
        )
    {
        $this->transaction = $transaction;
        $this->transactionListVViewRepository = $transactionListVViewRepository;
        $this->transactionRepository = $transactionRepository;
    }
    public function withParams(TransactionListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->transactionListVViewRepository->applyJoins();
        if ($this->params->treatmentCode) {
            $data = $this->transactionListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
            $data = $this->transactionListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->transactionListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit, $this->params->cursorPaginate, $this->params->lastId);
            $count = $data->count();
            return ['data' => $data, 'count' => $count];
        }
        if ($this->params->transactionCode) {
            $data = $this->transactionListVViewRepository->applyTransactionCodeFilter($data, $this->params->transactionCode);
            $data = $this->transactionListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->transactionListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit, $this->params->cursorPaginate, $this->params->lastId);
            $count = $data->count();
            return ['data' => $data, 'count' => $count];
        }
        if ($this->params->accountBookCode) {
            $data = $this->transactionListVViewRepository->applyAccountBookCodeFilter($data, $this->params->accountBookCode);
            $data = $this->transactionListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->transactionListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit, $this->params->cursorPaginate, $this->params->lastId);
            $count = $data->count();
            return ['data' => $data, 'count' => $count];
        }
        if ($this->params->transReqCode) {
            $data = $this->transactionListVViewRepository->applyTransReqCodeFilter($data, $this->params->transReqCode);
            $data = $this->transactionListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->transactionListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit, $this->params->cursorPaginate, $this->params->lastId);
            $count = $data->count();
            return ['data' => $data, 'count' => $count];
        }
        $data = $this->transactionListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->transactionListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $this->transactionListVViewRepository->applyTransactionTypeIdsFilter($data, $this->params->transactionTypeIds);
        $data = $this->transactionListVViewRepository->applyCreateFromTimeFilter($data, $this->params->createFromTime);
        $data = $this->transactionListVViewRepository->applyCreateToTimeFilter($data, $this->params->createToTime);
        $data = $this->transactionListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->transactionListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit, $this->params->cursorPaginate, $this->params->lastId);
        $count = $data->count();
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->transactionListVViewRepository->applyJoins()
        ->where('id', $id);
    $data = $this->transactionListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->transactionListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_list_v_view'], $e);
        }
    }
    public function cancelTransaction($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->transaction->find($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->transactionRepository->cancelTransaction($request, $data, $this->params->time, $this->params->appModifier);
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
        }
    }
}
