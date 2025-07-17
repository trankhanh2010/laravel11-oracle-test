<?php

namespace App\Services\Model;

use App\DTOs\BloodRhDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BloodRh\InsertBloodRhIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BloodRhRepository;
use Illuminate\Support\Facades\Redis;

class BloodRhService
{
    protected $bloodRhRepository;
    protected $params;
    public function __construct(BloodRhRepository $bloodRhRepository)
    {
        $this->bloodRhRepository = $bloodRhRepository;
    }
    public function withParams(BloodRhDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bloodRhRepository->applyJoins();
            $data = $this->bloodRhRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bloodRhRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bloodRhRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bloodRhRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_rh'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->bloodRhRepository->applyJoins();
        $data = $this->bloodRhRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->bloodRhRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bloodRhRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->bloodRhRepository->applyJoins()
            ->where('his_blood_rh.id', $id);
        $data = $this->bloodRhRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->bloodRhName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->bloodRhName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_rh'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->bloodRhName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->bloodRhName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_rh'], $e);
        }
    }
}
