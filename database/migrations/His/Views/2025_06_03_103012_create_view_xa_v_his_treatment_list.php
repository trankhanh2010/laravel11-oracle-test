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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_TREATMENT_LIST AS
SELECT
     treatment.id,
     treatment.is_active,
     treatment.is_delete,
     treatment.treatment_code,
     treatment.tdl_patient_code,
     treatment.tdl_patient_name,
     treatment.in_time,
     treatment.CLINICAL_IN_TIME,
     treatment.out_time,
     treatment.IS_EMERGENCY,
     treatment.icd_code,
     treatment.icd_name,
     treatment.icd_sub_code,
     treatment.icd_text,
     treatment_result.treatment_result_code,
     treatment_result.treatment_result_name,
     treatment_end_type.treatment_end_type_code,
     treatment_end_type.treatment_end_type_name,
     treatment_type.treatment_type_code,
     treatment_type.treatment_type_name,
     in_treatment_type.treatment_type_code as in_treatment_type_code,
     in_treatment_type.treatment_type_name as in_treatment_type_name,
     end_department.department_code as end_department_code,
     end_department.department_name as end_department_name

    FROM his_treatment treatment
    LEFT JOIN his_treatment_result treatment_result on treatment_result.id = treatment.treatment_result_id
    LEFT JOIN his_treatment_end_type treatment_end_type on treatment_end_type.id = treatment.treatment_end_type_id
    LEFT JOIN his_treatment_type treatment_type on treatment_type.id = treatment.tdl_treatment_type_id
    LEFT JOIN his_treatment_type in_treatment_type on in_treatment_type.id = treatment.in_treatment_type_id
    LEFT JOIN his_department end_department on end_department.id = treatment.end_department_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_TREATMENT_LIST");
    }
};
