<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HIS\ExecuteGroup;
use App\Events\Cache\DeleteCache;
use App\Http\Requests\ExecuteGroup\CreateExecuteGroupRequest;
use App\Http\Requests\ExecuteGroup\UpdateExecuteGroupRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExecuteGroupController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->execute_group = new ExecuteGroup();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            // foreach ($this->order_by as $key => $item) {
            //     if (!$this->execute_group->getConnection()->getSchemaBuilder()->hasColumn($this->execute_group->getTable(), $key)) {
            //         unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
            //         unset($this->order_by[$key]);               
            //     }
            // }
            $this->order_by_join = [];
            $columns = Cache::remember('columns_' . $this->execute_group_name, $this->columns_time, function () {
                return  Schema::connection('oracle_his')->getColumnListing($this->execute_group->getTable()) ?? [];

            });
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if ((!in_array($key, $columns))) {
                        $this->errors[snakeToCamel($key)] = $this->mess_order_by_name;
                        unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                        unset($this->order_by[$key]);
                    }
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function execute_group($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
        try {
        $keyword = $this->keyword;
        if ($keyword != null) {
            $param = [
            ];
            $data = $this->execute_group;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('execute_group_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('execute_group_name'), 'like', $keyword . '%');
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
                $name = $this->execute_group_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active;
                $param = [
                ];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->execute_group->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $name = $this->execute_group_name . '_' . $id. '_is_active_' . $this->is_active;
                $param = [
                ];
            }
            $data = get_cache_full($this->execute_group, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active);
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
    } catch (\Exception $e) {
        // Xử lý lỗi và trả về phản hồi lỗi
        return return_500_error();
    }
    }
    public function execute_group_create(CreateExecuteGroupRequest $request)
    {
        $data = $this->execute_group::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'execute_group_code' => $request->execute_group_code,
            'execute_group_name' => $request->execute_group_name,
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->execute_group_name));
        return return_data_create_success($data);
    }

    public function execute_group_update(UpdateExecuteGroupRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->execute_group->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'execute_group_code' => $request->execute_group_code,
            'execute_group_name' => $request->execute_group_name,
            'is_active' => $request->is_active,

        ];
        $data->fill($data_update);
        $data->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->execute_group_name));
        return return_data_update_success($data);
    }

    public function execute_group_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->execute_group->find($id);
        if ($data == null) {
            return return_not_record($id);
        }

        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->execute_group_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }


}
}