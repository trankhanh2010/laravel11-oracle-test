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
CREATE OR REPLACE VIEW XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT AS
SELECT
     service_req.id as key,
     service_req.id,
     service_req.is_active,
     service_req.is_delete,
     service_req.is_no_execute,
     service_req_stt.service_req_stt_code,
     service_req_stt.service_req_stt_name,
     service_req.treatment_id,
     service_req.tdl_patient_name,
     service_req.tdl_patient_dob,
     service_req.tdl_patient_code,
     treatment.treatment_code,
     service_req.service_req_code,
     service_req.TDL_PATIENT_GENDER_NAME,
     request_department.department_code as request_department_code,
     request_department.department_name as request_department_name,
     request_room.room_code as request_room_code,
     request_room.room_name as request_room_name,
     service_req.intruction_time,
     service_req.intruction_date,
     service_req.start_time,
     service_req.BLOCK,
     patient_classify.patient_classify_code,
     patient_classify.patient_classify_name,
     service_req.finish_time,
     patient_type.patient_type_code,
     patient_type.patient_type_name,
     machine.machine_code,
     machine.machine_name,
     service_req.execute_room_id,
     service_req.TDL_TREATMENT_TYPE_ID,
     bed.bed_code,
     bed.bed_name,
     service_req.note,
     service_req.exam_end_type, /*Loai xu tri ket thuc kham. 1: Kham them, 2: Nhap vien, 3: Ket thuc dieu tri, 4: Ket thuc kham */
     service_req.CALL_COUNT,
     service_req.IS_NOT_IN_DEBT,
     service_req.IS_ENOUGH_SUBCLINICAL_PRES,
     service_req.tdl_ksk_contract_id,
     service_req.tdl_service_ids,

     service_req.DHST_ID,
     service_req.HOSPITALIZATION_REASON,
     service_req.NEXT_TREATMENT_INSTRUCTION,
     service_req.PATHOLOGICAL_PROCESS,
     service_req.PATHOLOGICAL_HISTORY,
     service_req.PROVISIONAL_DIAGNOSIS,
     service_req.SUBCLINICAL,
     service_req.FULL_EXAM,
     service_req.PART_EXAM,
     service_req.icd_code,
     service_req.icd_name,
     service_req.icd_cause_code,
     service_req.icd_cause_name,
     service_req.icd_sub_code,
     service_req.icd_text,
     service_req.num_order,
     service_req.priority

FROM his_service_req service_req
LEFT JOIN HIS_TREATMENT treatment ON treatment.id = service_req.treatment_id
LEFT JOIN HIS_SERVICE_REQ_STT service_req_stt ON service_req_stt.id = service_req.service_req_stt_id
LEFT JOIN HIS_DEPARTMENT request_department ON request_department.id = service_req.request_department_id
LEFT JOIN V_HIS_ROOM request_room ON request_room.id = service_req.request_room_id
LEFT JOIN HIS_PATIENT_CLASSIFY patient_classify ON patient_classify.id = service_req.TDL_PATIENT_CLASSIFY_ID
LEFT JOIN HIS_PATIENT_TYPE patient_type ON patient_type.id = service_req.TDL_PATIENT_TYPE_ID
LEFT JOIN HIS_MACHINE machine ON machine.id = service_req.machine_id
LEFT JOIN HIS_TREATMENT_BED_ROOM treatment_bed_room ON treatment_bed_room.treatment_id = treatment.id AND remove_time IS NULL
LEFT JOIN HIS_BED bed ON bed.id = treatment_bed_room.bed_id
SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT");
    }
};
