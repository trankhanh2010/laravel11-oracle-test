<?php

namespace App\Services\Model;

use App\DTOs\RoleDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Role\InsertRoleIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\RoleRepository;

class RoleService 
{
    protected $roleRepository;
    protected $params;
    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }
    public function withParams(RoleDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->roleRepository->applyJoins();
            $data = $this->roleRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->roleRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->roleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->roleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['role'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->roleName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->roleRepository->applyJoins();
                $data = $this->roleRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->roleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->roleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['role'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->roleName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->roleRepository->applyJoins()
                    ->where('acs_role.id', $id);
                $data = $this->roleRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['role'], $e);
        }
    }

    public function createRole($request)
    {
        try {
            $data = $this->roleRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->roleName));
            // Gọi event để thêm index vào elastic
            event(new InsertRoleIndex($data, $this->params->roleName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['role'], $e);
        }
    }

    public function updateRole($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->roleRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->roleRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->roleName));
            // Gọi event để thêm index vào elastic
            event(new InsertRoleIndex($data, $this->params->roleName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['role'], $e);
        }
    }

    public function deleteRole($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->roleRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->roleRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->roleName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->roleName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['role'], $e);
        }
    }
}
