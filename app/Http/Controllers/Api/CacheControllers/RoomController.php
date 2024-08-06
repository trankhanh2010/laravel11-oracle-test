<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\Department;
use App\Models\HIS\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RoomController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->room = new Room();
        $this->department = new Department();

        // Kiểm tra tên trường trong bảng
        $this->order_by_join = ['room_name', 'room_code'];

        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->room);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function room()
    {
        $keyword = $this->keyword;
        if (($keyword != null) || ($this->department_id != null)) {
            $data = $this->room
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
                    'his_room.id',
                    'his_room.department_id',
                    'his_room.room_type_id',
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
                ->whereNotNull(DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                        NVL(cashier.cashier_room_name, 
                        NVL(execute.execute_room_name, 
                        NVL(reception.reception_room_name,
                        NVL(refectory.refectory_name,
                        NVL(sample_room.sample_room_name,
                        NVL(medi_stock.medi_stock_name,
                        NVL(data_store.data_store_name,
                        station.station_name))))))))'));
            if ($this->department_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_room.department_id'), $this->department_id);
                });
            }
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query->where(DB::connection('oracle_his')->raw('bed.bed_room_name'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('cashier.cashier_room_name'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('execute.execute_room_name'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('reception.reception_room_name'), 'like', $keyword . '%')
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
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_room.is_active'), $this->is_active);
                });
            }
            if ($this->department_id !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_room.department_id'), $this->department_id);
                });
            }
            if ($this->room_type_id !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_room.room_type_id'), $this->room_type_id);
                });
            }
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    if (!in_array($key, $this->order_by_join)) {
                        $data->orderBy(DB::connection('oracle_his')->raw('his_room.' . $key . ''), $item);
                    } else {
                        $data->orderBy(DB::connection('oracle_his')->raw('"' . $key . '"'), $item);
                    }
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = Cache::remember('room_name_code_with_bed_room_bed_room_execute_room_reception_room' . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active, $this->time, function () {
                $data = $this->room
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
                        'his_room.id',
                        'his_room.department_id',
                        'his_room.room_type_id',
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
                    ->whereNotNull(DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                NVL(cashier.cashier_room_name, 
                NVL(execute.execute_room_name, 
                NVL(reception.reception_room_name,
                NVL(refectory.refectory_name,
                NVL(sample_room.sample_room_name,
                NVL(medi_stock.medi_stock_name,
                NVL(data_store.data_store_name,
                station.station_name))))))))'));
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw("his_room.is_active"), $this->is_active);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        if (!in_array($key, $this->order_by_join)) {
                            $data->orderBy(DB::connection('oracle_his')->raw('his_room.' . $key . ''), $item);
                        } else {
                            $data->orderBy(DB::connection('oracle_his')->raw('"' . $key . '"'), $item);
                        }
                    }
                }
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
                return ['data' => $data, 'count' => $count];
            });
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count'],
            'is_active' => $this->is_active,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request,
            'department_id' => $this->department_id ?? null,
            'room_type_id' => $this->room_type_id ?? null,
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
    // public function room_with_department($id)
    // {
    //     if (!is_numeric($id)) {
    //         return return_id_error($id);
    //     }
    //     $data = $this->department->find($id);
    //     if ($data == null) {
    //         return return_not_record($id);
    //     }
    //     $data = Cache::remember('room_name_code_with_bed_room_bed_room_execute_room_reception_room_with_department_id_' . $id, $this->time, function () use ($id) {
    //         return $this->room
    //             ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
    //             ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
    //             ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
    //             ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
    //             ->select(
    //                 'his_room.id',
    //                 'his_room.department_id',
    //                 DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
    //             NVL(cashier.cashier_room_name, 
    //             NVL(execute.execute_room_name, 
    //             reception.reception_room_name))) AS "room_name"'),
    //                 DB::connection('oracle_his')->raw('NVL(bed.bed_room_code, 
    //             NVL(cashier.cashier_room_code, 
    //             NVL(execute.execute_room_code, 
    //             reception.reception_room_code))) AS "room_code"')
    //             )
    //             ->where(function ($query) {
    //                 $query->whereNotNull('bed.bed_room_name')
    //                     ->orWhereNotNull('cashier.cashier_room_name')
    //                     ->orWhereNotNull('execute.execute_room_name')
    //                     ->orWhereNotNull('reception.reception_room_name');
    //             })
    //             ->where('his_room.department_id', $id)
    //             ->get();
    //     });
    //     $count = $data->count();
    //     $param_return = [
    //         'start' => null,
    //         'limit' => null,
    //         'count' => $count
    //     ];
    //     return return_data_success($param_return, $data);
    // }
}
