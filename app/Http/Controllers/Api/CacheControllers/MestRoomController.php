<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MestRoom\CreateMestRoomRequest;
use App\Models\HIS\MediStock;
use App\Models\HIS\MestRoom;
use App\Models\HIS\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MestRoomController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->mest_room = new MestRoom();
        $this->medi_stock = new MediStock();
        $this->room = new Room();
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->mest_room);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function mest_export_room($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->mest_room
                    ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_mest_room.medi_stock_id')
                    ->leftJoin('his_room ', 'his_room.id', '=', 'his_mest_room.room_id')

                    ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                    ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                    ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                    ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                    ->leftJoin('his_refectory as refectory', 'his_room.id', '=', 'refectory.room_id')
                    ->leftJoin('his_sample_room as sample_room', 'his_room.id', '=', 'sample_room.room_id')
                    ->leftJoin('his_medi_stock as medi_stock', 'his_room.id', '=', 'medi_stock.room_id')
                    ->leftJoin('his_data_store as data_store', 'his_room.id', '=', 'data_store.room_id')
                    ->leftJoin('his_station as station', 'his_room.id', '=', 'station.room_id')

                    ->leftJoin('his_room_type as room_type', 'room_type.id', '=', 'his_room.room_type_id')
                    ->leftJoin('his_department as department', 'department.id', '=', 'his_room.department_id')

                    ->select(
                        'his_mest_room.*',
                        'medi_stock.medi_stock_code',
                        'medi_stock.medi_stock_name',
                        'room_type.room_type_code',
                        'room_type.room_type_name',
                        'department.department_code',
                        'department.department_name',
                        DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                        NVL(cashier.cashier_room_name, 
                        NVL(execute.execute_room_name, 
                        NVL(reception.reception_room_name,
                        NVL(refectory.refectory_name,
                        NVL(sample_room.sample_room_name,
                        NVL(medi_stock.medi_stock_name,
                        NVL(data_store.data_store_name,
                        station.station_name)))))))) AS "room_name"'),
                        DB::connection('oracle_his')->raw('NVL(bed.bed_room_code, 
                        NVL(cashier.cashier_room_code, 
                        NVL(execute.execute_room_code, 
                        NVL(reception.reception_room_code,
                        NVL(refectory.refectory_code,
                        NVL(sample_room.sample_room_code,
                        NVL(medi_stock.medi_stock_code,
                        NVL(data_store.data_store_code,
                        station.station_code)))))))) AS "room_code"')

                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('medi_stock.medi_stock_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('bed.bed_room_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('cashier.cashier_room_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('execute.execute_room_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('reception.reception_room_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('refectory.refectory_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('sample_room.sample_room_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('medi_stock.medi_stock_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('data_store.data_store_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('station.station_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_mest_room.is_active'), $this->is_active);
                    });
                }
                if ($this->medi_stock_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_mest_room.medi_stock_id'), $this->medi_stock_id);
                    });
                }
                if ($this->room_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_mest_room.room_id'), $this->room_id);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_mest_room.' . $key, $item);
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
                    $data = Cache::remember($this->mest_room_name . '_medi_stock_id_' . $this->medi_stock_id . '_room_id_' . $this->room_id . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->mest_room
                        ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_mest_room.medi_stock_id')
                        ->leftJoin('his_room ', 'his_room.id', '=', 'his_mest_room.room_id')
    
                        ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                        ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                        ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                        ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                        ->leftJoin('his_refectory as refectory', 'his_room.id', '=', 'refectory.room_id')
                        ->leftJoin('his_sample_room as sample_room', 'his_room.id', '=', 'sample_room.room_id')
                        ->leftJoin('his_medi_stock as medi_stock', 'his_room.id', '=', 'medi_stock.room_id')
                        ->leftJoin('his_data_store as data_store', 'his_room.id', '=', 'data_store.room_id')
                        ->leftJoin('his_station as station', 'his_room.id', '=', 'station.room_id')
    
                        ->leftJoin('his_room_type as room_type', 'room_type.id', '=', 'his_room.room_type_id')
                        ->leftJoin('his_department as department', 'department.id', '=', 'his_room.department_id')
    
                        ->select(
                            'his_mest_room.*',
                            'medi_stock.medi_stock_code',
                            'medi_stock.medi_stock_name',
                            'room_type.room_type_code',
                            'room_type.room_type_name',
                            'department.department_code',
                            'department.department_name',
                            DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                            NVL(cashier.cashier_room_name, 
                            NVL(execute.execute_room_name, 
                            NVL(reception.reception_room_name,
                            NVL(refectory.refectory_name,
                            NVL(sample_room.sample_room_name,
                            NVL(medi_stock.medi_stock_name,
                            NVL(data_store.data_store_name,
                            station.station_name)))))))) AS "room_name"'),
                            DB::connection('oracle_his')->raw('NVL(bed.bed_room_code, 
                            NVL(cashier.cashier_room_code, 
                            NVL(execute.execute_room_code, 
                            NVL(reception.reception_room_code,
                            NVL(refectory.refectory_code,
                            NVL(sample_room.sample_room_code,
                            NVL(medi_stock.medi_stock_code,
                            NVL(data_store.data_store_code,
                            station.station_code)))))))) AS "room_code"')
    
                        );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_mest_room.is_active'), $this->is_active);
                            });
                        }
                        if ($this->medi_stock_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_mest_room.medi_stock_id'), $this->medi_stock_id);
                            });
                        }
                        if ($this->room_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_mest_room.room_id'), $this->room_id);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_mest_room.' . $key, $item);
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
                    $check_id = $this->check_id($id, $this->mest_room, $this->mest_room_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->mest_room_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->mest_room
                        ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_mest_room.medi_stock_id')
                        ->leftJoin('his_room ', 'his_room.id', '=', 'his_mest_room.room_id')
    
                        ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                        ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                        ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                        ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                        ->leftJoin('his_refectory as refectory', 'his_room.id', '=', 'refectory.room_id')
                        ->leftJoin('his_sample_room as sample_room', 'his_room.id', '=', 'sample_room.room_id')
                        ->leftJoin('his_medi_stock as medi_stock', 'his_room.id', '=', 'medi_stock.room_id')
                        ->leftJoin('his_data_store as data_store', 'his_room.id', '=', 'data_store.room_id')
                        ->leftJoin('his_station as station', 'his_room.id', '=', 'station.room_id')
    
                        ->leftJoin('his_room_type as room_type', 'room_type.id', '=', 'his_room.room_type_id')
                        ->leftJoin('his_department as department', 'department.id', '=', 'his_room.department_id')
    
                        ->select(
                            'his_mest_room.*',
                            'medi_stock.medi_stock_code',
                            'medi_stock.medi_stock_name',
                            'room_type.room_type_code',
                            'room_type.room_type_name',
                            'department.department_code',
                            'department.department_name',
                            DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                            NVL(cashier.cashier_room_name, 
                            NVL(execute.execute_room_name, 
                            NVL(reception.reception_room_name,
                            NVL(refectory.refectory_name,
                            NVL(sample_room.sample_room_name,
                            NVL(medi_stock.medi_stock_name,
                            NVL(data_store.data_store_name,
                            station.station_name)))))))) AS "room_name"'),
                            DB::connection('oracle_his')->raw('NVL(bed.bed_room_code, 
                            NVL(cashier.cashier_room_code, 
                            NVL(execute.execute_room_code, 
                            NVL(reception.reception_room_code,
                            NVL(refectory.refectory_code,
                            NVL(sample_room.sample_room_code,
                            NVL(medi_stock.medi_stock_code,
                            NVL(data_store.data_store_code,
                            station.station_code)))))))) AS "room_code"')
    
                        )
                            ->where('his_mest_room.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_mest_room.is_active'), $this->is_active);
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
                $this->medi_stock_id_name => $this->medi_stock_id,
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
    // /// Mest Export Room
    // public function mest_export_room($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->mest_export_room_name;
    //         $param = [
    //             'medi_stock:id,medi_stock_name,medi_stock_code,is_active,is_delete,creator,modifier',
    //             'room:id,department_id',
    //             'room.execute_room:id,room_id,execute_room_name,execute_room_code',
    //             'room.department:id,department_name,department_code'
    //         ];
    //     } else {
    //         $name = $this->mest_export_room_name . '_' . $id;
    //         $param = [
    //             'medi_stock',
    //             'room',
    //             'room.execute_room',
    //             'room.department'
    //         ];
    //     }
    //     $data = get_cache_full($this->mest_export_room, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function medi_stock_with_room($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medi_stock_name . '_with_' . $this->room_name;
    //         $param = [
    //             'rooms:id,department_id,room_type_id',
    //             'rooms.execute_room:id,room_id,execute_room_name,execute_room_code'
    //         ];
    //     } else {
    //         $name = $this->medi_stock_name . '_' . $id . '_with_' . $this->room_name;
    //         $param = [
    //             'rooms',
    //             'rooms.execute_room',
    //             'rooms.department',
    //             'rooms.room_type'
    //         ];
    //     }
    //     $data = get_cache_full($this->medi_stock, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function room_with_medi_stock($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->room_name . '_with_' . $this->medi_stock_name;
    //         $param = [
    //             'execute_room:id,room_id,execute_room_name,execute_room_code',
    //             'department:id,department_name,department_code',
    //             'room_type:id,room_type_name,room_type_code',
    //             'medi_stocks:id,medi_stock_name,medi_stock_code'
    //         ];
    //     } else {
    //         $name = $this->room_name . '_' . $id . '_with_' . $this->medi_stock_name;
    //         $param = [
    //             'execute_room',
    //             'department',
    //             'room_type',
    //             'medi_stocks'
    //         ];
    //     }
    //     $data = get_cache_full($this->room, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
    public function mest_export_room_create(CreateMestRoomRequest $request)
    {
        if($request->medi_stock_id != null){
            $id = $request->medi_stock_id;
            if (!is_numeric($id)) {
                return return_id_error($id);
            } 
            $data = $this->medi_stock->find($id);
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

                        ];
                    }
                    foreach($room_ids_arr as $key => $item){
                        $data->rooms()->sync($room_ids_arr_data);
                    }
                }else{
                    MestRoom::where('medi_stock_id', $data->id)->delete();
                }
                DB::connection('oracle_his')->commit();
                // Gọi event để xóa cache
                event(new DeleteCache($this->mest_room_name));
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
                return return_id_error($id);
            }
            $data = $this->room->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            // Start transaction
            DB::connection('oracle_his')->beginTransaction();
            try {
                if($request->medi_stock_ids !== null){
                    $medi_stock_ids_arr = explode(',', $request->medi_stock_ids);
                    foreach($medi_stock_ids_arr as $key => $item){
                        $medi_stock_ids_arr_data[$item] =  [
                            'create_time' => now()->format('Ymdhis'),
                            'modify_time' => now()->format('Ymdhis'),
                            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                            'app_creator' => $this->app_creator,
                            'app_modifier' => $this->app_modifier,

                        ];
                    }
                    foreach($medi_stock_ids_arr as $key => $item){
                        $data->medi_stocks()->sync($medi_stock_ids_arr_data);
                    }
                }else{
                    MestRoom::where('room_id', $data->id)->delete();
                }
                DB::connection('oracle_his')->commit();
                // Gọi event để xóa cache
                event(new DeleteCache($this->mest_room_name));
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
