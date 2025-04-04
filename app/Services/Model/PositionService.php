<?php

namespace App\Services\Model;

use App\DTOs\PositionDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Position\InsertPositionIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PositionRepository;
use Illuminate\Support\Facades\Redis;

class PositionService
{
    protected $positionRepository;
    protected $params;
    public function __construct(PositionRepository $positionRepository)
    {
        $this->positionRepository = $positionRepository;
    }
    public function withParams(PositionDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->positionRepository->applyJoins();
            $data = $this->positionRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->positionRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->positionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->positionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['position'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->positionRepository->applyJoins();
        $data = $this->positionRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->positionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->positionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->positionRepository->applyJoins()
            ->where('his_position.id', $id);
        $data = $this->positionRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->positionName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->positionName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['position'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->positionName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->positionName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['position'], $e);
        }
    }

    public function createPosition($request)
    {
        try {
            $data = $this->positionRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPositionIndex($data, $this->params->positionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->positionName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['position'], $e);
        }
    }

    public function updatePosition($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->positionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->positionRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPositionIndex($data, $this->params->positionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->positionName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['position'], $e);
        }
    }

    public function deletePosition($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->positionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->positionRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->positionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->positionName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['position'], $e);
        }
    }
}
