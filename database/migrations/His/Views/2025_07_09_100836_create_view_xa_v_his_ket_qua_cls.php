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
CREATE OR REPLACE VIEW HIS_RS.XA_V_HIS_KET_QUA_CLS AS
(
SELECT 
    ext.id,
    service_req.intruction_date,
    service_req.intruction_time,
    ext.tdl_treatment_id,
    ext.conclude as ket_qua,
    ext.description as ghi_chu,
    service.service_code,
    service.service_name,
    null as parent_id,
    service_type.service_type_code,
    service_type.service_type_name,
    null as test_index_num_order,
    null as ma_chi_so,
    null as ten_chi_so,
    null as is_important,
    1 as is_leaf   

FROM HIS_SERE_SERV_EXT ext     
LEFT JOIN HIS_SERE_SERV sere_serv ON ext.sere_serv_id = sere_serv.id
LEFT JOIN HIS_SERVICE service ON sere_serv.service_id = service.id
JOIN HIS_SERVICE_TYPE service_type ON service.service_type_id = service_type.id AND service_type_code in ('XN', 'HA', 'TT', 'CN', 'NS', 'SA', 'GB') -- chỉ join những loại dịch vụ này
LEFT JOIN HIS_SERVICE_REQ service_req ON sere_serv.service_req_id = service_req.id
JOIN HIS_SERVICE_REQ_STT service_req_stt ON service_req.service_req_stt_id = service_req_stt.id AND service_req_stt.service_req_stt_code in ('02', '03') -- join y lệnh đang thực hiện và đã hoàn thành

WHERE 
ext.is_delete = 0 -- chỉ lấy kết quả chưa bị xóa
AND sere_serv.is_delete = 0

  UNION ALL

SELECT 
    tein.id,
    service_req.intruction_date,
    service_req.intruction_time,
    tein.tdl_treatment_id,
    tein.value as ket_qua,
    TO_CLOB(tein.description) as ghi_chu, -- ép sang clob để hợp
    service.service_code,
    service.service_name,
    service.parent_id,
    service_type.service_type_code,
    service_type.service_type_name,
    test_index.num_order as test_index_num_order,
    test_index.test_index_code as ma_chi_so,
    test_index.test_index_name as ten_chi_so,
    test_index.is_important,
    1 as is_leaf     

FROM HIS_SERE_SERV_TEIN tein     
LEFT JOIN HIS_SERE_SERV sere_serv ON tein.sere_serv_id = sere_serv.id
LEFT JOIN HIS_SERVICE service ON sere_serv.service_id = service.id
JOIN HIS_SERVICE_TYPE service_type ON service.service_type_id = service_type.id AND service_type_code in ('XN', 'HA', 'TT', 'CN', 'NS', 'SA', 'GB') -- chỉ join những loại dịch vụ này
LEFT JOIN HIS_TEST_INDEX test_index ON tein.test_index_id = test_index.id
LEFT JOIN HIS_SERVICE_REQ service_req ON sere_serv.service_req_id = service_req.id
JOIN HIS_SERVICE_REQ_STT service_req_stt ON service_req.service_req_stt_id = service_req_stt.id AND service_req_stt.service_req_stt_code in ('02', '03') -- join y lệnh đang thực hiện và đã hoàn thành

WHERE 
tein.is_delete = 0 -- chỉ lấy kết quả chưa bị xóa
AND sere_serv.is_delete = 0

)
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_KET_QUA_CLS");
    }
};
