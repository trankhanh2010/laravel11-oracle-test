<?php

namespace App\Services\Model;

use App\DTOs\DataStoreDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DataStore\InsertDataStoreIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DataStoreRepository;
use Illuminate\Support\Facades\Redis;

class DataStoreService 
{
    protected $dataStoreRepository;
    protected $params;
    public function __construct(DataStoreRepository $dataStoreRepository)
    {
        $this->dataStoreRepository = $dataStoreRepository;
    }
    public function withParams(DataStoreDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->dataStoreRepository->applyJoins();
            $data = $this->dataStoreRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->dataStoreRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->dataStoreRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->dataStoreRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['data_store'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->dataStoreName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->dataStoreName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->dataStoreRepository->applyJoins();
                $data = $this->dataStoreRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->dataStoreRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->dataStoreRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['data_store'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->dataStoreName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->dataStoreRepository->applyJoins()
                    ->where('his_data_store.id', $id);
                $data = $this->dataStoreRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['data_store'], $e);
        }
    }

    public function createDataStore($request)
    {
        try {
            $data = $this->dataStoreRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertDataStoreIndex($data, $this->params->dataStoreName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->dataStoreName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['data_store'], $e);
        }
    }

    public function updateDataStore($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->dataStoreRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->dataStoreRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertDataStoreIndex($data, $this->params->dataStoreName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->dataStoreName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['data_store'], $e);
        }
    }

    public function deleteDataStore($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->dataStoreRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->dataStoreRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->dataStoreName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->dataStoreName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['data_store'], $e);
        }
    }
}
