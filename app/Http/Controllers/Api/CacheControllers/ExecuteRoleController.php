<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\HIS\ExecuteRole;
use Illuminate\Support\Facades\DB;
use App\Events\Cache\DeleteCache;
use App\Http\Requests\ExecuteRole\CreateExecuteRoleRequest;
use App\Http\Requests\ExecuteRole\UpdateExecuteRoleRequest;

class ExecuteRoleController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->execute_role = new ExecuteRole();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->execute_role->getConnection()->getSchemaBuilder()->hasColumn($this->execute_role->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function execute_role($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $param = [
            ];
            $select = [
                'id',
                'create_time',
                'modify_time',
                'creator',
                'modifier',
                'app_creator',
                'app_modifier',
                'is_active',
                'is_delete',
                'execute_role_code',
                'execute_role_name',
                'is_surg_main',
                'is_surgry',
                'is_stock',
                'is_position',
                'is_title',
                'allow_simultaneity'
            ];
            $data = $this->execute_role;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('lower(execute_role_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(execute_role_name)'), 'like', '%' . $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('is_active'), $this->is_active);
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
                ->with($param)
                ->get();
        } else {
            if ($id == null) {
                $name = $this->execute_role_name . '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active;
                $param = [];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->execute_role->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $name = $this->execute_role_name . '_' . $id. '_is_active_' . $this->is_active;
                $param = [
                    'debate_ekip_users:id,debate_id,loginname,username,execute_role_id',
                    'debate_invite_users:id,debate_id,loginname,username',
                    'debate_users:id,debate_id,loginname,username',
                    'ekip_plan_users:id,execute_role_id,loginname,username',
                    'ekip_temp_users:id,execute_role_id,loginname,username',
                    'execute_role_users:id,execute_role_id,loginname',
                    'exp_mest_users:id,execute_role_id,loginname,username',
                    'imp_mest_users:id,execute_role_id,loginname,username',
                    'imp_user_temp_dts:id,execute_role_id,loginname,username',
                    'mest_inve_users:id,execute_role_id,loginname,username',
                    'remunerations:id,execute_role_id,service_id,price,execute_loginname,execute_username',
                    'surg_remu_details:id,execute_role_id,group_code,price,surg_remuneration_id',
                    'user_group_temp_dts:id,execute_role_id,group_code,user_group_temp_id,loginname,username,description'
                ];
            }
            $select = [
                'id',
                'create_time',
                'modify_time',
                'creator',
                'modifier',
                'app_creator',
                'app_modifier',
                'is_active',
                'is_delete',
                'execute_role_code',
                'execute_role_name',
                'is_surg_main',
                'is_surgry',
                'is_stock',
                'is_position',
                'is_title',
                'allow_simultaneity'
            ];
            $data = get_cache_full_select($this->execute_role, $param, $select, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count'] ?? null,
            'is_active' => $this->is_active,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }

    public function execute_role_create(CreateExecuteRoleRequest $request)
    {
        $data = $this->execute_role::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'is_active' => 1,
            'is_delete' => 0,
            'execute_role_code' => $request->execute_role_code,
            'execute_role_name' => $request->execute_role_name,
            'is_title' => $request->is_title,
            'is_surgry' => $request->is_surgry,
            'is_stock' => $request->is_stock,
            'is_position' => $request->is_position,
            'is_surg_main' => $request->is_surg_main,
            'is_subclinical' => $request->is_subclinical,
            'is_subclinical_result' => $request->is_subclinical_result,
            'allow_simultaneity' => $request->allow_simultaneity,
            'is_single_in_ekip' => $request->is_single_in_ekip,
            'is_disable_in_ekip' => $request->is_disable_in_ekip,
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->execute_role_name));
        return return_data_create_success($data);
    }

    public function execute_role_update(UpdateExecuteRoleRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->execute_role->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'execute_role_code' => $request->execute_role_code,
            'execute_role_name' => $request->execute_role_name,
            'is_title' => $request->is_title,
            'is_surgry' => $request->is_surgry,
            'is_stock' => $request->is_stock,
            'is_position' => $request->is_position,
            'is_surg_main' => $request->is_surg_main,
            'is_subclinical' => $request->is_subclinical,
            'is_subclinical_result' => $request->is_subclinical_result,
            'allow_simultaneity' => $request->allow_simultaneity,
            'is_single_in_ekip' => $request->is_single_in_ekip,
            'is_disable_in_ekip' => $request->is_disable_in_ekip,
            
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->execute_role_name));
        return return_data_update_success($data);
    }

    public function execute_role_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->execute_role->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->execute_role_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }
}
