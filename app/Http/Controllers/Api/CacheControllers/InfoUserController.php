<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Controllers\Controller;
use App\Http\Requests\InfoUser\UpdateInfoUserRequest;
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
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
    public function info_user_update(UpdateInfoUserRequest $request)
    {
        $login_name = $this->token->where('token_code', $request->bearerToken())->first()->login_name;
        $id = $this->employee->where('loginname', $login_name)->first()->id;
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->employee->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->update([
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,

                'tdl_username' => $request->tdl_username,
                'dob' => $request->dob,
                'tdl_email' => $request->tdl_email,
                'tdl_mobile' => $request->tdl_mobile,
                'diploma' => $request->diploma,
                'title' => $request->title,

                'account_number' => $request->account_number,
                'bank' => $request->bank,
                'department_id' => $request->department_id,
                'default_medi_stock_ids' => $request->default_medi_stock_ids,
                'social_insurance_number' => $request->social_insurance_number,
                'erx_loginname' => $request->erx_loginname,
                'erx_password' => $request->erx_password,

                'is_active' => $request->is_active,
            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->employee_name));
            return return_data_update_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
                 
}
