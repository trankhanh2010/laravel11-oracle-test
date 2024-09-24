<?php

namespace App\Repositories;

use App\Models\HIS\Room;
use Illuminate\Support\Facades\DB;

class RoomRepository
{
    protected $room;
    public function __construct(Room $room)
    {
        $this->room = $room;
    }

    public function applyJoins()
    {
        return $this->room
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
            station.station_name)))))))) AS room_name'),
                DB::connection('oracle_his')->raw('NVL(bed.bed_room_code, 
            NVL(cashier.cashier_room_code, 
            NVL(execute.execute_room_code, 
            NVL(reception.reception_room_code,
            NVL(refectory.refectory_code,
            NVL(sample_room.sample_room_code,
            NVL(medi_stock.medi_stock_code,
            NVL(data_store.data_store_code,
            station.station_code)))))))) AS room_code')
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
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
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
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_room.is_active'), $isActive);
        }
        return $query;
    }
    public function applyDepartmentIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_room.department_id'), $id);
        }
        return $query;
    }
    public function applyRoomTypeIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_room.room_type_id'), $id);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['room_name', 'room_code'])) {
                        $query->orderBy($key, $item);
                    }
                } else {
                    $query->orderBy('his_room.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->room->find($id);
    }
    public function getDataFromDbToElastic($id = null)
    {
        $data = $this->applyJoins();
        if($id != null){
            $data = $data->where('his_room.id','=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
            }
        } else {
            $data = $data->get();
            $data = $data->map(function ($item) {
                return $item->getAttributes(); 
            })->toArray(); 
        }
        return $data;
    }
}
