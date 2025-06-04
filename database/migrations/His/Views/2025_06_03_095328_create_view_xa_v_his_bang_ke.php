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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_BANG_KE AS
SELECT
     sere_serv.id,
     sere_serv.tdl_patient_id,
     sere_serv.tdl_treatment_id,
     sere_serv.is_specimen,
     sere_serv.is_no_execute,
     sere_serv.is_no_pay,
     sere_serv.TDL_IS_MAIN_EXAM,
     service_type.service_type_name,
     service_type.service_type_code,
     sere_serv.amount,  -- so luong
     sere_serv.price,  -- don gia
     sere_serv.vir_total_price,  -- tong tien
     sere_serv.vir_total_hein_price,  -- bao hiem tra
     sere_serv.vir_total_patient_price,  -- benh nhan tra
     sere_serv.discount,  -- chiet khau
     sere_serv.other_source_price, -- nguon thanh toan khac
     sere_serv.vir_price_no_expend,  -- hao phi
     sere_serv.vir_total_price_no_expend,  -- hao phi
     sere_serv.TDL_HEIN_SERVICE_BHYT_CODE,
     sere_serv.PACKAGE_PRICE,
     sere_serv.EXPEND_TYPE_ID,
     sere_serv.IS_NOT_USE_BHYT,
     sere_serv.AMOUNT_TEMP,
     sere_serv.VIR_TOTAL_PATIENT_PRICE_TEMP,
     sere_serv.STENT_ORDER,
     sere_serv.SHARE_COUNT,
     sere_serv.TDL_SERVICE_DESCRIPTION,
     sere_serv.IS_USER_PACKAGE_PRICE,
     sere_serv.is_out_parent_fee,

     parent.tdl_service_code as parent_code,
     parent.tdl_service_name as parent_name,
     parent.tdl_service_req_code as parent_service_req_code,

     sere_serv.service_id,
     sere_serv.other_pay_source_id,
     sere_serv.patient_type_id,
     sere_serv.parent_id,
     patient_type.patient_type_name,
     patient_type.patient_type_code,
     sere_serv.primary_patient_type_id,
     primary_patient_type.patient_type_name as primary_patient_type_name,
     primary_patient_type.patient_type_code as primary_patient_type_code,
     sere_serv.vat_ratio,
     sere_serv.is_expend,
     sere_serv.service_req_id,
     sere_serv.tdl_service_code,
     sere_serv.tdl_service_name,
     sere_serv.HEIN_CARD_NUMBER,
     sere_serv.hein_price,
     sere_serv.JSON_PATIENT_TYPE_ALTER,
     sere_serv.PATIENT_PRICE_BHYT,
     sere_serv.VIR_HEIN_PRICE,
     sere_serv.ORIGINAL_PRICE, -- gia goc BHYT
     sere_serv.HEIN_LIMIT_PRICE, -- gia tran BHYT
     sere_serv.vir_total_patient_price_no_dc,
     sere_serv.vir_patient_price,
     sere_serv.vir_patient_price_bhyt,
     sere_serv.vir_total_patient_price_bhyt,

     service_req_stt.service_req_stt_code,
     service_req_stt.service_req_stt_name,
     service_req_type.service_req_type_code,
     service_req_type.service_req_type_name,

     request_department.department_name as request_department_name,
     request_department.department_code as request_department_code,
     request_department.num_order as request_department_num_order,
     request_department.IS_CLINICAL as request_department_IS_CLINICAL,
     execute_department.department_name as execute_department_name,
     execute_department.department_code as execute_department_code,
     execute_department.num_order as execute_department_num_order,
     execute_department.IS_CLINICAL as execute_department_IS_CLINICAL,
     service_req.intruction_time,
     service_req.intruction_date,
     service_req.service_req_code,
     service_req.DESCRIPTION,
     request_room.room_code as request_room_code,
     request_room.room_name as request_room_name,
     request_room.room_type_code as request_room_type_code,
     execute_room.room_code as execute_room_code,
     execute_room.room_name as execute_room_name,
     execute_room.room_type_code as execute_room_type_code,
     execute_room.IS_EXAM as execute_room_is_exam,
     service_unit.service_unit_code,
     service_unit.service_unit_name,
     sere_serv.equipment_set_id,
     equipment_set.equipment_set_code,
     equipment_set.equipment_set_name,
     sere_serv.equipment_set_order,
     package.package_code,
     package.package_name,
     other_pay_source.other_pay_source_code,
     other_pay_source.other_pay_source_name,
     sere_serv.service_condition_id,
     service_condition.service_condition_code,
     service_condition.service_condition_name,
     treatment.tdl_treatment_type_id,
     treatment_type.treatment_type_code,
     treatment_type.treatment_type_name,
     hein_service_type.hein_service_type_name,
     hein_service_type.hein_service_type_code,
     hein_service_type.VIR_PARENT_NUM_ORDER  as hein_service_type_num_order,
     sere_serv.hein_ratio,
     sere_serv.vir_price,
     treatment.in_time,
     treatment.id as treatment_id,
     sere_serv.invoice_id,
     sere_serv.medicine_id,
     sere_serv.hein_approval_id,
     medicine.EXPIRED_DATE as medicine_EXPIRED_DATE,
     medicine.PACKAGE_NUMBER as medicine_PACKAGE_NUMBER,
     sere_serv.material_id,
     material.EXPIRED_DATE as material_EXPIRED_DATE,
     material.PACKAGE_NUMBER as material_PACKAGE_NUMBER,
     patient.career_code,
     patient.career_name,
     sere_serv.tdl_hst_bhyt_code,
    CASE
        WHEN EXISTS (
            SELECT 1
            FROM his_sere_serv_deposit hss_deposit
            WHERE hss_deposit.sere_serv_id = sere_serv.id
              AND hss_deposit.is_delete = 0
              AND (hss_deposit.is_cancel IS NULL OR hss_deposit.is_cancel = 0)
              AND NOT EXISTS (
              SELECT 1
              FROM his_sese_depo_repay repay
              WHERE repay.sere_serv_deposit_id = hss_deposit.id
                AND repay.is_delete = 0
                AND (repay.is_cancel IS NULL OR repay.is_cancel = 0)
          )
        ) THEN 1
        ELSE 0
    END AS da_tam_ung,
    CASE
        WHEN EXISTS (
            SELECT 1
            FROM his_sere_serv_bill hss_bill
            WHERE hss_bill.sere_serv_id = sere_serv.id
              AND hss_bill.is_delete = 0
              AND (hss_bill.is_cancel IS NULL OR hss_bill.is_cancel = 0)
        ) THEN 1
        ELSE 0
    END AS da_thanh_toan

    FROM his_sere_serv sere_serv
    LEFT JOIN his_service_type service_type on service_type.id = sere_serv.tdl_service_type_id
    LEFT JOIN his_patient_type patient_type on patient_type.id = sere_serv.patient_type_id
    LEFT JOIN his_patient_type primary_patient_type on primary_patient_type.id = sere_serv.primary_patient_type_id
    LEFT JOIN his_service_req service_req on service_req.id = sere_serv.service_req_id
    LEFT JOIN his_service_req_stt service_req_stt on service_req_stt.id = service_req.service_req_stt_id
    LEFT JOIN his_service_req_type service_req_type on service_req_type.id = service_req.service_req_type_id
    LEFT JOIN his_department request_department on request_department.id = sere_serv.tdl_request_department_id
    LEFT JOIN his_department execute_department on execute_department.id = sere_serv.tdl_execute_department_id
    LEFT JOIN v_his_room request_room on request_room.id = sere_serv.tdl_request_room_id
    LEFT JOIN v_his_room execute_room on execute_room.id = sere_serv.tdl_execute_room_id
    LEFT JOIN his_service_unit service_unit on service_unit.id = sere_serv.tdl_service_unit_id
    LEFT JOIN his_sere_serv parent on parent.id = sere_serv.parent_id
    LEFT JOIN his_equipment_set equipment_set on equipment_set.id = sere_serv.equipment_set_id
    LEFT JOIN his_package package on package.id = sere_serv.package_id
    LEFT JOIN his_other_pay_source other_pay_source on other_pay_source.id = sere_serv.other_pay_source_id
    LEFT JOIN his_service_condition service_condition on service_condition.id = sere_serv.service_condition_id
    LEFT JOIN his_treatment treatment on treatment.id = sere_serv.tdl_treatment_id
    LEFT JOIN his_treatment_type treatment_type on treatment_type.id = treatment.tdl_treatment_type_id
    LEFT JOIN his_service service on service.id = sere_serv.service_id
    LEFT JOIN his_hein_service_type hein_service_type on hein_service_type.id = service.hein_service_type_id
    LEFT JOIN his_medicine medicine on medicine.id = sere_serv.medicine_id
    LEFT JOIN his_material material on material.id = sere_serv.material_id
    LEFT JOIN his_patient patient on patient.id = treatment.patient_id

    WHERE
         sere_serv.is_delete = 0
         AND
         sere_serv.is_active = 1
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_BANG_KE");
    }
};
