<?php

namespace App\Repositories;

use App\Models\ACS\Role;
use Illuminate\Support\Facades\DB;

class RoleRepository
{
    protected $role;
    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function applyJoins()
    {
        return $this->role
            ->with([
                'modules:id,module_name'
            ])
            ->select(
                'acs_role.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_acs')->raw('acs_role.role_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_acs')->raw('acs_role.role_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_acs')->raw('acs_role.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('acs_role.' . $key, $item);
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
        return $this->role->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->role::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'role_code' => $request->role_code,
            'role_name' => $request->role_name,
            'is_full' => $request->is_full,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'role_code' => $request->role_code,
            'role_name' => $request->role_name,
            'is_full' => $request->is_full,
            'is_active' => $request->is_active,
        ]);
        return $data;
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($id = null)
    {
        $data = $this->applyJoins();
        if ($id != null) {
            $data = $data->where('acs_role.id', '=', $id)->first();
            if ($data) {
                $data->toArray();
            }
        } else {
            $data = $data->get();
            $data->toArray();
        }
        return $data;
    }
}
