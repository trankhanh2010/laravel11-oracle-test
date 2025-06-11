<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'oracle_his';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            <<<SQL
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_SERE_SERV_LIST AS
SELECT
     sere_serv.id,
     sere_serv.create_time,
     sere_serv.modify_time,
     sere_serv.creator,
     sere_serv.modifier,
     sere_serv.app_creator,
     sere_serv.app_modifier,
     sere_serv.is_no_execute,
     sere_serv.is_active,
     sere_serv.is_delete,
     sere_serv.amount,
     sere_serv.vir_total_price,
     sere_serv.service_req_id,
     sere_serv.exp_mest_medicine_id,
     sere_serv.service_id,
     service_req.execute_room_id,
     service_req.request_room_id,
     service.service_code,
     service.service_name,
     service_unit.service_unit_code,
     service_unit.service_unit_name,
     service_req.service_req_code,
     service_req.treatment_id,
     service_req.tracking_id,
     service_req.intruction_time,
     service_req.intruction_date,
     service_req.VIR_INTRUCTION_MONTH,
     service_req.is_no_execute as service_req_is_no_execute,
     patient_type.patient_type_code,
     patient_type.patient_type_name,
     request_department.department_code as request_department_code,
     request_department.department_name as request_department_name,
     execute_department.department_code as execute_department_code,
     execute_department.department_name as execute_department_name,
     department.department_code,
     department.department_name,
     exp_mest_medicine.tutorial,
     employee.tdl_username as tracking_creator,
     service_type.service_type_code,
     service_type.service_type_name,
     service_req_stt.service_req_stt_code,
     service_req_stt.service_req_stt_name,
     patient.patient_code,
     service_req.block,
     service_req.machine_id,
     service_req.TDL_INSTRUCTION_NOTE, /*ghi chu cua nguoi chi dinh */
     execute_room.room_code as execute_room_code,
     execute_room.room_name as execute_room_name,
     service_req.EXAM_END_TYPE  

    FROM his_sere_serv sere_serv
    LEFT JOIN his_service service on service.id = sere_serv.service_id
    LEFT JOIN his_service_unit service_unit on service_unit.id = service.service_unit_id
    LEFT JOIN his_service_req service_req on service_req.id = sere_serv.service_req_id
    LEFT JOIN his_patient_type patient_type on patient_type.id = sere_serv.patient_type_id
    LEFT JOIN his_department request_department on request_department.id = service_req.request_department_id
    LEFT JOIN his_department execute_department on execute_department.id = service_req.execute_department_id
    LEFT JOIN his_tracking tracking on tracking.id = service_req.tracking_id
    LEFT JOIN his_department department on department.id = tracking.department_id
    LEFT JOIN his_exp_mest_medicine exp_mest_medicine on exp_mest_medicine.id = sere_serv.exp_mest_medicine_id
    LEFT JOIN his_employee employee on employee.loginname = tracking.creator
    LEFT JOIN his_service_type service_type on service_type.id = service.service_type_id
    LEFT JOIN his_service_req_stt service_req_stt on service_req_stt.id = service_req.service_req_stt_id
    LEFT JOIN his_patient patient on patient.id = sere_serv.tdl_patient_id
    LEFT JOIN v_his_room execute_room on execute_room.id = service_req.execute_room_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_SERE_SERV_LIST");
    }
};
