<?php

namespace App\Services\Model;

use App\DTOs\ModuleRoleDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ModuleRole\InsertModuleRoleIndex;
use App\Events\Elastic\DeleteIndex;
use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ModuleRoleRepository;
use App\Repositories\ModuleRepository;
use Illuminate\Support\Facades\DB;

class ModuleRoleService
{
    protected $moduleRoleRepository;
    protected $moduleRepository;
    protected $roleRepository;
    protected $params;
    public function __construct(ModuleRoleRepository $moduleRoleRepository, ModuleRepository $moduleRepository, RoleRepository $roleRepository)
    {
        $this->moduleRoleRepository = $moduleRoleRepository;
        $this->moduleRepository = $moduleRepository;
        $this->roleRepository = $roleRepository;
    }
    public function withParams(ModuleRoleDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->moduleRoleRepository->applyJoins();
            $data = $this->moduleRoleRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->moduleRoleRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->moduleRoleRepository->applyRoleIdFilter($data, $this->params->roleId);
            $data = $this->moduleRoleRepository->applyModuleIdFilter($data, $this->params->moduleId);
            $count = $data->count();
            $data = $this->moduleRoleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->moduleRoleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['module_role'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->moduleRoleName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_module_id_' . $this->params->moduleId . '_role_id_' . $this->params->roleId . '_get_all_' . $this->params->getAll, $this->params->time, function () {
                $data = $this->moduleRoleRepository->applyJoins();
                $data = $this->moduleRoleRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->moduleRoleRepository->applyRoleIdFilter($data, $this->params->roleId);
                $data = $this->moduleRoleRepository->applyModuleIdFilter($data, $this->params->moduleId);
                $count = $data->count();
                $data = $this->moduleRoleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->moduleRoleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['module_role'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->moduleRoleName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->moduleRoleRepository->applyJoins()
                    ->where('acs_module_role.id', $id);
                $data = $this->moduleRoleRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['module_role'], $e);
        }
    }
    private function buildSyncData($request)
    {
        return [
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->params->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->params->time),
            'app_creator' => $this->params->appCreator,
            'app_modifier' => $this->params->appModifier,
        ];
    }
    public function createModuleRole($request)
    {
        try {
            if ($request->role_id != null) {
                $id = $request->role_id;
                $data = $this->roleRepository->getById($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_acs')->beginTransaction();
                try {
                    if ($request->module_ids !== null) {
                        $module_ids_arr = explode(',', $request->module_ids);
                        foreach ($module_ids_arr as $key => $item) {
                            $module_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->modules()->sync($module_ids_arr_data);
                    } else {
                        $deleteIds = $this->moduleRoleRepository->deleteByRoleId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->moduleRoleName));
                    }
                    DB::connection('oracle_acs')->commit();
                    //Cập nhật trong elastic
                    $records = $this->moduleRoleRepository->getByRoleIdAndModuleIds($id, $module_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertModuleRoleIndex($item, $this->params->moduleRoleName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_acs')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            if ($request->module_id != null) {
                $id = $request->module_id;
                $data = $this->moduleRepository->getById($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_acs')->beginTransaction();
                try {
                    if ($request->role_ids !== null) {
                        $role_ids_arr = explode(',', $request->role_ids);
                        foreach ($role_ids_arr as $key => $item) {
                            $role_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->roles()->sync($role_ids_arr_data);
                    } else {
                        $deleteIds = $this->moduleRoleRepository->deleteByModuleId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->moduleRoleName));
                    }
                    DB::connection('oracle_acs')->commit();
                    //Cập nhật trong elastic
                    $records = $this->moduleRoleRepository->getByModuleIdAndRoleIds($id, $role_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertModuleRoleIndex($item, $this->params->moduleRoleName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_acs')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            event(new DeleteCache($this->params->moduleRoleName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['module_role'], $e);
        }
    }
}