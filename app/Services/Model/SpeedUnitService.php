<?php

namespace App\Services\Model;

use App\DTOs\SpeedUnitDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SpeedUnit\InsertSpeedUnitIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SpeedUnitRepository;
use Illuminate\Support\Facades\Redis;

class SpeedUnitService
{
    protected $speedUnitRepository;
    protected $params;
    public function __construct(SpeedUnitRepository $speedUnitRepository)
    {
        $this->speedUnitRepository = $speedUnitRepository;
    }
    public function withParams(SpeedUnitDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->speedUnitRepository->applyJoins();
            $data = $this->speedUnitRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->speedUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->speedUnitRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->speedUnitRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['speed_unit'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->speedUnitRepository->applyJoins();
        $data = $this->speedUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->speedUnitRepository->applyIsDeleteFilter($data, 0);
        $count = $data->count();
        $data = $this->speedUnitRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->speedUnitRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->speedUnitRepository->applyJoins()
            ->where('his_speed_unit.id', $id);
        $data = $this->speedUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->speedUnitName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->speedUnitName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['speed_unit'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->speedUnitName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->speedUnitName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['speed_unit'], $e);
        }
    }

}
