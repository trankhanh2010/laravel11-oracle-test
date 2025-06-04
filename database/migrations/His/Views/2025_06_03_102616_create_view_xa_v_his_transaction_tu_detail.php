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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_TRANSACTION_TU_DETAIL AS
SELECT
     sere_serv_deposit.id,
     sere_serv_deposit.deposit_id,
     bill.transaction_code as deposit_code,
     sere_serv_deposit.tdl_treatment_id,
     service_type.service_type_name,
     sere_serv_deposit.amount,
     sere_serv.amount as sere_serv_amount,
     sere_serv.vir_total_price,
     sere_serv.vir_total_hein_price,
     sere_serv.vir_total_patient_price,

     sere_serv.vir_total_price_no_expend,

     patient_type.patient_type_name,

     sere_serv.vat_ratio,

     sere_serv.is_expend,

     service_req.service_req_code as tdl_tdl_service_req_code,
     sere_serv_deposit.tdl_service_req_id as service_req_id,
     sere_serv_deposit.tdl_service_code,
     sere_serv_deposit.tdl_service_name,
     CASE
        WHEN (
               sere_serv_deposit.is_delete = 0
              AND (sere_serv_deposit.is_cancel IS NULL OR sere_serv_deposit.is_cancel = 0)
              AND NOT EXISTS (
              SELECT 1
              FROM his_sese_depo_repay repay
              WHERE repay.sere_serv_deposit_id = sere_serv_deposit.id
                AND repay.is_delete = 0
                AND (repay.is_cancel IS NULL OR repay.is_cancel = 0)
          )
        ) THEN 1
        ELSE 0
    END AS da_tam_ung


    FROM his_sere_serv_deposit sere_serv_deposit
    LEFT JOIN his_service_type service_type on service_type.id = sere_serv_deposit.tdl_service_type_id
    LEFT JOIN his_patient_type patient_type on patient_type.id = sere_serv_deposit.tdl_patient_type_id
    LEFT JOIN his_service_req service_req on service_req.id = sere_serv_deposit.tdl_service_req_id
    LEFT JOIN his_sere_serv sere_serv on sere_serv.id = sere_serv_deposit.sere_serv_id
    LEFT JOIN his_transaction bill on bill.id = sere_serv_deposit.deposit_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_TRANSACTION_TU_DETAIL");
    }
};
