<?php

namespace App\Services\Model;

use App\DTOs\AwarenessDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Awareness\InsertAwarenessIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AwarenessRepository;
use Illuminate\Support\Facades\Redis;

class AwarenessService
{
    protected $awarenessRepository;
    protected $params;
    public function __construct(AwarenessRepository $awarenessRepository)
    {
        $this->awarenessRepository = $awarenessRepository;
    }
    public function withParams(AwarenessDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->awarenessRepository->applyJoins();
            $data = $this->awarenessRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->awarenessRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->awarenessRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->awarenessRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->awarenessRepository->applyJoins();
        $data = $this->awarenessRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->awarenessRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->awarenessRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->awarenessRepository->applyJoins()
            ->where('his_awareness.id', $id);
        $data = $this->awarenessRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->awarenessName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->awarenessName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->awarenessName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->awarenessName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }

    public function createAwareness($request)
    {
        try {
            $data = $this->awarenessRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertAwarenessIndex($data, $this->params->awarenessName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->awarenessName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }

    public function updateAwareness($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->awarenessRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->awarenessRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertAwarenessIndex($data, $this->params->awarenessName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->awarenessName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }

    public function deleteAwareness($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->awarenessRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->awarenessRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->awarenessName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->awarenessName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }
}
