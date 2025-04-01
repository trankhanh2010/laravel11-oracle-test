<?php

namespace App\Services\Model;

use App\DTOs\ExecuteGroupDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ExecuteGroup\InsertExecuteGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ExecuteGroupRepository;
use Illuminate\Support\Facades\Redis;

class ExecuteGroupService
{
    protected $executeGroupRepository;
    protected $params;
    public function __construct(ExecuteGroupRepository $executeGroupRepository)
    {
        $this->executeGroupRepository = $executeGroupRepository;
    }
    public function withParams(ExecuteGroupDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->executeGroupRepository->applyJoins();
            $data = $this->executeGroupRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->executeGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->executeGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->executeGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_group'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->executeGroupRepository->applyJoins();
        $data = $this->executeGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->executeGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->executeGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->executeGroupRepository->applyJoins()
            ->where('his_execute_group.id', $id);
        $data = $this->executeGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->executeGroupName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->executeGroupName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_group'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->executeGroupName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->executeGroupName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_group'], $e);
        }
    }

    public function createExecuteGroup($request)
    {
        try {
            $data = $this->executeGroupRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertExecuteGroupIndex($data, $this->params->executeGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->executeGroupName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_group'], $e);
        }
    }

    public function updateExecuteGroup($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->executeGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->executeGroupRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertExecuteGroupIndex($data, $this->params->executeGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->executeGroupName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_group'], $e);
        }
    }

    public function deleteExecuteGroup($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->executeGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->executeGroupRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->executeGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->executeGroupName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_group'], $e);
        }
    }
}
