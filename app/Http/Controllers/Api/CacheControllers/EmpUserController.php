<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Employee\CreateEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\HIS\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmpUserController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->emp_user = new Employee();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->emp_user);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function emp_user($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
        try {
        $keyword = $this->keyword;
        if ($keyword != null) {
            $param = [
                'department:id,department_name',
                'gender:id,gender_name',
                'career_title:id,career_title_name,career_title_code'
            ];
            $data = $this->emp_user;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('loginname'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('tdl_username'), 'like', $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_employee.is_active'), $this->is_active);
            });
        } 
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy($key, $item);
                }
            }
            if($this->get_all){
                $data = $data
                ->with($param)
                ->get();
            }else{
                $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
            }
        } else {
            if ($id == null) {
                $name = $this->emp_user_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring. '_is_active_' . $this->is_active. '_get_all_' . $this->get_all;
                $param = [
                ];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $check_id = $this->check_id($id, $this->emp_user, $this->emp_user_name);
                if($check_id){
                    return $check_id; 
                }
                $name =  $this->emp_user_name . '_' . $id. '_is_active_' . $this->is_active;
                $param = [
                    'department:id,department_name',
                    'gender:id,gender_name',
                    'career_title:id,career_title_name,career_title_code'
                ];
            }
            $model = $this->emp_user;
            $data = get_cache_full($model, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active, $this->get_all);
        }
        $param_return = [
            $this->get_all_name => $this->get_all,
            $this->start_name => ($this->get_all || !is_null($id)) ? null : $this->start,
            $this->limit_name => ($this->get_all || !is_null($id)) ? null : $this->limit,
            $this->count_name => $count ?? ($data['count'] ?? null),
            $this->is_active_name=> $this->is_active,
            $this->keyword_name => $this->keyword,
            $this->order_by_name => $this->order_by_request
        ];
        return return_data_success($param_return, $data?? ($data['data'] ?? null));
    } catch (\Exception $e) {
        // Xử lý lỗi và trả về phản hồi lỗi
        return return_500_error();
    }
    }

    public function emp_user_create(CreateEmployeeRequest $request)
    {
        try {
            $data = $this->emp_user::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'is_active' => 1,
                'is_delete' => 0,

                'loginname' => $request->loginname,
                'tdl_username' => $request->tdl_username,
                'dob' => $request->dob,
                'gender_id' => $request->gender_id,
                'ethnic_code' => $request->ethnic_code,

                'tdl_email' => $request->tdl_email,
                'tdl_mobile' => $request->tdl_mobile,
                'diploma' => $request->diploma,
                'diploma_date' => $request->diploma_date,
                'diploma_place' => $request->diploma_place,
                'title' => $request->title,

                'medicine_type_rank' => $request->medicine_type_rank,
                'max_bhyt_service_req_per_day' => $request->max_bhyt_service_req_per_day,
                'max_service_req_per_day' => $request->max_service_req_per_day,
                'is_service_req_exam' => $request->is_service_req_exam,
                'account_number' => $request->account_number,
                'bank' => $request->bank,

                'department_id' => $request->department_id,
                'default_medi_stock_ids' => $request->default_medi_stock_ids,
                'erx_loginname' => $request->erx_loginname,
                'erx_password' => $request->erx_password,
                'identification_number' => $request->identification_number,
                'social_insurance_number' => $request->social_insurance_number,

                'career_title_id' => $request->career_title_id,
                'position' => $request->position,
                'speciality_codes' => $request->speciality_codes,
                'type_of_time' => $request->type_of_time,
                'branch_id' => $request->branch_id,
                'medi_org_codes' => $request->medi_org_codes,

                'is_doctor' => $request->is_doctor,
                'is_nurse' => $request->is_nurse,
                'is_admin' => $request->is_admin,
                'allow_update_other_sclinical' => $request->allow_update_other_sclinical,
                'do_not_allow_simultaneity' => $request->do_not_allow_simultaneity,
                'is_limit_schedule' => $request->is_limit_schedule,

                'is_need_sign_instead' => $request->is_need_sign_instead,
            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->emp_user_name));
            return return_data_create_success($data);
        } catch (\Exception $e) {
            return return_500_error();
        }
    }
                            
    public function emp_user_update(UpdateEmployeeRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->emp_user->find($id);
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
                'gender_id' => $request->gender_id,
                'ethnic_code' => $request->ethnic_code,

                'tdl_email' => $request->tdl_email,
                'tdl_mobile' => $request->tdl_mobile,
                'diploma' => $request->diploma,
                'diploma_date' => $request->diploma_date,
                'diploma_place' => $request->diploma_place,
                'title' => $request->title,

                'medicine_type_rank' => $request->medicine_type_rank,
                'max_bhyt_service_req_per_day' => $request->max_bhyt_service_req_per_day,
                'max_service_req_per_day' => $request->max_service_req_per_day,
                'is_service_req_exam' => $request->is_service_req_exam,
                'account_number' => $request->account_number,
                'bank' => $request->bank,

                'department_id' => $request->department_id,
                'default_medi_stock_ids' => $request->default_medi_stock_ids,
                'erx_loginname' => $request->erx_loginname,
                'erx_password' => $request->erx_password,
                'identification_number' => $request->identification_number,
                'social_insurance_number' => $request->social_insurance_number,

                'career_title_id' => $request->career_title_id,
                'position' => $request->position,
                'speciality_codes' => $request->speciality_codes,
                'type_of_time' => $request->type_of_time,
                'branch_id' => $request->branch_id,
                'medi_org_codes' => $request->medi_org_codes,

                'is_doctor' => $request->is_doctor,
                'is_nurse' => $request->is_nurse,
                'is_admin' => $request->is_admin,
                'allow_update_other_sclinical' => $request->allow_update_other_sclinical,
                'do_not_allow_simultaneity' => $request->do_not_allow_simultaneity,
                'is_limit_schedule' => $request->is_limit_schedule,

                'is_need_sign_instead' => $request->is_need_sign_instead,
                'is_active' => $request->is_active
            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->emp_user_name));
            return return_data_update_success($data);
        } catch (\Exception $e) {
            return return_500_error();
        }
    }

    public function emp_user_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->emp_user->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->emp_user_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }
}
