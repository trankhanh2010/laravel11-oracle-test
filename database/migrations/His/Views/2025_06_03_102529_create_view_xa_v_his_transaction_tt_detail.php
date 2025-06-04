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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_TRANSACTION_TT_DETAIL AS
SELECT
     sere_serv_bill.id,
     sere_serv_bill.is_delete,
     sere_serv_bill.is_cancel,
     sere_serv_bill.bill_id,
     bill.transaction_code as bill_code,
     sere_serv_bill.tdl_treatment_id,
     service_type.service_type_name,
     sere_serv_bill.tdl_amount as amount,  -- so luong
     sere_serv_bill.tdl_price as price,  -- don gia
     sere_serv.vir_total_price,  -- tong tien
     sere_serv.vir_total_hein_price,  -- bao hiem tra
     sere_serv.vir_total_patient_price,  -- benh nhan tra
     sere_serv_bill.tdl_discount as discount,  -- chiet khau
     sere_serv_bill.tdl_other_source_price as other_source_price, -- nguon thanh toan khac

     sere_serv.vir_total_price_no_expend,  -- hao phi

     patient_type.patient_type_name,

     sere_serv_bill.tdl_vat_ratio as vat_ratio,

     sere_serv.is_expend,

     service_req.service_req_code as tdl_tdl_service_req_code,
     sere_serv_bill.tdl_service_req_id as service_req_id,
     sere_serv_bill.tdl_service_code,
     sere_serv_bill.tdl_service_name,
     CASE
        WHEN
              sere_serv_bill.is_delete = 0
              AND (sere_serv_bill.is_cancel IS NULL OR sere_serv_bill.is_cancel = 0)
              THEN 1
        ELSE 0
    END AS da_thanh_toan


    FROM his_sere_serv_bill sere_serv_bill
    LEFT JOIN his_service_type service_type on service_type.id = sere_serv_bill.tdl_service_type_id
    LEFT JOIN his_patient_type patient_type on patient_type.id = sere_serv_bill.tdl_patient_type_id
    LEFT JOIN his_service_req service_req on service_req.id = sere_serv_bill.tdl_service_req_id
    LEFT JOIN his_sere_serv sere_serv on sere_serv.id = sere_serv_bill.sere_serv_id
    LEFT JOIN his_transaction bill on bill.id = sere_serv_bill.bill_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_TRANSACTION_TT_DETAIL");
    }
};
