<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\HIS\Department;
use App\Events\Cache\DeleteCache;
use App\Http\Requests\Department\CreateDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DepartmentController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->department = new Department();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->department);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function department($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
        try {
        $keyword = $this->keyword;
        if ($keyword != null) {
            $param = [
                'branch:id,branch_name,branch_code',
                'req_surg_treatment_type:id,treatment_type_code,treatment_type_name',
                'default_instr_patient_type:id,patient_type_code,patient_type_name',
            ];
            $data = $this->department;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('department_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('department_name'), 'like', $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_department.is_active'), $this->is_active);
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
                $name = $this->department_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active. '_get_all_' . $this->get_all;
                $param = [
                    'branch:id,branch_name,branch_code',
                    'req_surg_treatment_type:id,treatment_type_code,treatment_type_name',
                    'default_instr_patient_type:id,patient_type_code,patient_type_name',
                ];
            } else {
                // if ($id != 'deleted') {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $check_id = $this->check_id($id, $this->department, $this->department_name);
                if($check_id){
                    return $check_id; 
                }
                // }
                $name = $this->department_name . '_' . $id. '_is_active_' . $this->is_active;
                $param = [
                    'branch:id,branch_name,branch_code',
                    'req_surg_treatment_type:id,treatment_type_code,treatment_type_name',
                    'default_instr_patient_type:id,patient_type_code,patient_type_name'
                ];
            }
            $data = get_cache_full($this->department, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active, $this->get_all);
        }

        $param_return = [
            $this->get_all_name => $this->get_all,
            $this->start_name => ($this->get_all || !is_null($id)) ? null : $this->start,
            $this->limit_name => ($this->get_all || !is_null($id)) ? null : $this->limit,
            $this->count_name => $count ?? ($data['count'] ?? null),
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

    public function department_create(CreateDepartmentRequest $request)
    {
        $data = $this->department::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'department_code' => $request->department_code,
            'department_name' => $request->department_name,
            'g_code' => $request->g_code,
            'bhyt_code' => $request->bhyt_code,
            'branch_id' => $request->branch_id,
            'default_instr_patient_type_id' => $request->default_instr_patient_type_id,
            'num_order' => $request->num_order,
            'allow_treatment_type_ids' => $request->allow_treatment_type_ids,
            'theory_patient_count' => $request->theory_patient_count,
            'reality_patient_count' => $request->reality_patient_count,
            'req_surg_treatment_type_id' => $request->req_surg_treatment_type_id,
            'phone' => $request->phone,
            'head_loginname' => $request->head_loginname,
            'head_username' => $request->head_username,
            'accepted_icd_codes' => $request->accepted_icd_codes,
            'is_exam' => $request->is_exam,
            'is_clinical' => $request->is_clinical,
            'allow_assign_package_price' => $request->allow_assign_package_price,
            'auto_bed_assign_option' => $request->auto_bed_assign_option,
            'is_emergency' => $request->is_emergency,
            'is_auto_receive_patient' => $request->is_auto_receive_patient,
            'allow_assign_surgery_price' => $request->allow_assign_surgery_price,
            'is_in_dep_stock_moba' => $request->is_in_dep_stock_moba,
            'warning_when_is_no_surg' => $request->warning_when_is_no_surg,
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->department_name));
        return return_data_create_success($data);
    }

    public function department_update(UpdateDepartmentRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->department->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'department_name' => $request->department_name,
            'g_code' => $request->g_code,
            'bhyt_code' => $request->bhyt_code,
            'default_instr_patient_type_id' => $request->default_instr_patient_type_id,
            'num_order' => $request->num_order,
            'allow_treatment_type_ids' => $request->allow_treatment_type_ids,
            'theory_patient_count' => $request->theory_patient_count,
            'reality_patient_count' => $request->reality_patient_count,
            'req_surg_treatment_type_id' => $request->req_surg_treatment_type_id,
            'phone' => $request->phone,
            'head_loginname' => $request->head_loginname,
            'head_username' => $request->head_username,
            'accepted_icd_codes' => $request->accepted_icd_codes,
            'is_exam' => $request->is_exam,
            'is_clinical' => $request->is_clinical,
            'allow_assign_package_price' => $request->allow_assign_package_price,
            'auto_bed_assign_option' => $request->auto_bed_assign_option,
            'is_emergency' => $request->is_emergency,
            'is_auto_receive_patient' => $request->is_auto_receive_patient,
            'allow_assign_surgery_price' => $request->allow_assign_surgery_price,
            'is_in_dep_stock_moba' => $request->is_in_dep_stock_moba,
            'warning_when_is_no_surg' => $request->warning_when_is_no_surg,
            'is_active' => $request->is_active,

        ];
        $data->fill($data_update);
        $data->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->department_name));
        return return_data_update_success($data);
    }

    public function department_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->department->find($id);
        if ($data == null) {
            return return_not_record($id);
        }

        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->department_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }

    }

    // public function department_delete(Request $request, $id)
    // {
    //     if (!is_numeric($id)) {
    //         return return_id_error($id);
    //     }
    //     $data = $this->department->find($id);
    //     if ($data == null) {
    //         return return_not_record($id);
    //     }
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
    //         'app_modifier' => $this->app_modifier,
    //         'is_delete' => 1
    //     ]);
    //     // Gọi event để xóa cache
    //     event(new DeleteCache($this->department_name));
    //     return return_data_delete_success($data);
    // }

    // public function department_restore($id = null, Request $request)
    // {
    //     if (!is_numeric($id)) {
    //         return return_id_error($id);
    //     }
    //     $data = $this->department::withDeleted()->find($id);
    //     if ($data == null) {
    //         return return_not_record($id);
    //     }
    //     $data_update = [
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
    //         'app_modifier' => $this->app_modifier,
    //         'is_delete' => 0,
    //     ];
    //     $data->fill($data_update);
    //     $data->save();
    //     // Gọi event để xóa cache
    //     event(new DeleteCache($this->department_name));
    //     return redirect()->route('HIS.Desktop.Plugins.HisDepartment.api.department.index_with_id', compact('id'));
    // }
}
