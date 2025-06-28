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
CREATE OR REPLACE VIEW XA_V_HIS_THUOC_VAT_TU_TU_MUA AS
(
SELECT 
    medicine.id,
    null as bean_id,
    medicine.is_active,
    medicine.is_delete,
    medicine_type.is_leaf,
    medicine_type.parent_id,
    medicine.medicine_type_id as m_type_id,
    medicine_type.medicine_type_code as m_type_code,
    medicine_type.medicine_type_name as m_type_name,
    medicine_type.service_id,
    null as m_parent_code,
    null as m_parent_name,
    medicine_type.CONCENTRA,
    medicine_type.ACTIVE_INGR_BHYT_CODE,
    medicine_type.ACTIVE_INGR_BHYT_NAME,
    service_unit.service_unit_code,
    service_unit.service_unit_name,
    null as bean_amount,
    null as tdl_package_number,
    null as tdl_medicine_register_number,
    null as tdl_medicine_expired_date,
    medicine.national_name,
    medicine_type.last_exp_price,
    medicine_type.last_exp_vat_ratio,
    medicine_type.last_imp_vat_ratio,
    null as medi_stock_id,
    null as medi_stock_code,
    null as medi_stock_name,
    null as is_drug_store,
    manufacturer.manufacturer_code,
    manufacturer.manufacturer_name,
    service_type.service_type_code,
    service_type.service_type_name,
    medicine_type.DESCRIPTION, --ghi chú lúc rê chuột vào
    medicine_type.medicine_use_form_id,
    medicine_use_form.medicine_use_form_name,
    medicine_use_form.medicine_use_form_code,
    medicine_type.ALERT_MAX_IN_PRESCRIPTION, -- số lượng max kê trên 1 đơn 
    medicine_type.ALERT_MAX_IN_DAY, -- số lượng max kê trên 1 ngày 
    medicine_type.ALERT_MAX_IN_TREATMENT, -- số lượng max kê cho 1 hồ sơ điều trị
    medicine_type.IS_BLOCK_MAX_IN_PRESCRIPTION, -- chặn khi kê quá số lượng trên 1 đơn 
    medicine_type.IS_BLOCK_MAX_IN_DAY, -- chặn khi kê quá số lượng trên 1 ngày 
    medicine_type.IS_BLOCK_MAX_IN_TREATMENT -- chặn khi kê quá số lượng 1 hồ sơ điều trị

FROM HIS_MEDICINE medicine     
LEFT JOIN HIS_MEDICINE_TYPE medicine_type on medicine_type.id = medicine.medicine_type_id   
LEFT JOIN HIS_MANUFACTURER manufacturer on manufacturer.id = medicine_type.manufacturer_id
LEFT JOIN HIS_SERVICE_UNIT service_unit on service_unit.id = medicine_type.tdl_service_unit_id
LEFT JOIN HIS_SERVICE service on service.id = medicine.tdl_service_id
LEFT JOIN HIS_SERVICE_TYPE service_type on service_type.id = service.service_type_id
LEFT JOIN HIS_MEDICINE_USE_FORM medicine_use_form on medicine_use_form.id = medicine_type.medicine_use_form_id

  UNION ALL

SELECT 
    material.id,
    null as bean_id,
    material.is_active,
    material.is_delete,
    material_type.is_leaf,
    material_type.parent_id,
    material.material_type_id as m_type_id,
    material_type.material_type_code as m_type_code,
    material_type.material_type_name as m_type_name,
    material_type.service_id,
    null as m_parent_code,
    null as m_parent_name,
    material_type.CONCENTRA,
    null as ACTIVE_INGR_BHYT_CODE,
    null as ACTIVE_INGR_BHYT_NAME,
    service_unit.service_unit_code,
    service_unit.service_unit_name,
    null as bean_amount,
    null as tdl_package_number,
    null as tdl_medicine_register_number,
    null as tdl_medicine_expired_date,
    material.national_name,
    material_type.last_exp_price,
    material_type.last_exp_vat_ratio,
    material_type.last_imp_vat_ratio,
    null as medi_stock_id,
    null as medi_stock_code,
    null as medi_stock_name,
    null as is_drug_store,
    manufacturer.manufacturer_code,
    manufacturer.manufacturer_name,
    service_type.service_type_code,
    service_type.service_type_name,
    material_type.DESCRIPTION, --ghi chú lúc rê chuột vào
    null as medicine_use_form_id,
    null as medicine_use_form_name,
    null as medicine_use_form_code,
    material_type.ALERT_MAX_IN_PRESCRIPTION, -- số lượng max kê trên 1 đơn 
    material_type.ALERT_MAX_IN_DAY, -- số lượng max kê trên 1 ngày 
    null as ALERT_MAX_IN_TREATMENT, -- số lượng max kê cho 1 hồ sơ điều trị
    null as IS_BLOCK_MAX_IN_PRESCRIPTION, -- chặn khi kê quá số lượng trên 1 đơn 
    null as IS_BLOCK_MAX_IN_DAY, -- chặn khi kê quá số lượng trên 1 ngày 
    null as IS_BLOCK_MAX_IN_TREATMENT -- chặn khi kê quá số lượng 1 hồ sơ điều trị

FROM HIS_MATERIAL material     
LEFT JOIN HIS_MATERIAL_TYPE material_type on material_type.id = material.material_type_id   
LEFT JOIN HIS_MANUFACTURER manufacturer on manufacturer.id = material_type.manufacturer_id
LEFT JOIN HIS_SERVICE_UNIT service_unit on service_unit.id = material_type.tdl_service_unit_id
LEFT JOIN HIS_SERVICE service on service.id = material.tdl_service_id
LEFT JOIN HIS_SERVICE_TYPE service_type on service_type.id = service.service_type_id
)
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_THUOC_VAT_TU_TU_MUA");
    }
};
