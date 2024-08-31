<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ServiceRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServiceRoomController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->service_room = new ServiceRoom();
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->service_room);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function service_room($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if (($keyword != null) || ($this->service_ids != null) || ($this->room_ids != null)) {
                $data = $this->service_room
                    ->leftJoin('his_service as service', 'service.id', '=', 'his_service_room.service_id')
                    ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                    ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                    ->leftJoin('his_room', 'his_room.id', '=', 'his_service_room.room_id')
                    ->leftJoin('his_room_type as room_type', 'room_type.id', '=', 'his_room.room_type_id')
                    ->leftJoin('his_department as department', 'department.id', '=', 'his_room.department_id')

                    ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                    ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                    ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                    ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                    ->leftJoin('his_refectory as refectory', 'his_room.id', '=', 'refectory.room_id')
                    ->leftJoin('his_sample_room as sample_room', 'his_room.id', '=', 'sample_room.room_id')
                    ->leftJoin('his_medi_stock as medi_stock', 'his_room.id', '=', 'medi_stock.room_id')
                    ->leftJoin('his_data_store as data_store', 'his_room.id', '=', 'data_store.room_id')
                    ->leftJoin('his_station as station', 'his_room.id', '=', 'station.room_id')

                    ->select(
                        'his_service_room.*',
                        'service.service_name',
                        'service.service_code',
                        'service_type.service_type_name',
                        'service_type.service_type_code',
                        'room_type.room_type_name',
                        'room_type.room_type_code',
                        'department.department_name',
                        'department.department_code',
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
                        ->where(DB::connection('oracle_his')->raw('service.service_code'), 'like', $keyword . '%')
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
                        $query = $query->where(DB::connection('oracle_his')->raw('his_service_room.is_active'), $this->is_active);
                    });
                }
                if ($this->service_ids != null) {
                    $data = $data->where(function ($query) {
                        $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_room.service_id'), $this->service_ids);
                    });
                }
                if ($this->room_ids != null) {
                    $data = $data->where(function ($query) {
                        $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_room.room_id'), $this->room_ids);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_service_room.' . $key, $item);
                    }
                }
                if ($this->get_all) {
                    $data = $data
                        ->get();
                } else {
                    $data = $data
                        ->skip($this->start)
                        ->take($this->limit)
                        ->get();
                }
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->service_room_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active . '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->service_room
                            ->leftJoin('his_service as service', 'service.id', '=', 'his_service_room.service_id')
                            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                            ->leftJoin('his_room', 'his_room.id', '=', 'his_service_room.room_id')
                            ->leftJoin('his_room_type as room_type', 'room_type.id', '=', 'his_room.room_type_id')
                            ->leftJoin('his_department as department', 'department.id', '=', 'his_room.department_id')

                            ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                            ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                            ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                            ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                            ->leftJoin('his_refectory as refectory', 'his_room.id', '=', 'refectory.room_id')
                            ->leftJoin('his_sample_room as sample_room', 'his_room.id', '=', 'sample_room.room_id')
                            ->leftJoin('his_medi_stock as medi_stock', 'his_room.id', '=', 'medi_stock.room_id')
                            ->leftJoin('his_data_store as data_store', 'his_room.id', '=', 'data_store.room_id')
                            ->leftJoin('his_station as station', 'his_room.id', '=', 'station.room_id')

                            ->select(
                                'his_service_room.*',
                                'service.service_name',
                                'service.service_code',
                                'service_type.service_type_name',
                                'service_type.service_type_code',
                                'room_type.room_type_name',
                                'room_type.room_type_code',
                                'department.department_name',
                                'department.department_code',
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
                                $query = $query->where(DB::connection('oracle_his')->raw('his_service_room.is_active'), $this->is_active);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_service_room.' . $key, $item);
                            }
                        }
                        if ($this->get_all) {
                            $data = $data
                                ->get();
                        } else {
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
                    $check_id = $this->check_id($id, $this->service_room, $this->service_room_name);
                    if ($check_id) {
                        return $check_id;
                    }
                    $data = Cache::remember($this->service_room_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->service_room
                            ->leftJoin('his_service as service', 'service.id', '=', 'his_service_room.service_id')
                            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                            ->leftJoin('his_room', 'his_room.id', '=', 'his_service_room.room_id')
                            ->leftJoin('his_room_type as room_type', 'room_type.id', '=', 'his_room.room_type_id')
                            ->leftJoin('his_department as department', 'department.id', '=', 'his_room.department_id')

                            ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                            ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                            ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                            ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                            ->leftJoin('his_refectory as refectory', 'his_room.id', '=', 'refectory.room_id')
                            ->leftJoin('his_sample_room as sample_room', 'his_room.id', '=', 'sample_room.room_id')
                            ->leftJoin('his_medi_stock as medi_stock', 'his_room.id', '=', 'medi_stock.room_id')
                            ->leftJoin('his_data_store as data_store', 'his_room.id', '=', 'data_store.room_id')
                            ->leftJoin('his_station as station', 'his_room.id', '=', 'station.room_id')

                            ->select(
                                'his_service_room.*',
                                'service.service_name',
                                'service.service_code',
                                'service_type.service_type_name',
                                'service_type.service_type_code',
                                'room_type.room_type_name',
                                'room_type.room_type_code',
                                'department.department_name',
                                'department.department_code',
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
                                $query = $query->where(DB::connection('oracle_his')->raw('his_service_room.is_active'), $this->is_active);
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
                $this->service_ids_name => $this->service_ids ?? null,
                $this->room_ids_name => $this->room_ids ?? null,
                $this->is_active_name => $this->is_active,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data['data'] ?? $data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }


    // public function service_with_room($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->service_name . '_with_' . $this->execute_room_name;
    //         $param = [
    //             'execute_rooms:id,room_id,execute_room_name,execute_room_code',
    //             'execute_rooms.room:id,department_id,room_type_id',
    //             'execute_rooms.room.department:id,department_name,department_code',
    //             'execute_rooms.room.room_type:id,room_type_name,room_type_code'
    //         ];
    //     } else {
    //         $name = $this->service_name . '_' . $id . '_with_' . $this->execute_room_name;
    //         $param = [
    //             'execute_rooms',
    //             'execute_rooms.room:id,department_id,room_type_id',
    //             'execute_rooms.room.department',
    //             'execute_rooms.room.room_type'
    //         ];
    //     }
    //     $data = get_cache_full($this->service, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function room_with_service($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->execute_room_name . '_with_' . $this->service_name;
    //         $param = [
    //             'services:id,service_name,service_code',
    //             'room:id,department_id,room_type_id',
    //             'room.department:id,department_name,department_code',
    //             'room.room_type:id,room_type_name,room_type_code',
    //             'room.execute_room:id,room_id,execute_room_name,execute_room_code'
    //         ];
    //     } else {
    //         $name = $this->execute_room_name . '_' . $id . '_with_' . $this->service_name;
    //         $param = [
    //             'services',
    //             'room:id,department_id,room_type_id',
    //             'room.department:id,department_name,department_code',
    //             'room.room_type:id,room_type_name,room_type_code',
    //             'room.execute_room'
    //         ];
    //     }
    //     $data = get_cache_full($this->execute_room, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
}
