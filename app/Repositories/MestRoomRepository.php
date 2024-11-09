<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\MestRoom;
use Illuminate\Support\Facades\DB;

class MestRoomRepository
{
    protected $mestRoom;
    public function __construct(MestRoom $mestRoom)
    {
        $this->mestRoom = $mestRoom;
    }

    public function applyJoins()
    {
        return $this->mestRoom
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
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query
                ->where(DB::connection('oracle_his')->raw('medi_stock.medi_stock_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('medi_stock.medi_stock_name'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('room_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('room_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_mest_room.is_active'), $isActive);
        }
        return $query;
    }
    public function applyMediStockIdFilter($query, $mediStockId)
    {
        if ($mediStockId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_mest_room.medi_stock_id'), $mediStockId);
        }
        return $query;
    }
    public function applyRoomIdFilter($query, $roomId)
    {
        if ($roomId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_mest_room.room_id'), $roomId);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['medi_stock_code', 'medi_stock_name'])) {
                        $query->orderBy('medi_stock.' . $key, $item);
                    }
                    if (in_array($key, ['room_code', 'room_name'])) {
                        $query->orderBy($key, $item);
                    }
                    if (in_array($key, ['room_type_code', 'room_type_name'])) {
                        $query->orderBy('room_type.' . $key, $item);
                    }
                    if (in_array($key, ['department_code', 'department_name'])) {
                        $query->orderBy('department.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_mest_room.' . $key, $item);
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
        return $this->mestRoom->find($id);
    }
    public function getByMediStockIdAndRoomIds($mediStockId, $roomIds)
    {
        return $this->mestRoom->where('medi_stock_id', $mediStockId)->whereIn('room_id', $roomIds)->get();
    }
    public function getByRoomIdAndMediStockIds($roomId, $mediStockIds)
    {
        return $this->mestRoom->whereIn('medi_stock_id', $mediStockIds)->where('room_id', $roomId)->get();
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function deleteByMediStockId($id)
    {
        $ids = $this->mestRoom->where('medi_stock_id', $id)->pluck('id')->toArray();
        $this->mestRoom->where('medi_stock_id', $id)->delete();
        return $ids;
    }
    public function deleteByRoomId($id)
    {
        $ids = $this->mestRoom->where('room_id', $id)->pluck('id')->toArray();
        $this->mestRoom->where('room_id', $id)->delete();
        return $ids;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_mest_room.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_mest_room.id');
            $maxId = $this->applyJoins()->max('his_mest_room.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('mest_room', 'his_mest_room', $startId, $endId, $batchSize);
            }
        }
    }
}
