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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_TREATMENT_FEE_LIST AS
SELECT
    treatment.id,
    treatment.create_time,
    treatment.modify_time,
    treatment.creator,
    treatment.modifier,
    treatment.is_active,
    treatment.is_delete,
    treatment.IS_EMERGENCY,
    treatment.IS_TUBERCULOSIS,
    treatment.treatment_code,
    treatment.tdl_patient_name as patient_name,
    treatment.tdl_patient_dob as date_of_birth,
    treatment.TDL_PATIENT_GENDER_NAME as gender,
    treatment.TDL_PATIENT_ADDRESS as address,
    treatment.TDL_HEIN_CARD_NUMBER as HEIN_CARD_NUMBER,
    treatment.TDL_HEIN_CARD_FROM_TIME as HEIN_CARD_FROM_TIME,
    treatment.TDL_HEIN_CARD_TO_TIME as HEIN_CARD_TO_TIME,
    treatment.TDL_HEIN_MEDI_ORG_NAME as HEIN_MEDI_ORG_NAME,
    treatment.IN_TIME,
    treatment.OUT_TIME,
    treatment.CLINICAL_IN_TIME,
    treatment.ICD_CODE,
    treatment.ICD_NAME,
    treatment.ICD_SUB_CODE,
    treatment.ICD_TEXT,
    treatment.TDL_PATIENT_CODE as patient_code,
    treatment.TDL_PATIENT_TAX_CODE as patient_tax_code,
    treatment.TDL_PATIENT_ACCOUNT_NUMBER as patient_account_number,
    treatment.TDL_PATIENT_PHONE as patient_phone,
    treatment.TDL_PATIENT_MOBILE as patient_mobile,
    treatment.TDL_PATIENT_RELATIVE_PHONE  as PATIENT_RELATIVE_PHONE,
    treatment.TDL_PATIENT_RELATIVE_MOBILE  as PATIENT_RELATIVE_MOBILE,
    treatment.TDL_PATIENT_MILITARY_RANK_NAME as PATIENT_MILITARY_RANK_NAME,
    treatment.TDL_PATIENT_CAREER_NAME as PATIENT_CAREER_NAME,
    treatment.TDL_PATIENT_WORK_PLACE_NAME as PATIENT_WORK_PLACE_NAME,
    treatment.FEE_LOCK_LOGINNAME,
    treatment.FEE_LOCK_TIME,
    treatment.IS_LOCK_FEE,
    treatment.HEIN_LOCK_TIME,
    treatment.IS_LOCK_HEIN,
    treatment.is_pause,
    treatment.TREATMENT_END_TYPE_ID,

    treatment_stt.treatment_stt_code,
    treatment_stt.treatment_stt_name,
    treatment.FUND_BUDGET,
    treatment.IS_APPROVE_FINISH,
    treatment.TREATMENT_DAY_COUNT,
    treatment.store_code,
    treatment.TREATMENT_ORDER,
    treatment.KSK_ORDER,

    CASE
        WHEN EXISTS (
            SELECT 1
            FROM HIS_HEIN_APPROVAL ha
            WHERE ha.treatment_id = treatment.id
        ) THEN 1
        ELSE 0
    END AS is_hein_approval,

    CASE
        WHEN EXISTS (
            SELECT 1
            FROM HIS_SERE_SERV_BILL ssb
            WHERE ssb.tdl_treatment_id = treatment.id
            AND ssb.is_active = 1
            AND ssb.is_delete = 0
            AND (ssb.is_cancel = 0 or ssb.is_cancel is null)
        ) THEN 1
        ELSE 0
    END AS co_thanh_toan,

    patient.email as patient_email,

    end_room.room_name as end_room_name,
    end_room.room_code as end_room_code,
    start_room.room_name as start_room_name,
    start_room.room_code as start_room_code,

    treatment_end_type.treatment_end_type_name,
    treatment_end_type.treatment_end_type_code,
    treatment_result.treatment_result_name,
    (SELECT right_route_code
            from his_patient_type_alter patient_type_alter
            where patient_type_alter.treatment_id = treatment.id
                 AND ROWNUM = 1
                 AND patient_type_alter.hein_card_number = treatment.tdl_hein_card_number
                 AND patient_type_alter.treatment_type_id = treatment.TDL_TREATMENT_TYPE_ID
    ) AS right_route_code,
    (SELECT RIGHT_ROUTE_TYPE_CODE
            from his_patient_type_alter patient_type_alter
            where patient_type_alter.treatment_id = treatment.id
                 AND ROWNUM = 1
                 AND patient_type_alter.hein_card_number = treatment.tdl_hein_card_number
                 AND patient_type_alter.treatment_type_id = treatment.TDL_TREATMENT_TYPE_ID
    ) AS RIGHT_ROUTE_TYPE_CODE,
    (SELECT address
            from his_patient_type_alter patient_type_alter
            where patient_type_alter.treatment_id = treatment.id
                 AND ROWNUM = 1
                 AND patient_type_alter.hein_card_number = treatment.tdl_hein_card_number
                 AND patient_type_alter.treatment_type_id = treatment.TDL_TREATMENT_TYPE_ID
    ) AS hein_address,
    --patient_type_alter.right_route_code,
    --patient_type_alter.address as hein_address,
    treatment_type.treatment_type_code,
    treatment_type.treatment_type_name,
    patient_type.patient_type_code,
    patient_type.patient_type_name,
    end_department.department_code as end_department_code,
    end_department.department_name as end_department_name
    /*
    treatment_log_type.treatment_log_type_code as last_treatment_log_type_code,
    treatment_log_type.treatment_log_type_name as last_treatment_log_type_name
    */

FROM HIS_TREATMENT treatment
LEFT JOIN v_his_room end_room ON end_room.ID  = treatment.end_room_id
LEFT JOIN v_his_room start_room ON start_room.ID  = treatment.TDL_FIRST_EXAM_ROOM_ID
LEFT JOIN his_treatment_result treatment_result ON treatment.treatment_result_id = treatment_result.id
LEFT JOIN his_treatment_end_type treatment_end_type ON treatment.treatment_end_type_id = treatment_end_type.id
LEFT JOIN his_patient_type patient_type ON patient_type.id = treatment.tdl_patient_type_id
LEFT JOIN his_patient patient ON patient.id = treatment.patient_id
LEFT JOIN his_treatment_stt treatment_stt ON treatment_stt.id = treatment.treatment_stt_id
LEFT JOIN his_treatment_type treatment_type ON treatment_type.id = treatment.tdl_treatment_type_id
LEFT JOIN his_patient_type patient_type ON patient_type.id = treatment.tdl_patient_type_id
LEFT JOIN his_department end_department ON end_department.id = treatment.end_department_id
/*
LEFT JOIN (
    SELECT treatment_logging.*
    FROM his_treatment_logging treatment_logging
    JOIN (
        SELECT treatment_id, MAX(id) AS max_id
        FROM his_treatment_logging
        GROUP BY treatment_id
    ) l2 ON treatment_logging.treatment_id = l2.treatment_id AND treatment_logging.id = l2.max_id
) last_treatment_logging ON last_treatment_logging.treatment_id = treatment.id
LEFT JOIN his_treatment_log_type treatment_log_type ON treatment_log_type.id = last_treatment_logging.treatment_log_type_id
LEFT JOIN his_patient_type_alter patient_type_alter ON patient_type_alter.hein_card_number = treatment.tdl_hein_card_number and patient_type_alter.treatment_id = treatment.id and patient_type_alter.treatment_type_id = treatment.TDL_TREATMENT_TYPE_ID
*/
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_TREATMENT_FEE_LIST");
    }
};
