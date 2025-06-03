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
        DB::connection('oracle_emr')->statement(
            <<<SQL
CREATE OR REPLACE VIEW XA_V_EMR_DOCUMENT_LIST AS
SELECT
    document."ID",
    document."CREATE_TIME",
    document."MODIFY_TIME",
    document."CREATOR",
    document."MODIFIER",
    document."APP_CREATOR",
    document."APP_MODIFIER",
    document."IS_ACTIVE",
    document."IS_DELETE",
    document."GROUP_CODE",
    document."DOCUMENT_CODE",
    document."DOCUMENT_NAME",
    document."TREATMENT_CODE",
    document."TREATMENT_ID",
    document."DOCUMENT_TYPE_ID",
    document."HIS_CODE",
    document."IS_MULTI_SIGN",
    document."IS_CAPTURE",
    document."REQUEST_LOGINNAME",
    document."REQUEST_USERNAME",
    document."HEADER_URL",
    document."MERGE_CODE",
    document."ORIGINAL_HIGH",
    document."COUNT_RESIGN_WAIT",
    document."COUNT_RESIGN_FAILED",
    document."COUNT_RESIGN_SUCCESS",
    document."IS_SIGN_PARALLEL",
    document."REJECTER",
    document."NEXT_SIGNER",
    document."NEXT_FLOW_ID",
    document."NEXT_ROOM",
    document."SIGNERS",
    document."UN_SIGNERS",
    document."CREATE_DATE",
    document."DOCUMENT_NAME_UNSIGN",
    document."DOCUMENT_TIME",
    document."DOCUMENT_DATE",
    document."DOCUMENT_GROUP_ID",
    document."ATTACHMENT_COUNT",
    document."MAIN_DOCUMENT_ID",
    document."HAS_SUB_DOCUMENT",
    document."DEPENDENT_CODE",
    document."PARENT_DEPENDENT_CODE",
    document."HIS_ORDER",
    document."PAPER_NAME",
    document."RAW_KIND",
    document."WIDTH",
    document."HEIGHT",
    document."IS_PATIENT_ISSUED",
    document."LAST_VERSION_URL",
    document."REJECT_TIME",
    document."REJECT_DATE",
    document."REJECT_REASON",
    document."VIR_CREATE_MONTH",
    document."VIR_CREATE_YEAR",
    document."CANCEL_REASON",
    document."CANCEL_LOGINNAME",
    document."CANCEL_USERNAME",
    document."DOCUMENT_FILE_TYPE",
    document."LAST_JSON_VERSION_URL",
    document."LAST_XML_VERSION_URL",
    document."APPROVAL_SIGNER_ID",
    document."CANCELER",
    document_type.document_type_code,
    document_type.document_type_name,
    document_type.num_order as document_type_num_order,
    document_group.document_group_code,
    document_group.document_group_name,
    document_group.num_order as document_group_num_order

    FROM emr_document document
    LEFT JOIN emr_document_type document_type on document_type.id = document.document_type_id
    LEFT JOIN emr_document_group document_group on document_group.id = document.document_group_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('oracle_emr')->statement("DROP VIEW XA_V_EMR_DOCUMENT_LIST");
    }
};
