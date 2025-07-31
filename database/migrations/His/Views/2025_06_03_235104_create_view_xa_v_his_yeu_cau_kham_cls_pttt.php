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

    CASE 
        WHEN service_req_stt.service_req_stt_code = '01' THEN 
            CASE 
                WHEN service_req.call_count >= 1 THEN 'Gọi nhỡ'
                ELSE 'Chưa xử lý'
            END
        WHEN service_req_stt.service_req_stt_code = '02' THEN 'Đang xử lý'
        WHEN service_req_stt.service_req_stt_code <> '03' AND (service_req.finish_time IS NULL OR treatment.treatment_end_type_id IS NULL)
            THEN 'Chưa kết thúc'
        ELSE 'Kết thúc' -- ngược lại thì kết thúc
    END AS yeu_cau_kham_cls_status_text, -- trạng thái của yêu cầu khám cls

    CASE 
        WHEN service_req.call_count >= 1 THEN 1
        ELSE 0
    END AS goi_nho,

     service_req.id as key,
     service_req.id,
     service_req.is_active,
     service_req.is_delete,
     service_req.is_no_execute,
     service_req.service_req_stt_id,
     service_req_stt.service_req_stt_code,
     service_req_stt.service_req_stt_name,
     service_req.treatment_id,
     service_req.tdl_patient_name,
     service_req.tdl_patient_dob,
     service_req.tdl_patient_code,
     service_req.tdl_treatment_code,
     service_req.tdl_treatment_code as treatment_code,
     service_req.tdl_patient_id as patient_id,
     service_req.service_req_type_id,
     service_req.parent_id,
     service_req_type.service_req_type_code,
     service_req_type.service_req_type_name,
     service_req.service_req_code,
     service_req.TDL_PATIENT_GENDER_NAME,
     request_department.department_code as request_department_code,
     request_department.department_name as request_department_name,
     request_room.room_code as request_room_code,
     request_room.room_name as request_room_name,
     service_req.execute_department_id,
     execute_room.execute_room_code,
     execute_room.execute_room_name,
     service_req.intruction_time,
     service_req.intruction_date,
     service_req.start_time,
     service_req.BLOCK,
     patient_classify.patient_classify_code,
     patient_classify.patient_classify_name,
     service_req.finish_time,
     service_req.tdl_patient_type_id,
     patient_type.patient_type_code,
     patient_type.patient_type_name,
     machine.machine_code,
     machine.machine_name,
     service_req.execute_room_id,
     service_req.TDL_TREATMENT_TYPE_ID,
     bed.bed_code,
     bed.bed_name,
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
     service_req.PATHOLOGICAL_HISTORY_FAMILY,
     service_req.PROVISIONAL_DIAGNOSIS,
     service_req.SUBCLINICAL,
     service_req.icd_code,
     service_req.icd_name,
     service_req.icd_cause_code,
     service_req.icd_cause_name,
     service_req.icd_sub_code,
     service_req.icd_text,
     service_req.num_order,
     service_req.priority,

     service_req.sick_day,
     service_req.patient_case_id,
     service_req.FULL_EXAM, 
     service_req.PART_EXAM, 
     service_req.PART_EXAM_CIRCULATION, 
     service_req.PART_EXAM_RESPIRATORY,
     service_req.PART_EXAM_DIGESTION, 
     service_req.PART_EXAM_KIDNEY_UROLOGY, 
     service_req.PART_EXAM_NEUROLOGICAL, 
     service_req.PART_EXAM_MUSCLE_BONE, 
     service_req.PART_EXAM_ENT, 
     service_req.PART_EXAM_EAR,
     service_req.PART_EXAM_NOSE,
     service_req.PART_EXAM_THROAT,
     service_req.PART_EXAM_EAR_RIGHT_NORMAL, 
     service_req.PART_EXAM_EAR_RIGHT_WHISPER, 
     service_req.PART_EXAM_EAR_LEFT_NORMAL, 
     service_req.PART_EXAM_EAR_LEFT_WHISPER, 
     service_req.PART_EXAM_STOMATOLOGY, 
     service_req.PART_EXAM_UPPER_JAW, 
     service_req.PART_EXAM_LOWER_JAW, 
     service_req.PART_EXAM_EYE,
     service_req.PART_EXAM_EYE_BLIND_COLOR, 
     service_req.PART_EXAM_EYE_ST_PLUS, 
     service_req.PART_EXAM_EYE_ST_MINUS, 
     service_req.PART_EXAM_EYE_TENSION, 
     service_req.PART_EXAM_EYE_TENSION_RIGHT, 
     service_req.PART_EXAM_EYESIGHT_RIGHT, 
     service_req.PART_EXAM_HOLE_GLASS_RIGHT, 
     service_req.PART_EXAM_EYE_COUNT_FINGER, 
     service_req.PART_EXAM_EYE_TENSION_LEFT, 
     service_req.PART_EXAM_EYESIGHT_LEFT, 
     service_req.PART_EXAM_HOLE_GLASS_LEFT, 
     service_req.PART_EXAM_HORIZONTAL_SIGHT, 
     service_req.PART_EXAM_VERTICAL_SIGHT, 
     service_req.PART_EYE_GLASS_OLD_SPH_RIGHT, 
     service_req.PART_EYE_GLASS_OLD_SPH_LEFT, 
     service_req.PART_EYE_GLASS_SPH_RIGHT, 
     service_req.PART_EYE_GLASS_SPH_LEFT, 
     service_req.PART_EYE_GLASS_OLD_CYL_RIGHT, 
     service_req.PART_EYE_GLASS_OLD_CYL_LEFT, 
     service_req.PART_EYE_GLASS_CYL_RIGHT, 
     service_req.PART_EYE_GLASS_CYL_LEFT, 
     service_req.PART_EYE_GLASS_OLD_AXE_RIGHT, 
     service_req.PART_EYE_GLASS_OLD_AXE_LEFT, 
     service_req.PART_EYE_GLASS_AXE_RIGHT, 
     service_req.PART_EYE_GLASS_AXE_LEFT, 
     service_req.PART_EYESIGHT_GLASS_OLD_RIGHT, 
     service_req.PART_EYESIGHT_GLASS_OLD_LEFT, 
     service_req.PART_EXAM_EYESIGHT_GLASS_RIGHT, 
     service_req.PART_EXAM_EYESIGHT_GLASS_LEFT, 
     service_req.PART_EYE_GLASS_OLD_KCDT_RIGHT, 
     service_req.PART_EYE_GLASS_OLD_KCDT_LEFT,
     service_req.PART_EYE_GLASS_KCDT_RIGHT, 
     service_req.PART_EYE_GLASS_KCDT_LEFT, 
     service_req.PART_EYE_GLASS_OLD_ADD_RIGHT, 
     service_req.PART_EYE_GLASS_OLD_ADD_LEFT, 
     service_req.PART_EYE_GLASS_ADD_RIGHT, 
     service_req.PART_EYE_GLASS_ADD_LEFT, 
     service_req.PART_EXAM_OEND, 
     service_req.PART_EXAM_MENTAL, 
     service_req.PART_EXAM_NUTRITION, 
     service_req.PART_EXAM_MOTION, 
     service_req.PART_EXAM_OBSTETRIC, 
     service_req.PART_EXAM_DERMATOLOGY, 
     service_req.TREATMENT_INSTRUCTION, 
     service_req.NOTE, 
     service_req.NEXT_TREAT_INTR_CODE, 
     service_req.health_exam_rank_id,
     service_req.is_main_exam,
     service_req.tdl_hein_card_number,
     service_req.is_auto_finished,
     service_req.is_wait_child,
     service_req.treatment_type_id, -- treatment_type của y lệnh lúc lọc ở ngoài, còn treatment_type để hiện tracking phải join từ bảng treatment
     service_req.IS_KIDNEY, -- chạy thận
     service_req.tracking_id,
     treatment.treatment_end_type_id,
     service_req.advise       

FROM his_service_req service_req
LEFT JOIN HIS_TREATMENT treatment ON treatment.id = service_req.treatment_id
LEFT JOIN HIS_SERVICE_REQ_STT service_req_stt ON service_req_stt.id = service_req.service_req_stt_id
LEFT JOIN HIS_DEPARTMENT request_department ON request_department.id = service_req.request_department_id
LEFT JOIN V_HIS_ROOM request_room ON request_room.id = service_req.request_room_id
LEFT JOIN HIS_EXECUTE_ROOM execute_room ON execute_room.room_id = service_req.execute_room_id
LEFT JOIN HIS_PATIENT_CLASSIFY patient_classify ON patient_classify.id = service_req.TDL_PATIENT_CLASSIFY_ID
LEFT JOIN HIS_PATIENT_TYPE patient_type ON patient_type.id = service_req.TDL_PATIENT_TYPE_ID
LEFT JOIN HIS_MACHINE machine ON machine.id = service_req.machine_id
LEFT JOIN HIS_TREATMENT_BED_ROOM treatment_bed_room ON treatment_bed_room.treatment_id = service_req.treatment_id AND remove_time IS NULL
LEFT JOIN HIS_BED bed ON bed.id = treatment_bed_room.bed_id
LEFT JOIN HIS_SERVICE_REQ_TYPE service_req_type ON service_req_type.id = service_req.service_req_type_id

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
