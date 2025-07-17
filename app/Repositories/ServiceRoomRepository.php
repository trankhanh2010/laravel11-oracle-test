<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ServiceRoom;
use Illuminate\Support\Facades\DB;

class ServiceRoomRepository
{
    protected $serviceRoom;
    public function __construct(ServiceRoom $serviceRoom)
    {
        $this->serviceRoom = $serviceRoom;
    }

    public function applyJoins()
    {
        return $this->serviceRoom
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
                'his_service_room.id as key',
                'his_service_room.*',
                'service.service_name',
                'service.service_code',
                'service_type.service_type_name',
                'service_type.service_type_code',
                'room_type.room_type_name',
                'room_type.room_type_code',
                'department.department_name',
                'department.department_code',
                'his_room.DEFAULT_INSTR_PATIENT_TYPE_ID',
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
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_room.is_active'), $isActive);
        }
        return $query;
    }
    public function applyServiceIdFilter($query, $serviceId)
    {
        if ($serviceId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_room.service_id'), $serviceId);
        }
        return $query;
    }
    public function applyRoomIdFilter($query, $roomId)
    {
        if ($roomId != null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_room.room_id'), $roomId);
        }
        return $query;
    }
    public function applyRoomIdsFilter($query, $roomIds)
    {
        if ($roomIds != null) {
            $query->whereIn(DB::connection('oracle_his')->raw('his_service_room.room_id'), $roomIds);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['service_code', 'service_name'])) {
                        $query->orderBy('service.' . $key, $item);
                    }
                    if (in_array($key, ['room_code', 'room_name'])) {
                        $query->orderBy($key, $item);
                    }
                    if (in_array($key, ['service_type_code', 'service_type_name'])) {
                        $query->orderBy('service_type' . $key, $item);
                    }
                    if (in_array($key, ['room_type_code', 'room_type_name'])) {
                        $query->orderBy('room_type' . $key, $item);
                    }
                    if (in_array($key, ['department_code', 'department_name'])) {
                        $query->orderBy('department' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_service_room.' . $key, $item);
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
        return $this->serviceRoom->find($id);
    }
    public function getByServiceIdAndRoomIds($serviceId, $roomIds)
    {
        return $this->serviceRoom->where('service_id', $serviceId)->whereIn('room_id', $roomIds)->get();
    }
    public function getByRoomIdAndServiceIds($roomId, $serviceIds)
    {
        return $this->serviceRoom->whereIn('service_id', $serviceIds)->where('room_id', $roomId)->get();
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function deleteByServiceId($id)
    {
        $ids = $this->serviceRoom->where('service_id', $id)->pluck('id')->toArray();
        $this->serviceRoom->where('service_id', $id)->delete();
        return $ids;
    }
    public function deleteByRoomId($id)
    {
        $ids = $this->serviceRoom->where('room_id', $id)->pluck('id')->toArray();
        $this->serviceRoom->where('room_id', $id)->delete();
        return $ids;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_service_room.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_service_room.id');
            $maxId = $this->applyJoins()->max('his_service_room.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('service_room', 'his_service_room', $startId, $endId, $batchSize);
            }
        }
    }
}
