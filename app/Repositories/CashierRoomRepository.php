<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\CashierRoom;
use App\Models\HIS\Room;
use Illuminate\Support\Facades\DB;

class CashierRoomRepository
{
    protected $cashierRoom;
    protected $room;
    public function __construct(CashierRoom $cashierRoom, Room $room)
    {
        $this->cashierRoom = $cashierRoom;
        $this->room = $room;
    }

    public function applyJoins()
    {
        return $this->cashierRoom
            ->leftJoin('his_room as room', 'room.id', '=', 'his_cashier_room.room_id')
            ->leftJoin('his_room_type as room_type', 'room_type.id', '=', 'room.room_type_id')
            ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')
            ->leftJoin('his_area as area', 'area.id', '=', 'room.area_id')
            ->select(
                'his_cashier_room.*',
                'room.is_pause',
                'room.department_id',
                'room.area_id',
                'room_type.room_type_name',
                'room_type.room_type_code',
                'department.department_name',
                'department.department_code',
                'area.area_name',
                'area.area_code',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_cashier_room.cashier_room_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_cashier_room.cashier_room_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_cashier_room.is_active'), $isActive);
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
                    if (in_array($key, ['is_pause', 'department_id', 'area_id'])) {
                        $query->orderBy('room.' . $key, $item);
                    }
                    if (in_array($key, ['room_type_name', 'room_type_code'])) {
                        $query->orderBy('room_type.' . $key, $item);
                    }
                    if (in_array($key, ['department_name', 'department_code'])) {
                        $query->orderBy('department.' . $key, $item);
                    }
                    if (in_array($key, ['area_name', 'area_code'])) {
                        $query->orderBy('area.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_cashier_room.' . $key, $item);
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
        return $this->cashierRoom->find($id);
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
            'room_type_id' => $request->room_type_id,
            'area_id' => $request->area_id,
            'is_pause' => $request->is_pause,
        ]);
        $data = $this->cashierRoom::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'cashier_room_code' => $request->cashier_room_code,
            'cashier_room_name' => $request->cashier_room_name,
            'einvoice_room_code' => $request->einvoice_room_code,
            'einvoice_room_name' => $request->einvoice_room_name,
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
            'room_type_id' => $request->room_type_id,
            'area_id' => $request->area_id,
            'is_pause' => $request->is_pause,
            'is_active' => $request->is_active,

        ];
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'cashier_room_name' => $request->cashier_room_name,
            'einvoice_room_code' => $request->einvoice_room_code,
            'einvoice_room_name' => $request->einvoice_room_name,
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
            $data = $this->applyJoins()->where('his_cashier_room.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_cashier_room.id');
            $maxId = $this->applyJoins()->max('his_cashier_room.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('cashier_room', 'his_cashier_room', $startId, $endId, $batchSize);
            }
        }
    }
}
