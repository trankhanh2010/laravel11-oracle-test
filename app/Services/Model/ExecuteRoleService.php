<?php

namespace App\Services\Model;

use App\DTOs\ExecuteRoleDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ExecuteRole\InsertExecuteRoleIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ExecuteRoleRepository;
use Illuminate\Support\Facades\Redis;

class ExecuteRoleService 
{
    protected $executeRoleRepository;
    protected $params;
    public function __construct(ExecuteRoleRepository $executeRoleRepository)
    {
        $this->executeRoleRepository = $executeRoleRepository;
    }
    public function withParams(ExecuteRoleDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->executeRoleRepository->applyJoins();
            $data = $this->executeRoleRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->executeRoleRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->executeRoleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->executeRoleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->executeRoleName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->executeRoleName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->executeRoleRepository->applyJoins();
                $data = $this->executeRoleRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->executeRoleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->executeRoleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->executeRoleName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->executeRoleRepository->applyJoins()
                    ->where('his_execute_role.id', $id);
                $data = $this->executeRoleRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role'], $e);
        }
    }

    public function createExecuteRole($request)
    {
        try {
            $data = $this->executeRoleRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertExecuteRoleIndex($data, $this->params->executeRoleName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->executeRoleName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role'], $e);
        }
    }

    public function updateExecuteRole($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->executeRoleRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->executeRoleRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertExecuteRoleIndex($data, $this->params->executeRoleName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->executeRoleName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role'], $e);
        }
    }

    public function deleteExecuteRole($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->executeRoleRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->executeRoleRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->executeRoleName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->executeRoleName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role'], $e);
        }
    }
}
