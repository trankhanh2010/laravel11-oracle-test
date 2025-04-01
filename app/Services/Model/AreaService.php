<?php

namespace App\Services\Model;

use App\DTOs\AreaDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Area\InsertAreaIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AreaRepository;
use Illuminate\Support\Facades\Redis;

class AreaService
{
    protected $areaRepository;
    protected $params;
    public function __construct(AreaRepository $areaRepository)
    {
        $this->areaRepository = $areaRepository;
    }
    public function withParams(AreaDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->areaRepository->applyJoins();
            $data = $this->areaRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->areaRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->areaRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->areaRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->areaRepository->applyJoins();
        $data = $this->areaRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->areaRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->areaRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->areaRepository->applyJoins()
            ->where('his_area.id', $id);
        $data = $this->areaRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->areaName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->areaName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->areaName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->areaName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }

    public function createArea($request)
    {
        try {
            $data = $this->areaRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertAreaIndex($data, $this->params->areaName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->areaName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }

    public function updateArea($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->areaRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->areaRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertAreaIndex($data, $this->params->areaName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->areaName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }

    public function deleteArea($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->areaRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->areaRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->areaName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->areaName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }
}
