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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_PAYMENT_FEE_LOCK_LIST AS
SELECT
       treatment.id,
       treatment.is_active,
       payment.treatment_id,
       payment.treatment_code,
       treatment.fee_lock_time
FROM XA_HIS_TREATMENT_MOMO_PAYMENT payment
LEFT JOIN HIS_TREATMENT treatment ON treatment.id = payment.treatment_id
WHERE
     payment.result_code = 1000
     and treatment.fee_lock_time is not null
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('oracle_his')->statement("DROP VIEW XA_V_HIS_PAYMENT_FEE_LOCK_LIST");
    }
};
