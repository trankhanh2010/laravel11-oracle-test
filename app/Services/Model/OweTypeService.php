<?php

namespace App\Services\Model;

use App\DTOs\OweTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\OweType\InsertOweTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\OweTypeRepository;
use Illuminate\Support\Facades\Redis;

class OweTypeService
{
    protected $oweTypeRepository;
    protected $params;
    public function __construct(OweTypeRepository $oweTypeRepository)
    {
        $this->oweTypeRepository = $oweTypeRepository;
    }
    public function withParams(OweTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->oweTypeRepository->applyJoins();
            $data = $this->oweTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->oweTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->oweTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->oweTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['owe_type'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->oweTypeRepository->applyJoins();
        $data = $this->oweTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->oweTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->oweTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->oweTypeRepository->applyJoins()
            ->where('his_owe_type.id', $id);
        $data = $this->oweTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->oweTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->oweTypeName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['owe_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->oweTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->oweTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['owe_type'], $e);
        }
    }
}
