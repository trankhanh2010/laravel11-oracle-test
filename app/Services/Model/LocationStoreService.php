<?php

namespace App\Services\Model;

use App\DTOs\LocationStoreDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\LocationStore\InsertLocationStoreIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\LocationStoreRepository;
use Illuminate\Support\Facades\Redis;

class LocationStoreService
{
    protected $locationStoreRepository;
    protected $params;
    public function __construct(LocationStoreRepository $locationStoreRepository)
    {
        $this->locationStoreRepository = $locationStoreRepository;
    }
    public function withParams(LocationStoreDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->locationStoreRepository->applyJoins();
            $data = $this->locationStoreRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->locationStoreRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->locationStoreRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->locationStoreRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['location_store'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->locationStoreRepository->applyJoins();
        $data = $this->locationStoreRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->locationStoreRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->locationStoreRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->locationStoreRepository->applyJoins()
            ->where('his_location_store.id', $id);
        $data = $this->locationStoreRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->locationStoreName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->locationStoreName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['location_store'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->locationStoreName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->locationStoreName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['location_store'], $e);
        }
    }

    public function createLocationStore($request)
    {
        try {
            $data = $this->locationStoreRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertLocationStoreIndex($data, $this->params->locationStoreName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->locationStoreName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['location_store'], $e);
        }
    }

    public function updateLocationStore($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->locationStoreRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->locationStoreRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertLocationStoreIndex($data, $this->params->locationStoreName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->locationStoreName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['location_store'], $e);
        }
    }

    public function deleteLocationStore($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->locationStoreRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->locationStoreRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->locationStoreName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->locationStoreName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['location_store'], $e);
        }
    }
}
