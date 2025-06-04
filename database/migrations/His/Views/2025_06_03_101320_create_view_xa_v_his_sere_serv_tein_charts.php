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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_SERE_SERV_TEIN_CHARTS AS
SELECT
     sere_serv_tein."ID",sere_serv_tein."CREATE_TIME",sere_serv_tein."MODIFY_TIME",sere_serv_tein."CREATOR",sere_serv_tein."MODIFIER",sere_serv_tein."APP_CREATOR",sere_serv_tein."APP_MODIFIER",sere_serv_tein."IS_ACTIVE",sere_serv_tein."IS_DELETE",sere_serv_tein."GROUP_CODE",sere_serv_tein."SERE_SERV_ID",sere_serv_tein."TEST_INDEX_ID",sere_serv_tein."VALUE",sere_serv_tein."RESULT_CODE",sere_serv_tein."DESCRIPTION",sere_serv_tein."TDL_TREATMENT_ID",sere_serv_tein."MACHINE_ID",sere_serv_tein."BACTERIUM_CODE",sere_serv_tein."BACTERIUM_NAME",sere_serv_tein."BACTERIUM_NOTE",sere_serv_tein."BACTERIUM_AMOUNT",sere_serv_tein."BACTERIUM_DENSITY",sere_serv_tein."ANTIBIOTIC_RESISTANCE_CODE",sere_serv_tein."ANTIBIOTIC_RESISTANCE_NAME",sere_serv_tein."SRI_CODE",sere_serv_tein."NOTE",sere_serv_tein."LEAVEN",sere_serv_tein."EXP_MEST_ID",sere_serv_tein."TDL_SERVICE_REQ_ID",sere_serv_tein."OLD_VALUE",sere_serv_tein."RESULT_DESCRIPTION",
     test_index.num_order,
     test_index.test_index_code,
     test_index.test_index_name,
     test_index_unit.test_index_unit_code,
     test_index_unit.test_index_unit_name,
     service_req.service_req_code,
     service_req.intruction_time,
     service_req.intruction_date,
     service_req.VIR_INTRUCTION_MONTH,
     service_req.is_no_execute as service_req_is_no_execute,
     sere_serv.is_no_execute,
     patient.patient_code,
     service.service_code,
     service.service_name,
     service.description as service_description,
     service_type.service_type_code,
     service_type.service_type_name

    FROM his_sere_serv_tein sere_serv_tein
    LEFT JOIN his_test_index test_index on test_index.id = sere_serv_tein.test_index_id
    LEFT JOIN his_test_index_unit test_index_unit on test_index_unit.id = test_index.test_index_unit_id
    LEFT JOIN his_sere_serv sere_serv on sere_serv.id = sere_serv_tein.sere_serv_id
    LEFT JOIN his_service_req service_req on service_req.id = sere_serv.service_req_id
    LEFT JOIN his_patient patient on patient.id = sere_serv.tdl_patient_id
    LEFT JOIN his_service service on service.id = sere_serv.service_id
    LEFT JOIN his_service_type service_type on service_type.id = service.service_type_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_SERE_SERV_TEIN_CHARTS");
    }
};
