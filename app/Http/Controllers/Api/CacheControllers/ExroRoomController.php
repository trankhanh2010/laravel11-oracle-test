<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ExroRoom\CreateExroRoomRequest;
use App\Models\HIS\ExecuteRoom;
use App\Models\HIS\ExroRoom;
use App\Models\HIS\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExroRoomController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->exro_room = new ExroRoom();
        $this->room = new Room();
        $this->execute_room = new ExecuteRoom();
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->exro_room);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function exro_room($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->exro_room
                    ->leftJoin('his_execute_room as execute_room', 'execute_room.id', '=', 'his_exro_room.execute_room_id')
                    ->leftJoin('his_room', 'his_room.id', '=', 'his_exro_room.room_id')
                    ->leftJoin('his_room as room_execute_room', 'room_execute_room.id', '=', 'execute_room.room_id')

                    ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                    ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                    ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                    ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                    ->leftJoin('his_refectory as refectory', 'his_room.id', '=', 'refectory.room_id')
                    ->leftJoin('his_sample_room as sample_room', 'his_room.id', '=', 'sample_room.room_id')
                    ->leftJoin('his_execute_room as execute_room', 'his_room.id', '=', 'execute_room.room_id')
                    ->leftJoin('his_data_store as data_store', 'his_room.id', '=', 'data_store.room_id')
                    ->leftJoin('his_station as station', 'his_room.id', '=', 'station.room_id')

                    ->leftJoin('his_room_type as room_type', 'room_type.id', '=', 'his_room.room_type_id')
                    ->leftJoin('his_department as department', 'department.id', '=', 'his_room.department_id')
                    ->leftJoin('his_room_type as execute_room_type', 'execute_room_type.id', '=', 'room_execute_room.room_type_id')
                    ->leftJoin('his_department as execute_department', 'execute_department.id', '=', 'room_execute_room.department_id')
                    ->select(
                        'his_exro_room.*',
                        'execute_room.execute_room_code',
                        'execute_room.execute_room_name',
                        'execute_room_type.room_type_code as execute_room_type_code',
                        'execute_room_type.room_type_name as execute_room_type_name',
                        'execute_department.department_code as execute_department_code',
                        'execute_department.department_name as execute_department_name',
                        DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                        NVL(cashier.cashier_room_name, 
                        NVL(execute.execute_room_name, 
                        NVL(reception.reception_room_name,
                        NVL(refectory.refectory_name,
                        NVL(sample_room.sample_room_name,
                        NVL(execute_room.execute_room_name,
                        NVL(data_store.data_store_name,
                        station.station_name)))))))) AS "room_name"'),
                        DB::connection('oracle_his')->raw('NVL(bed.bed_room_code, 
                        NVL(cashier.cashier_room_code, 
                        NVL(execute.execute_room_code, 
                        NVL(reception.reception_room_code,
                        NVL(refectory.refectory_code,
                        NVL(sample_room.sample_room_code,
                        NVL(execute_room.execute_room_code,
                        NVL(data_store.data_store_code,
                        station.station_code)))))))) AS "room_code"'),
                        'room_type.room_type_code',
                        'room_type.room_type_name',
                        'department.department_code',
                        'department.department_name',

                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('execute_room.execute_room_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('bed.bed_room_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('cashier.cashier_room_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('execute.execute_room_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('reception.reception_room_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('refectory.refectory_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('sample_room.sample_room_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('execute_room.execute_room_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('data_store.data_store_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('station.station_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_exro_room.is_active'), $this->is_active);
                    });
                }
                if ($this->execute_room_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_exro_room.execute_room_id'), $this->execute_room_id);
                    });
                }
                if ($this->room_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_exro_room.room_id'), $this->room_id);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_exro_room.' . $key, $item);
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
                    $data = Cache::remember($this->exro_room_name . '_execute_room_id_' . $this->execute_room_id . '_room_id_' . $this->room_id . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->exro_room
                        ->leftJoin('his_execute_room as execute_room', 'execute_room.id', '=', 'his_exro_room.execute_room_id')
                        ->leftJoin('his_room', 'his_room.id', '=', 'his_exro_room.room_id')
                        ->leftJoin('his_room as room_execute_room', 'room_execute_room.id', '=', 'execute_room.room_id')
    
                        ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                        ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                        ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                        ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                        ->leftJoin('his_refectory as refectory', 'his_room.id', '=', 'refectory.room_id')
                        ->leftJoin('his_sample_room as sample_room', 'his_room.id', '=', 'sample_room.room_id')
                        ->leftJoin('his_execute_room as execute_room', 'his_room.id', '=', 'execute_room.room_id')
                        ->leftJoin('his_data_store as data_store', 'his_room.id', '=', 'data_store.room_id')
                        ->leftJoin('his_station as station', 'his_room.id', '=', 'station.room_id')
    
                        ->leftJoin('his_room_type as room_type', 'room_type.id', '=', 'his_room.room_type_id')
                        ->leftJoin('his_department as department', 'department.id', '=', 'his_room.department_id')
                        ->leftJoin('his_room_type as execute_room_type', 'execute_room_type.id', '=', 'room_execute_room.room_type_id')
                        ->leftJoin('his_department as execute_department', 'execute_department.id', '=', 'room_execute_room.department_id')
                        ->select(
                            'his_exro_room.*',
                            'execute_room.execute_room_code',
                            'execute_room.execute_room_name',
                            'execute_room_type.room_type_code as execute_room_type_code',
                            'execute_room_type.room_type_name as execute_room_type_name',
                            'execute_department.department_code as execute_department_code',
                            'execute_department.department_name as execute_department_name',
                            DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                            NVL(cashier.cashier_room_name, 
                            NVL(execute.execute_room_name, 
                            NVL(reception.reception_room_name,
                            NVL(refectory.refectory_name,
                            NVL(sample_room.sample_room_name,
                            NVL(execute_room.execute_room_name,
                            NVL(data_store.data_store_name,
                            station.station_name)))))))) AS "room_name"'),
                            DB::connection('oracle_his')->raw('NVL(bed.bed_room_code, 
                            NVL(cashier.cashier_room_code, 
                            NVL(execute.execute_room_code, 
                            NVL(reception.reception_room_code,
                            NVL(refectory.refectory_code,
                            NVL(sample_room.sample_room_code,
                            NVL(execute_room.execute_room_code,
                            NVL(data_store.data_store_code,
                            station.station_code)))))))) AS "room_code"'),
                            'room_type.room_type_code',
                            'room_type.room_type_name',
                            'department.department_code',
                            'department.department_name',
    
                        );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_exro_room.is_active'), $this->is_active);
                            });
                        }
                        if ($this->execute_room_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_exro_room.execute_room_id'), $this->execute_room_id);
                            });
                        }
                        if ($this->room_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_exro_room.room_id'), $this->room_id);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_exro_room.' . $key, $item);
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
                        return returnIdError($id);
                    }
                    $check_id = $this->check_id($id, $this->exro_room, $this->exro_room_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->exro_room_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->exro_room
                        ->leftJoin('his_execute_room as execute_room', 'execute_room.id', '=', 'his_exro_room.execute_room_id')
                        ->leftJoin('his_room', 'his_room.id', '=', 'his_exro_room.room_id')
                        ->leftJoin('his_room as room_execute_room', 'room_execute_room.id', '=', 'execute_room.room_id')
    
                        ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                        ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                        ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                        ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                        ->leftJoin('his_refectory as refectory', 'his_room.id', '=', 'refectory.room_id')
                        ->leftJoin('his_sample_room as sample_room', 'his_room.id', '=', 'sample_room.room_id')
                        ->leftJoin('his_execute_room as execute_room', 'his_room.id', '=', 'execute_room.room_id')
                        ->leftJoin('his_data_store as data_store', 'his_room.id', '=', 'data_store.room_id')
                        ->leftJoin('his_station as station', 'his_room.id', '=', 'station.room_id')
    
                        ->leftJoin('his_room_type as room_type', 'room_type.id', '=', 'his_room.room_type_id')
                        ->leftJoin('his_department as department', 'department.id', '=', 'his_room.department_id')
                        ->leftJoin('his_room_type as execute_room_type', 'execute_room_type.id', '=', 'room_execute_room.room_type_id')
                        ->leftJoin('his_department as execute_department', 'execute_department.id', '=', 'room_execute_room.department_id')
                        ->select(
                            'his_exro_room.*',
                            'execute_room.execute_room_code',
                            'execute_room.execute_room_name',
                            'execute_room_type.room_type_code as execute_room_type_code',
                            'execute_room_type.room_type_name as execute_room_type_name',
                            'execute_department.department_code as execute_department_code',
                            'execute_department.department_name as execute_department_name',
                            DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                            NVL(cashier.cashier_room_name, 
                            NVL(execute.execute_room_name, 
                            NVL(reception.reception_room_name,
                            NVL(refectory.refectory_name,
                            NVL(sample_room.sample_room_name,
                            NVL(execute_room.execute_room_name,
                            NVL(data_store.data_store_name,
                            station.station_name)))))))) AS "room_name"'),
                            DB::connection('oracle_his')->raw('NVL(bed.bed_room_code, 
                            NVL(cashier.cashier_room_code, 
                            NVL(execute.execute_room_code, 
                            NVL(reception.reception_room_code,
                            NVL(refectory.refectory_code,
                            NVL(sample_room.sample_room_code,
                            NVL(execute_room.execute_room_code,
                            NVL(data_store.data_store_code,
                            station.station_code)))))))) AS "room_code"'),
                            'room_type.room_type_code',
                            'room_type.room_type_name',
                            'department.department_code',
                            'department.department_name',
    
                        )
                            ->where('his_exro_room.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_exro_room.is_active'), $this->is_active);
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
                $this->execute_room_id_name => $this->execute_room_id,
                $this->room_id_name => $this->room_id,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? ($data['data'] ?? null) ?? null);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }
    // /// Exro Room
    // public function exro_room($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->exro_room_name;
    //         $param = [
    //             'room:id,department_id',
    //             'room.execute_room:id,room_id,execute_room_name',
    //             'room.department:id,department_name,department_code',
    //             'execute_room:id,room_id,execute_room_name,execute_room_code',
    //             'execute_room.room:id,department_id',
    //             'execute_room.room.department:id,department_name,department_code'
    //         ];
    //     } else {
    //         $name = $this->exro_room_name . '_' . $id;
    //         $param = [
    //             'room',
    //             'room.execute_room',
    //             'room.department',
    //             'execute_room',
    //             'execute_room.room',
    //             'execute_room.room.department'
    //         ];
    //     }
    //     $data = get_cache_full($this->exro_room, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function execute_room_with_room($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->execute_room_name . '_with_' . $this->room_name;
    //         $param = [
    //             'rooms:id,department_id',
    //             'rooms.execute_room:id,room_id,execute_room_name',
    //             'rooms.department:id,department_name,department_code',
    //             'room.department:id,department_name,department_code',
    //         ];
    //     } else {
    //         $name = $this->execute_room_name . '_' . $id . '_with_' . $this->room_name;
    //         $param = [
    //             'rooms',
    //             'rooms.execute_room',
    //             'rooms.department',
    //             'room.department',
    //         ];
    //     }
    //     $data = get_cache_full($this->execute_room, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function room_with_execute_room($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->room_name . '_with_' . $this->execute_room_name;
    //         $param = [
    //             'department:id,department_name,department_code',
    //             'execute_room:id,room_id,execute_room_name,execute_room_code',
    //             'execute_rooms:id,room_id,execute_room_name,execute_room_code',
    //             'execute_rooms.room:id,department_id',
    //             'execute_rooms.room.department:id,department_name,department_code',
    //         ];
    //     } else {
    //         $name = $this->room_name . '_' . $id . '_with_' . $this->execute_room_name;
    //         $param = [
    //             'department',
    //             'execute_room',
    //             'execute_rooms',
    //             'execute_room.rooms',
    //             'execute_room.room.departments',
    //         ];
    //     }
    //     $data = get_cache_full($this->room, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
    public function exro_room_create(CreateExroRoomRequest $request)
    {   
        if($request->execute_room_id != null){
            $id = $request->execute_room_id;
            if (!is_numeric($id)) {
                return returnIdError($id);
            } 
            $data = $this->execute_room->find($id);
            if ($data == null) {
                return return_not_record($id);
            }   
            // Start transaction
            DB::connection('oracle_his')->beginTransaction();
            try {
                if($request->room_ids !== null){
                    $room_ids_arr = explode(',', $request->room_ids);
                    foreach($room_ids_arr as $key => $item){
                        $room_ids_arr_data[$item] =  [
                            'create_time' => now()->format('Ymdhis'),
                            'modify_time' => now()->format('Ymdhis'),
                            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                            'app_creator' => $this->app_creator,
                            'app_modifier' => $this->app_modifier,
                            'is_hold_order' => $request->is_hold_order,
                            'is_allow_request' => $request->is_allow_request,
                            'is_priority_require' => $request->is_priority_require,
                        ];
                    }
                    foreach($room_ids_arr as $key => $item){
                        $data->rooms()->sync($room_ids_arr_data);
                    }
                }else{
                    ExroRoom::where('execute_room_id', $data->id)->delete();
                }
                DB::connection('oracle_his')->commit();
                // Gọi event để xóa cache
                event(new DeleteCache($this->exro_room_name));
                return return_data_create_success([$data]);
            } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
                // Rollback transaction nếu có lỗi
                DB::connection('oracle_his')->rollBack();
                return return_data_fail_transaction();
            }  
        }else{
            $id = $request->room_id;
            if (!is_numeric($id)) {
                return returnIdError($id);
            }
            $data = $this->room->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            // Start transaction
            DB::connection('oracle_his')->beginTransaction();
            try {
                if($request->execute_room_ids !== null){
                    $execute_room_ids_arr = explode(',', $request->execute_room_ids);
                    foreach($execute_room_ids_arr as $key => $item){
                        $execute_room_ids_arr_data[$item] =  [
                            'create_time' => now()->format('Ymdhis'),
                            'modify_time' => now()->format('Ymdhis'),
                            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                            'app_creator' => $this->app_creator,
                            'app_modifier' => $this->app_modifier,
                            'is_hold_order' => $request->is_hold_order,
                            'is_allow_request' => $request->is_allow_request,
                            'is_priority_require' => $request->is_priority_require,
                        ];
                    }
                    foreach($execute_room_ids_arr as $key => $item){
                        $data->execute_rooms()->sync($execute_room_ids_arr_data);
                    }
                }else{
                    ExroRoom::where('room_id', $data->id)->delete();
                }
                DB::connection('oracle_his')->commit();
                // Gọi event để xóa cache
                event(new DeleteCache($this->exro_room_name));
                return return_data_create_success([$data]);
            } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
                // Rollback transaction nếu có lỗi
                DB::connection('oracle_his')->rollBack();
                return return_data_fail_transaction();
            }
        }
    }
}
