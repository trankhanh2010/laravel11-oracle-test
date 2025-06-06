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
                return $query->whereNull('finish_time');
            case 'chuaXuLy':
                return $query->where('service_req_stt_code', '01');
            case 'dangXuLy':
                return $query->where('service_req_stt_code', '02');
            case 'ketThuc':
                return $query->whereNotNull('finish_time');
            case 'goiNho':
                return $query->where('call_count', '>=', 1)
                ->where('service_req_stt_code', '01');
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
