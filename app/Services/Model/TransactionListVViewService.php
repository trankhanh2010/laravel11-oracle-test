<?php

namespace App\Services\Model;

use App\DTOs\TransactionListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TransactionListVView\InsertTransactionListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TransactionListVViewRepository;

class TransactionListVViewService
{
    protected $transactionListVViewRepository;
    protected $params;
    public function __construct(TransactionListVViewRepository $transactionListVViewRepository)
    {
        $this->transactionListVViewRepository = $transactionListVViewRepository;
    }
    public function withParams(TransactionListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->transactionListVViewRepository->applyJoins();
            if($this->params->treatmentCode || $this->params->transactionCode){
                if($this->params->treatmentCode){
                    $data = $this->transactionListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
                }
                if($this->params->transactionCode){
                    $data = $this->transactionListVViewRepository->applyTransactionCodeFilter($data, $this->params->transactionCode);
                }
            }else{
                $data = $this->transactionListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->transactionListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
                $data = $this->transactionListVViewRepository->applyTransactionTypeIdsFilter($data, $this->params->transactionTypeIds);
                $data = $this->transactionListVViewRepository->applyCreateFromTimeFilter($data, $this->params->createFromTime);
                $data = $this->transactionListVViewRepository->applyCreateToTimeFilter($data, $this->params->createToTime);
            }
            $data = $this->transactionListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->transactionListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit, $this->params->cursorPaginate, $this->params->lastId);
            if ($this->params->getAll) {
                $count = $data->count();
            } else {
                $count = null;
            }
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->transactionListVViewRepository->applyJoins()
                ->where('v_his_transaction_list.id', $id);
            $data = $this->transactionListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->transactionListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_list_v_view'], $e);
        }
    }
}
