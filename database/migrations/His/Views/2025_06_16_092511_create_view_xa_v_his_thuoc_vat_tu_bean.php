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
CREATE OR REPLACE VIEW XA_V_HIS_THUOC_VAT_TU_BEAN AS
(
SELECT 
    medicine.id,
    medicine_bean.id as bean_id,
    medicine.is_active,
    medicine.is_delete,
    medicine_type.is_leaf,
    medicine_type.parent_id,
    medicine.medicine_type_id as m_type_id,
    medicine_type.medicine_type_code as m_type_code,
    medicine_type.medicine_type_name as m_type_name,
    medicine_type.service_id,
    parent.medicine_type_code as m_parent_code,
    parent.medicine_type_name as m_parent_name,
    medicine_type.CONCENTRA,
    medicine_type.ACTIVE_INGR_BHYT_CODE,
    medicine_type.ACTIVE_INGR_BHYT_NAME,
    service_unit.service_unit_code,
    service_unit.service_unit_name,
    medicine_bean.amount as bean_amount,
    medicine_bean.tdl_package_number,
    medicine_bean.tdl_medicine_register_number,
    medicine_bean.tdl_medicine_expired_date,
    medicine.national_name,
    medicine_type.last_exp_price,
    medicine_type.last_exp_vat_ratio,
    medicine_type.last_imp_vat_ratio,
    medicine_bean.medi_stock_id,
    medi_stock.medi_stock_code,
    medi_stock.medi_stock_name,
    medi_stock.is_drug_store,
    manufacturer.manufacturer_code,
    manufacturer.manufacturer_name,
    service_type.service_type_code,
    service_type.service_type_name
FROM HIS_MEDICINE medicine     
LEFT JOIN HIS_MEDICINE_TYPE medicine_type on medicine_type.id = medicine.medicine_type_id   
JOIN HIS_MEDICINE_TYPE parent 
    on parent.id = medicine_type.parent_id 
    AND parent.id in (
        SELECT ID FROM HIS_MEDICINE_TYPE m1
        WHERE m1.parent_id in (
            SELECT ID FROM HIS_MEDICINE_TYPE m2 WHERE m2.is_leaf is null and m2.parent_id is null
        )
        AND m1.is_leaf is null
    )
LEFT JOIN HIS_MANUFACTURER manufacturer on manufacturer.id = medicine_type.manufacturer_id
LEFT JOIN HIS_SERVICE_UNIT service_unit on service_unit.id = medicine_type.tdl_service_unit_id
LEFT JOIN HIS_SERVICE service on service.id = medicine.tdl_service_id
LEFT JOIN HIS_SERVICE_TYPE service_type on service_type.id = service.service_type_id
LEFT JOIN HIS_MEDICINE_BEAN medicine_bean 
on medicine_bean.medicine_id = medicine.id and medicine_bean.is_active = 1 and medicine_bean.is_delete = 0
LEFT JOIN HIS_MEDI_STOCK medi_stock on medi_stock.id = medicine_bean.medi_stock_id

  UNION ALL

SELECT 
    material.id,
    material_bean.id as bean_id,
    material.is_active,
    material.is_delete,
    material_type.is_leaf,
    material_type.parent_id,
    material.material_type_id as m_type_id,
    material_type.material_type_code as m_type_code,
    material_type.material_type_name as m_type_name,
    material_type.service_id,
    parent.material_type_code as m_parent_code,
    parent.material_type_name as m_parent_name,
    material_type.CONCENTRA,
    null as ACTIVE_INGR_BHYT_CODE,
    null as ACTIVE_INGR_BHYT_NAME,
    service_unit.service_unit_code,
    service_unit.service_unit_name,
    material_bean.amount as bean_amount,
    material_bean.tdl_package_number,
    null as tdl_medicine_register_number,
    null as tdl_medicine_expired_date,
    material.national_name,
    material_type.last_exp_price,
    material_type.last_exp_vat_ratio,
    material_type.last_imp_vat_ratio,
    material_bean.medi_stock_id,
    medi_stock.medi_stock_code,
    medi_stock.medi_stock_name,
    medi_stock.is_drug_store,
    manufacturer.manufacturer_code,
    manufacturer.manufacturer_name,
    service_type.service_type_code,
    service_type.service_type_name
FROM HIS_MATERIAL material     
LEFT JOIN HIS_MATERIAL_TYPE material_type on material_type.id = material.material_type_id   
JOIN HIS_MATERIAL_TYPE parent 
    on parent.id = material_type.parent_id 
    AND parent.id in (
        SELECT ID FROM HIS_MATERIAL_TYPE m2 WHERE m2.is_leaf is null and m2.parent_id is null -- lấy theo nhóm lớn
    )
LEFT JOIN HIS_MANUFACTURER manufacturer on manufacturer.id = material_type.manufacturer_id
LEFT JOIN HIS_SERVICE_UNIT service_unit on service_unit.id = material_type.tdl_service_unit_id
LEFT JOIN HIS_SERVICE service on service.id = material.tdl_service_id
LEFT JOIN HIS_SERVICE_TYPE service_type on service_type.id = service.service_type_id
LEFT JOIN HIS_MATERIAL_BEAN material_bean 
on material_bean.material_id = material.id and material_bean.is_active = 1 and material_bean.is_delete = 0
LEFT JOIN HIS_MEDI_STOCK medi_stock on medi_stock.id = material_bean.medi_stock_id
)
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_THUOC_VAT_TU_BEAN");
    }
};
