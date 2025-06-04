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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_SERVICE_REQ_LIST AS
SELECT
     service_req.id,
     service_req.create_time,
     service_req.modify_time,
     service_req.creator,
     service_req.modifier,
     service_req.app_creator,
     service_req.app_modifier,
     service_req.is_active,
     service_req.is_delete,
     service_req.is_no_execute,
     service_req.intruction_time,
     service_req.intruction_date,
     service_req.treatment_id,
     service_req.tracking_id,
     service_req.service_req_code,
     service_req.note,
     service_req.CONCLUSION_CLINICAL,
     service_req.CONCLUSION_SUBCLINICAL,
     service_req_stt.service_req_stt_code,
     service_req_stt.service_req_stt_name,


     request_department.department_code as request_department_code,
     request_department.department_name as request_department_name
    FROM his_service_req service_req
    LEFT JOIN his_department request_department on request_department.id = service_req.request_department_id
    LEFT JOIN his_service_req_stt service_req_stt on service_req_stt.id = service_req.service_req_stt_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_SERVICE_REQ_LIST");
    }
};
