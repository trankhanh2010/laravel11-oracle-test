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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_TEST_SERV_TYPE_LIST AS
SELECT
     sere_serv.id,
     sere_serv.tdl_patient_id,
     sere_serv.tdl_treatment_id,
     sere_serv.is_specimen,
     sere_serv.is_no_execute,
     sere_serv.is_no_pay,
     sere_serv.TDL_IS_MAIN_EXAM,
     service_type.service_type_name,
     sere_serv.amount,  -- so luong
     sere_serv.price,  -- don gia
     sere_serv.vir_total_price,  -- tong tien
     sere_serv.vir_total_hein_price,  -- bao hiem tra
     sere_serv.vir_total_patient_price,  -- benh nhan tra
     sere_serv.discount,  -- chiet khau
     sere_serv.other_source_price, -- nguon thanh toan khac
     sere_serv.vir_total_price_no_expend,  -- hao phi

     patient_type.patient_type_name,
     sere_serv.vat_ratio,
     sere_serv.is_expend,
     sere_serv.tdl_service_req_code,
     sere_serv.service_req_id,
     sere_serv.tdl_service_code,
     sere_serv.tdl_service_name,

     service_req_stt.service_req_stt_code,
     service_req_stt.service_req_stt_name,

     request_department.department_name as request_department_name,
     request_department.department_code as request_department_code,
     service_req.intruction_time,
     service_req.service_req_code,
     request_room.room_code as request_room_code,
     request_room.room_name as request_room_name,
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
    (
      SELECT SUM(hss_deposit.amount)
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
      ) AS tam_ung,
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
    LEFT JOIN his_service_req service_req on service_req.id = sere_serv.service_req_id
    LEFT JOIN his_service_req_stt service_req_stt on service_req_stt.id = service_req.service_req_stt_id
    LEFT JOIN his_department request_department on request_department.id = service_req.request_department_id
    LEFT JOIN v_his_room request_room on request_room.id = service_req.request_room_id
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
        DB::connection('oracle_his')->statement("DROP VIEW XA_V_HIS_TEST_SERV_TYPE_LIST");
    }
};
