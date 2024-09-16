<?php

namespace App\Services\Model;

use App\DTOs\ExecuteRoleUserDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ExecuteRoleUser\InsertExecuteRoleUserIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ExecuteRoleUserRepository;

class ExecuteRoleUserService 
{
    protected $executeRoleUserRepository;
    protected $params;
    public function __construct(ExecuteRoleUserRepository $executeRoleUserRepository)
    {
        $this->executeRoleUserRepository = $executeRoleUserRepository;
    }
    public function withParams(ExecuteRoleUserDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->executeRoleUserRepository->applyJoins();
            $data = $this->executeRoleUserRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->executeRoleUserRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->executeRoleUserRepository->applyLoginnameFilter($data, $this->params->loginname);
            $data = $this->executeRoleUserRepository->applyExecuteRoleIdFilter($data, executeRoleId: $this->params->executeRoleId);
            $count = $data->count();
            $data = $this->executeRoleUserRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->executeRoleUserRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role_user'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->executeRoleUserName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_loginname_' . $this->params->loginname . '_execute_role_id_'. $this->params->executeRoleId . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->executeRoleUserRepository->applyJoins();
                $data = $this->executeRoleUserRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->executeRoleUserRepository->applyLoginnameFilter($data, $this->params->loginname);
                $data = $this->executeRoleUserRepository->applyExecuteRoleIdFilter($data, executeRoleId: $this->params->executeRoleId);
                $count = $data->count();
                $data = $this->executeRoleUserRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->executeRoleUserRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role_user'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->executeRoleUserName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->executeRoleUserRepository->applyJoins()
                    ->where('his_execute_role_user.id', $id);
                $data = $this->executeRoleUserRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role_user'], $e);
        }
    }
    public function deleteExecuteRoleUser($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->executeRoleUserRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->executeRoleUserRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->executeRoleUserName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->executeRoleUserName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_role_user'], $e);
        }
    }
}
