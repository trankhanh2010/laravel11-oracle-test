<?php

namespace App\Http\Controllers\Api\CacheControllers;

use Illuminate\Http\Request;
use App\Models\HIS\ExecuteRoom;
use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ExecuteRoom\CreateExecuteRoomRequest;
use App\Http\Requests\ExecuteRoom\UpdateExecuteRoomRequest;
use App\Models\HIS\Room;

class ExecuteRoomController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->execute_room = new ExecuteRoom();
        $this->room = new Room();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->execute_room->getConnection()->getSchemaBuilder()->hasColumn($this->execute_room->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function execute_room($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $param = [
                'room',
                'room.department:id,department_name,department_code',
                'room.area:id,area_name,area_code',
                'room.room_group:id,room_group_name,room_group_code',
                'room.room_type:id,room_type_name,room_type_code',
                'room.speciality:id,speciality_name,speciality_code',
                'room.default_cashier_room:id,cashier_room_name,cashier_room_code',
                'room.default_instr_patient_type',
                'room.default_service:id,service_name,service_code',
                'room.deposit_account_book',
                'room.bill_account_book'
            ];
            $data = $this->execute_room;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('lower(execute_room_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(execute_room_name)'), 'like', '%' . $keyword . '%');
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
                $name = $this->execute_room_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active;
                $param = [
                    'room',
                    'room.department:id,department_name,department_code',
                    'room.area:id,area_name,area_code',
                    'room.room_group:id,room_group_name,room_group_code',
                    'room.room_type:id,room_type_name,room_type_code',
                    'room.speciality:id,speciality_name,speciality_code',
                    'room.default_cashier_room:id,cashier_room_name,cashier_room_code',
                    'room.default_instr_patient_type',
                    'room.default_service:id,service_name,service_code',
                    'room.deposit_account_book',
                    'room.bill_account_book'
                ];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->execute_room->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $name = $this->execute_room_name . '_' . $id. '_is_active_' . $this->is_active;
                $param = [
                    'room',
                    'room.department',
                    'room.area',
                    'room.room_group',
                    'room.room_type',
                    'room.speciality',
                    'room.default_cashier_room',
                    'room.default_instr_patient_type',
                    'room.default_service',
                    'room.deposit_account_book',
                    'room.bill_account_book'
                ];
            }
            $data = get_cache_full($this->execute_room, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active);
            // foreach ($data as $key => $item) {
            //     $item->default_drug_store = get_cache_1_1_n_with_ids($this->execute_room, "room.default_drug_store", $this->execute_room_name, $item->id, $this->time);
            // }
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count'],
            'is_active' => $this->is_active,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }

    public function execute_room_create(CreateExecuteRoomRequest $request)
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
                'room_group_id' => $request->room_group_id,
                'room_type_id' => $request->room_type_id,
                'order_issue_code' => $request->order_issue_code,
                'hold_order' => $request->hold_order,
                'speciality_id' => $request->speciality_id,
                'address' => $request->address,
                'responsible_loginname' => $request->responsible_loginname,
                'responsible_username' => $request->responsible_username,
                'default_instr_patient_type_id' => $request->default_instr_patient_type_id,
                'default_drug_store_ids' => $request->default_drug_store_ids,
                'default_cashier_room_id' => $request->default_cashier_room_id,
                'area_id' => $request->area_id,
                'screen_saver_module_link' => $request->screen_saver_module_link,
                'bhyt_code' => $request->bhyt_code,
                'deposit_account_book_id' => $request->deposit_account_book_id,
                'bill_account_book_id' => $request->bill_account_book_id,
                'is_use_kiosk' => $request->is_use_kiosk,
                'is_restrict_execute_room' => $request->is_restrict_execute_room,
                'is_restrict_time' => $request->is_restrict_time,
                'is_restrict_req_service' => $request->is_restrict_req_service,
                'is_allow_no_icd' => $request->is_allow_no_icd,
                'is_pause' => $request->is_pause,
                'is_restrict_medicine_type' => $request->is_restrict_medicine_type,
                'is_restrict_patient_type' => $request->is_restrict_patient_type,
                'is_block_num_order' => $request->is_block_num_order,
                'default_service_id' => $request->default_service_id
            ]);
            $data = $this->execute_room::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'execute_room_code' => $request->execute_room_code,
                'execute_room_name' => $request->execute_room_name,
                'num_order' => $request->num_order,
                'test_type_code' => $request->test_type_code,
                'max_request_by_day' => $request->max_request_by_day,
                'max_appointment_by_day' => $request->max_appointment_by_day,
                'max_req_bhyt_by_day' => $request->max_req_bhyt_by_day,
                'max_patient_by_day' => $request->max_patient_by_day,
                'average_eta' => $request->average_eta,
                'is_emergency' => $request->is_emergency,
                'is_exam' => $request->is_exam,
                'is_speciality' => $request->is_speciality,
                'is_vaccine' => $request->is_vaccine,
                'allow_not_choose_service' => $request->allow_not_choose_service,
                'is_kidney' => $request->is_kidney,
                'kidney_shift_count' => $request->kidney_shift_count,
                'is_surgery' => $request->is_surgery,
                'is_auto_expend_add_exam' => $request->is_auto_expend_add_exam,
                'is_pause_enclitic' => $request->is_pause_enclitic,
                'is_vitamin_a' => $request->is_vitamin_a,
                'room_id' => $room->id,
            ]);
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->execute_room_name));
            return return_data_create_success([$data, $room]);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }

    public function execute_room_update(UpdateExecuteRoomRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->execute_room->find($id);
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
                'room_group_id' => $request->room_group_id,
                'room_type_id' => $request->room_type_id,
                'order_issue_code' => $request->order_issue_code,
                'hold_order' => $request->hold_order,
                'speciality_id' => $request->speciality_id,
                'address' => $request->address,
                'responsible_loginname' => $request->responsible_loginname,
                'responsible_username' => $request->responsible_username,
                'default_instr_patient_type_id' => $request->default_instr_patient_type_id,
                'default_drug_store_ids' => $request->default_drug_store_ids,
                'default_cashier_room_id' => $request->default_cashier_room_id,
                'area_id' => $request->area_id,
                'screen_saver_module_link' => $request->screen_saver_module_link,
                'bhyt_code' => $request->bhyt_code,
                'deposit_account_book_id' => $request->deposit_account_book_id,
                'bill_account_book_id' => $request->bill_account_book_id,
                'is_use_kiosk' => $request->is_use_kiosk,
                'is_restrict_execute_room' => $request->is_restrict_execute_room,
                'is_restrict_time' => $request->is_restrict_time,
                'is_restrict_req_service' => $request->is_restrict_req_service,
                'is_allow_no_icd' => $request->is_allow_no_icd,
                'is_pause' => $request->is_pause,
                'is_restrict_medicine_type' => $request->is_restrict_medicine_type,
                'is_restrict_patient_type' => $request->is_restrict_patient_type,
                'is_block_num_order' => $request->is_block_num_order,
                'default_service_id' => $request->default_service_id,
                'is_active' => $request->is_active,

            ];
            $data_update = [
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,
                'execute_room_name' => $request->execute_room_name,
                'num_order' => $request->num_order,
                'test_type_code' => $request->test_type_code,
                'max_request_by_day' => $request->max_request_by_day,
                'max_appointment_by_day' => $request->max_appointment_by_day,
                'max_req_bhyt_by_day' => $request->max_req_bhyt_by_day,
                'max_patient_by_day' => $request->max_patient_by_day,
                'average_eta' => $request->average_eta,
                'is_emergency' => $request->is_emergency,
                'is_exam' => $request->is_exam,
                'is_speciality' => $request->is_speciality,
                'is_vaccine' => $request->is_vaccine,
                'allow_not_choose_service' => $request->allow_not_choose_service,
                'is_kidney' => $request->is_kidney,
                'kidney_shift_count' => $request->kidney_shift_count,
                'is_surgery' => $request->is_surgery,
                'is_auto_expend_add_exam' => $request->is_auto_expend_add_exam,
                'is_pause_enclitic' => $request->is_pause_enclitic,
                'is_vitamin_a' => $request->is_vitamin_a,
                'is_active' => $request->is_active,

            ];
            $room->fill($room_update);
            $room->save();
            $data->fill($data_update);
            $data->save();
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->execute_room_name));
            return return_data_create_success(['data' => $data, 'room' => $room]);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }

    public function execute_room_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->execute_room->find($id);
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
            event(new DeleteCache($this->execute_room_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }
}
