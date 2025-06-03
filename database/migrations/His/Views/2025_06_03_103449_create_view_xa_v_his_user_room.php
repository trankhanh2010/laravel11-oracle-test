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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_USER_ROOM AS
SELECT USRO."ID",USRO."CREATE_TIME",USRO."MODIFY_TIME",USRO."CREATOR",USRO."MODIFIER",USRO."APP_CREATOR",USRO."APP_MODIFIER",USRO."IS_ACTIVE",USRO."IS_DELETE",USRO."GROUP_CODE",USRO."LOGINNAME",USRO."ROOM_ID",
ROOM.ROOM_CODE,ROOM.ROOM_NAME,ROOM.DEPARTMENT_ID,ROOM.ROOM_TYPE_ID,ROOM.ROOM_TYPE_CODE,ROOM.ROOM_TYPE_NAME,ROOM.DEPARTMENT_CODE,ROOM.DEPARTMENT_NAME,ROOM.IS_PAUSE,ROOM.G_CODE,ROOM.BRANCH_ID,ROOM.BRANCH_CODE,ROOM.BRANCH_NAME,ROOM.HEIN_MEDI_ORG_CODE,ROOM.IS_EXAM
FROM HIS_USER_ROOM USRO
JOIN V_HIS_ROOM ROOM ON USRO.ROOM_ID = ROOM.ID AND ROOM.IS_ACTIVE = 1 AND ROOM.IS_DELETE = 0
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('oracle_his')->statement("DROP VIEW XA_V_HIS_USER_ROOM");
    }
};
