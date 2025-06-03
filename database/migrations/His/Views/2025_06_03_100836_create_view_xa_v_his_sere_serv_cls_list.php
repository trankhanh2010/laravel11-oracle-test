<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::connection('oracle_his')->statement(
            <<<SQL
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_SERE_SERV_CLS_LIST AS
SELECT
     sere_serv.id,
     sere_serv.is_active,
     sere_serv.is_delete,
     sere_serv.is_no_execute,
     sere_serv.amount,
     sere_serv.execute_time,
     service.service_code,
     service.service_name,
     service_unit.service_unit_code,
     service_unit.service_unit_name,
     service_req.service_req_code,
     service_req.intruction_time,
     service_req.intruction_date,
     service_req.VIR_INTRUCTION_MONTH,
     service_req.REQUEST_USERNAME,
     service_req.EXECUTE_LOGINNAME,
     service_req.EXECUTE_USERNAME,
     service_req.is_no_execute as service_req_is_no_execute,
     patient_type.patient_type_code,
     patient_type.patient_type_name,
     primary_patient_type.patient_type_code as primary_patient_type_code,
     primary_patient_type.patient_type_name as primary_patient_type_name,
     request_department.department_code as request_department_code,
     request_department.department_name as request_department_name,
     request_room.room_code as request_room_code,
     request_room.room_name as request_room_name,
     service_type.service_type_code,
     service_type.service_type_name,
     service_req_stt.service_req_stt_code,
     service_req_stt.service_req_stt_name,
     patient.patient_code,
     report_type_cat.report_type_code,
     report_type_cat.category_code,
     report_type_cat.category_name,
     report_type_cat.num_order,
     treatment_type.treatment_type_code,
     treatment_type.treatment_type_name,
     service_req_type.service_req_type_code,
     service_req_type.service_req_type_name,
     speciality.speciality_code,
     speciality.speciality_name,
     execute_room.room_code as execute_room_code,
     execute_room.room_name as execute_room_name,
     test_type.test_type_code,
     test_type.test_type_name

    FROM his_sere_serv sere_serv
    LEFT JOIN his_service service on service.id = sere_serv.service_id
    LEFT JOIN his_service_unit service_unit on service_unit.id = service.service_unit_id
    LEFT JOIN his_service_req service_req on service_req.id = sere_serv.service_req_id
    LEFT JOIN his_patient_type patient_type on patient_type.id = sere_serv.patient_type_id
    LEFT JOIN his_patient_type primary_patient_type on primary_patient_type.id = sere_serv.primary_patient_type_id
    LEFT JOIN his_department request_department on request_department.id = service_req.request_department_id
    LEFT JOIN v_his_room request_room on request_room.id = service_req.request_room_id
    LEFT JOIN his_service_type service_type on service_type.id = service.service_type_id
    LEFT JOIN his_service_req_stt service_req_stt on service_req_stt.id = service_req.service_req_stt_id
    LEFT JOIN his_patient patient on patient.id = sere_serv.tdl_patient_id
    LEFT JOIN HIS_SERVICE_RETY_CAT service_rety_cat on service_rety_cat.service_id = sere_serv.service_id
    LEFT JOIN HIS_REPORT_TYPE_CAT report_type_cat on report_type_cat.id = service_rety_cat.report_type_cat_id
    LEFT JOIN his_treatment_type treatment_type on treatment_type.id = service_req.treatment_type_id
    LEFT JOIN his_service_req_type service_req_type on service_req_type.id = service_req.service_req_type_id
    LEFT JOIN v_his_room execute_room on execute_room.id = service_req.execute_room_id
    LEFT JOIN his_speciality speciality on speciality.id = execute_room.speciality_id
    LEFT JOIN his_test_type test_type on test_type.id = service.test_type_id

    WHERE service_type.service_type_code != 'VT'
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('oracle_his')->statement("DROP VIEW XA_V_HIS_SERE_SERV_CLS_LIST");
    }
};
