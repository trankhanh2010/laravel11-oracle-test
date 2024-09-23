<?php

namespace App\Repositories;

use App\Models\HIS\PatientTypeRoom;
use Illuminate\Support\Facades\DB;

class PatientTypeRoomRepository
{
    protected $patientTypeRoom;
    public function __construct(PatientTypeRoom $patientTypeRoom)
    {
        $this->patientTypeRoom = $patientTypeRoom;
    }

    public function applyJoins()
    {
        return $this->patientTypeRoom
            ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_patient_type_room.patient_type_id')
            ->leftJoin('his_room', 'his_room.id', '=', 'his_patient_type_room.room_id')

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
            ->select(
                'his_patient_type_room.*',
                'patient_type.patient_type_code',
                'patient_type.patient_type_name',

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
                ->where(DB::connection('oracle_his')->raw('patient_type.patient_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('patient_type.patient_type_name'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('room_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('room_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient_type_room.is_active'), $isActive);
        }
        return $query;
    }
    public function applyPatientTypeIdFilter($query, $patientTypeId)
    {
        if ($patientTypeId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient_type_room.patient_type_id'), $patientTypeId);
        }
        return $query;
    }
    public function applyRoomIdFilter($query, $roomId)
    {
        if ($roomId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient_type_room.room_id'), $roomId);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['patient_type_code', 'patient_type_name'])) {
                        $query->orderBy('patient_type.' . $key, $item);
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
                    $query->orderBy('his_patient_type_room.' . $key, $item);
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
        return $this->patientTypeRoom->find($id);
    }
    public function getByPatientTypeIdAndRoomIds($patientTypeId, $roomIds)
    {
        return $this->patientTypeRoom->where('patient_type_id', $patientTypeId)->whereIn('room_id', $roomIds)->get();
    }
    public function getByRoomIdAndPatientTypeIds($roomId, $patientTypeIds)
    {
        return $this->patientTypeRoom->whereIn('patient_type_id', $patientTypeIds)->where('room_id', $roomId)->get();
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function deleteByPatientTypeId($id)
    {
        $ids = $this->patientTypeRoom->where('patient_type_id', $id)->pluck('id')->toArray();
        $this->patientTypeRoom->where('patient_type_id', $id)->delete();
        return $ids;
    }
    public function deleteByRoomId($id)
    {
        $ids = $this->patientTypeRoom->where('room_id', $id)->pluck('id')->toArray();
        $this->patientTypeRoom->where('room_id', $id)->delete();
        return $ids;
    }
    public function getDataFromDbToElastic($id = null)
    {
        $data = $this->applyJoins();
        if ($id != null) {
            $data = $data->where('his_patient_type_room.id', '=', $id)->first();
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
