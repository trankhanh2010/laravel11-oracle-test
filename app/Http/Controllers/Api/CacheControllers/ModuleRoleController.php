<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\ACS\ModuleRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ModuleRoleController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->module_role = new ModuleRole();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->module_role);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function module_role($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->module_role
                ->leftJoin('acs_module as module', 'module.id', '=', 'acs_module_role.module_id')
                ->leftJoin('acs_role as role', 'role.id', '=', 'acs_module_role.role_id')
                    ->select(
                        'acs_module_role.*',
                        'module.module_name',
                        'role.role_name'
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_acs')->raw('module.module_name'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_acs')->raw('role.role_name'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_acs')->raw('acs_module_role.is_active'), $this->is_active);
                    });
                }
                if ($this->module_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('acs_module_role.module_id'), $this->module_id);
                    });
                }
                if ($this->role_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('acs_module_role.role_id'), $this->role_id);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('acs_module_role.' . $key, $item);
                    }
                }
                if($this->get_all){
                    $data = $data
                    ->get();
                }else{
                    $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
                }
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->module_role_name .'_module_id_'.$this->module_id. '_role_id_'.$this->role_id. '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->module_role
                        ->leftJoin('acs_module as module', 'module.id', '=', 'acs_module_role.module_id')
                        ->leftJoin('acs_role as role', 'role.id', '=', 'acs_module_role.role_id')
                            ->select(
                                'acs_module_role.*',
                                'module.module_name',
                                'role.role_name'
                            );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_acs')->raw('acs_module_role.is_active'), $this->is_active);
                            });
                        }
                        if ($this->module_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('acs_module_role.module_id'), $this->module_id);
                            });
                        }
                        if ($this->role_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('acs_module_role.role_id'), $this->role_id);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('acs_module_role.' . $key, $item);
                            }
                        }
                        if($this->get_all){
                            $data = $data
                            ->get();
                        }else{
                            $data = $data
                            ->skip($this->start)
                            ->take($this->limit)
                            ->get();
                        }
                        return ['data' => $data, 'count' => $count];
                    });
                } else {
                    if (!is_numeric($id)) {
                        return return_id_error($id);
                    }
                    $check_id = $this->check_id($id, $this->module_role, $this->module_role_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->module_role_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->module_role
                        ->leftJoin('acs_module as module', 'module.id', '=', 'acs_module_role.module_id')
                        ->leftJoin('acs_role as role', 'role.id', '=', 'acs_module_role.role_id')
                            ->select(
                                'acs_module_role.*',
                                'module.module_name',
                                'role.role_name'
                            )
                            ->where('acs_module_role.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_acs')->raw('acs_module_role.is_active'), $this->is_active);
                            });
                        }
                        $data = $data->first();
                        return $data;
                    });
                }
            }
            $param_return = [
                $this->get_all_name => $this->get_all,
                $this->start_name => ($this->get_all || !is_null($id)) ? null : $this->start,
                $this->limit_name => ($this->get_all || !is_null($id)) ? null : $this->limit,
                $this->count_name => $count ?? ($data['count'] ?? null),
                $this->is_active_name => $this->is_active,
                $this->module_id_name => $this->module_id,
                $this->role_id_name => $this->role_id,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? ($data['data'] ?? null) ?? null);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
    // /// Module
    // public function module_role($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->module_role_name;
    //         $param = [
    //             'module:id,module_name',
    //             'role:id,role_name,role_code',
    //         ];
    //     } else {
    //         $name = $this->module_role_name . '_' . $id;
    //         $param = [
    //             'module',
    //             'role',
    //         ];
    //     }
    //     $data = get_cache_full($this->module_role, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
}
