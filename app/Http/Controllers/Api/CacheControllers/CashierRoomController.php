<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\HIS\CashierRoom;
use App\Events\Cache\DeleteCache;
use App\Http\Requests\CashierRoom\CreateCashierRoomRequest;
use App\Http\Requests\CashierRoom\UpdateCashierRoomRequest;
use App\Models\HIS\Room;
use Illuminate\Support\Facades\DB;

class CashierRoomController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->cashier_room = new CashierRoom();
        $this->room = new Room();
    }
    public function cashier_room($id = null)
    {
        if ($id == null) {
            $name = $this->cashier_room_name;
            $param = [
                'room:id,department_id,area_id',
                'room.department:id,department_name,department_code',
                'room.area'
            ];
        } else {
            $name = $this->cashier_room_name . '_' . $id;
            $param = [
                'room',
                'room.department',
                'room.area'
            ];
        }
        $data = get_cache_full($this->cashier_room, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }
    public function cashier_room_create(CreateCashierRoomRequest $request)
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
                'area_id' => $request->area_id,
                'is_pause' => $request->is_pause,
            ]);
            $data = $this->cashier_room::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'cashier_room_code' => $request->cashier_room_code,
                'cashier_room_name' => $request->cashier_room_name,
                'einvoice_room_code' => $request->einvoice_room_code,
                'einvoice_room_name' => $request->einvoice_room_name,
                'room_id' => $room->id,
            ]);
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->cashier_room_name));
            return return_data_create_success([$data, $room]);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }

    public function cashier_room_update(UpdateCashierRoomRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->cashier_room->find($id);
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
                'area_id' => $request->area_id,
                'is_pause' => $request->is_pause,
            ];
            $data_update = [
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,
                'cashier_room_name' => $request->cashier_room_name,
                'einvoice_room_code' => $request->einvoice_room_code,
                'einvoice_room_name' => $request->einvoice_room_name,
            ];
            $room->fill($room_update);
            $room->save();
            $data->fill($data_update);
            $data->save();
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->cashier_room_name));
            return return_data_update_success([$data, $room]);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }

    public function cashier_room_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->cashier_room->find($id);
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
            event(new DeleteCache($this->cashier_room_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }

}
