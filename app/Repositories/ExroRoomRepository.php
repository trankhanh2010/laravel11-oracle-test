<?php 
namespace App\Repositories;

use App\Models\HIS\ExroRoom;
use Illuminate\Support\Facades\DB;

class ExroRoomRepository
{
    protected $exroRoom;
    public function __construct(ExroRoom $exroRoom)
    {
        $this->exroRoom = $exroRoom;
    }

    public function applyJoins()
    {
        return $this->exroRoom
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
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query                        
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
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_exro_room.is_active'), $isActive);
        }
        return $query;
    }
    public function applyRoomIdFilter($query, $roomId)
    {
        if ($roomId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_exro_room.room_id'), $roomId);
        }
        return $query;
    }
    public function applyExecuteRoomIdFilter($query, $executeRoomId)
    {
        if ($executeRoomId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_exro_room.execute_room_id'), $executeRoomId);
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
                    if (in_array($key, ['execute_room_name', 'execute_room_code'])) {
                        $query->orderBy('execute_room.' . $key, $item);
                    }
                    if (in_array($key, ['execute_room_type_code', 'execute_room_type_name'])) {
                        $query->orderBy('execute_room_type.' . $key, $item);
                    }
                    if (in_array($key, ['department_name', 'department_code'])) {
                        $query->orderBy('department.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_exro_room.' . $key, $item);
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
        return $this->exroRoom->find($id);
    }
    public function getByRoomIdAndExecuteRoomIds($roomId, $executeRoomIds)
    {
        return $this->exroRoom->where('room_id', $roomId)->whereIn('execute_room_id',$executeRoomIds)->get();
    }
    public function getByExecuteRoomIdAndRoomIds($executeRoomId, $roomIds)
    {
        return $this->exroRoom->whereIn('room_id', $roomIds)->where('execute_room_id',$executeRoomId)->get();
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function deleteByRoomId($id){
        $ids = $this->exroRoom->where('room_id', $id)->pluck('id')->toArray();
        $this->exroRoom->where('room_id', $id)->delete();
        return $ids;
    }
    public function deleteByExecuteRoomId($id){
        $ids = $this->exroRoom->where('execute_room_id', $id)->pluck('id')->toArray();
        $this->exroRoom->where('execute_room_id', $id)->delete();
        return $ids;
    }
    public function getDataFromDbToElastic($id = null){
        $data = $this->applyJoins();
        if($id != null){
            $data = $data->where('his_exro_room.id','=', $id)->first();
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