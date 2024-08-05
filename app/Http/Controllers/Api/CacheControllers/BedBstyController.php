<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\BedBsty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BedBstyController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->bed_bsty = new BedBsty();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            // foreach ($this->order_by as $key => $item) {
            //     if (!$this->bed_bsty->getConnection()->getSchemaBuilder()->hasColumn($this->bed_bsty->getTable(), $key)) {
            //         unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
            //         unset($this->order_by[$key]);               
            //     }
            // }
            $this->order_by_join = [];
            $columns = Cache::remember('columns_' . $this->bed_bsty_name, $this->columns_time, function () {
                return  Schema::connection('oracle_his')->getColumnListing($this->bed_bsty->getTable()) ?? [];

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
    public function bed_bsty($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
        try {
        $keyword = $this->keyword;
        if (($keyword != null) || ($this->service_ids != null) || ($this->bed_ids != null)) {
            $data = $this->bed_bsty
                ->leftJoin('his_service as service', 'service.id', '=', 'his_bed_bsty.bed_service_type_id')
                ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                ->leftJoin('his_bed as bed', 'bed.id', '=', 'his_bed_bsty.bed_id')
                ->leftJoin('his_bed_room as bed_room', 'bed_room.id', '=', 'bed.bed_room_id')
                ->leftJoin('his_room as room', 'room.id', '=', 'bed_room.room_id')
                ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')

                ->select(
                    'his_bed_bsty.*',
                    'service.service_name',
                    'service.service_code',
                    'service_type.service_type_name',
                    'service_type.service_type_code',
                    'bed.bed_name',
                    'bed.bed_code',
                    'bed_room.bed_room_name',
                    'bed_room.bed_room_code',
                    'department.department_name',
                    'department.department_code'
                );
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query
                    ->where(DB::connection('oracle_his')->raw('service.service_code'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('bed.bed_code'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('bed_room.bed_room_code'), 'like', $keyword . '%');
            });
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_bed_bsty.is_active'), $this->is_active);
                });
            }
            if ($this->service_ids != null) {
                $data = $data->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_bed_bsty.bed_service_type_id'), $this->service_ids);
                });
            }
            if ($this->bed_ids != null) {
                $data = $data->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_bed_bsty.bed_id'), $this->bed_ids);
                });
            }
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_bed_bsty.' . $key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            if ($id == null) {
                $data = Cache::remember($this->bed_bsty_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active, $this->time, function () {
                    $data = $this->bed_bsty
                ->leftJoin('his_service as service', 'service.id', '=', 'his_bed_bsty.bed_service_type_id')
                ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                ->leftJoin('his_bed as bed', 'bed.id', '=', 'his_bed_bsty.bed_id')
                ->leftJoin('his_bed_room as bed_room', 'bed_room.id', '=', 'bed.bed_room_id')
                ->leftJoin('his_room as room', 'room.id', '=', 'bed_room.room_id')
                ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')

                ->select(
                    'his_bed_bsty.*',
                    'service.service_name',
                    'service.service_code',
                    'service_type.service_type_name',
                    'service_type.service_type_code',
                    'bed.bed_name',
                    'bed.bed_code',
                    'bed_room.bed_room_name',
                    'bed_room.bed_room_code',
                    'department.department_name',
                    'department.department_code'
                );
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_bed_bsty.is_active'), $this->is_active);
                });
            }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_bed_bsty.' . $key, $item);
                    }
                }
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
                    return ['data' => $data, 'count' => $count];
                });
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->bed_bsty->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $data = Cache::remember($this->bed_bsty_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                    $data = $this->bed_bsty
                ->leftJoin('his_service as service', 'service.id', '=', 'his_bed_bsty.bed_service_type_id')
                ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                ->leftJoin('his_bed as bed', 'bed.id', '=', 'his_bed_bsty.bed_id')
                ->leftJoin('his_bed_room as bed_room', 'bed_room.id', '=', 'bed.bed_room_id')
                ->leftJoin('his_room as room', 'room.id', '=', 'bed_room.room_id')
                ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')

                ->select(
                    'his_bed_bsty.*',
                    'service.service_name',
                    'service.service_code',
                    'service_type.service_type_name',
                    'service_type.service_type_code',
                    'bed.bed_name',
                    'bed.bed_code',
                    'bed_room.bed_room_name',
                    'bed_room.bed_room_code',
                    'department.department_name',
                    'department.department_code'
                )
                ->where('his_bed_bsty.id', $id);;
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_bed_bsty.is_active'), $this->is_active);
                    });
                }
                    $data = $data->first();
                    return $data;
                });
            }
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? (is_array($data) ? $data['count'] : null),
            'service_ids' => $this->service_ids ?? null,
            'bed_ids' => $this->bed_ids ?? null,
            'is_active' => $this->is_active,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data['data'] ?? $data);
    } catch (\Exception $e) {
        // Xử lý lỗi và trả về phản hồi lỗi
        return return_500_error();
    }
    }

    // public function service_with_bed($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->service_name . '_with_' . $this->bed_name;
    //         $param = [
    //             'beds:id,bed_name,bed_room_id',
    //             'beds.bed_room:id,bed_room_name,room_id',
    //             'beds.bed_room.room:id,department_id',
    //             'beds.bed_room.room.department:id,department_name,department_code',
    //         ];
    //     } else {
    //         $name = $this->service_name . '_' . $id . '_with_' . $this->bed_name;
    //         $param = [
    //             'beds',
    //             'beds.bed_room:id,bed_room_name,room_id',
    //             'beds.bed_room.room:id,department_id',
    //             'beds.bed_room.room.department:id,department_name,department_code',
    //         ];
    //     }
    //     $data = get_cache_full($this->service, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function bed_with_service($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->bed_name . '_with_' . $this->service_name;
    //         $param = [
    //             'bed_room:id,bed_room_name,room_id',
    //             'bed_room.room:id,department_id',
    //             'bed_room.room.department:id,department_name,department_code',
    //             'services:id,service_name,service_code'
    //         ];
    //     } else {
    //         $name = $this->bed_name . '_' . $id . '_with_' . $this->service_name;
    //         $param = [
    //             'bed_room',
    //             'bed_room.room:id,department_id',
    //             'bed_room.room.department:id,department_name,department_code',
    //             'services'
    //         ];
    //     }
    //     $data = get_cache_full($this->bed, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

}
