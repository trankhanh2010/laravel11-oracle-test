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
CREATE OR REPLACE VIEW XA_V_HIS_DON AS
(
SELECT 
exp_mest_material.id,
'VT' as m_type,
'VT' || exp_mest_material.id AS key,
exp_mest_material.IS_ACTIVE,
exp_mest_material.IS_DELETE,
exp_mest.EXP_MEST_CODE, -- mã xuất
material_type.material_type_code as m_type_code,
material_type.material_type_name as m_type_name,
exp_mest_material.amount,
service_unit.service_unit_code,
service_unit.service_unit_name,
null as tutorial,
exp_mest_type.exp_mest_type_code,
exp_mest_type.exp_mest_type_name,
exp_mest.service_req_id,
exp_mest.tdl_service_req_code,
exp_mest_medi_stock.medi_stock_code as exp_mest_medi_stock_code, -- kho xuất của đơn
exp_mest_medi_stock.medi_stock_name as exp_mest_medi_stock_name,
m_medi_stock.medi_stock_code as m_medi_stock_code, -- kho xuất của vật tư
m_medi_stock.medi_stock_name as m_medi_stock_name,
exp_mest.tdl_intruction_time,
exp_mest.tdl_intruction_date,
exp_mest.TDL_PATIENT_ID,
req_room.room_code as req_room_code,
req_room.room_name as req_room_name,
exp_mest.req_loginname,
exp_mest.req_username,
exp_mest.icd_code,
exp_mest.icd_name,
exp_mest.icd_sub_code,
exp_mest.icd_text   

FROM HIS_EXP_MEST_MATERIAL exp_mest_material     
LEFT JOIN HIS_EXP_MEST exp_mest on exp_mest.id = exp_mest_material.exp_mest_id and exp_mest.is_delete = 0
LEFT JOIN HIS_MATERIAL_TYPE material_type on material_type.id = exp_mest_material.tdl_material_type_id
LEFT JOIN HIS_SERVICE_UNIT service_unit on service_unit.id = material_type.tdl_service_unit_id
LEFT JOIN HIS_EXP_MEST_TYPE exp_mest_type on exp_mest_type.id = exp_mest.exp_mest_type_id
LEFT JOIN HIS_MEDI_STOCK exp_mest_medi_stock on exp_mest_medi_stock.id = exp_mest.medi_stock_id
LEFT JOIN HIS_MEDI_STOCK m_medi_stock on m_medi_stock.id = exp_mest_material.tdl_medi_stock_id
LEFT JOIN V_HIS_ROOM req_room on req_room.id = exp_mest.req_room_id

  UNION ALL

SELECT 
exp_mest_medicine.id,
'TH' as m_type,
'TH' || exp_mest_medicine.id AS key,
exp_mest_medicine.IS_ACTIVE,
exp_mest_medicine.IS_DELETE,
exp_mest.EXP_MEST_CODE, -- mã xuất
medicine_type.medicine_type_code as m_type_code,
medicine_type.medicine_type_name as m_type_name,
exp_mest_medicine.amount,
service_unit.service_unit_code,
service_unit.service_unit_name,
medicine_type.tutorial,
exp_mest_type.exp_mest_type_code,
exp_mest_type.exp_mest_type_name,
exp_mest.service_req_id,
exp_mest.tdl_service_req_code,
exp_mest_medi_stock.medi_stock_code as exp_mest_medi_stock_code, -- kho xuất của đơn
exp_mest_medi_stock.medi_stock_name as exp_mest_medi_stock_name,
m_medi_stock.medi_stock_code as m_medi_stock_code, -- kho xuất của thuốc
m_medi_stock.medi_stock_name as m_medi_stock_name,
exp_mest.tdl_intruction_time,
exp_mest.tdl_intruction_date,
exp_mest.TDL_PATIENT_ID,
req_room.room_code as req_room_code,
req_room.room_name as req_room_name,
exp_mest.req_loginname,
exp_mest.req_username,
exp_mest.icd_code,
exp_mest.icd_name,
exp_mest.icd_sub_code,
exp_mest.icd_text   

FROM HIS_EXP_MEST_MEDICINE exp_mest_medicine     
LEFT JOIN HIS_EXP_MEST exp_mest on exp_mest.id = exp_mest_medicine.exp_mest_id and exp_mest.is_delete = 0
LEFT JOIN HIS_MEDICINE_TYPE medicine_type on medicine_type.id = exp_mest_medicine.tdl_medicine_type_id
LEFT JOIN HIS_SERVICE_UNIT service_unit on service_unit.id = medicine_type.tdl_service_unit_id
LEFT JOIN HIS_EXP_MEST_TYPE exp_mest_type on exp_mest_type.id = exp_mest.exp_mest_type_id
LEFT JOIN HIS_MEDI_STOCK exp_mest_medi_stock on exp_mest_medi_stock.id = exp_mest.medi_stock_id
LEFT JOIN HIS_MEDI_STOCK m_medi_stock on m_medi_stock.id = exp_mest_medicine.tdl_medi_stock_id
LEFT JOIN V_HIS_ROOM req_room on req_room.id = exp_mest.req_room_id

)
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_DON");
    }
};
