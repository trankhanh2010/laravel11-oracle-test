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
exp_mest.id as exp_mest_id,
exp_mest.EXP_MEST_CODE, -- mã xuất
exp_mest_material.tdl_material_type_id as m_type_id,
material_type.material_type_code as m_type_code,
material_type.material_type_name as m_type_name,
exp_mest_material.amount,
service_unit.service_unit_code,
service_unit.service_unit_name,
exp_mest_material.is_expend, -- hao phí
exp_mest_material.EXPEND_TYPE_ID, -- loại hao phí, hiện chỉ có hao phí tiền giường
exp_mest_material.IS_OUT_PARENT_FEE, -- chi phí ngoài gói  
exp_mest_material.other_pay_source_id,
exp_mest_material.EXCEED_LIMIT_IN_PRES_REASON,-- ly do ke thuoc/vat tu vuot qua so luong toi da/don  
exp_mest_material.EXCEED_LIMIT_IN_DAY_REASON,--    ly do ke thuoc/vat tu vuot qua so luong toi da/ngay  
null as ODD_PRES_REASON,--   ly do ke thuoc le  
null as OVER_RESULT_TEST_REASON,--  ly do ke thuoc vuot ket qua xet nghiem  
null as OVER_KIDNEY_REASON,--   Ly do ke thuoc vuot liêu theo chưc nang than  
null as EXCEED_LIMIT_IN_TREAT_REASON,--   ly do ke thuoc vuot qua so luong toi da/ho so 
exp_mest_type.exp_mest_type_code,
exp_mest_type.exp_mest_type_name,
exp_mest.service_req_id,
exp_mest.tdl_service_req_code,
exp_mest_medi_stock.id as exp_mest_medi_stock_id,
exp_mest_medi_stock.medi_stock_code as exp_mest_medi_stock_code, -- kho xuất của đơn
exp_mest_medi_stock.medi_stock_name as exp_mest_medi_stock_name,
--m_medi_stock.medi_stock_code as m_medi_stock_code, -- kho xuất của vật tư
--m_medi_stock.medi_stock_name as m_medi_stock_name,
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
exp_mest.icd_text,
exp_mest_material.num_order,
null as ACTIVE_INGR_BHYT_CODE,
null as ACTIVE_INGR_BHYT_NAME,
service_req.session_code,
null as concentra,
'VT' as service_type_code,
null as tutorial,
null as speed,    
null as description,    
null as day_count,    
null as morning,
null as noon,
null as afternoon,
null as evening,
material_type.service_id,
null as medicine_use_form_id,
exp_mest_material.price,
null as htu_id,
null as htu_ids,
exp_mest_material.pres_amount,
exp_mest.IS_NOT_TAKEN -- Đơn không lấy (phòng khám) => đơn không lấy thì k cho sửa đơn

FROM HIS_EXP_MEST_MATERIAL exp_mest_material     
LEFT JOIN HIS_EXP_MEST exp_mest on exp_mest.id = exp_mest_material.exp_mest_id and exp_mest.is_delete = 0
LEFT JOIN HIS_MATERIAL_TYPE material_type on material_type.id = exp_mest_material.tdl_material_type_id
LEFT JOIN HIS_SERVICE_UNIT service_unit on service_unit.id = material_type.tdl_service_unit_id
LEFT JOIN HIS_EXP_MEST_TYPE exp_mest_type on exp_mest_type.id = exp_mest.exp_mest_type_id
LEFT JOIN HIS_MEDI_STOCK exp_mest_medi_stock on exp_mest_medi_stock.id = exp_mest.medi_stock_id
--LEFT JOIN HIS_MEDI_STOCK m_medi_stock on m_medi_stock.id = exp_mest_material.tdl_medi_stock_id
LEFT JOIN V_HIS_ROOM req_room on req_room.id = exp_mest.req_room_id
LEFT JOIN HIS_SERVICE_REQ service_req on service_req.id = exp_mest.service_req_id

  UNION ALL

SELECT 
exp_mest_medicine.id,
'TH' as m_type,
'TH' || exp_mest_medicine.id AS key,
exp_mest_medicine.IS_ACTIVE,
exp_mest_medicine.IS_DELETE,
exp_mest.id as exp_mest_id,
exp_mest.EXP_MEST_CODE, -- mã xuất
exp_mest_medicine.tdl_medicine_type_id as m_type_id,
medicine_type.medicine_type_code as m_type_code,
medicine_type.medicine_type_name as m_type_name,
exp_mest_medicine.amount,
service_unit.service_unit_code,
service_unit.service_unit_name,
exp_mest_medicine.is_expend, -- hao phí
exp_mest_medicine.EXPEND_TYPE_ID, -- loại hao phí, hiện chỉ có hao phí tiền giường
exp_mest_medicine.IS_OUT_PARENT_FEE, -- chi phí ngoài gói  
exp_mest_medicine.other_pay_source_id,
exp_mest_medicine.EXCEED_LIMIT_IN_PRES_REASON,-- ly do ke thuoc/vat tu vuot qua so luong toi da/don  
exp_mest_medicine.EXCEED_LIMIT_IN_DAY_REASON,--    ly do ke thuoc/vat tu vuot qua so luong toi da/ngay  
exp_mest_medicine.ODD_PRES_REASON,--   ly do ke thuoc le  
exp_mest_medicine.OVER_RESULT_TEST_REASON,--  ly do ke thuoc vuot ket qua xet nghiem  
exp_mest_medicine.OVER_KIDNEY_REASON,--   Ly do ke thuoc vuot liêu theo chưc nang than  
exp_mest_medicine.EXCEED_LIMIT_IN_TREAT_REASON,--   ly do ke thuoc vuot qua so luong toi da/ho so 
exp_mest_type.exp_mest_type_code,
exp_mest_type.exp_mest_type_name,
exp_mest.service_req_id,
exp_mest.tdl_service_req_code,
exp_mest_medi_stock.id as exp_mest_medi_stock_id,
exp_mest_medi_stock.medi_stock_code as exp_mest_medi_stock_code, -- kho xuất của đơn
exp_mest_medi_stock.medi_stock_name as exp_mest_medi_stock_name,
--m_medi_stock.medi_stock_code as m_medi_stock_code, -- kho xuất của thuốc
--m_medi_stock.medi_stock_name as m_medi_stock_name,
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
exp_mest.icd_text,
exp_mest_medicine.num_order,
medicine_type.ACTIVE_INGR_BHYT_CODE,
medicine_type.ACTIVE_INGR_BHYT_NAME,
service_req.session_code,
medicine_type.concentra,
'TH' as service_type_code,
exp_mest_medicine.tutorial,   
exp_mest_medicine.speed, 
exp_mest_medicine.description,    
exp_mest_medicine.day_count,    
exp_mest_medicine.morning,
exp_mest_medicine.noon,
exp_mest_medicine.afternoon,
exp_mest_medicine.evening,
medicine_type.service_id,
medicine_type.medicine_use_form_id,
exp_mest_medicine.price,
exp_mest_medicine.htu_id,
exp_mest_medicine.htu_ids,
exp_mest_medicine.pres_amount,
exp_mest.IS_NOT_TAKEN -- Đơn không lấy (phòng khám) => đơn không lấy thì k cho sửa đơn

FROM HIS_EXP_MEST_MEDICINE exp_mest_medicine     
LEFT JOIN HIS_EXP_MEST exp_mest on exp_mest.id = exp_mest_medicine.exp_mest_id and exp_mest.is_delete = 0
LEFT JOIN HIS_MEDICINE_TYPE medicine_type on medicine_type.id = exp_mest_medicine.tdl_medicine_type_id
LEFT JOIN HIS_SERVICE_UNIT service_unit on service_unit.id = medicine_type.tdl_service_unit_id
LEFT JOIN HIS_EXP_MEST_TYPE exp_mest_type on exp_mest_type.id = exp_mest.exp_mest_type_id
LEFT JOIN HIS_MEDI_STOCK exp_mest_medi_stock on exp_mest_medi_stock.id = exp_mest.medi_stock_id
--LEFT JOIN HIS_MEDI_STOCK m_medi_stock on m_medi_stock.id = exp_mest_medicine.tdl_medi_stock_id
LEFT JOIN V_HIS_ROOM req_room on req_room.id = exp_mest.req_room_id
LEFT JOIN HIS_SERVICE_REQ service_req on service_req.id = exp_mest.service_req_id

  UNION ALL

SELECT 
service_req_mety.id,
'REQ_TH' as m_type,
'REQ_TH' || service_req_mety.id AS key,
service_req_mety.IS_ACTIVE,
service_req_mety.IS_DELETE,
null as exp_mest_id,
null as EXP_MEST_CODE, -- mã xuất
service_req_mety.medicine_type_id as m_type_id,
medicine_type.medicine_type_code as m_type_code,
medicine_type.medicine_type_name as m_type_name,
service_req_mety.amount,
null as service_unit_code,
service_req_mety.unit_name as service_unit_name,
null as is_expend, -- hao phí
null as EXPEND_TYPE_ID, -- loại hao phí, hiện chỉ có hao phí tiền giường
null as IS_OUT_PARENT_FEE, -- chi phí ngoài gói  
null as other_pay_source_id,
service_req_mety.EXCEED_LIMIT_IN_PRES_REASON,-- ly do ke thuoc/vat tu vuot qua so luong toi da/don  
service_req_mety.EXCEED_LIMIT_IN_DAY_REASON,--    ly do ke thuoc/vat tu vuot qua so luong toi da/ngay  
service_req_mety.ODD_PRES_REASON,--   ly do ke thuoc le  
service_req_mety.OVER_RESULT_TEST_REASON,--  ly do ke thuoc vuot ket qua xet nghiem  
service_req_mety.OVER_KIDNEY_REASON,--   Ly do ke thuoc vuot liêu theo chưc nang than  
service_req_mety.EXCEED_LIMIT_IN_TREAT_REASON,--   ly do ke thuoc vuot qua so luong toi da/ho so 
null as exp_mest_type_code,
null as exp_mest_type_name,
service_req_mety.service_req_id,
service_req.service_req_code as tdl_service_req_code,
null as exp_mest_medi_stock_id,
null as exp_mest_medi_stock_code, -- kho xuất của đơn
null as exp_mest_medi_stock_name,
--null as m_medi_stock_code, -- kho xuất của thuốc
--null as m_medi_stock_name,
service_req.intruction_time as tdl_intruction_time,
service_req.intruction_date as tdl_intruction_date,
service_req.TDL_PATIENT_ID,
req_room.room_code as req_room_code,
req_room.room_name as req_room_name,
service_req.request_loginname as req_loginname,
service_req.request_username as req_username,
service_req.icd_code,
service_req.icd_name,
service_req.icd_sub_code,
service_req.icd_text,
service_req_mety.num_order,
medicine_type.ACTIVE_INGR_BHYT_CODE,
medicine_type.ACTIVE_INGR_BHYT_NAME,
service_req.session_code,
medicine_type.concentra,
'TH' as service_type_code,
service_req_mety.tutorial, 
service_req_mety.speed,   
null as description,    
service_req_mety.day_count,    
service_req_mety.morning,
service_req_mety.noon,
service_req_mety.afternoon,
service_req_mety.evening,
medicine_type.service_id,
medicine_type.medicine_use_form_id,
service_req_mety.price,
service_req_mety.htu_id,
null as htu_ids,
service_req_mety.pres_amount,
null as IS_NOT_TAKEN -- Đơn không lấy (phòng khám) => đơn không lấy thì k cho sửa đơn

FROM HIS_SERVICE_REQ_METY service_req_mety     
LEFT JOIN HIS_MEDICINE_TYPE medicine_type on medicine_type.id = service_req_mety.medicine_type_id
LEFT JOIN HIS_SERVICE_REQ service_req on service_req.id = service_req_mety.service_req_id
LEFT JOIN V_HIS_ROOM req_room on req_room.id = service_req.request_room_id


  UNION ALL

SELECT 
service_req_maty.id,
'REQ_TH' as m_type,
'REQ_TH' || service_req_maty.id AS key,
service_req_maty.IS_ACTIVE,
service_req_maty.IS_DELETE,
null as exp_mest_id,
null as EXP_MEST_CODE, -- mã xuất
service_req_maty.material_type_id as m_type_id,
material_type.material_type_code as m_type_code,
material_type.material_type_name as m_type_name,
service_req_maty.amount,
null as service_unit_code,
service_req_maty.unit_name as service_unit_name,
null as is_expend, -- hao phí
null as EXPEND_TYPE_ID, -- loại hao phí, hiện chỉ có hao phí tiền giường
null as IS_OUT_PARENT_FEE, -- chi phí ngoài gói  
null as other_pay_source_id,
service_req_maty.EXCEED_LIMIT_IN_PRES_REASON,-- ly do ke thuoc/vat tu vuot qua so luong toi da/don  
service_req_maty.EXCEED_LIMIT_IN_DAY_REASON,--    ly do ke thuoc/vat tu vuot qua so luong toi da/ngay  
null as ODD_PRES_REASON,--   ly do ke thuoc le  
null as OVER_RESULT_TEST_REASON,--  ly do ke thuoc vuot ket qua xet nghiem  
null as OVER_KIDNEY_REASON,--   Ly do ke thuoc vuot liêu theo chưc nang than  
null as EXCEED_LIMIT_IN_TREAT_REASON,--   ly do ke thuoc vuot qua so luong toi da/ho so 
null as exp_mest_type_code,
null as exp_mest_type_name,
service_req_maty.service_req_id,
service_req.service_req_code as tdl_service_req_code,
null as exp_mest_medi_stock_id,
null as exp_mest_medi_stock_code, -- kho xuất của đơn
null as exp_mest_medi_stock_name,
--null as m_medi_stock_code, -- kho xuất của thuốc
--null as m_medi_stock_name,
service_req.intruction_time as tdl_intruction_time,
service_req.intruction_date as tdl_intruction_date,
service_req.TDL_PATIENT_ID,
req_room.room_code as req_room_code,
req_room.room_name as req_room_name,
service_req.request_loginname as req_loginname,
service_req.request_username as req_username,
service_req.icd_code,
service_req.icd_name,
service_req.icd_sub_code,
service_req.icd_text,
service_req_maty.num_order,
null as ACTIVE_INGR_BHYT_CODE,
null as ACTIVE_INGR_BHYT_NAME,
service_req.session_code,
null as concentra,
'VT' as service_type_code,
null as tutorial,   
null as speed, 
null as description,    
null as day_count,    
null as morning,
null as noon,
null as afternoon,
null as evening,
material_type.service_id,
null as medicine_use_form_id,
service_req_maty.price,
null as htu_id,
null as htu_ids,
service_req_maty.pres_amount,
null as IS_NOT_TAKEN -- Đơn không lấy (phòng khám) => đơn không lấy thì k cho sửa đơn

FROM HIS_SERVICE_REQ_MATY service_req_maty     
LEFT JOIN HIS_MATERIAL_TYPE material_type on material_type.id = service_req_maty.material_type_id
LEFT JOIN HIS_SERVICE_REQ service_req on service_req.id = service_req_maty.service_req_id
LEFT JOIN V_HIS_ROOM req_room on req_room.id = service_req.request_room_id
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
