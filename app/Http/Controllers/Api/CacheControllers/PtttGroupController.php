<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PtttGroup\CreatePtttGroupRequest;
use App\Http\Requests\PtttGroup\UpdatePtttGroupRequest;
use App\Models\HIS\PtttGroup;
use App\Models\HIS\PtttGroupBest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
class PtttGroupController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->pttt_group = new PtttGroup();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->pttt_group);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function pttt_group($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $param = [
                'bed_services:service_name,service_code'
            ];
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->pttt_group
                    ->select(
                        'his_pttt_group.*',
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('his_pttt_group.pttt_group_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_pttt_group.is_active'), $this->is_active);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_pttt_group.' . $key, $item);
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
                    $data = Cache::remember($this->pttt_group_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active . '_get_all_' . $this->get_all, $this->time, function () use ($param) {
                        $data = $this->pttt_group
                        ->select(
                            'his_pttt_group.*',
                        );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_pttt_group.is_active'), $this->is_active);
                            });
                        }

                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_pttt_group.' . $key, $item);
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
                        return ['data' => $data, 'count' => $count];
                    });
                } else {
                    $param = [
                        'bed_services:service_name,service_code'
                    ];
                    if (!is_numeric($id)) {
                        return return_id_error($id);
                    }
                    $check_id = $this->check_id($id, $this->pttt_group, $this->pttt_group_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->pttt_group_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id, $param) {
                        $data = $this->pttt_group
                        ->select(
                            'his_pttt_group.*',
                        )
                            ->where('his_pttt_group.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_pttt_group.is_active'), $this->is_active);
                            });
                        }
                        $data = $data->with($param)
                        ->first();
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
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? ($data['data'] ?? null) ?? null);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
    public function pttt_group_create(CreatePtttGroupRequest $request)
    {
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        try {
            $data = $this->pttt_group::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'pttt_group_code' => $request->pttt_group_code,
                'pttt_group_name' => $request->pttt_group_name,
                'num_order' => $request->num_order,
                'remuneration' => $request->remuneration,
            ]);
            if($request->bed_service_type_ids !== null){
                $bed_service_type_ids_arr = explode(',', $request->bed_service_type_ids);
                foreach($bed_service_type_ids_arr as $key => $item){
                    $bed_service_type_ids_arr_data[$item] =  [
                        'create_time' => now()->format('Ymdhis'),
                        'modify_time' => now()->format('Ymdhis'),
                        'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                        'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                        'app_creator' => $this->app_creator,
                        'app_modifier' => $this->app_modifier,
                    ];
                }
                foreach($bed_service_type_ids_arr as $key => $item){
                    $data->bed_services()->sync($bed_service_type_ids_arr_data);
                }
            }
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->pttt_group_name));
            return return_data_create_success([$data]);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }

    public function pttt_group_update(UpdatePtttGroupRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->pttt_group->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        try {
            $data_update = [
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,

                'pttt_group_code' => $request->pttt_group_code,
                'pttt_group_name' => $request->pttt_group_name,
                'num_order' => $request->num_order,
                'remuneration' => $request->remuneration,
                'is_active' => $request->is_active,

            ];
            $data->fill($data_update);
            $data->save();
            if($request->bed_service_type_ids !== null){
                $bed_service_type_ids_arr = explode(',', $request->bed_service_type_ids);
                foreach($bed_service_type_ids_arr as $key => $item){
                    $bed_service_type_ids_arr_data[$item] =  [
                        'create_time' => now()->format('Ymdhis'),
                        'modify_time' => now()->format('Ymdhis'),
                        'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                        'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                        'app_creator' => $this->app_creator,
                        'app_modifier' => $this->app_modifier,
                    ];
                }
                foreach($bed_service_type_ids_arr as $key => $item){
                    $data->bed_services()->sync($bed_service_type_ids_arr_data);
                }
            }else{
                PtttGroupBest::where('PTTT_GROUP_ID', $data->id)->delete();
            }
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->pttt_group_name));
            return return_data_create_success([$data]);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }

    public function pttt_group_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->pttt_group->find($id);
        if ($data == null) {
            return return_not_record($id);
        }

        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        try {
            PtttGroupBest::where('PTTT_GROUP_ID', $data->id)->delete();
            $data->delete();
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->pttt_group_name));
            return return_data_delete_success();
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }
}
