<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MediRecordType\CreateMediRecordTypeRequest;
use App\Http\Requests\MediRecordType\UpdateMediRecordTypeRequest;
use App\Models\HIS\MediRecordType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MediRecordTypeController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->medi_record_type = new MediRecordType();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->medi_record_type);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function medi_record_type($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->medi_record_type
                    ->select(
                        'his_medi_record_type.*',
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('his_medi_record_type.medi_record_type_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_medi_record_type.is_active'), $this->is_active);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_medi_record_type.' . $key, $item);
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
                    $data = Cache::remember($this->medi_record_type_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->medi_record_type
                        ->select(
                            'his_medi_record_type.*',
                        );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medi_record_type.is_active'), $this->is_active);
                            });
                        }

                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_medi_record_type.' . $key, $item);
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
                    $check_id = $this->check_id($id, $this->medi_record_type, $this->medi_record_type_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->medi_record_type_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->medi_record_type
                        ->select(
                            'his_medi_record_type.*',
                        )
                            ->where('his_medi_record_type.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medi_record_type.is_active'), $this->is_active);
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
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? ($data['data'] ?? null) ?? null);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
    public function medi_record_type_create(CreateMediRecordTypeRequest $request)
    {
        try {
            $data = $this->medi_record_type::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'is_active' => 1,
                'is_delete' => 0,
                'medi_record_type_code' => $request->medi_record_type_code,
                'medi_record_type_name' => $request->medi_record_type_name,
            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->medi_record_type_name));
            return return_data_create_success($data);
        } catch (\Exception $e) {
            return return_500_error();
        }
    }

    public function medi_record_type_update(UpdateMediRecordTypeRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->medi_record_type->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->update([
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,

                'medi_record_type_name' => $request->medi_record_type_name,
                'is_active' => $request->is_active
            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->medi_record_type_name));
            return return_data_update_success($data);
        } catch (\Exception $e) {
            return return_500_error();
        }
    }
      
    public function medi_record_type_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->medi_record_type->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->medi_record_type_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }

}
