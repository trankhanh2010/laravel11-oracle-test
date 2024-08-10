<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\HIS\ReceptionRoom;
use App\Events\Cache\DeleteCache;
use App\Http\Requests\ReceptionRoom\CreateReceptionRoomRequest;
use App\Http\Requests\ReceptionRoom\UpdateReceptionRoomRequest;
use App\Models\HIS\Room;
use Illuminate\Support\Facades\DB;
class ReceptionRoomController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->reception_room = new ReceptionRoom();
        $this->room = new Room();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->reception_room);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function reception_room($id = null)
    {
        $keyword = $this->keyword;
        if ($keyword != null) {
            $param = [
                'room.department',
                'room.area',
                'room.default_cashier_room',
            ];
            $data = $this->reception_room;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('reception_room_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('reception_room_name'), 'like', $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('is_active'), $this->is_active);
            });
        } 
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy($key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            if ($id == null) {
                $name = $this->reception_room_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active;
                $param = [
                    'room.department',
                    'room.area',
                    'room.default_cashier_room',
                ];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $check_id = $this->check_id($id, $this->reception_room, $this->reception_room_name);
                if($check_id){
                    return $check_id; 
                }
                $name = $this->reception_room_name . '_' . $id. '_is_active_' . $this->is_active;
                $param = [
                    'room.department',
                    'room.area',
                    'room.default_cashier_room',
                ];
            }
            $data = get_cache_full($this->reception_room, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? ($data['count'] ?? null),
            'is_active' => $this->is_active,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data?? ($data['data'] ?? null));
    }
    public function reception_room_create(CreateReceptionRoomRequest $request)
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
                'room_type_id' => $request->room_type_id,
                'default_cashier_room_id' => $request->default_cashier_room_id,
                'area_id' => $request->area_id,
                'screen_saver_module_link' => $request->screen_saver_module_link,
                'deposit_account_book_id' => $request->deposit_account_book_id,
                'is_restrict_execute_room' => $request->is_restrict_execute_room,
                'is_allow_no_icd' => $request->is_allow_no_icd,
                'is_pause' => $request->is_pause,
            ]);
            $data = $this->reception_room::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'reception_room_code' => $request->reception_room_code,
                'reception_room_name' => $request->reception_room_name,
                'patient_type_ids' => $request->patient_type_ids,
                'room_id' => $room->id,
            ]);
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->reception_room_name));
            return return_data_create_success(['data' => $data, 'room' => $room]);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }

    public function reception_room_update(UpdateReceptionRoomRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->reception_room->find($id);
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
                'room_type_id' => $request->room_type_id,
                'default_cashier_room_id' => $request->default_cashier_room_id,
                'area_id' => $request->area_id,
                'screen_saver_module_link' => $request->screen_saver_module_link,
                'deposit_account_book_id' => $request->deposit_account_book_id,
                'is_restrict_execute_room' => $request->is_restrict_execute_room,
                'is_allow_no_icd' => $request->is_allow_no_icd,
                'is_pause' => $request->is_pause,
                'is_active' => $request->is_active,

            ];
            $data_update = [
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,
                'reception_room_name' => $request->reception_room_name,
                'patient_type_ids' => $request->patient_type_ids,
                'is_active' => $request->is_active,

            ];
            $room->fill($room_update);
            $room->save();
            $data->fill($data_update);
            $data->save();
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->reception_room_name));
            return return_data_update_success([$data, $room]);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }

    public function reception_room_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->reception_room->find($id);
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
            event(new DeleteCache($this->reception_room_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }
}
