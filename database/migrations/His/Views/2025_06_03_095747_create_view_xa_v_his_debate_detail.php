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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_DEBATE_DETAIL AS
SELECT
     debate."ID",debate."CREATE_TIME",debate."MODIFY_TIME",debate."CREATOR",debate."MODIFIER",debate."APP_CREATOR",debate."APP_MODIFIER",debate."IS_ACTIVE",debate."IS_DELETE",debate."GROUP_CODE",debate."TREATMENT_ID",debate."ICD_ID__DELETE",debate."ICD_CODE",debate."ICD_NAME",debate."ICD_SUB_CODE",debate."ICD_TEXT",debate."DEPARTMENT_ID",debate."DEBATE_TIME",debate."REQUEST_LOGINNAME",debate."REQUEST_USERNAME",debate."TREATMENT_TRACKING",debate."TREATMENT_FROM_TIME",debate."TREATMENT_TO_TIME",debate."TREATMENT_METHOD",debate."LOCATION",debate."REQUEST_CONTENT",debate."PATHOLOGICAL_HISTORY",debate."HOSPITALIZATION_STATE",debate."BEFORE_DIAGNOSTIC",debate."DIAGNOSTIC",debate."CARE_METHOD",debate."CONCLUSION",debate."DISCUSSION",debate."MEDICINE_TUTORIAL",debate."MEDICINE_USE_FORM_NAME",debate."MEDICINE_TYPE_NAME",debate."MEDICINE_CONCENTRA",debate."MEDICINE_USE_TIME",debate."DEBATE_TYPE_ID",debate."CONTENT_TYPE",debate."SUBCLINICAL_PROCESSES",debate."INTERNAL_MEDICINE_STATE",debate."SURGERY_SERVICE_ID",debate."EMOTIONLESS_METHOD_ID",debate."SURGERY_TIME",debate."PROGNOSIS",debate."PTTT_METHOD_ID",debate."PTTT_METHOD_NAME",debate."MEDICINE_TYPE_IDS",debate."ACTIVE_INGREDIENT_IDS",debate."TRACKING_ID",debate."SERVICE_ID",debate."TMP_ID",debate."DEBATE_REASON_ID",
     patient.patient_code,
     treatment.TDL_PATIENT_NAME,
     treatment.treatment_code,
     department.department_code,
     department.department_name,
     debate_type.debate_type_code,
     debate_type.debate_type_name,
     debate_reason.debate_reason_code,
     debate_reason.debate_reason_name,
     pttt_method.pttt_method_code,
     emotionless_method.emotionless_method_code,
     emotionless_method.emotionless_method_name

    FROM his_debate debate
    LEFT JOIN his_treatment treatment on treatment.id = debate.treatment_id
    LEFT JOIN his_patient patient on patient.id = treatment.patient_id
    LEFT JOIN his_department department on department.id = debate.department_id
    LEFT JOIN his_debate_type debate_type on debate_type.id = debate.debate_type_id
    LEFT JOIN his_debate_reason debate_reason on debate_reason.id = debate.debate_reason_id
    LEFT JOIN his_pttt_method pttt_method on pttt_method.id = debate.pttt_method_id
    LEFT JOIN his_emotionless_method emotionless_method on emotionless_method.id = debate.emotionless_method_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('oracle_his')->statement("DROP VIEW XA_V_HIS_DEBATE_DETAIL");
    }
};
