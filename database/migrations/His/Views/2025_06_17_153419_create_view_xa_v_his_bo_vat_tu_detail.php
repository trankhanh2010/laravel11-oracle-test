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
CREATE OR REPLACE VIEW XA_V_HIS_BO_VAT_TU_DETAIL AS
(
SELECT 
equipment_set_maty.equipment_set_id,
equipment_set_maty.IS_ACTIVE,
equipment_set_maty.IS_DELETE,
null as concentra,
null as active_ingr_bhyt_name,
equipment_set_maty.MATERIAL_TYPE_ID as m_type_id,
material_type.MATERIAL_TYPE_NAME as m_type_name,
'VT' as service_type_code,
equipment_set_maty.AMOUNT,
null as IS_EXPEND,
null as IS_OUT_MEDI_STOCK,
null as SERVICE_UNIT_NAME,
null as TUTORIAL,
material_type.DESCRIPTION, --ghi chú lúc rê chuột vào
null as DAY_COUNT,
null as MORNING,
null as NOON,
null as AFTERNOON,
null as EVENING,
material_type.service_id,
null as medicine_use_form_id,
null as medicine_use_form_name,
null as medicine_use_form_code

FROM HIS_EQUIPMENT_SET_MATY equipment_set_maty     
LEFT JOIN HIS_MATERIAL_TYPE  material_type on material_type.id = equipment_set_maty.material_type_id
)
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_BO_VAT_TU_DETAIL");
    }
};
