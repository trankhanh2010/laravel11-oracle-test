<?php

namespace App\Services\Model;

use App\DTOs\RefectoryDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Refectory\InsertRefectoryIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\RefectoryRepository;
use Illuminate\Support\Facades\Redis;

class RefectoryService
{
    protected $refectoryRepository;
    protected $params;
    public function __construct(RefectoryRepository $refectoryRepository)
    {
        $this->refectoryRepository = $refectoryRepository;
    }
    public function withParams(RefectoryDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->refectoryRepository->applyJoins();
            $data = $this->refectoryRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->refectoryRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->refectoryRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->refectoryRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['refectory'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->refectoryRepository->applyJoins();
        $data = $this->refectoryRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->refectoryRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->refectoryRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->refectoryRepository->applyJoins()
            ->where('his_refectory.id', $id);
        $data = $this->refectoryRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->refectoryName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->refectoryName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['refectory'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->refectoryName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->refectoryName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['refectory'], $e);
        }
    }

    public function createRefectory($request)
    {
        try {
            $data = $this->refectoryRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertRefectoryIndex($data, $this->params->refectoryName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->refectoryName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['refectory'], $e);
        }
    }

    public function updateRefectory($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->refectoryRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->refectoryRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertRefectoryIndex($data, $this->params->refectoryName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->refectoryName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['refectory'], $e);
        }
    }

    public function deleteRefectory($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->refectoryRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->refectoryRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->refectoryName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->refectoryName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['refectory'], $e);
        }
    }
}
