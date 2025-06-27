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
CREATE OR REPLACE VIEW XA_V_HIS_MAU_DON_TH_VT_DETAIL AS
(
SELECT 
emte_material_type.EXP_MEST_TEMPLATE_ID,
emte_material_type.IS_ACTIVE,
emte_material_type.IS_DELETE,
null as concentra,
null as active_ingr_bhyt_name,
emte_material_type.MATERIAL_TYPE_ID as m_type_id,
emte_material_type.MATERIAL_TYPE_NAME as m_type_name,
'VT' as service_type_code,
emte_material_type.AMOUNT,
emte_material_type.IS_EXPEND,
emte_material_type.IS_OUT_MEDI_STOCK,
emte_material_type.SERVICE_UNIT_NAME,
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

FROM HIS_EMTE_MATERIAL_TYPE emte_material_type     
LEFT JOIN HIS_MATERIAL_TYPE  material_type on material_type.id = emte_material_type.material_type_id

  UNION ALL

SELECT 
emte_medicine_type.EXP_MEST_TEMPLATE_ID,
emte_medicine_type.IS_ACTIVE,
emte_medicine_type.IS_DELETE,
medicine_type.concentra,
medicine_type.active_ingr_bhyt_name,
emte_medicine_type.MEDICINE_TYPE_ID as m_type_id,
emte_medicine_type.MEDICINE_TYPE_NAME as m_type_name,
'TH' as service_type_code,
emte_medicine_type.AMOUNT,
emte_medicine_type.IS_EXPEND,
emte_medicine_type.IS_OUT_MEDI_STOCK,
emte_medicine_type.SERVICE_UNIT_NAME,
emte_medicine_type.TUTORIAL,
medicine_type.DESCRIPTION, --ghi chú lúc rê chuột vào
emte_medicine_type.DAY_COUNT,
emte_medicine_type.MORNING,
emte_medicine_type.NOON,
emte_medicine_type.AFTERNOON,
emte_medicine_type.EVENING,
medicine_type.service_id,
medicine_type.medicine_use_form_id,
medicine_use_form.medicine_use_form_name,
medicine_use_form.medicine_use_form_code

FROM HIS_EMTE_MEDICINE_TYPE emte_medicine_type     
LEFT JOIN HIS_MEDICINE_TYPE  medicine_type on medicine_type.id = emte_medicine_type.medicine_type_id
LEFT JOIN HIS_MEDICINE_USE_FORM medicine_use_form on medicine_use_form.id = medicine_type.medicine_use_form_id

)
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_MAU_DON_TH_VT_DETAIL");
    }
};
