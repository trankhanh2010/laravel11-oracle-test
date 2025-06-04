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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_TREAT_EXE_ROOM_LIST AS
SELECT
    service_req.id,
    service_req.create_time,
    service_req.modify_time,
    service_req.creator,
    service_req.modifier,
    service_req.app_creator,
    service_req.app_modifier,
    service_req.is_active,
    service_req.is_delete,
    service_req.is_no_execute,
    service_req.treatment_id,
    service_req.intruction_time,
    service_req.intruction_date,
    service_req.service_req_type_id,
    service_req.service_req_stt_id,
    service_req_stt.service_req_stt_code,
    service_req_stt.service_req_stt_name,
    treatment.treatment_code,
    treatment.tdl_patient_code,
    department.id as department_id,
    department.department_code,
    department.department_name,
    treatment.OUT_TIME,
    treatment.STORE_BORDEREAU_TIME, -- thoi gian luu tru, ke don
    service_req.execute_room_id,
    execute_room.execute_room_code as room_code,
    execute_room.execute_room_name as room_name,
    in_treatment_type.treatment_type_code as in_treatment_type_code,
    in_treatment_type.treatment_type_name as in_treatment_type_name,
    treatment.in_treatment_type_id,
    treatment.CO_DEPARTMENT_IDS,
    treatment.TDL_PATIENT_CLASSIFY_ID,
    patient_classify.patient_classify_name,
    patient_classify.patient_classify_code,

    treatment.in_code,

    treatment.tdl_patient_name,
    treatment.tdl_patient_dob,
    treatment.tdl_patient_gender_name,
    treatment.TDL_PATIENT_CAREER_NAME,
    treatment.TDL_PATIENT_ETHNIC_NAME,
    treatment.TDL_PATIENT_NATIONAL_NAME,
    treatment.TDL_PATIENT_ADDRESS,
    treatment.TDL_PATIENT_WORK_PLACE_NAME,
    patient_type.patient_type_name,
    treatment.tdl_hein_card_number,
    treatment.TDL_HEIN_CARD_FROM_TIME,
    treatment.TDL_HEIN_CARD_TO_TIME,
    treatment.TDL_PATIENT_RELATIVE_ADDRESS,
    treatment.TDL_PATIENT_PHONE,

    treatment.in_time,
    treatment.IS_EMERGENCY, -- truc tiep vao cap cuu
    in_department.department_name as in_department_name,
    in_department.department_code as in_department_code,
    treatment.clinical_in_time,
    last_department.department_code as last_department_code,
    last_department.department_name as last_department_name,
    treatment.TRANSFER_IN_MEDI_ORG_CODE,
    treatment.TRANSFER_IN_MEDI_ORG_NAME,
    treatment_end_type.treatment_end_type_code,
    treatment_end_type.treatment_end_type_name,

    treatment_result.treatment_result_code,
    treatment_result.treatment_result_name,
    treatment.SURGERY,
    treatment.MAIN_CAUSE,
    treatment.IS_HAS_AUPOPSY,
    treatment.DEATH_TIME,
    room_type.room_type_code,
    room_type.room_type_name


FROM his_service_req service_req
    LEFT JOIN his_room room on room.id = service_req.execute_room_id
    LEFT JOIN his_room_type room_type on room_type.id = room.room_type_id
    LEFT JOIN his_execute_room execute_room on execute_room.room_id = service_req.execute_room_id
    LEFT JOIN his_treatment treatment on treatment.id = service_req.treatment_id
    LEFT JOIN his_department department on department.id = room.department_id
    LEFT JOIN his_department in_department on in_department.id = treatment.in_department_id
    LEFT JOIN his_department last_department on last_department.id = treatment.last_department_id
    LEFT JOIN his_patient_type patient_type on patient_type.id = treatment.tdl_patient_type_id
    LEFT JOIN his_treatment_end_type treatment_end_type on treatment_end_type.id = treatment.treatment_end_type_id
    LEFT JOIN his_treatment_result treatment_result on treatment_result.id = treatment.treatment_result_id
    LEFT JOIN his_treatment_type in_treatment_type on in_treatment_type.id = treatment.in_treatment_type_id
    LEFT JOIN his_patient_classify patient_classify on patient_classify.id = treatment.tdl_patient_classify_id
    LEFT JOIN his_service_req_stt service_req_stt on service_req_stt.id = service_req.service_req_stt_id
    LEFT JOIN his_service_req_type service_req_type on service_req_type.id = service_req.service_req_type_id
WHERE
    service_req_type.service_req_type_code = 'KH'
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_TREAT_EXE_ROOM_LIST");
    }
};
