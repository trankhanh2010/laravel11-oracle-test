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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_TREAT_BED_ROOM_LIST AS
SELECT
    treatment_bed_room.id,
    treatment_bed_room.create_time,
    treatment_bed_room.modify_time,
    treatment_bed_room.creator,
    treatment_bed_room.modifier,
    treatment_bed_room.app_creator,
    treatment_bed_room.app_modifier,
    treatment_bed_room.is_active,
    treatment_bed_room.is_delete,
    treatment_bed_room.treatment_id,
    treatment.treatment_code,
    treatment.tdl_patient_code,
    department.id as department_id,
    department.department_code,
    department.department_name,
    treatment.OUT_TIME,
    treatment_bed_room.bed_id,
    treatment_bed_room.add_time,
    treatment_bed_room.add_loginname,
    treatment_bed_room.add_username,
    treatment_bed_room.remove_time,
    treatment_bed_room.co_treatment_id,
    treatment.co_treat_department_ids,
    treatment.STORE_BORDEREAU_TIME, -- thoi gian luu tru, ke don
    --treatment_bed_room.bed_room_id,
    room.id as bed_room_id,
    bed_room.bed_room_code as room_code,
    bed_room.bed_room_name as room_name,
    in_treatment_type.treatment_type_code as in_treatment_type_code,
    in_treatment_type.treatment_type_name as in_treatment_type_name,
    treatment.in_treatment_type_id,
    treatment.CO_DEPARTMENT_IDS,
    treatment.TDL_PATIENT_CLASSIFY_ID,
    patient_classify.patient_classify_name,
    patient_classify.patient_classify_code,

    bed.bed_code,
    bed.bed_name,
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


FROM his_treatment_bed_room treatment_bed_room
    LEFT JOIN his_bed_room bed_room on bed_room.id = treatment_bed_room.bed_room_id
    LEFT JOIN his_treatment treatment on treatment.id = treatment_bed_room.treatment_id
    LEFT JOIN his_room room on room.id = bed_room.room_id
    LEFT JOIN his_room_type room_type on room_type.id = room.room_type_id
    LEFT JOIN his_department department on department.id = room.department_id
    LEFT JOIN his_department in_department on in_department.id = treatment.in_department_id
    LEFT JOIN his_department last_department on last_department.id = treatment.last_department_id
    LEFT JOIN his_patient_type patient_type on patient_type.id = treatment.tdl_patient_type_id
    LEFT JOIN his_treatment_end_type treatment_end_type on treatment_end_type.id = treatment.treatment_end_type_id
    LEFT JOIN his_treatment_result treatment_result on treatment_result.id = treatment.treatment_result_id
    LEFT JOIN his_treatment_type in_treatment_type on in_treatment_type.id = treatment.in_treatment_type_id
    LEFT JOIN his_patient_classify patient_classify on patient_classify.id = treatment.tdl_patient_classify_id
    LEFT JOIN his_bed bed on bed.id = treatment_bed_room.bed_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('oracle_his')->statement("DROP VIEW XA_V_HIS_TREAT_BED_ROOM_LIST");
    }
};
