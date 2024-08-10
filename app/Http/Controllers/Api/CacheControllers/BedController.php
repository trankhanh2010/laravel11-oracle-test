<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\Bed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BedController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->bed = new Bed();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->bed);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function bed($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
        try {
        $keyword = $this->keyword;
        if ($keyword != null) {
            $param = [
                'bed_type:id,bed_type_name',
                'bed_room:id,bed_room_name,room_id',
                'bed_room.room:id,department_id',
                'bed_room.room.department:id,department_name'
            ];
            $data = $this->bed;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('bed_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('bed_name'), 'like', $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_bed.is_active'), $this->is_active);
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
                $name = $this->bed_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring. '_is_active_' . $this->is_active. '_get_all_' . $this->get_all;
                $param = [
                    'bed_type:id,bed_type_name',
                    'bed_room:id,bed_room_name,room_id',
                    'bed_room.room:id,department_id',
                    'bed_room.room.department:id,department_name'
                ];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $check_id = $this->check_id($id, $this->bed, $this->bed_name);
                if($check_id){
                    return $check_id; 
                }
                $name =  $this->bed_name . '_' . $id. '_is_active_' . $this->is_active;
                $param = [
                    'bed_type',
                    'bed_room',
                    'bed_room.room',
                    'bed_room.room.department'
                ];
            }
            $model = $this->bed;
            $data = get_cache_full($model, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by,$this->is_active, $this->get_all);
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

}
