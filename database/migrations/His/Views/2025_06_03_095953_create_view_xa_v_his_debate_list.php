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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_DEBATE_LIST AS
SELECT
     debate.id,
     debate.create_time,
     debate.modify_time,
     debate.creator,
     debate.modifier,
     debate.is_active,
     debate.is_delete,
     debate.DEBATE_TIME,
     debate.REQUEST_USERNAME,
     debate.REQUEST_LOGINNAME,
     debate.treatment_id,
     debate.department_id,
     patient.patient_code,
     treatment.TDL_PATIENT_NAME,
     treatment.treatment_code,
     department.department_code,
     department.department_name,
     debate.LOCATION,
     debate.CONCLUSION,
     debate.icd_code,
     debate.icd_name,
     debate.icd_sub_code,
     debate.icd_text

    FROM his_debate debate
    LEFT JOIN his_treatment treatment on treatment.id = debate.treatment_id
    LEFT JOIN his_patient patient on patient.id = treatment.patient_id
    LEFT JOIN his_department department on department.id = debate.department_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('oracle_his')->statement("DROP VIEW XA_V_HIS_DEBATE_LIST");
    }
};
