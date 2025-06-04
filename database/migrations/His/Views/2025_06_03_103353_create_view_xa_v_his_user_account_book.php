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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_USER_ACCOUNT_BOOK AS
SELECT
user_account_book."ID",user_account_book."CREATE_TIME",user_account_book."MODIFY_TIME",user_account_book."CREATOR",user_account_book."MODIFIER",user_account_book."APP_CREATOR",user_account_book."APP_MODIFIER",user_account_book."IS_ACTIVE",user_account_book."IS_DELETE",user_account_book."GROUP_CODE",user_account_book."LOGINNAME",user_account_book."ACCOUNT_BOOK_ID",
account_book.is_active as account_book_is_active,
account_book.ACCOUNT_BOOK_CODE,
account_book.account_book_name,
account_book.TOTAL,
account_book.FROM_NUM_ORDER,
account_book.IS_FOR_DEPOSIT,
account_book.IS_FOR_REPAY,
account_book.IS_FOR_BILL,
account_book.DESCRIPTION,
account_book.NUM_ORDER,
account_book.TEMPLATE_CODE,
account_book.SYMBOL_CODE,
account_book.RELEASE_TIME,
account_book.LINK_ID,
account_book.BILL_TYPE_ID,
account_book.IS_NOT_GEN_TRANSACTION_ORDER,
account_book.MAX_ITEM_NUM_PER_TRANS,
account_book.IS_FOR_DEBT,
account_book.IS_FOR_OTHER_SALE,
account_book.WORKING_SHIFT_ID,
account_book.EINVOICE_TYPE_ID,
account_book.NUM_ORDER_SPLIT_BY_BOOK,
account_book.EINVOICE_PAGE_SIZE

FROM his_user_account_book user_account_book
LEFT JOIN his_account_book account_book on account_book.id = user_account_book.account_book_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_USER_ACCOUNT_BOOK");
    }
};
