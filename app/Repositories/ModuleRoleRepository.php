<?php

namespace App\Repositories;

use App\Models\ACS\ModuleRole;
use Illuminate\Support\Facades\DB;

class ModuleRoleRepository
{
    protected $moduleRole;
    public function __construct(ModuleRole $moduleRole)
    {
        $this->moduleRole = $moduleRole;
    }

    public function applyJoins()
    {
        return $this->moduleRole
            ->leftJoin('acs_module as module', 'module.id', '=', 'acs_module_role.module_id')
            ->leftJoin('acs_role as role', 'role.id', '=', 'acs_module_role.role_id')
            ->select(
                'acs_module_role.*',
                'module.module_link',
                'module.module_name',
                'role.role_code',
                'role.role_name',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query
                ->where(DB::connection('oracle_acs')->raw('module.module_link'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_acs')->raw('module.module_name'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_acs')->raw('role.role_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_acs')->raw('role.role_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_acs')->raw('acs_module_role.is_active'), $isActive);
        }
        return $query;
    }
    public function applyModuleIdFilter($query, $moduleId)
    {
        if ($moduleId !== null) {
            $query->where(DB::connection('oracle_acs')->raw('acs_module_role.module_id'), $moduleId);
        }
        return $query;
    }
    public function applyRoleIdFilter($query, $roleId)
    {
        if ($roleId !== null) {
            $query->where(DB::connection('oracle_acs')->raw('acs_module_role.role_id'), $roleId);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['module_link', 'module_name'])) {
                        $query->orderBy('module.' . $key, $item);
                    }
                    if (in_array($key, ['role_code', 'role_name'])) {
                        $query->orderBy('role.' . $key, $item);
                    }
                } else {
                    $query->orderBy('acs_module_role.' . $key, $item);
                }
            }
        }
        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->moduleRole->find($id);
    }
    public function getByModuleIdAndRoleIds($moduleId, $roleIds)
    {
        return $this->moduleRole->where('module_id', $moduleId)->whereIn('role_id', $roleIds)->get();
    }
    public function getByRoleIdAndModuleIds($roleId, $moduleIds)
    {
        return $this->moduleRole->whereIn('module_id', $moduleIds)->where('role_id', $roleId)->get();
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function deleteByModuleId($id)
    {
        $ids = $this->moduleRole->where('module_id', $id)->pluck('id')->toArray();
        $this->moduleRole->where('module_id', $id)->delete();
        return $ids;
    }
    public function deleteByRoleId($id)
    {
        $ids = $this->moduleRole->where('role_id', $id)->pluck('id')->toArray();
        $this->moduleRole->where('role_id', $id)->delete();
        return $ids;
    }
    public function getDataFromDbToElastic($id = null)
    {
        $data = $this->applyJoins();
        if ($id != null) {
            $data = $data->where('acs_module_role.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
            }
        } else {
            $data = $data->get();
            $data = $data->map(function ($item) {
                return $item->getAttributes();
            })->toArray();
        }
        return $data;
    }
}
