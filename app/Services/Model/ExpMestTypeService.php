<?php

namespace App\Services\Model;

use App\DTOs\ExpMestTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ExpMestType\InsertExpMestTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ExpMestTypeRepository;
use Illuminate\Support\Facades\Redis;

class ExpMestTypeService
{
    protected $expMestTypeRepository;
    protected $params;
    public function __construct(ExpMestTypeRepository $expMestTypeRepository)
    {
        $this->expMestTypeRepository = $expMestTypeRepository;
    }
    public function withParams(ExpMestTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->expMestTypeRepository->applyJoins();
            $data = $this->expMestTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->expMestTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->expMestTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->expMestTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exp_mest_type'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->expMestTypeRepository->applyJoins();
        $data = $this->expMestTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->expMestTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->expMestTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->expMestTypeRepository->applyJoins()
            ->where('his_exp_mest_type.id', $id);
        $data = $this->expMestTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->expMestTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->expMestTypeName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exp_mest_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->expMestTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->expMestTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exp_mest_type'], $e);
        }
    }
}
