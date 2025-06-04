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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_MEDICINE_LIST AS
SELECT
    sere_serv."ID",sere_serv."CREATE_TIME",sere_serv."MODIFY_TIME",sere_serv."CREATOR",sere_serv."MODIFIER",sere_serv."APP_CREATOR",sere_serv."APP_MODIFIER",sere_serv."IS_ACTIVE",sere_serv."IS_DELETE",sere_serv."GROUP_CODE",sere_serv."SERVICE_ID",sere_serv."SERVICE_REQ_ID",sere_serv."PATIENT_TYPE_ID",sere_serv."PRIMARY_PATIENT_TYPE_ID",sere_serv."PRIMARY_PRICE",sere_serv."LIMIT_PRICE",sere_serv."PARENT_ID",sere_serv."HEIN_APPROVAL_ID",sere_serv."JSON_PATIENT_TYPE_ALTER",sere_serv."AMOUNT",sere_serv."PRICE",sere_serv."ORIGINAL_PRICE",sere_serv."HEIN_PRICE",sere_serv."HEIN_RATIO",sere_serv."HEIN_LIMIT_PRICE",sere_serv."HEIN_LIMIT_RATIO",sere_serv."HEIN_NORMAL_PRICE",sere_serv."ADD_PRICE",sere_serv."OVERTIME_PRICE",sere_serv."DISCOUNT",sere_serv."VAT_RATIO",sere_serv."SHARE_COUNT",sere_serv."STENT_ORDER",sere_serv."IS_EXPEND",sere_serv."IS_NO_PAY",sere_serv."IS_NO_EXECUTE",sere_serv."IS_OUT_PARENT_FEE",sere_serv."IS_NO_HEIN_DIFFERENCE",sere_serv."IS_SPECIMEN",sere_serv."IS_ADDITION",sere_serv."IS_SENT_EXT",sere_serv."EXECUTE_TIME",sere_serv."HEIN_CARD_NUMBER",sere_serv."MEDICINE_ID",sere_serv."MATERIAL_ID",sere_serv."EXP_MEST_MEDICINE_ID",sere_serv."EXP_MEST_MATERIAL_ID",sere_serv."BLOOD_ID",sere_serv."EKIP_ID",sere_serv."PACKAGE_ID",sere_serv."EQUIPMENT_SET_ID",sere_serv."EQUIPMENT_SET_ORDER",sere_serv."TDL_INTRUCTION_TIME",sere_serv."TDL_INTRUCTION_DATE",sere_serv."TDL_PATIENT_ID",sere_serv."TDL_TREATMENT_ID",sere_serv."TDL_TREATMENT_CODE",sere_serv."TDL_SERVICE_CODE",sere_serv."TDL_SERVICE_NAME",sere_serv."TDL_HEIN_SERVICE_BHYT_CODE",sere_serv."TDL_HEIN_SERVICE_BHYT_NAME",sere_serv."TDL_HEIN_ORDER",sere_serv."TDL_SERVICE_TYPE_ID",sere_serv."TDL_SERVICE_UNIT_ID",sere_serv."TDL_HEIN_SERVICE_TYPE_ID",sere_serv."TDL_ACTIVE_INGR_BHYT_CODE",sere_serv."TDL_ACTIVE_INGR_BHYT_NAME",sere_serv."TDL_MEDICINE_CONCENTRA",sere_serv."TDL_MEDICINE_BID_NUM_ORDER",sere_serv."TDL_MEDICINE_REGISTER_NUMBER",sere_serv."TDL_MEDICINE_PACKAGE_NUMBER",sere_serv."TDL_SERVICE_REQ_CODE",sere_serv."TDL_REQUEST_ROOM_ID",sere_serv."TDL_REQUEST_DEPARTMENT_ID",sere_serv."TDL_REQUEST_LOGINNAME",sere_serv."TDL_REQUEST_USERNAME",sere_serv."TDL_EXECUTE_ROOM_ID",sere_serv."TDL_EXECUTE_DEPARTMENT_ID",sere_serv."TDL_EXECUTE_BRANCH_ID",sere_serv."TDL_EXECUTE_GROUP_ID",sere_serv."TDL_SPECIALITY_CODE",sere_serv."TDL_SERVICE_REQ_TYPE_ID",sere_serv."TDL_HST_BHYT_CODE",sere_serv."TDL_PACS_TYPE_CODE",sere_serv."TDL_IS_MAIN_EXAM",sere_serv."TDL_BILL_OPTION",sere_serv."TDL_MATERIAL_GROUP_BHYT",sere_serv."TDL_IS_SPECIFIC_HEIN_PRICE",sere_serv."EXPEND_TYPE_ID",sere_serv."INVOICE_ID",sere_serv."USE_ORIGINAL_UNIT_FOR_PRES",sere_serv."AMOUNT_TEMP",sere_serv."IS_FUND_ACCEPTED",sere_serv."IS_OTHER_SOURCE_PAID",sere_serv."IS_NOT_PRES",sere_serv."IS_USER_PACKAGE_PRICE",sere_serv."PACKAGE_PRICE",sere_serv."USER_PRICE",sere_serv."PATIENT_PRICE_BHYT",sere_serv."OTHER_SOURCE_PRICE",sere_serv."VIR_PRICE",sere_serv."VIR_PRICE_NO_ADD_PRICE",sere_serv."VIR_PRICE_NO_EXPEND",sere_serv."VIR_HEIN_PRICE",sere_serv."VIR_PATIENT_PRICE",sere_serv."VIR_PATIENT_PRICE_BHYT",sere_serv."VIR_TOTAL_PRICE",sere_serv."VIR_TOTAL_PRICE_NO_ADD_PRICE",sere_serv."VIR_TOTAL_PRICE_NO_EXPEND",sere_serv."VIR_TOTAL_HEIN_PRICE",sere_serv."VIR_TOTAL_PATIENT_PRICE",sere_serv."VIR_TOTAL_PATIENT_PRICE_BHYT",sere_serv."VIR_TOTAL_PATIENT_PRICE_NO_DC",sere_serv."VIR_TOTAL_PATIENT_PRICE_TEMP",sere_serv."OTHER_PAY_SOURCE_ID",sere_serv."TDL_SERVICE_TAX_RATE_TYPE",sere_serv."CONFIG_HEIN_LIMIT_PRICE",sere_serv."TDL_SERVICE_DESCRIPTION",sere_serv."TDL_IS_OUT_OF_DRG",sere_serv."SERVICE_CONDITION_ID",sere_serv."IS_ACCEPTING_NO_EXECUTE",sere_serv."TDL_REQUEST_USER_TITLE",sere_serv."DISCOUNT_LOGINNAME",sere_serv."DISCOUNT_USERNAME",sere_serv."NO_EXECUTE_REASON",sere_serv."ACTUAL_PRICE",sere_serv."IS_TEMP_BED_PROCESSED",sere_serv."IS_NOT_USE_BHYT",sere_serv."TDL_CARER_CARD_BORROW_ID",sere_serv."TDL_RATION_TIME_ID",sere_serv."IS_CONFIRM_NO_EXCUTE",sere_serv."CONFIRM_NO_EXCUTE_REASON",sere_serv."TDL_IS_VACCINE",sere_serv."ASSIGN_NUM_ORDER",
    medicine.package_number,
    medicine.ACTIVE_INGR_BHYT_NAME,
    medicine.CONCENTRA,
    medicine_use_form.medicine_use_form_code,
    medicine_use_form.medicine_use_form_name,
    service_req.intruction_time,
    service_req.intruction_date,
    service_req.tdl_patient_code,
    service_unit.service_unit_code,
    service_unit.service_unit_name,
    service_type.service_type_code,
    service_type.service_type_name,
    exp_mest_medicine.tutorial
FROM his_sere_serv sere_serv
LEFT JOIN his_medicine medicine on medicine.id = sere_serv.medicine_id
LEFT JOIN his_medicine_use_form medicine_use_form on medicine_use_form.id = medicine.medicine_use_form_id
LEFT JOIN his_service_req service_req on service_req.id = sere_serv.service_req_id
LEFT JOIN his_service_unit service_unit on service_unit.id = sere_serv.tdl_service_unit_id
LEFT JOIN his_service_type service_type on service_type.id = sere_serv.tdl_service_type_id
LEFT JOIN his_exp_mest_medicine exp_mest_medicine on exp_mest_medicine.id = sere_serv.exp_mest_medicine_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_MEDICINE_LIST");
    }
};
