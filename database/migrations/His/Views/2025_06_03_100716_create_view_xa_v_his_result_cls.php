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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_RESULT_CLS AS
(
  SELECT
         tein.id,
         tein.is_delete,
         test_index.test_index_name,
         test_index.test_index_code,
         tein.value,
         tein.note,
         TO_CLOB(tein.DESCRIPTION) AS description,
         sere_serv.tdl_service_name as service_name,
         sere_serv.tdl_service_code as service_code,
         service_req.is_no_execute as service_req_is_no_execute,
         sere_serv.is_no_execute,
         service_req.tdl_patient_code as patient_code,
         service_req.tdl_treatment_code as treatment_code,
         test_index_unit.test_index_unit_code as unit_code,
         test_index_unit.test_index_unit_name as unit_name,
         service_req.intruction_time,
         service_req.intruction_date,
         service_type.service_type_code,
         service_type.service_type_name,
         test_index.num_order as test_index_num_order

  FROM his_sere_serv_tein tein
  LEFT JOIN his_test_index test_index on test_index.id = tein.test_index_id
  LEFT JOIN his_sere_serv sere_serv on sere_serv.id = tein.sere_serv_id
  LEFT JOIN his_service_req service_req on service_req.id = sere_serv.service_req_id
  LEFT JOIN his_test_index_unit test_index_unit on test_index_unit.id = test_index.test_index_unit_id
  LEFT JOIN his_service_type service_type on service_type.id = sere_serv.tdl_service_type_id

  UNION ALL

  SELECT
       ext.id,
       ext.is_delete,
       NULL AS test_index_name,
       NULL AS test_index_code,
       ext.conclude AS value,
       ext.note,
       ext.DESCRIPTION,
       sere_serv.tdl_service_name as service_name,
       sere_serv.tdl_service_code as service_code,
       service_req.is_no_execute as service_req_is_no_execute,
       sere_serv.is_no_execute,
       service_req.tdl_patient_code as patient_code,
       service_req.tdl_treatment_code as treatment_code,
       service_unit.service_unit_code as unit_code,
       service_unit.service_unit_name as unit_name,
       service_req.intruction_time,
       service_req.intruction_date,
       service_type.service_type_code,
       service_type.service_type_name,
       null as test_index_num_order

  FROM his_sere_serv_ext ext
  LEFT JOIN his_sere_serv sere_serv on sere_serv.id = ext.sere_serv_id
  LEFT JOIN his_service_req service_req on service_req.id = sere_serv.service_req_id
  LEFT JOIN his_service_unit service_unit on service_unit.id = sere_serv.tdl_service_unit_id
  LEFT JOIN his_service_type service_type on service_type.id = sere_serv.tdl_service_type_id
)
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_RESULT_CLS");
    }
};
