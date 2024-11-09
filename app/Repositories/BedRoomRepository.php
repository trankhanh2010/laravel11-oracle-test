<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\BedRoom;
use App\Models\HIS\Room;
use Illuminate\Support\Facades\DB;

class BedRoomRepository
{
    protected $bedRoom;
    protected $room;
    public function __construct(BedRoom $bedRoom, Room $room)
    {
        $this->bedRoom = $bedRoom;
        $this->room = $room;
    }

    public function applyJoins()
    {
        return $this->bedRoom
            ->leftJoin('his_room as room', 'room.id', '=', 'his_bed_room.room_id')
            ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')
            ->leftJoin('his_area as area', 'area.id', '=', 'room.area_id')
            ->leftJoin('his_speciality as speciality', 'speciality.id', '=', 'room.speciality_id')
            ->leftJoin('his_cashier_room as default_cashier_room', 'default_cashier_room.id', '=', 'room.default_cashier_room_id')
            ->leftJoin('his_patient_type as default_instr_patient_type', 'default_instr_patient_type.id', '=', 'room.default_instr_patient_type_id')

            ->select(
                'his_bed_room.*',
                'room.is_pause',
                'department.id as department_id',
                'department.department_name',
                'department.department_code',
                'area.area_name',
                'area.area_code',
                'speciality.speciality_name',
                'speciality.speciality_code',
                'default_cashier_room.cashier_room_name',
                'default_cashier_room.cashier_room_code',
                'default_instr_patient_type.patient_type_name',
                'default_instr_patient_type.patient_type_code',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_bed_room.bed_room_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_bed_room.bed_room_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_bed_room.is_active'), $isActive);
        }
        return $query;
    }
    public function applyDepartmentIdFilter($query, $departmentId)
    {
        if ($departmentId !== null) {
            $query->where(DB::connection('oracle_his')->raw('room.department_id'), $departmentId);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['is_pause'])) {
                        $query->orderBy('his_room.' . $key, $item);
                    }
                    if (in_array($key, ['department_id', 'department_name', 'department_code'])) {
                        $query->orderBy('his_department.' . $key, $item);
                    }
                    if (in_array($key, ['area_name', 'area_code'])) {
                        $query->orderBy('his_area.' . $key, $item);
                    }
                    if (in_array($key, ['speciality_name', 'speciality_code'])) {
                        $query->orderBy('his_speciality.' . $key, $item);
                    }
                    if (in_array($key, ['cashier_room_name', 'cashier_room_code'])) {
                        $query->orderBy('default_cashier_room.' . $key, $item);
                    }
                    if (in_array($key, ['patient_type_name', 'patient_type_code'])) {
                        $query->orderBy('default_instr_patient_type.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_bed_room.' . $key, $item);
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
        return $this->bedRoom->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        $room = $this->room::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'department_id' => $request->department_id,
            'area_id' => $request->area_id,
            'speciality_id' => $request->speciality_id,
            'default_cashier_room_id' => $request->default_cashier_room_id,
            'default_instr_patient_type_id' => $request->default_instr_patient_type_id,
            'is_restrict_req_service' => $request->is_restrict_req_service,
            'is_pause' => $request->is_pause,
            'is_restrict_execute_room' => $request->is_restrict_execute_room,
            'room_type_id' => $request->room_type_id
        ]);
        $data = $this->bedRoom::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'bed_room_code' => $request->bed_room_code,
            'bed_room_name' => $request->bed_room_name,
            'is_surgery' => $request->is_surgery,
            'treatment_type_ids' => $request->treatment_type_ids,
            'room_id' => $room->id,
        ]);
        DB::connection('oracle_his')->commit();
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        $room_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'area_id' => $request->area_id,
            'speciality_id' => $request->speciality_id,
            'default_cashier_room_id' => $request->default_cashier_room_id,
            'default_instr_patient_type_id' => $request->default_instr_patient_type_id,
            'is_restrict_req_service' => $request->is_restrict_req_service,
            'is_pause' => $request->is_pause,
            'is_restrict_execute_room' => $request->is_restrict_execute_room,
            'room_type_id' => $request->room_type_id,
            'is_active' => $request->is_active,

        ];
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'bed_room_name' => $request->bed_room_name,
            'is_surgery' => $request->is_surgery,
            'treatment_type_ids' => $request->treatment_type_ids,
            'is_active' => $request->is_active

        ];
        $room = $this->room->find($data->room_id);
        $room->fill($room_update);
        $room->save();
        $data->fill($data_update);
        $data->save();
        DB::connection('oracle_his')->commit();
        return $data;
    }
    public function delete($data)
    {
        DB::connection('oracle_his')->beginTransaction();
        $data->delete();
        $room = $this->room->find($data->room_id);
        $room->delete();
        DB::connection('oracle_his')->commit();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_bed_room.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_bed_room.id');
            $maxId = $this->applyJoins()->max('his_bed_room.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('bed_room', 'his_bed_room', $startId, $endId, $batchSize);
            }
        }
    }
}
