<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\HIS\Branch;
use App\Events\Cache\DeleteCache;
use App\Http\Requests\Branch\CreateBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use Illuminate\Support\Facades\DB;

class BranchController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->branch = new Branch();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->branch->getConnection()->getSchemaBuilder()->hasColumn($this->branch->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function branch($id = null)
    {
        $keyword = create_slug(mb_strtolower($this->keyword, 'UTF-8'));
        if ($keyword != null) {
            $data = $this->branch;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('FUN_CONVERT_TO_UNSIGN(lower(branch_code))'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('FUN_CONVERT_TO_UNSIGN(lower(branch_name))'), 'like', '%' . $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_branch.is_active'), $this->is_active);
            });
        } 
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy($key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            if ($id == null) {
                $name = $this->branch_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active;
                $param = [];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->branch->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $name = $this->branch_name . '_' . $id. '_is_active_' . $this->is_active;
                $param = [];
            }
            $data = get_cache_full($this->branch, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count'],
            'is_active' => $this->is_active,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }

    public function branch_create(CreateBranchRequest $request)
    {
        $data = $this->branch::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'branch_code' => $request->branch_code,
            'branch_name' => $request->branch_name,
            'hein_medi_org_code' => $request->hein_medi_org_code,
            'accept_hein_medi_org_code' => $request->accept_hein_medi_org_code,
            'sys_medi_org_code' => $request->sys_medi_org_code,
            'province_code' => $request->province_code,
            'province_name' => $request->province_name,
            'district_code' => $request->district_code,
            'district_name' => $request->district_name,
            'commune_code' => $request->commune_code,
            'commune_name' => $request->commune_name,
            'address' => $request->address,
            'parent_organization_name' => $request->parent_organization_name,
            'hein_province_code' => $request->hein_province_code,
            'hein_level_code' => $request->hein_level_code,
            'do_not_allow_hein_level_code' => $request->do_not_allow_hein_level_code,
            'tax_code' => $request->tax_code,
            'account_number' => $request->account_number,
            'phone' => $request->phone,
            'representative' => $request->representative,
            'position' => $request->position,
            'representative_hein_code' => $request->representative_hein_code,
            'auth_letter_issue_date' => $request->auth_letter_issue_date,
            'auth_letter_num' => $request->auth_letter_num,
            'bank_info' => $request->bank_info,
            'the_branch_code' => $request->the_branch_code,
            'director_loginname' => $request->director_loginname,
            'director_username' => $request->director_username,
            'venture' => $request->venture,
            'type' => $request->type,
            'form' => $request->form,
            'bed_approved' => $request->bed_approved,
            'bed_actual' => $request->bed_actual,
            'bed_resuscitation' => $request->bed_resuscitation,
            'bed_resuscitation_emg' => $request->bed_resuscitation_emg,
            'is_use_branch_time' => $request->is_use_branch_time
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->branch_name));
        return return_data_create_success($data);
    }

    public function branch_update(UpdateBranchRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->branch->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'branch_name' => $request->branch_name,
            'hein_medi_org_code' => $request->hein_medi_org_code,
            'accept_hein_medi_org_code' => $request->accept_hein_medi_org_code,
            'sys_medi_org_code' => $request->sys_medi_org_code,
            'province_code' => $request->province_code,
            'province_name' => $request->province_name,
            'district_code' => $request->district_code,
            'district_name' => $request->district_name,
            'commune_code' => $request->commune_code,
            'commune_name' => $request->commune_name,
            'address' => $request->address,
            'parent_organization_name' => $request->parent_organization_name,
            'hein_province_code' => $request->hein_province_code,
            'hein_level_code' => $request->hein_level_code,
            'do_not_allow_hein_level_code' => $request->do_not_allow_hein_level_code,
            'tax_code' => $request->tax_code,
            'account_number' => $request->account_number,
            'phone' => $request->phone,
            'representative' => $request->representative,
            'position' => $request->position,
            'representative_hein_code' => $request->representative_hein_code,
            'auth_letter_issue_date' => $request->auth_letter_issue_date,
            'auth_letter_num' => $request->auth_letter_num,
            'bank_info' => $request->bank_info,
            'the_branch_code' => $request->the_branch_code,
            'director_loginname' => $request->director_loginname,
            'director_username' => $request->director_username,
            'venture' => $request->venture,
            'type' => $request->type,
            'form' => $request->form,
            'bed_approved' => $request->bed_approved,
            'bed_actual' => $request->bed_actual,
            'bed_resuscitation' => $request->bed_resuscitation,
            'bed_resuscitation_emg' => $request->bed_resuscitation_emg,
            'is_use_branch_time' => $request->is_use_branch_time,
            'is_active' => $request->is_active

        ];
        $data->fill($data_update);
        $data->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->branch_name));
        return return_data_update_success($data);
    }

    public function branch_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->branch->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->branch_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }
}
