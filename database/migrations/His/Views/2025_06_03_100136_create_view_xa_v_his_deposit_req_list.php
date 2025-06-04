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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_DEPOSIT_REQ_LIST AS
SELECT
       deposit_req.id,
       deposit_req.create_time,
       deposit_req.modify_time,
       deposit_req.creator,
       deposit_req.modifier,
       deposit_req.app_creator,
       deposit_req.app_modifier,
       deposit_req.is_active,
       deposit_req.is_delete,
       deposit_req.group_code,
       deposit_req.deposit_req_code,
       deposit_req.treatment_id,
       deposit_req.amount,
       deposit_req.request_room_id,
       deposit_req.request_department_id,
       deposit_req.request_loginname,
       deposit_req.request_username,
       deposit_req.description,
       deposit_req.deposit_id,
       deposit_req.trans_req_id,

       treatment.treatment_code,
       treatment.PATIENT_ID,
       treatment.TDL_PATIENT_CODE,
       treatment.TDL_PATIENT_FIRST_NAME,
       treatment.TDL_PATIENT_LAST_NAME,
       treatment.TDL_PATIENT_DOB,
       treatment.TDL_PATIENT_GENDER_NAME,
       treatment.TDL_PATIENT_NAME,
       treatment.TDL_PATIENT_ADDRESS,
       treatment.TDL_HEIN_CARD_NUMBER,
       treatment.TDL_PATIENT_TYPE_ID,
       treatment.TDL_HEIN_MEDI_ORG_CODE,
       treatment.TDL_TREATMENT_TYPE_ID,
       treatment.TDL_HEIN_MEDI_ORG_NAME,

       request_room.room_code,
       request_room.room_name,
       request_room.room_type_code,
       request_room.room_type_name,

       request_department.department_code,
       request_department.department_name,

       transaction.BANK_TRANSACTION_CODE,
       transaction.BANK_TRANSACTION_TIME,
       transaction.TRANSACTION_TIME,
       transaction.AMOUNT AS TRANSACTION_AMOUNT,
       transaction.NUM_ORDER AS TRANSACTION_NUM_ORDER,
       transaction.IS_CANCEL AS TRANSACTION_IS_CANCEL,

       account_book.ACCOUNT_BOOK_NAME,
       account_book.SYMBOL_CODE,
       account_book.TEMPLATE_CODE


FROM his_deposit_req deposit_req
LEFT JOIN his_treatment treatment ON treatment.id = deposit_req.treatment_id
LEFT JOIN v_his_room request_room ON request_room.id = deposit_req.request_room_id
LEFT JOIN his_department request_department ON request_department.id = deposit_req.request_department_id
LEFT JOIN HIS_TRANSACTION transaction ON transaction.ID = deposit_req.DEPOSIT_ID
LEFT JOIN HIS_ACCOUNT_BOOK account_book ON account_book.ID = transaction.ACCOUNT_BOOK_ID
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_DEPOSIT_REQ_LIST");
    }
};
