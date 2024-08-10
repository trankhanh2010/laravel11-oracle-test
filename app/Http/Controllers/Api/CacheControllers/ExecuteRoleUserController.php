<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Controllers\Controller;
use App\Models\HIS\ExecuteRoleUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExecuteRoleUserController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->execute_role_user = new ExecuteRoleUser();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->execute_role_user);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function execute_role_user($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->execute_role_user
                ->leftJoin('his_employee as employee', 'employee.loginname', '=', 'his_execute_role_user.loginname')
                ->leftJoin('his_department as department', 'department.id', '=', 'employee.department_id')
                ->leftJoin('his_execute_role as execute_role', 'execute_role.id', '=', 'his_execute_role_user.execute_role_id')
                    ->select(
                        'his_execute_role_user.*',
                        'employee.tdl_username',
                        'employee.diploma',
                        'employee.tdl_email',
                        'employee.tdl_mobile',
                        'employee.DOB',
                        'department.department_code',
                        'department.department_name',
                        'execute_role.execute_role_code',
                        'execute_role.execute_role_name'
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('execute_role.execute_role_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_execute_role_user.loginname'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_execute_role_user.is_active'), $this->is_active);
                    });
                }
                if ($this->loginname !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_execute_role_user.loginname'), $this->loginname);
                    });
                }
                if ($this->execute_role_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_execute_role_user.execute_role_id'), $this->execute_role_id);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_execute_role_user.' . $key, $item);
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
                    $data = Cache::remember($this->execute_role_user_name .'_loginname_'.$this->loginname. '_execute_role_id_'.$this->execute_role_id. '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->execute_role_user
                        ->leftJoin('his_employee as employee', 'employee.loginname', '=', 'his_execute_role_user.loginname')
                        ->leftJoin('his_department as department', 'department.id', '=', 'employee.department_id')
                        ->leftJoin('his_execute_role as execute_role', 'execute_role.id', '=', 'his_execute_role_user.execute_role_id')
                            ->select(
                                'his_execute_role_user.*',
                                'employee.tdl_username',
                                'employee.diploma',
                                'employee.tdl_email',
                                'employee.tdl_mobile',
                                'employee.DOB',
                                'department.department_code',
                                'department.department_name',
                                'execute_role.execute_role_code',
                                'execute_role.execute_role_name'
                            );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_execute_role_user.is_active'), $this->is_active);
                            });
                        }
                        if ($this->loginname !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_execute_role_user.loginname'), $this->loginname);
                            });
                        }
                        if ($this->execute_role_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_execute_role_user.execute_role_id'), $this->execute_role_id);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_execute_role_user.' . $key, $item);
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
                    $check_id = $this->check_id($id, $this->execute_role_user, $this->execute_role_user_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->execute_role_user_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->execute_role_user
                        ->leftJoin('his_employee as employee', 'employee.loginname', '=', 'his_execute_role_user.loginname')
                        ->leftJoin('his_department as department', 'department.id', '=', 'employee.department_id')
                        ->leftJoin('his_execute_role as execute_role', 'execute_role.id', '=', 'his_execute_role_user.execute_role_id')
                            ->select(
                                'his_execute_role_user.*',
                                'employee.tdl_username',
                                'employee.diploma',
                                'employee.tdl_email',
                                'employee.tdl_mobile',
                                'employee.DOB',
                                'department.department_code',
                                'department.department_name',
                                'execute_role.execute_role_code',
                                'execute_role.execute_role_name'
                            )
                            ->where('his_execute_role_user.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_execute_role_user.is_active'), $this->is_active);
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
                $this->loginname_name => $this->loginname,
                $this->execute_role_id_name => $this->execute_role_id,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data?? ($data['data'] ?? null));
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
    // /// Execute Role User
    // public function execute_role_user($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->execute_role_user_name;
    //         $param = [
    //             'execute_role:id,execute_role_name',
    //         ];
    //     } else {
    //         $name = $this->execute_role_user_name . '_' . $id;
    //         $param = [
    //             'execute_role',
    //         ];
    //     }
    //     $data = get_cache_full($this->execute_role_user, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function execute_role_with_user($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->execute_role_name . '_with_' . $this->emp_user_name;
    //         $param = [
    //             'employees:id,loginname,tdl_username,department_id',
    //             'employees.department:id,department_name,department_code'
    //         ];
    //     } else {
    //         $name = $this->execute_role_name . '_' . $id . '_with_' . $this->emp_user_name;
    //         $param = [
    //             'employees',
    //             'employees.department'
    //         ];
    //     }
    //     $data = get_cache_full($this->execute_role, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function user_with_execute_role($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->emp_user_name . '_with_' . $this->execute_role_name;
    //         $param = [
    //             'execute_roles:id,execute_role_name,execute_role_code',
    //             'department:id,department_code,department_name'
    //         ];
    //     } else {
    //         $name = $this->emp_user_name . '_' . $id . '_with_' . $this->execute_role_name;
    //         $param = [
    //             'execute_roles',
    //             'department'
    //         ];
    //     }
    //     $data = get_cache_full($this->emp_user, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
}
