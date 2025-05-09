<?php

namespace App\Services\Model;

use App\DTOs\ExpMestReasonDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ExpMestReason\InsertExpMestReasonIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ExpMestReasonRepository;
use Illuminate\Support\Facades\Redis;

class ExpMestReasonService
{
    protected $expMestReasonRepository;
    protected $params;
    public function __construct(ExpMestReasonRepository $expMestReasonRepository)
    {
        $this->expMestReasonRepository = $expMestReasonRepository;
    }
    public function withParams(ExpMestReasonDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->expMestReasonRepository->applyJoins();
            $data = $this->expMestReasonRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->expMestReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->expMestReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->expMestReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exp_mest_reason'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->expMestReasonRepository->applyJoins();
        $data = $this->expMestReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->expMestReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->expMestReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->expMestReasonRepository->applyJoins()
            ->where('his_exp_mest_reason.id', $id);
        $data = $this->expMestReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->expMestReasonName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->expMestReasonName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exp_mest_reason'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->expMestReasonName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->expMestReasonName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exp_mest_reason'], $e);
        }
    }
    public function createExpMestReason($request)
    {
        try {
            $data = $this->expMestReasonRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertExpMestReasonIndex($data, $this->params->expMestReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->expMestReasonName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exp_mest_reason'], $e);
        }
    }

    public function updateExpMestReason($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->expMestReasonRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->expMestReasonRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertExpMestReasonIndex($data, $this->params->expMestReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->expMestReasonName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exp_mest_reason'], $e);
        }
    }

    public function deleteExpMestReason($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->expMestReasonRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->expMestReasonRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->expMestReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->expMestReasonName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exp_mest_reason'], $e);
        }
    }
}
