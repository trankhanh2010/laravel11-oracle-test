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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_TREATMENT_LIST AS
SELECT
    treatment.id,
    treatment.CREATE_TIME,
    treatment.MODIFY_TIME,
    treatment.CREATOR,
    treatment.MODIFIER,
    treatment.APP_CREATOR,
    treatment.APP_MODIFIER,
    treatment.is_active,
    treatment.is_delete,
    treatment.GROUP_CODE,
    treatment.treatment_code,
    treatment.patient_id,
    treatment.BRANCH_ID,
    treatment.ICD_CODE,
    treatment.ICD_NAME,
    treatment.IN_TIME,
    treatment.IN_DATE,
    treatment.HOSPITALIZATION_REASON,
    treatment.TDL_HEIN_CARD_NUMBER,
    treatment.TDL_FIRST_EXAM_ROOM_ID,
    treatment.TDL_TREATMENT_TYPE_ID,
    treatment.TDL_PATIENT_TYPE_ID,
    treatment.TDL_HEIN_MEDI_ORG_CODE,
    treatment.TDL_HEIN_MEDI_ORG_NAME,
    treatment.tdl_patient_code,
    treatment.tdl_patient_name,
    treatment.TDL_PATIENT_FIRST_NAME,
    treatment.TDL_PATIENT_LAST_NAME,
    treatment.TDL_PATIENT_DOB,
    treatment.TDL_PATIENT_IS_HAS_NOT_DAY_DOB,
    treatment.TDL_PATIENT_ADDRESS,
    treatment.TDL_PATIENT_GENDER_ID,
    treatment.TDL_PATIENT_GENDER_NAME,
    treatment.TDL_PATIENT_CAREER_NAME,
    treatment.TDL_PATIENT_PROVINCE_CODE,
    treatment.TDL_PATIENT_COMMUNE_CODE,
    treatment.TDL_PATIENT_NATIONAL_NAME,
    treatment.DEPARTMENT_IDS,
    treatment.LAST_DEPARTMENT_ID,
    treatment.TDL_PATIENT_PHONE,
    treatment.TDL_HEIN_CARD_FROM_TIME,
    treatment.TDL_HEIN_CARD_TO_TIME,
    treatment.VIR_IN_MONTH,
    treatment.VIR_IN_YEAR,
    treatment.TDL_PATIENT_RELATIVE_MOBILE,
    treatment.TDL_PATIENT_NATIONAL_CODE,
    treatment.TDL_PATIENT_PROVINCE_NAME,
    treatment.TDL_PATIENT_COMMUNE_NAME,
    treatment.IS_BHYT_HOLDED,
    treatment.TDL_PATIENT_UNSIGNED_NAME,
    treatment.TDL_PATIENT_ETHNIC_NAME,
    treatment.IS_TUBERCULOSIS,
    treatment.TDL_PATIENT_MPS_NATIONAL_CODE,
    treatment.XML_CHECKIN_URL,
    treatment.XML_CHECKIN_RESULT,
    treatment.TDL_PATIENT_CAREER_CODE,
    treatment.CLINICAL_IN_TIME,
    treatment.out_time,
    treatment.IS_EMERGENCY,
    treatment.icd_sub_code,
    treatment.icd_text,
    treatment_result.treatment_result_code,
    treatment_result.treatment_result_name,
    treatment_end_type.treatment_end_type_code,
    treatment_end_type.treatment_end_type_name,
    treatment_type.treatment_type_code,
    treatment_type.treatment_type_name,
    in_treatment_type.treatment_type_code as in_treatment_type_code,
    in_treatment_type.treatment_type_name as in_treatment_type_name,
    end_department.department_code as end_department_code,
    end_department.department_name as end_department_name

    FROM his_treatment treatment
    LEFT JOIN his_treatment_result treatment_result on treatment_result.id = treatment.treatment_result_id
    LEFT JOIN his_treatment_end_type treatment_end_type on treatment_end_type.id = treatment.treatment_end_type_id
    LEFT JOIN his_treatment_type treatment_type on treatment_type.id = treatment.tdl_treatment_type_id
    LEFT JOIN his_treatment_type in_treatment_type on in_treatment_type.id = treatment.in_treatment_type_id
    LEFT JOIN his_department end_department on end_department.id = treatment.end_department_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_TREATMENT_LIST");
    }
};
