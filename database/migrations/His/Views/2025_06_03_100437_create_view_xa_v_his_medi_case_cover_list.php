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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_MEDI_CASE_COVER_LIST AS
SELECT
    treatment.id,
    treatment.create_time,
    treatment.modify_time,
    treatment.creator,
    treatment.modifier,
    treatment.app_creator,
    treatment.app_modifier,
    treatment.is_active,
    treatment.is_delete,
    treatment.id as treatment_id,
    department.id as department_id,
    department.department_code,
    department.department_name,
    treatment_bed_room.bed_id,
    treatment_bed_room.add_time,
    treatment_bed_room.add_loginname,
    treatment_bed_room.add_username,
    treatment_bed_room.remove_time,
    treatment_bed_room.bed_room_id,
    bed_room.bed_room_code,
    bed_room.bed_room_name,
    in_treatment_type.treatment_type_code as in_treatment_type_code,
    in_treatment_type.treatment_type_name as in_treatment_type_name,
    treatment_type.treatment_type_code,
    treatment_type.treatment_type_name,
    patient_classify.patient_classify_name,
    patient_classify.patient_classify_code,


    patient_type.patient_type_name,

    in_department.department_name as in_department_name,
    in_department.department_code as in_department_code,
    in_department.bhyt_code as in_department_bhyt_code,
    last_department.department_code as last_department_code,
    last_department.department_name as last_department_name,
    last_department.bhyt_code as last_department_bhyt_code,
    end_department.department_name as end_department_name,
    end_department.department_code as end_department_code,
    end_department.bhyt_code as end_department_bhyt_code,
    exit_department.department_name as exit_department_name,
    exit_department.department_code as exit_department_code,
    exit_department.bhyt_code as exit_department_bhyt_code,
    hospitalize_department.department_code as hospitalize_department_code,
    hospitalize_department.department_name as hospitalize_department_name,
    treatment_end_type.treatment_end_type_code,
    treatment_end_type.treatment_end_type_name,

    treatment_result.treatment_result_code,
    treatment_result.treatment_result_name,
    SUBSTR(treatment_result.treatment_result_code, 2, 1) AS tinh_trang_ra_vien,

    death_cause.death_cause_code,
    death_cause.death_cause_name,
    death_within.death_within_code,
    death_within.death_within_name,

    tran_pati_form.tran_pati_form_name,
    tran_pati_form.tran_pati_form_code,
    (select count(*) from HIS_SERE_SERV_PTTT
            where HIS_SERE_SERV_PTTT.is_active = 1
            and HIS_SERE_SERV_PTTT.is_delete = 0
            and HIS_SERE_SERV_PTTT.Tdl_Treatment_Id = treatment_bed_room.treatment_id) as total_pttt,

    treatment.TREATMENT_CODE,
    treatment.TREATMENT_STT_ID,
    treatment.PATIENT_ID,
    treatment.BRANCH_ID,
    treatment.IS_PAUSE,
    treatment.IS_LOCK_HEIN,
    treatment.IS_TEMPORARY_LOCK,
    treatment.IS_LOCK_FEE,
    treatment.IS_REMOTE,
    treatment.ICD_ID__DELETE,
    treatment.ICD_CODE,
    treatment.ICD_NAME,
    treatment.ICD_SUB_CODE,
    treatment.ICD_TEXT,
    treatment.ICD_CAUSE_CODE,
    treatment.ICD_CAUSE_NAME,
    treatment.IS_HOLD_BHYT_CARD,
    treatment.IS_AUTO_DISCOUNT,
    treatment.AUTO_DISCOUNT_RATIO,
    treatment.FEE_LOCK_TIME,
    treatment.FEE_LOCK_ORDER,
    treatment.FEE_LOCK_ROOM_ID,
    treatment.FEE_LOCK_DEPARTMENT_ID,
    treatment.IN_TIME,
    treatment.IN_DATE,
    treatment.CLINICAL_IN_TIME,
    treatment.OUT_TIME,
    treatment.IS_IN_CODE_REQUEST,
    treatment.IN_CODE,
    treatment.IN_ROOM_ID,
    treatment.IN_DEPARTMENT_ID,
    treatment.IN_LOGINNAME,
    treatment.IN_USERNAME,
    treatment.IN_TREATMENT_TYPE_ID,
    treatment.IN_ICD_ID__DELETE,
    treatment.IN_ICD_CODE,
    treatment.IN_ICD_NAME,
    treatment.IN_ICD_SUB_CODE,
    treatment.IN_ICD_TEXT,
    treatment.HOSPITALIZATION_REASON,
    treatment.DOCTOR_LOGINNAME,
    treatment.DOCTOR_USERNAME,
    treatment.END_LOGINNAME,
    treatment.END_USERNAME,
    treatment.END_ROOM_ID,
    treatment.END_DEPARTMENT_ID,
    treatment.END_CODE,
    treatment.EXTRA_END_CODE,
    treatment.IS_END_CODE_REQUEST,
    treatment.TREATMENT_DAY_COUNT,
    treatment.TREATMENT_RESULT_ID,
    treatment.TREATMENT_END_TYPE_ID,
    treatment.TREATMENT_END_TYPE_EXT_ID,
    treatment.ADVISE,
    treatment.APPOINTMENT_TIME,
    treatment.APPOINTMENT_DESC,
    treatment.APPOINTMENT_CODE,
    treatment.OUT_DATE,
    treatment.OUT_CODE,
    treatment.IS_OUT_CODE_REQUEST,
    treatment.IS_CHRONIC,
    treatment.IS_YDT_UPLOAD,
    treatment.OWE_TYPE_ID,
    treatment.OWE_MODIFY_TIME,
    treatment.STORE_TIME,
    treatment.DATA_STORE_ID,
    treatment.STORE_CODE,
    treatment.IS_NOT_CHECK_LHMP,
    treatment.IS_NOT_CHECK_LHSP,
    treatment.TDL_HEIN_CARD_NUMBER,
    treatment.JSON_PRINT_ID,
    treatment.JSON_FORM_ID,
    treatment.IS_EMERGENCY,
    treatment.EMERGENCY_WTIME_ID,
    treatment.KSK_ORDER,
    treatment.TDL_KSK_CONTRACT_ID,
    treatment.HRM_KSK_CODE,
    treatment.TREATMENT_DIRECTION,
    treatment.TREATMENT_METHOD,
    treatment.PATIENT_CONDITION,
    treatment.MEDI_ORG_CODE,
    treatment.MEDI_ORG_NAME,
    treatment.TRAN_PATI_FORM_ID,
    treatment.TRAN_PATI_REASON_ID,
    treatment.TRAN_PATI_TECH_ID,
    treatment.USED_MEDICINE,
    treatment.TRANSPORT_VEHICLE,
    treatment.TRANSPORTER,
    treatment.IS_TRANSFER_IN,
    treatment.TRANSFER_IN_MEDI_ORG_CODE,
    treatment.TRANSFER_IN_MEDI_ORG_NAME,
    treatment.TRANSFER_IN_CODE,
    treatment.TRANSFER_IN_ICD_ID__DELETE,
    treatment.TRANSFER_IN_ICD_CODE,
    treatment.TRANSFER_IN_ICD_NAME,
    treatment.TRANSFER_IN_CMKT,
    treatment.TRANSFER_IN_FORM_ID,
    treatment.TRANSFER_IN_REASON_ID,
    treatment.SICK_LEAVE_DAY,
    treatment.SICK_LEAVE_FROM,
    treatment.SICK_LEAVE_TO,
    treatment.DEATH_TIME,
    treatment.DEATH_CAUSE_ID,
    treatment.DEATH_WITHIN_ID,
    treatment.DEATH_PLACE,
    treatment.DEATH_DOCUMENT_TYPE,
    treatment.DEATH_DOCUMENT_NUMBER,
    treatment.DEATH_DOCUMENT_PLACE,
    treatment.DEATH_DOCUMENT_DATE,
    treatment.MAIN_CAUSE,
    treatment.SURGERY,
    treatment.IS_HAS_AUPOPSY,
    treatment.TDL_FIRST_EXAM_ROOM_ID,
    treatment.TDL_TREATMENT_TYPE_ID,
    treatment.TDL_PATIENT_TYPE_ID,
    treatment.TDL_HEIN_MEDI_ORG_CODE,
    treatment.TDL_HEIN_MEDI_ORG_NAME,
    treatment.XML4210_URL,
    treatment.FUND_ID,
    treatment.FUND_NUMBER,
    treatment.FUND_FROM_TIME,
    treatment.FUND_TO_TIME,
    treatment.FUND_ISSUE_TIME,
    treatment.FUND_TYPE_NAME,
    treatment.FUND_COMPANY_NAME,
    treatment.FUND_BUDGET,
    treatment.FUND_PAY_TIME,
    treatment.FUND_SEND_FILE_TIME,
    treatment.FUND_CUSTOMER_NAME,
    treatment.IS_INTEGRATE_HIS_SENT,
    treatment.TDL_PATIENT_CODE,
    treatment.TDL_PATIENT_NAME,
    treatment.TDL_PATIENT_FIRST_NAME,
    treatment.TDL_PATIENT_LAST_NAME,
    treatment.TDL_PATIENT_DOB,
    treatment.TDL_PATIENT_IS_HAS_NOT_DAY_DOB,
    treatment.TDL_PATIENT_AVATAR_URL,
    treatment.TDL_PATIENT_ADDRESS,
    treatment.TDL_PATIENT_GENDER_ID,
    treatment.TDL_PATIENT_GENDER_NAME,
    treatment.TDL_PATIENT_CAREER_NAME,
    treatment.TDL_PATIENT_WORK_PLACE,
    treatment.TDL_PATIENT_WORK_PLACE_NAME,
    treatment.TDL_PATIENT_DISTRICT_CODE,
    treatment.TDL_PATIENT_PROVINCE_CODE,
    treatment.TDL_PATIENT_COMMUNE_CODE,
    treatment.TDL_PATIENT_MILITARY_RANK_NAME,
    treatment.TDL_PATIENT_NATIONAL_NAME,
    treatment.TDL_PATIENT_RELATIVE_TYPE,
    treatment.TDL_PATIENT_RELATIVE_NAME,
    treatment.TDL_PATIENT_ACCOUNT_NUMBER,
    treatment.TDL_PATIENT_TAX_CODE,
    treatment.TRANSFER_IN_TIME_FROM,
    treatment.TRANSFER_IN_TIME_TO,
    treatment.SURGERY_APPOINTMENT_TIME,
    treatment.APPOINTMENT_SURGERY,
    treatment.MEDI_RECORD_TYPE_ID,
    treatment.APPOINTMENT_EXAM_ROOM_IDS,
    treatment.DEPARTMENT_IDS,
    treatment.CO_DEPARTMENT_IDS,
    treatment.LAST_DEPARTMENT_ID,
    treatment.PROVISIONAL_DIAGNOSIS,
    treatment.TREATMENT_ORDER,
    treatment.TDL_PATIENT_MOBILE,
    treatment.TDL_PATIENT_PHONE,
    treatment.SICK_HEIN_CARD_NUMBER,
    treatment.NEED_SICK_LEAVE_CERT,
    treatment.MEDI_RECORD_ID,
    treatment.PROGRAM_ID,
    treatment.IS_SYNC_EMR,
    treatment.XML4210_RESULT,
    treatment.XML4210_DESC,
    treatment.COLLINEAR_XML4210_URL,
    treatment.COLLINEAR_XML4210_RESULT,
    treatment.COLLINEAR_XML4210_DESC,
    treatment.REJECT_STORE_REASON,
    treatment.IS_APPROVE_FINISH,
    treatment.APPROVE_FINISH_NOTE,
    treatment.TRADITIONAL_ICD_CODE,
    treatment.TRADITIONAL_ICD_NAME,
    treatment.TRADITIONAL_IN_ICD_CODE,
    treatment.TRADITIONAL_IN_ICD_NAME,
    treatment.TRADITIONAL_ICD_SUB_CODE,
    treatment.TRADITIONAL_ICD_TEXT,
    treatment.TRADITIONAL_IN_ICD_SUB_CODE,
    treatment.TRADITIONAL_IN_ICD_TEXT,
    treatment.TRADITIONAL_TRANS_IN_ICD_CODE,
    treatment.TRADITIONAL_TRANS_IN_ICD_NAME,
    treatment.OTHER_PAY_SOURCE_ID,
    treatment.IS_KSK_APPROVE, treatment.TDL_HEIN_CARD_FROM_TIME, treatment.TDL_HEIN_CARD_TO_TIME, treatment.APPOINTMENT_DATE, treatment.EYE_TENSION_LEFT, treatment.EYE_TENSION_RIGHT, treatment.EYESIGHT_LEFT, treatment.EYESIGHT_RIGHT, treatment.EYESIGHT_GLASS_LEFT, treatment.EYESIGHT_GLASS_RIGHT, treatment.APPOINTMENT_PERIOD_ID, treatment.SICK_LOGINNAME, treatment.SICK_USERNAME, treatment.IS_EXPORTED_XML2076, treatment.DOCUMENT_BOOK_ID, treatment.SICK_NUM_ORDER, treatment.TDL_DOCUMENT_BOOK_CODE, treatment.APPROVAL_STORE_STT_ID, treatment.TDL_PATIENT_CLASSIFY_ID, treatment.VIR_IN_MONTH, treatment.VIR_OUT_MONTH, treatment.IN_CODE_SEED_CODE, treatment.EXTRA_END_CODE_SEED_CODE, treatment.XML2076_URL, treatment.XML2076_DESC, treatment.VIR_IN_YEAR, treatment.VIR_OUT_YEAR, treatment.FEE_LOCK_LOGINNAME, treatment.FEE_LOCK_USERNAME, treatment.EMR_COVER_TYPE_ID, treatment.CONTRAINDICATION_IDS, treatment.IS_CREATING_TRANSACTION, treatment.CO_TREAT_DEPARTMENT_IDS, treatment.RECORD_INSPECTION_STT_ID, treatment.RECORD_INSPECTION_REJECT_NOTE, treatment.TDL_SOCIAL_INSURANCE_NUMBER, treatment.APPOINTMENT_EXAM_SERVICE_ID, treatment.TRANSFER_IN_URL, treatment.HOSPITALIZE_DEPARTMENT_ID, treatment.DEATH_CERT_BOOK_ID, treatment.DEATH_CERT_NUM, treatment.TDL_PATIENT_CMND_NUMBER, treatment.TDL_PATIENT_CMND_DATE, treatment.TDL_PATIENT_CMND_PLACE, treatment.TDL_PATIENT_CCCD_NUMBER, treatment.TDL_PATIENT_CCCD_DATE, treatment.TDL_PATIENT_CCCD_PLACE, treatment.NUM_ORDER_ISSUE_ID, treatment.NEXT_EXAM_NUM_ORDER, treatment.NEXT_EXAM_FROM_TIME, treatment.NEXT_EXAM_TO_TIME, treatment.TDL_PATIENT_RELATIVE_ADDRESS, treatment.TDL_RELATIVE_CMND_NUMBER, treatment.TDL_PATIENT_RELATIVE_MOBILE, treatment.TDL_PATIENT_RELATIVE_PHONE, treatment.TDL_PATIENT_MOTHER_NAME, treatment.TDL_PATIENT_FATHER_NAME, treatment.PERMISION_UPDATE, treatment.TDL_PATIENT_NATIONAL_CODE, treatment.TDL_PATIENT_PROVINCE_NAME, treatment.TDL_PATIENT_DISTRICT_NAME, treatment.TDL_PATIENT_COMMUNE_NAME, treatment.TDL_PATIENT_POSITION_ID, treatment.IS_BHYT_HOLDED, treatment.TDL_PATIENT_PASSPORT_NUMBER, treatment.TDL_PATIENT_PASSPORT_DATE, treatment.TDL_PATIENT_PASSPORT_PLACE, treatment.TDL_PATIENT_WORK_PLACE_ID, treatment.SHOW_ICD_CODE, treatment.SHOW_ICD_NAME, treatment.SHOW_ICD_SUB_CODE, treatment.SHOW_ICD_TEXT, treatment.TRAN_PATI_BOOK_TIME, treatment.TRAN_PATI_BOOK_NUMBER, treatment.TRAN_PATI_DOCTOR_LOGINNAME, treatment.TRAN_PATI_DOCTOR_USERNAME, treatment.TRAN_PATI_DEPARTMENT_LOGINNAME, treatment.TRAN_PATI_DEPARTMENT_USERNAME, treatment.TRAN_PATI_HOSPITAL_LOGINNAME, treatment.TRAN_PATI_HOSPITAL_USERNAME, treatment.VIR_TRAN_PATI_BOOK_YEAR, treatment.TDL_PATIENT_UNSIGNED_NAME, treatment.OUTPATIENT_DATE_FROM, treatment.OUTPATIENT_DATE_TO, treatment.VACCINE_ID, treatment.VACINATION_ORDER, treatment.EPIDEMILOGY_CONTACT_TYPE, treatment.EPIDEMILOGY_SYMPTOM, treatment.TDL_PATIENT_ETHNIC_NAME, treatment.IS_TUBERCULOSIS, treatment.DOCUMENT_VIEW_COUNT, treatment.EXIT_DEPARTMENT_ID, treatment.COVID_PATIENT_CODE, treatment.STORE_BORDEREAU_CODE, treatment.HEIN_LOCK_TIME, treatment.HAS_AUTO_CREATE_RATION, treatment.IS_OLD_TEMP_BED, treatment.TDL_KSK_CONTRACT_IS_RESTRICTED, treatment.NUMBER_OF_FULL_TERM_BIRTH, treatment.NUMBER_OF_PREMATURE_BIRTH, treatment.NUMBER_OF_MISCARRIAGE, treatment.NUMBER_OF_TESTS, treatment.TEST_HIV, treatment.TEST_SYPHILIS, treatment.IS_TEST_BLOOD_SUGAR, treatment.IS_EARLY_NEWBORN_CARE, treatment.NEWBORN_CARE_AT_HOME, treatment.TEST_HEPATITIS_B, treatment.RECEPTION_FORM, treatment.END_DEPARTMENT_HEAD_LOGINNAME, treatment.END_DEPARTMENT_HEAD_USERNAME, treatment.HOSPITAL_DIRECTOR_LOGINNAME, treatment.HOSPITAL_DIRECTOR_USERNAME, treatment.END_DEPT_SUBS_HEAD_LOGINNAME, treatment.END_DEPT_SUBS_HEAD_USERNAME, treatment.HOSP_SUBS_DIRECTOR_LOGINNAME, treatment.HOSP_SUBS_DIRECTOR_USERNAME, treatment.TUBERCULOSIS_ISSUED_ORG_CODE, treatment.TUBERCULOSIS_ISSUED_ORG_NAME, treatment.TUBERCULOSIS_ISSUED_DATE, treatment.HAS_CARD, treatment.APPROVAL_LOGINNAME, treatment.APPROVAL_USERNAME, treatment.APPROVAL_TIME, treatment.UNAPPROVAL_LOGINNAME, treatment.UNAPPROVAL_USERNAME, treatment.UNAPPROVAL_TIME, treatment.TRANSFER_IN_REVIEWS, treatment.HOSPITALIZE_REASON_CODE, treatment.HOSPITALIZE_REASON_NAME, treatment.END_TYPE_EXT_NOTE, treatment.TDL_PATIENT_MPS_NATIONAL_CODE, treatment.IS_PREGNANCY_TERMINATION, treatment.PREGNANCY_TERMINATION_REASON, treatment.GESTATIONAL_AGE, treatment.DEATH_CERT_BOOK_FIRST_ID, treatment.DEATH_CERT_NUM_FIRST, treatment.DEATH_CERT_ISSUER_LOGINNAME, treatment.DEATH_CERT_ISSUER_USERNAME, treatment.DEATH_SYNC_FAILD_REASON, treatment.DEATH_SYNC_RESULT_TYPE, treatment.DEATH_SYNC_TIME, treatment.PREGNANCY_TERMINATION_TIME, treatment.IS_HIV, treatment.XML_CHECKIN_URL, treatment.XML_CHECKIN_DESC, treatment.DEATH_DOCUMENT_TYPE_CODE, treatment.DEATH_STATUS, treatment.NUMBER_OF_BIRTH, treatment.DEATH_ISSUED_DATE, treatment.XML130_DESC, treatment.XML130_RESULT, treatment.VIR_STORE_BORDEREAU_CODE, treatment.XML130_CHECK_CODE, treatment.XML_CHECKIN_RESULT,
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
    (SELECT JOIN_5_YEAR_TIME
            from his_patient_type_alter patient_type_alter
            where patient_type_alter.treatment_id = treatment.id
                 AND ROWNUM = 1
                 AND patient_type_alter.hein_card_number = treatment.tdl_hein_card_number
                 AND patient_type_alter.treatment_type_id = treatment.TDL_TREATMENT_TYPE_ID
    ) AS JOIN_5_YEAR_TIME,
    (SELECT FREE_CO_PAID_TIME
            from his_patient_type_alter patient_type_alter
            where patient_type_alter.treatment_id = treatment.id
                 AND ROWNUM = 1
                 AND patient_type_alter.hein_card_number = treatment.tdl_hein_card_number
                 AND patient_type_alter.treatment_type_id = treatment.TDL_TREATMENT_TYPE_ID
    ) AS FREE_CO_PAID_TIME,
    (SELECT LIVE_AREA_CODE
            from his_patient_type_alter patient_type_alter
            where patient_type_alter.treatment_id = treatment.id
                 AND ROWNUM = 1
                 AND patient_type_alter.hein_card_number = treatment.tdl_hein_card_number
                 AND patient_type_alter.treatment_type_id = treatment.TDL_TREATMENT_TYPE_ID
    ) AS LIVE_AREA_CODE


FROM his_treatment treatment
    LEFT JOIN his_treatment_bed_room treatment_bed_room on treatment.id = treatment_bed_room.treatment_id
    LEFT JOIN his_bed_room bed_room on bed_room.id = treatment_bed_room.bed_room_id
    LEFT JOIN his_room room on room.id = bed_room.room_id
    LEFT JOIN his_department department on department.id = room.department_id
    LEFT JOIN his_department in_department on in_department.id = treatment.in_department_id
    LEFT JOIN his_department last_department on last_department.id = treatment.last_department_id
    LEFT JOIN his_department end_department on end_department.id = treatment.end_department_id
    LEFT JOIN his_department exit_department on exit_department.id = treatment.exit_department_id
    LEFT JOIN his_department hospitalize_department  on hospitalize_department.id = treatment.hospitalize_department_id
    LEFT JOIN his_patient_type patient_type on patient_type.id = treatment.tdl_patient_type_id
    LEFT JOIN his_treatment_end_type treatment_end_type on treatment_end_type.id = treatment.treatment_end_type_id
    LEFT JOIN his_treatment_result treatment_result on treatment_result.id = treatment.treatment_result_id
    LEFT JOIN his_death_cause death_cause on death_cause.id = treatment.death_cause_id
    LEFT JOIN his_death_within death_within on death_within.id = treatment.death_within_id
    LEFT JOIN his_treatment_type in_treatment_type on in_treatment_type.id = treatment.in_treatment_type_id
    LEFT JOIN his_treatment_type treatment_type on treatment_type.id = treatment.tdl_treatment_type_id
    LEFT JOIN his_patient_classify patient_classify on patient_classify.id = treatment.tdl_patient_classify_id
    LEFT JOIN his_tran_pati_form tran_pati_form on tran_pati_form.id = treatment.tran_pati_form_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_MEDI_CASE_COVER_LIST");
    }
};
