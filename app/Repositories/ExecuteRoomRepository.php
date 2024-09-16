<?php 
namespace App\Repositories;

use App\Models\HIS\ExecuteRoom;
use App\Models\HIS\Room;
use Illuminate\Support\Facades\DB;

class ExecuteRoomRepository
{
    protected $executeRoom;
    protected $room;

    public function __construct(ExecuteRoom $executeRoom, Room $room)
    {
        $this->executeRoom = $executeRoom;
        $this->room = $room;
    }

    public function applyJoins()
    {
        return $this->executeRoom
            ->leftJoin('his_room as room', 'room.id', '=', 'his_execute_room.room_id')
            ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')
            ->leftJoin('his_area as area', 'area.id', '=', 'room.area_id')
            ->leftJoin('his_room_group as room_group', 'room_group.id', '=', 'room.room_group_id')
            ->leftJoin('his_room_type as room_type', 'room_type.id', '=', 'room.room_type_id')
            ->leftJoin('his_speciality as speciality', 'speciality.id', '=', 'room.speciality_id')
            ->leftJoin('his_cashier_room as default_cashier_room', 'default_cashier_room.id', '=', 'room.default_cashier_room_id')
            ->leftJoin('his_patient_type as default_instr_patient_type', 'default_instr_patient_type.id', '=', 'room.default_instr_patient_type_id')
            ->leftJoin('his_service as default_service', 'default_service.id', '=', 'room.default_service_id')
            ->leftJoin('his_account_book as deposit_account_book', 'deposit_account_book.id', '=', 'room.deposit_account_book_id')
            ->leftJoin('his_account_book as bill_account_book', 'bill_account_book.id', '=', 'room.bill_account_book_id')
            ->select(
                'his_execute_room.*',
                'department.department_code',
                'department.department_name',
                'area.area_code',
                'area.area_name',
                'room_group.room_group_code',
                'room_group.room_group_name',
                'room_type.room_type_code',
                'room_type.room_type_name',
                'speciality.speciality_code',
                'speciality.speciality_name',
                'default_cashier_room.cashier_room_code as default_cashier_room_code',
                'default_cashier_room.cashier_room_name as default_cashier_room_name',
                'default_instr_patient_type.patient_type_code',
                'default_instr_patient_type.patient_type_name',
                'default_service.service_code as default_service_name',
                'default_service.service_name as default_service_code',
                'deposit_account_book.account_book_code as deposit_account_book_code',
                'deposit_account_book.account_book_name as deposit_account_book_name',
                'bill_account_book.account_book_code as bill_account_book_code',
                'bill_account_book.account_book_name as bill_account_book_name',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_execute_room.execute_room_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_execute_room.execute_room_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_execute_room.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['department_code', 'department_name'])) {
                        $query->orderBy('department.' . $key, $item);
                    }
                    if (in_array($key, ['room_type_code', 'room_type_name'])) {
                        $query->orderBy('room_type.' . $key, $item);
                    }
                    if (in_array($key, ['area_code', 'area_name'])) {
                        $query->orderBy('area.' . $key, $item);
                    }
                    if (in_array($key, ['room_group_code', 'room_group_name'])) {
                        $query->orderBy('room_group.' . $key, $item);
                    }
                    if (in_array($key, ['speciality_code', 'speciality_name'])) {
                        $query->orderBy('speciality.' . $key, $item);
                    }
                    if (in_array($key, ['default_cashier_room_code', 'default_cashier_room_name'])) {
                        $query->orderBy('default_cashier_room.' . $key, $item);
                    }
                    if (in_array($key, ['patient_type_code', 'patient_type_name'])) {
                        $query->orderBy('default_instr_patient_type.' . $key, $item);
                    }
                    if (in_array($key, ['default_service_code', 'default_service_name'])) {
                        $query->orderBy('default_service.' . $key, $item);
                    }
                    if (in_array($key, ['deposit_account_book_code', 'deposit_account_book_name'])) {
                        $query->orderBy('deposit_account_book.' . $key, $item);
                    }
                    if (in_array($key, ['bill_account_book_code', 'bill_account_book_name'])) {
                        $query->orderBy('bill_account_book.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_execute_room.' . $key, $item);
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
        return $this->executeRoom->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
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
               $data = $this->executeRoom::create([
                   'create_time' => now()->format('Ymdhis'),
                   'modify_time' => now()->format('Ymdhis'),
                   'creator' => get_loginname_with_token($request->bearerToken(), $time),
                   'modifier' => get_loginname_with_token($request->bearerToken(), $time),
                   'app_creator' => $appCreator,
                   'app_modifier' => $appModifier,
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
               return $data;
    }
    public function update($request, $data, $time, $appModifier){
              // Start transaction
              DB::connection('oracle_his')->beginTransaction();
              $room_update = [
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $time),
                'app_modifier' => $appModifier,
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
                  'modifier' => get_loginname_with_token($request->bearerToken(), $time),
                  'app_modifier' => $appModifier,
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
              $room = $this->room->find($data->room_id);
              $room->fill($room_update);
              $room->save();
              $data->fill($data_update);
              $data->save();
              DB::connection('oracle_his')->commit();
              return $data;
    }
    public function delete($data){
        DB::connection('oracle_his')->beginTransaction();
        $data->delete();
        $room = $this->room->find($data->room_id);
        $room->delete();
        DB::connection('oracle_his')->commit();
        return $data;
    }
    public function getDataFromDbToElastic($id = null){
        $data = $this->applyJoins();
        if($id != null){
            $data = $data->where('his_execute_room.id','=', $id)->first();
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