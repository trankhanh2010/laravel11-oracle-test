<?php

namespace App\Repositories;

use App\Models\HIS\Refectory;
use App\Models\HIS\Room;
use Illuminate\Support\Facades\DB;

class RefectoryRepository
{
    protected $refectory;
    protected $room;
    public function __construct(Refectory $refectory, Room $room)
    {
        $this->refectory = $refectory;
        $this->room = $room;
    }

    public function applyJoins()
    {
        return $this->refectory
            ->leftJoin('his_room as room', 'room.id', '=', 'his_refectory.room_id')
            ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')
        
            ->select(
                'his_refectory.*',
                'department.id as department_id',
                'department.department_name',
                'department.department_code',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_refectory.refectory_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_refectory.refectory_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_refectory.is_active'), $isActive);
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
                    if (in_array($key, ['department_id', 'department_name', 'department_code'])) {
                        $query->orderBy('his_department.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_refectory.' . $key, $item);
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
        return $this->refectory->find($id);
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
        ]);
        $data = $this->refectory::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'refectory_code' => $request->refectory_code,
            'refectory_name' => $request->refectory_name,
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
            'department_id' => $request->department_id,
            'room_type_id' => $request->room_type_id,
            'is_active' => $request->is_active,
        ];
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'refectory_name' => $request->refectory_name,
            'is_active' => $request->is_active,
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
    public function getDataFromDbToElastic($id = null)
    {
        $data = $this->applyJoins();
        if ($id != null) {
            $data = $data->where('his_refectory.id', '=', $id)->first();
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
