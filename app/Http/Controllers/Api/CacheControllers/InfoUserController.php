<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Controllers\Controller;
use App\Models\ACS\Token;
use App\Models\ACS\User;
use App\Models\HIS\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InfoUserController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->employee = new Employee();
        $this->token = new Token();
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->employee);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function info_user(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        $login_name = $this->token->where('token_code', $request->bearerToken())->first()->login_name;
        $id = $this->employee->where('loginname', $login_name)->first()->id;
        try {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $check_id = $this->check_id($id, $this->employee, $this->employee_name);
            if($check_id){
                return $check_id; 
            }
            $data = Cache::remember($this->employee_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                $data = $this->employee
                    ->leftJoin('his_department as department', 'department.id', '=', 'his_employee.department_id')
                    ->leftJoin('his_gender as gender', 'gender.id', '=', 'his_employee.gender_id')
                    ->leftJoin('his_branch as branch', 'branch.id', '=', 'his_employee.branch_id')
                    ->leftJoin('his_career_title as career_title', 'career_title.id', '=', 'his_employee.career_title_id')

                    ->select(
                        'his_employee.*',
                        'department.department_name',
                        'department.department_code',
                        'gender.gender_name',
                        'gender.gender_code',
                        'branch.branch_name',
                        'branch.branch_code',
                        'career_title.career_title_name',
                        'career_title.career_title_code',
                    )
                    ->where('his_employee.id', $id);
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_serv_segr.is_active'), $this->is_active);
                    });
                }
                $data = $data->first();
                return $data;
            });
            $param_return = [
                $this->get_all_name => $this->get_all,
                $this->start_name => ($this->get_all || !is_null($id)) ? null : $this->start,
                $this->limit_name => ($this->get_all || !is_null($id)) ? null : $this->limit,
                $this->count_name => null,
                $this->is_active_name => $this->is_active,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data?? ($data['data'] ?? null));
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
    // /// Info User
    // public function info_user_id($id)
    // {
    //     $data = get_cache($this->emp_user, $this->emp_user_name, $id, $this->time);
    //     $data1 = get_cache_1_1($this->emp_user, "department", $this->emp_user_name, $id, $this->time);
    //     $data2 = get_cache_1_1($this->emp_user, "gender", $this->emp_user_name, $id, $this->time);
    //     $data3 = get_cache_1_1($this->emp_user, "branch", $this->emp_user_name, $id, $this->time);
    //     $data4 = get_cache_1_1($this->emp_user, "career_title", $this->emp_user_name, $id, $this->time);
    //     $data5 = get_cache_1_n_with_ids($this->emp_user, "default_medi_stock", $this->emp_user_name, $id, $this->time);

    //     return response()->json(['data' => [
    //         'info_user' => $data,
    //         'department' => $data1,
    //         'genderr' => $data2,
    //         'branch' => $data3,
    //         'career_title' => $data4,
    //         'default_medi_stock' => $data5,

    //     ]], 200);
    // }
}
