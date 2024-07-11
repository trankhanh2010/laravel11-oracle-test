<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\Bed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BedController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->bed = new Bed();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->bed->getConnection()->getSchemaBuilder()->hasColumn($this->bed->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function bed($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $param = [
                'room:id,department_id,area_id,speciality_id,default_cashier_room_id,default_instr_patient_type_id,is_pause',
                'room.department:id,department_name,department_code',
                'room.area:id,area_name',
                'room.speciality:id,speciality_name,speciality_code',
                'room.default_cashier_room:id,cashier_room_name',
                'room.default_instr_patient_type:id,patient_type_name',
            ];
            $data = $this->bed;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('lower(bed_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(bed_name)'), 'like', '%' . $keyword . '%');
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
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            if ($id == null) {
                $name = $this->bed_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring;
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
                $data = $this->bed->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $name =  $this->bed_name . '_' . $id;
                $param = [
                    'bed_type',
                    'bed_room',
                    'bed_room.room',
                    'bed_room.room.department'
                ];
            }
            $model = $this->bed;
            $data = get_cache_full($model, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by);
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

}
