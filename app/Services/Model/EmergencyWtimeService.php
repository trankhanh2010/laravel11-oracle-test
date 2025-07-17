<?php

namespace App\Services\Model;

use App\DTOs\EmergencyWtimeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\EmergencyWtime\InsertEmergencyWtimeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\EmergencyWtimeRepository;
use Illuminate\Support\Facades\Redis;

class EmergencyWtimeService
{
    protected $emergencyWtimeRepository;
    protected $params;
    public function __construct(EmergencyWtimeRepository $emergencyWtimeRepository)
    {
        $this->emergencyWtimeRepository = $emergencyWtimeRepository;
    }
    public function withParams(EmergencyWtimeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->emergencyWtimeRepository->applyJoins();
            $data = $this->emergencyWtimeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->emergencyWtimeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->emergencyWtimeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->emergencyWtimeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emergency_wtime'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->emergencyWtimeRepository->applyJoins();
        $data = $this->emergencyWtimeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->emergencyWtimeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->emergencyWtimeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->emergencyWtimeRepository->applyJoins()
            ->where('his_emergency_wtime.id', $id);
        $data = $this->emergencyWtimeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->emergencyWtimeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->emergencyWtimeName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emergency_wtime'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->emergencyWtimeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->emergencyWtimeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emergency_wtime'], $e);
        }
    }
}
