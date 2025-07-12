<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\YeuCauKhamClsPtttVView;
use Illuminate\Support\Facades\DB;

class YeuCauKhamClsPtttVViewRepository
{
    protected $yeuCauKhamClsPtttVView;
    public function __construct(YeuCauKhamClsPtttVView $yeuCauKhamClsPtttVView)
    {
        $this->yeuCauKhamClsPtttVView = $yeuCauKhamClsPtttVView;
    }

    public function applyJoins()
    {
        return $this->yeuCauKhamClsPtttVView
            ->select(
                [
                    "key",
                    "id",
                    "yeu_cau_kham_cls_status_text",
                    "goi_nho",
                    "is_active",
                    "is_delete",
                    "is_no_execute",
                    "service_req_stt_code",
                    "service_req_stt_name",
                    "treatment_id",
                    "tdl_patient_name",
                    "tdl_patient_dob",
                    "tdl_patient_code",
                    "treatment_code",
                    "service_req_code",
                    "tdl_patient_gender_name",
                    "request_department_code",
                    "request_department_name",
                    "request_room_code",
                    "request_room_name",
                    "intruction_time",
                    "intruction_date",
                    "start_time",
                    "block",
                    "patient_classify_code",
                    "patient_classify_name",
                    "finish_time",
                    "patient_type_code",
                    "patient_type_name",
                    "machine_code",
                    "machine_name",
                    "execute_room_id",
                    "execute_room_code",
                    "execute_room_name",
                    "tdl_treatment_type_id",
                    "exam_end_type",
                    "bed_code",
                    "bed_name",
                    "call_count",
                    "is_not_in_debt",
                    "is_enough_subclinical_pres",
                    "tdl_ksk_contract_id",
                    "num_order",
                    "priority",
                    "is_wait_child",
                ]
            );
    }
    public function applyJoinsDataKhamBenh()
    {
        return $this->yeuCauKhamClsPtttVView
            ->leftJoin('his_dhst', 'his_dhst.id', '=', 'xa_v_his_yeu_cau_kham_cls_pttt.dhst_id')
            ->select(
                [
                    "xa_v_his_yeu_cau_kham_cls_pttt.id",
                    "xa_v_his_yeu_cau_kham_cls_pttt.treatment_id",
                    "xa_v_his_yeu_cau_kham_cls_pttt.execute_room_id",
                    "xa_v_his_yeu_cau_kham_cls_pttt.exam_end_type",
                    "xa_v_his_yeu_cau_kham_cls_pttt.note",
                    "xa_v_his_yeu_cau_kham_cls_pttt.HOSPITALIZATION_REASON",
                    "xa_v_his_yeu_cau_kham_cls_pttt.PATHOLOGICAL_PROCESS",
                    "xa_v_his_yeu_cau_kham_cls_pttt.PATHOLOGICAL_HISTORY",
                    "xa_v_his_yeu_cau_kham_cls_pttt.FULL_EXAM",
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM",
                    "his_dhst.PULSE",
                    "his_dhst.TEMPERATURE",
                    "his_dhst.BLOOD_PRESSURE_MAX",
                    "his_dhst.BLOOD_PRESSURE_MIN",
                    "his_dhst.BREATH_RATE",
                    "his_dhst.WEIGHT",
                    "his_dhst.HEIGHT",
                    "his_dhst.VIR_BMI",
                    "xa_v_his_yeu_cau_kham_cls_pttt.SUBCLINICAL",
                    "xa_v_his_yeu_cau_kham_cls_pttt.PROVISIONAL_DIAGNOSIS",
                    "xa_v_his_yeu_cau_kham_cls_pttt.NEXT_TREATMENT_INSTRUCTION",
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_code",
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_name",
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_cause_code",
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_cause_name",
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_sub_code",
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_text",
                ]
            );
    }
    public function applyJoinsLichSuKham()
    {
        return $this->yeuCauKhamClsPtttVView
            ->select(
                [
                    "xa_v_his_yeu_cau_kham_cls_pttt.id as key",
                    "xa_v_his_yeu_cau_kham_cls_pttt.id",
                    "xa_v_his_yeu_cau_kham_cls_pttt.intruction_time",
                    "xa_v_his_yeu_cau_kham_cls_pttt.finish_time",
                    "xa_v_his_yeu_cau_kham_cls_pttt.execute_room_code",
                    "xa_v_his_yeu_cau_kham_cls_pttt.execute_room_name",
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_code",
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_name",
                ]
            );
    }
    public function applyJoinsDotKhamHienTai()
    {
        return $this->yeuCauKhamClsPtttVView
            ->select(
                [
                    "xa_v_his_yeu_cau_kham_cls_pttt.id as key",
                    "xa_v_his_yeu_cau_kham_cls_pttt.id",
                    "xa_v_his_yeu_cau_kham_cls_pttt.service_req_stt_code",
                    "xa_v_his_yeu_cau_kham_cls_pttt.service_req_stt_name",
                    "xa_v_his_yeu_cau_kham_cls_pttt.intruction_time",
                    "xa_v_his_yeu_cau_kham_cls_pttt.execute_room_code",
                    "xa_v_his_yeu_cau_kham_cls_pttt.execute_room_name",
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_code",
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_name",
                ]
            );
    }
    public function applyJoinsLayDuLieu()
    {
        return $this->yeuCauKhamClsPtttVView
            ->leftJoin('his_dhst', 'his_dhst.id', '=', 'xa_v_his_yeu_cau_kham_cls_pttt.dhst_id')
            ->leftJoin('his_patient_case', 'his_patient_case.id', '=', 'xa_v_his_yeu_cau_kham_cls_pttt.patient_case_id')
            ->leftJoin('his_health_exam_rank', 'his_health_exam_rank.id', '=', 'xa_v_his_yeu_cau_kham_cls_pttt.health_exam_rank_id')
            ->leftJoin('his_treatment', 'his_treatment.id', '=', 'xa_v_his_yeu_cau_kham_cls_pttt.treatment_id')
            ->leftJoin('his_treatment_type', 'his_treatment_type.id', '=', 'his_treatment.tdl_treatment_type_id')
            ->leftJoin('his_patient_type', 'his_patient_type.id', '=', 'his_treatment.tdl_patient_type_id')
            ->leftJoin('his_sere_serv as sere_serv', function ($join) {
                $join->on('xa_v_his_yeu_cau_kham_cls_pttt.id', '=', 'sere_serv.service_req_id')
                    ->where('sere_serv.is_active', 1)
                    ->where('sere_serv.is_delete', 0)
                    ->where(function ($query) {
                        $query->where('sere_serv.is_no_execute', 0)
                            ->orWhereNull('sere_serv.is_no_execute');
                    });
            })
            ->select(
                [
                    "xa_v_his_yeu_cau_kham_cls_pttt.id",
                    "sere_serv.id as current_sere_serv_id",
                    "xa_v_his_yeu_cau_kham_cls_pttt.treatment_id",
                    "xa_v_his_yeu_cau_kham_cls_pttt.patient_id",
                    "xa_v_his_yeu_cau_kham_cls_pttt.execute_room_id",
                    "xa_v_his_yeu_cau_kham_cls_pttt.execute_room_code",
                    "xa_v_his_yeu_cau_kham_cls_pttt.execute_room_name",
                    "xa_v_his_yeu_cau_kham_cls_pttt.tdl_treatment_code",
                    "xa_v_his_yeu_cau_kham_cls_pttt.tdl_patient_name",
                    "xa_v_his_yeu_cau_kham_cls_pttt.tdl_patient_code",
                    "xa_v_his_yeu_cau_kham_cls_pttt.tdl_patient_dob",
                    "xa_v_his_yeu_cau_kham_cls_pttt.tdl_patient_gender_name",
                    "xa_v_his_yeu_cau_kham_cls_pttt.intruction_time",
                    "xa_v_his_yeu_cau_kham_cls_pttt.service_req_code",
                    "xa_v_his_yeu_cau_kham_cls_pttt.service_req_stt_code",
                    "xa_v_his_yeu_cau_kham_cls_pttt.service_req_stt_name",
                    "xa_v_his_yeu_cau_kham_cls_pttt.SICK_DAY", // vào ngày thứ mấy của bệnh
                    "xa_v_his_yeu_cau_kham_cls_pttt.patient_case_id",   // trường hợp bệnh

                    "his_patient_case.patient_case_code",
                    "his_patient_case.patient_case_name",

                    "xa_v_his_yeu_cau_kham_cls_pttt.HOSPITALIZATION_REASON", // lý do nhập viện
                    "xa_v_his_yeu_cau_kham_cls_pttt.PATHOLOGICAL_PROCESS", // quá trình bệnh lý
                    "xa_v_his_yeu_cau_kham_cls_pttt.PATHOLOGICAL_HISTORY", // tiền sử bệnh
                    "xa_v_his_yeu_cau_kham_cls_pttt.PATHOLOGICAL_HISTORY_FAMILY", // tiền sử bệnh gia đình

                    "his_dhst.id as dhst_id",
                    "his_dhst.EXECUTE_TIME", // thời gian
                    "his_dhst.PULSE",
                    "his_dhst.BLOOD_PRESSURE_MAX",
                    "his_dhst.BLOOD_PRESSURE_MIN",
                    "his_dhst.WEIGHT",
                    "his_dhst.HEIGHT",
                    "his_dhst.NOTE as dhst_note",
                    "his_dhst.VIR_BMI",
                    "his_dhst.SPO2",
                    "his_dhst.TEMPERATURE",
                    "his_dhst.BREATH_RATE",
                    "his_dhst.CHEST",
                    "his_dhst.BELLY",
                    "his_dhst.VIR_BODY_SURFACE_AREA",

                    "xa_v_his_yeu_cau_kham_cls_pttt.FULL_EXAM", // toàn thân
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM", // bộ phận chung chung
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_CIRCULATION", // tuần hoàn
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_RESPIRATORY", // hô hấp
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_DIGESTION", // tiêu hóa
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_KIDNEY_UROLOGY", // thận tiết niệu
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_NEUROLOGICAL", // thần kinh
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_MUSCLE_BONE", // cơ xương khớp
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_ENT", // tai mũi họng
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EAR", // tai
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_NOSE", // mũi
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_THROAT", // họng
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EAR_RIGHT_NORMAL", // tai phải nói thường
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EAR_RIGHT_WHISPER", // tai phải nói thầm
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EAR_LEFT_NORMAL", // tai trái nói thường
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EAR_LEFT_WHISPER", // tai trái nói thầm
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_STOMATOLOGY", // răng hàm mặt
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_UPPER_JAW", // hàm trên
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_LOWER_JAW", // hàm dưới
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EYE", // mắt
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EYE_BLIND_COLOR", // sắc giác  1: Binh thuong; 2: Mu mau toan bo; 3: Mu mau do; 4: Mu mau xanh la; 5: Mu mau vang 
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EYE_ST_PLUS", // ST+
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EYE_ST_MINUS", // ST-
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EYE_TENSION", // các đo nhãn áp
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EYE_TENSION_RIGHT", // nhãn áp mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EYESIGHT_RIGHT", // thị lực mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_HOLE_GLASS_RIGHT", // thị lực kính lỗ phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EYE_COUNT_FINGER", // đếm ngón tai
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EYE_TENSION_LEFT", // nhãn áp mắt trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EYESIGHT_LEFT", // thị lực mắt trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_HOLE_GLASS_LEFT", // thị lực kính lỗ trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_HORIZONTAL_SIGHT", // thị trường ngang 1: Binh thuong; 2: Han che 
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_VERTICAL_SIGHT", // thị trường đứng 1: Binh thuong; 2: Han che 
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_OLD_SPH_RIGHT", // SPH kính cũ mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_OLD_SPH_LEFT", // SPH kính cữ mắt trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_SPH_RIGHT", // SPH kính mới mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_SPH_LEFT", // SPH kính mới mắt trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_OLD_CYL_RIGHT", // CYL kính cũ mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_OLD_CYL_LEFT", // CYL kính cũ mắt trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_CYL_RIGHT", // CYL kính mới mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_CYL_LEFT", // CYL kính mới mắt trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_OLD_AXE_RIGHT", // AXE kính cũ mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_OLD_AXE_LEFT", // AXE kính cũ mắt trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_AXE_RIGHT", // AXE kính mới mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_AXE_LEFT", // AXE kính mới mắt trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYESIGHT_GLASS_OLD_RIGHT", // thị lực kính cũ mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYESIGHT_GLASS_OLD_LEFT", // thị lực kính mới mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EYESIGHT_GLASS_RIGHT", // thị lực kính mới mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_EYESIGHT_GLASS_LEFT", // thị lực kính mới mắt trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_OLD_KCDT_RIGHT", // KCDT kính cũ mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_OLD_KCDT_LEFT", // KCDT kính cũ mắt trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_KCDT_RIGHT", // KCDT kính mới mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_KCDT_LEFT", // KCDT kính mới mắt trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_OLD_ADD_RIGHT", // ADD kính cũ mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_OLD_ADD_LEFT", // ADD kính cũ mắt trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_ADD_RIGHT", // ADD kính mới mắt phải
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EYE_GLASS_ADD_LEFT", // ADD kính mới mắt trái
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_OEND", // nội tiết
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_MENTAL", // tâm thần
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_NUTRITION", // dinh dưỡng
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_MOTION", // vận động
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_OBSTETRIC", // sản phụ khoa
                    "xa_v_his_yeu_cau_kham_cls_pttt.PART_EXAM_DERMATOLOGY", // da liễu
                    "xa_v_his_yeu_cau_kham_cls_pttt.SUBCLINICAL", // tóm tắt cls
                    "xa_v_his_yeu_cau_kham_cls_pttt.TREATMENT_INSTRUCTION", // phương pháp điều trị
                    "xa_v_his_yeu_cau_kham_cls_pttt.PROVISIONAL_DIAGNOSIS", // chẩn đoán sơ bộ
                    "xa_v_his_yeu_cau_kham_cls_pttt.NOTE", // chú ý
                    "xa_v_his_yeu_cau_kham_cls_pttt.NEXT_TREAT_INTR_CODE", // mã hướng điều trị tiếp theo
                    "xa_v_his_yeu_cau_kham_cls_pttt.NEXT_TREATMENT_INSTRUCTION", // hướng điều trị tiếp theo
                    "xa_v_his_yeu_cau_kham_cls_pttt.health_exam_rank_id", // xếp loại khám sức khỏe
                    "his_health_exam_rank.health_exam_rank_code", // mã xếp loại khám sức khỏe
                    "his_health_exam_rank.health_exam_rank_name", // tên xếp loại khám sức khỏe

                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_code", // cd 9
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_name",
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_cause_code", // nguyên nhân ngoài
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_cause_name",
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_sub_code", // cd phụ 
                    "xa_v_his_yeu_cau_kham_cls_pttt.icd_text",
                    "xa_v_his_yeu_cau_kham_cls_pttt.is_main_exam",
                    "xa_v_his_yeu_cau_kham_cls_pttt.is_auto_finished",

                    "his_treatment_type.treatment_type_code", // Diện điều trị
                    "his_treatment_type.treatment_type_name",
                    "his_treatment.tdl_patient_type_id", // ĐTTT lấy theo treatment chứ k theo service_req
                    "his_patient_type.patient_type_code",
                    "his_patient_type.patient_type_name",

                    "his_treatment.tdl_hein_card_number",
                    "his_treatment.tdl_hein_card_from_time",
                    "his_treatment.tdl_hein_card_to_time",

                    "xa_v_his_yeu_cau_kham_cls_pttt.tracking_id",
                    "xa_v_his_yeu_cau_kham_cls_pttt.advise",
                ]
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        if ($keyword != null) {
            return $query->where(function ($query) use ($keyword) {
                $query->whereRaw("
                REGEXP_LIKE(
                    NLSSORT(tdl_patient_name, 'NLS_SORT=GENERIC_M_AI'),
                    NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                    'i'
                )
            ", [$keyword])
                    ->orWhere(('tdl_patient_code'), 'like', '%' . $keyword . '%')
                    ->orWhere(('treatment_code'), 'like', '%' . $keyword . '%')
                    ->orWhere(('service_req_code'), 'like', '%' . $keyword . '%');
            });
        }
        return $query;
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(('XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(('XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyIsNoExecuteFilter($query)
    {
        return $query->where(function ($query) {
            $query->where('XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT.is_no_execute', 0)
                ->orWhereNull('XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT.is_no_execute');
        });
    }
    public function applyIsYeuCauKhamPtttClsFilter($query)
    {
        return $query->where(function ($query) {
            $query->whereNotIn('XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT.service_req_type_code', ['DK', 'GI', 'DT', 'DN', 'DM']); // không lấy của y lệnh đơn với giường
        });
    }
    public function applyIntructionTimeFromFilter($query, $param)
    {
        if ($param != null) {
            return $query->where(function ($query) use ($param) {
                $query->where('intruction_time', '>=', $param);
            });
        }
        return $query;
    }
    public function applyIntructionTimeToFilter($query, $param)
    {
        if ($param != null) {
            return $query->where(function ($query) use ($param) {
                $query->where('intruction_time', '<=', $param);
            });
        }
        return $query;
    }
    public function applyExecuteRoomIdFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('execute_room_id'), $param);
        }
        return $query;
    }
    public function applyTreatmentTypeIdsFilter($query, $param)
    {
        if ($param != null) {
            $query->whereIn(('tdl_treatment_type_id'), $param);
        }
        return $query;
    }
    public function applyServiceIdsFilter($query, $param)
    {
        if (!empty($param)) {
            $query->whereHas('sereServs', function ($q) use ($param) {
                $q->whereIn('service_id', $param);
            });
        }

        return $query;
    }


    public function applyServiceReqCodeFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('service_req_code'), $param);
        }
        return $query;
    }
    public function applyBedCodeFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('bed_code'), $param);
        }
        return $query;
    }
    public function applyKskContractIdFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('tdl_ksk_contract_id'), $param);
        }
        return $query;
    }
    public function applyTrangThaiFilter($query, $param)
    {
        switch ($param) {
            case 'tatCa':
                return $query;
            case 'chuaKetThuc':
                return $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNull('finish_time')
                            ->orWhereNull('treatment_end_type_id');
                    })
                    ->where('service_req_stt_code', '<>', '03');
                });
            case 'chuaXuLy':
                return $query->where(function ($q) {
                    $q->where('service_req_stt_code', '01');
                });

            case 'dangXuLy':
                return $query->where(function ($q) {
                    $q->where('service_req_stt_code', '02');
                });
            case 'ketThuc':
                return $query
                    ->where(function ($q) {
                        $q->where(function ($sub) {
                            $sub->whereNotNull('finish_time')
                                ->whereNotNull('treatment_end_type_id');
                        })
                        ->orWhere('service_req_stt_code', '03');
                    });
            case 'goiNho':
                return $query->where(function ($q) {
                    $q->where('call_count', '>=', 1)
                    ->where('service_req_stt_code', '01');
                });
            default:
                return $query;
        }
    }
    public function applyTrangThaiVienPhiFilter($query, $param)
    {
        switch ($param) {
            case 'tatCa':
                return $query;
            case 'dangNoVienPhi':
                return $query->where(function ($q) {
                    $q->where('is_not_in_debt', 0)
                        ->orWhereNull('is_not_in_debt');
                });
            case 'khongNoVienPhi':
                return $query->where('is_not_in_debt', 1);
            default:
                return $query;
        }
    }

    public function applyTrangThaiKeThuocFilter($query, $param)
    {
        switch ($param) {
            case 'tatCa':
                return $query;
            case 'chuaKeDuThuocVTTH':
                return $query->where(function ($q) {
                    $q->where('is_enough_subclinical_pres', 0)
                        ->orWhereNull('is_enough_subclinical_pres');
                });
            case 'daKeDuThuocVTTH':
                return $query->where('is_enough_subclinical_pres', 1);
            default:
                return $query;
        }
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->yeuCauKhamClsPtttVView->find($id);
    }
}
