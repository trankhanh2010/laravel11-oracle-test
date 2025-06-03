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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_SERE_SERV_TEIN_LIST AS
SELECT
     sere_serv_tein.id,
     sere_serv_tein.IS_ACTIVE,
     sere_serv_tein.IS_DELETE,
     sere_serv_tein.value,
     sere_serv_tein.result_code,
     sere_serv_tein.description,
     sere_serv_tein.result_description,
     sere_serv_tein.LEAVEN,
     sere_serv_tein.OLD_VALUE,
     sere_serv_tein.sere_serv_id,
     sere_serv.tdl_service_code,
     sere_serv.tdl_service_name,
     sere_serv.service_req_id,
     sere_serv.IS_NO_EXECUTE,
     service_req.service_req_code,
     service_req.is_no_execute as service_req_is_no_execute,
     test_index.test_index_code,
     test_index.test_index_name,
     test_index.num_order,
     test_index_unit.test_index_unit_code,
     test_index_unit.test_index_unit_name

    FROM his_sere_serv_tein sere_serv_tein
    LEFT JOIN his_sere_serv sere_serv on sere_serv.id = sere_serv_tein.sere_serv_id
    LEFT JOIN his_service_req service_req on service_req.id = sere_serv.service_req_id
    LEFT JOIN his_test_index test_index on test_index.id = sere_serv_tein.test_index_id
    LEFT JOIN his_test_index_unit test_index_unit on test_index_unit.id = test_index.test_index_unit_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('oracle_his')->statement("DROP VIEW XA_V_HIS_SERE_SERV_TEIN_LIST");
    }
};
