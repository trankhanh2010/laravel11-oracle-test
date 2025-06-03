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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_TRACKING_LIST AS
SELECT
     tracking."ID",
     tracking."CREATE_TIME",
     tracking."MODIFY_TIME",
     tracking."CREATOR",
     tracking."MODIFIER",
     tracking."APP_CREATOR",
     tracking."APP_MODIFIER",
     tracking."IS_ACTIVE",
     tracking."IS_DELETE",
     tracking."GROUP_CODE",
     tracking."TREATMENT_ID",
     tracking."TRACKING_TIME",
     tracking."ICD_ID__DELETE",
     tracking."ICD_CODE",
     tracking."ICD_NAME",
     tracking."ICD_SUB_CODE",
     tracking."ICD_TEXT",
     tracking."MEDICAL_INSTRUCTION",
     tracking."DEPARTMENT_ID",
     tracking."CARE_INSTRUCTION",
     tracking."GENERAL_EXPRESSION",
     tracking."ORIENTATION_CAPACITY",
     tracking."EMOTION",
     tracking."PERCEPTION",
     tracking."FORM_OF_THINKING",
     tracking."CONTENT_OF_THINKING",
     tracking."INSTINCTIVELY_BEHAVIOR",
     tracking."AWARENESS_BEHAVIOR",
     tracking."MEMORY",
     tracking."INTELLECTUAL",
     tracking."CONCENTRATION",
     tracking."CARDIOVASCULAR",
     tracking."RESPIRATORY",
     tracking."ROOM_ID",
     tracking."TRADITIONAL_ICD_CODE",
     tracking."TRADITIONAL_ICD_NAME",
     tracking."TRADITIONAL_ICD_SUB_CODE",
     tracking."TRADITIONAL_ICD_TEXT",
     tracking."EYE_TENSION_LEFT",
     tracking."EYE_TENSION_RIGHT",
     tracking."EYESIGHT_LEFT",
     tracking."EYESIGHT_RIGHT",
     tracking."EYESIGHT_GLASS_LEFT",
     tracking."EYESIGHT_GLASS_RIGHT",
     tracking."SHEET_ORDER",
     tracking."EMR_DOCUMENT_STT_ID",
     tracking."EMR_DOCUMENT_URL",
     tracking."EMR_DOCUMENT_CODE",
     tracking."CONTENT",
     tracking."SUBCLINICAL_PROCESSES",
     tracking."DISEASE_STAGE",
     tracking."REHABILITATION_CONTENT",
     department.department_code as department_code,
     department.department_name as department_name,
     SUBSTR(tracking."TRACKING_TIME", 1, 8) || '000000' AS intruction_date, -- L?y YYYYMMDD v� n?i '000000'
     employee.tdl_username as tracking_creator

    FROM his_tracking tracking
    LEFT JOIN his_department department on department.id = tracking.department_id
    LEFT JOIN his_employee employee on employee.loginname = tracking.creator
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('oracle_his')->statement("DROP VIEW XA_V_HIS_TRACKING_LIST");
    }
};
