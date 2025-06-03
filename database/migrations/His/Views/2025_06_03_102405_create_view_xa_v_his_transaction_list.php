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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_TRANSACTION_LIST AS
SELECT
     transaction.id,
     transaction.create_time,
     transaction.creator,
     transaction.modify_time,
     transaction.modifier,
     transaction.is_delete,
     transaction.is_active,
     transaction.transaction_code,
     transaction.transaction_type_id,
     transaction.AMOUNT,
     transaction.IS_CANCEL,
     transaction.KC_AMOUNT,
     transaction.TDL_BILL_FUND_AMOUNT, -- quy chi tra
     transaction.EXEMPTION, -- mien giam
     transaction.EXEMPTION_REASON,
     transaction.ROUNDED_TOTAL_PRICE, -- lam tron
     transaction.SWIPE_AMOUNT, -- so tien quet the
     transaction.TRANSFER_AMOUNT, -- so tien chuyen khoan
     transaction.BANK_TRANSACTION_CODE, -- ma giao dich cua ngan hang
     transaction.TRANSACTION_TIME,
     transaction.CASHIER_LOGINNAME,
     transaction.CASHIER_USERNAME,
     transaction_type.transaction_type_code,
     transaction_type.transaction_type_name, -- loai giao dich
     transaction.EINVOICE_NUM_ORDER, -- so chung tu
     transaction.CANCEL_REASON, -- ly do huy
     transaction.CANCEL_USERNAME,
     transaction.CANCEL_LOGINNAME,
     transaction.CANCEL_TIME,
     transaction.TDL_TREATMENT_CODE,
     transaction.TDL_PATIENT_CODE,
     transaction.TDL_PATIENT_DOB,
     transaction.TDL_PATIENT_NAME,
     transaction.TDL_PATIENT_GENDER_NAME,
     transaction.NUM_ORDER,
     transaction.NATIONAL_TRANSACTION_CODE,
     transaction.tdl_sere_serv_deposit_count,
     transaction.buyer_phone,
     transaction.buyer_address,
     transaction.buyer_organization,
     transaction.buyer_account_number,
     transaction.buyer_tax_code,
     transaction.buyer_name,
     transaction.repay_reason_id,
     transaction.account_book_id,
     transaction.pay_form_id,
     transaction.IS_DIRECTLY_BILLING, -- 1- thu truc tiep con lai thanh toan ra vien
     transaction.bill_type_id,

     bank.bank_code,
     bank.bank_name,
     pay_form.pay_form_code,
     pay_form.pay_form_name,
     cashier_room.cashier_room_name,
     cashier_room.cashier_room_code,
     account_book.account_book_name,
     account_book.account_book_code,
     trans_req.trans_req_code,
     deposit_req.REQUEST_LOGINNAME,
     deposit_req.REQUEST_USERNAME,
     deposit_req.DESCRIPTION,
     request_department.department_code as request_department_code,
     request_department.department_name as request_department_name,
     request_room.room_code as request_room_code,
     request_room.room_name as request_room_name

    FROM his_transaction transaction
    LEFT JOIN his_transaction_type transaction_type on transaction_type.id = transaction.transaction_type_id
    LEFT JOIN his_bank bank on bank.id = transaction.bank_id
    LEFT JOIN his_pay_form pay_form on pay_form.id = transaction.pay_form_id
    LEFT JOIN his_cashier_room cashier_room on cashier_room.id = transaction.cashier_room_id
    LEFT JOIN his_account_book account_book on account_book.id = transaction.account_book_id
    LEFT JOIN his_trans_req trans_req on trans_req.id = transaction.trans_req_id
    LEFT JOIN his_deposit_req deposit_req on deposit_req.deposit_id = transaction.id
    LEFT JOIN his_department request_department on request_department.id = deposit_req.request_department_id
    LEFT JOIN v_his_room request_room on request_room.id = deposit_req.request_room_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('oracle_his')->statement("DROP VIEW XA_V_HIS_TRANSACTION_LIST");
    }
};
