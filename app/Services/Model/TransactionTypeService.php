<?php

namespace App\Services\Model;

use App\DTOs\TransactionTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TransactionType\InsertTransactionTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TransactionTypeRepository;
use Illuminate\Support\Facades\Redis;

class TransactionTypeService
{
    protected $transactionTypeRepository;
    protected $params;
    public function __construct(TransactionTypeRepository $transactionTypeRepository)
    {
        $this->transactionTypeRepository = $transactionTypeRepository;
    }
    public function withParams(TransactionTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->transactionTypeRepository->applyJoins();
            $data = $this->transactionTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->transactionTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->transactionTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->transactionTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->transactionTypeRepository->applyJoins();
        $data = $this->transactionTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->transactionTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->transactionTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->transactionTypeRepository->applyJoins()
            ->where('his_transaction_type.id', $id);
        $data = $this->transactionTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabase();
            } else {
                $cacheKey = $this->params->transactionTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->transactionTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->transactionTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->transactionTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_type'], $e);
        }
    }

    public function createTransactionType($request)
    {
        try {
            $data = $this->transactionTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertTransactionTypeIndex($data, $this->params->transactionTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->transactionTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_type'], $e);
        }
    }

    public function updateTransactionType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->transactionTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->transactionTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertTransactionTypeIndex($data, $this->params->transactionTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->transactionTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_type'], $e);
        }
    }

    public function deleteTransactionType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->transactionTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->transactionTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->transactionTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->transactionTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_type'], $e);
        }
    }
}
