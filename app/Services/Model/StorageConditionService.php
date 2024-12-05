<?php

namespace App\Services\Model;

use App\DTOs\StorageConditionDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\StorageCondition\InsertStorageConditionIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\StorageConditionRepository;

class StorageConditionService 
{
    protected $storageConditionRepository;
    protected $params;
    public function __construct(StorageConditionRepository $storageConditionRepository)
    {
        $this->storageConditionRepository = $storageConditionRepository;
    }
    public function withParams(StorageConditionDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->storageConditionRepository->applyJoins();
            $data = $this->storageConditionRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->storageConditionRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->storageConditionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->storageConditionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['storage_condition'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->storageConditionName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->storageConditionRepository->applyJoins();
                $data = $this->storageConditionRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->storageConditionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->storageConditionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['storage_condition'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->storageConditionName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->storageConditionRepository->applyJoins()
                    ->where('his_storage_condition.id', $id);
                $data = $this->storageConditionRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['storage_condition'], $e);
        }
    }

    public function createStorageCondition($request)
    {
        try {
            $data = $this->storageConditionRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertStorageConditionIndex($data, $this->params->storageConditionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->storageConditionName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['storage_condition'], $e);
        }
    }

    public function updateStorageCondition($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->storageConditionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->storageConditionRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertStorageConditionIndex($data, $this->params->storageConditionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->storageConditionName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['storage_condition'], $e);
        }
    }

    public function deleteStorageCondition($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->storageConditionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->storageConditionRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->storageConditionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->storageConditionName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['storage_condition'], $e);
        }
    }
}
