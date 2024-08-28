<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\HIS\BedRoom;
use App\Http\Requests\BedRoom\CreateBedRoomRequest;
use App\Http\Requests\BedRoom\UpdateBedRoomRequest;
use App\Events\Cache\DeleteCache;
use App\Models\HIS\Room;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BedRoomController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->bed_room = new BedRoom();
        $this->room = new Room();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->bed_room);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function bed_room($id = null)
    {     
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }        
        try {
        $keyword = $this->keyword;
        if ($keyword != null) {
            $param = [
                'room:id,department_id,area_id,speciality_id,default_cashier_room_id,default_instr_patient_type_id,is_pause',
                'room.department:id,department_name,department_code',
                'room.area:id,area_name',
                'room.speciality:id,speciality_name,speciality_code',
                'room.default_cashier_room:id,cashier_room_name',
                'room.default_instr_patient_type:id,patient_type_name',
            ];
            $data = $this->bed_room
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
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('his_bed_room.bed_room_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_bed_room.bed_room_name'), 'like', $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_bed_room.is_active'), $this->is_active);
            });
        }
        if ($this->department_id !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('room.department_id'), $this->department_id);
            });
        } 
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_bed_room.'.$key, $item);
                }
            }
            if($this->get_all){
                $data = $data
                ->get();
            }else{
                $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
            }

        } else {
            if ($id == null) {
                $data = Cache::remember($this->bed_room_name. '_department_id_'.$this->department_id. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function (){
                    $data = $this->bed_room
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
                    if ($this->is_active !== null) {
                        $data = $data->where(function ($query) {
                            $query = $query->where(DB::connection('oracle_his')->raw('his_bed_room.is_active'), $this->is_active);
                        });
                    }
                    if ($this->department_id !== null) {
                        $data = $data->where(function ($query) {
                            $query = $query->where(DB::connection('oracle_his')->raw('room.department_id'), $this->department_id);
                        });
                    } 
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_bed_room.'.$key, $item);
                            }
                        }
                        if($this->get_all){
                            $data = $data
                            ->get();
                        }else{
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
                $check_id = $this->check_id($id, $this->bed_room, $this->bed_room_name);
                if($check_id){
                    return $check_id; 
                }
                $name =  $this->bed_room_name . '_' . $id. '_is_active_' . $this->is_active;
                $param = [
                    'room',
                    'room.department',
                    'room.area',
                    'room.speciality',
                    'room.default_cashier_room',
                    'room.default_instr_patient_type',
                ];
                $model = $this->bed_room;
                $data = get_cache_full($model, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active, $this->get_all);
            }

        }
        $param_return = [
            $this->get_all_name => $this->get_all,
            $this->start_name => ($this->get_all || !is_null($id)) ? null : $this->start,
            $this->limit_name => ($this->get_all || !is_null($id)) ? null : $this->limit,
            $this->count_name => $count ?? ($data['count'] ?? null),
            $this->is_active_name => $this->is_active,
            $this->keyword_name => $this->keyword,
            $this->order_by_name => $this->order_by_request
        ];
        return return_data_success($param_return, $data?? ($data['data'] ?? null));
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
        return return_500_error();
    }
    }

    public function bed_room_create(CreateBedRoomRequest $request)
    {
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        try {
            $room = $this->room::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
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
            $data = $this->bed_room::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'bed_room_code' => $request->bed_room_code,
                'bed_room_name' => $request->bed_room_name,
                'is_surgery' => $request->is_surgery,
                'treatment_type_ids' => $request->treatment_type_ids,
                'room_id' => $room->id,
            ]);
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->bed_room_name));
            return return_data_create_success(['data' => $data, 'room' => $room]);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }

    public function bed_room_update(UpdateBedRoomRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->bed_room->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $room = $this->room->find($data->room_id);
        if ($room == null) {
            return return_not_record($data->room_id);
        }
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        try {
            $room_update = [
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,
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
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,
                'bed_room_code' => $request->bed_room_code,
                'bed_room_name' => $request->bed_room_name,
                'is_surgery' => $request->is_surgery,
                'treatment_type_ids' => $request->treatment_type_ids,
                'is_active' => $request->is_active

            ];
            $room->fill($room_update);
            $room->save();
            $data->fill($data_update);
            $data->save();
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->bed_room_name));
            return return_data_update_success([$data, $room]);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }

    public function bed_room_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->bed_room->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $room = $this->room->find($data->room_id);
        if ($room == null) {
            return return_not_record($data->room_id);
        }
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        try {
            $data->delete();
            $room->delete();
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->bed_room_name));
            return return_data_delete_success();
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }
}
