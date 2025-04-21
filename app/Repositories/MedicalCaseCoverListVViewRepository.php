<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\MedicalCaseCoverListVView;
use Illuminate\Support\Facades\DB;

class MedicalCaseCoverListVViewRepository
{
    protected $medicalCaseCoverListVView;
    public function __construct(MedicalCaseCoverListVView $medicalCaseCoverListVView)
    {
        $this->medicalCaseCoverListVView = $medicalCaseCoverListVView;
    }

    public function applyJoins($tab = null)
    {
        if($tab == 'info') {
            return $this->medicalCaseCoverListVView
            ->select([
                'id',
                'treatment_id',
                'department_code',
                'patient_type_name',
                'treatment_code',
                'patient_id',               
                'icd_code',
                'icd_name',
                'icd_sub_code',
                'icd_text',
                'tdl_patient_code',
                'tdl_patient_name',
                'tdl_patient_dob',
                'tdl_patient_address',
                'tdl_patient_gender_name',            
                'tdl_patient_mobile',
                'tdl_patient_phone',
                'in_code',
                'last_department_code',
                'last_department_name',
                'bed_id',
                'in_time',
                'in_date',   
                'tdl_patient_relative_type',
                'tdl_patient_relative_name',            
            ]);
        }
        return $this->medicalCaseCoverListVView
            ->select([
                'id',
                'treatment_id',
                'department_code',
                'add_time',
                'remove_time',
                'patient_type_name',
                'in_department_name',
                'in_department_code',
                'last_department_code',
                'last_department_name',
                'treatment_end_type_code',
                'treatment_end_type_name',
                'treatment_result_code',
                'treatment_result_name',
                'death_cause_code',
                'death_cause_name',
                'death_within_code',
                'death_within_name',
                'tran_pati_form_name',
                'tran_pati_form_code',
                'total_pttt',
                'treatment_code',
                'patient_id',
                'is_pause',
                'icd_id__delete',
                'icd_code',
                'icd_name',
                'icd_sub_code',
                'icd_text',
                'icd_cause_code',
                'icd_cause_name',
                'in_time',
                'in_date',
                'clinical_in_time',
                'out_time',
                'in_icd_code',
                'in_icd_name',
                'in_icd_sub_code',
                'in_icd_text',
                'hospitalization_reason',
                'end_department_id',
                'end_code',
                'extra_end_code',
                'treatment_day_count',
                'appointment_time',
                'appointment_desc',
                'appointment_code',
                'out_date',
                'out_code',
                'tdl_hein_card_number',
                'medi_org_code',
                'medi_org_name',
                'tran_pati_form_id',
                'tran_pati_reason_id',
                'is_transfer_in',
                'transfer_in_medi_org_code',
                'transfer_in_medi_org_name',
                'transfer_in_code',
                'transfer_in_icd_id__delete',
                'transfer_in_icd_code',
                'transfer_in_icd_name',
                'transfer_in_cmkt',
                'transfer_in_form_id',
                'transfer_in_reason_id',
                'sick_leave_day',
                'sick_leave_from',
                'sick_leave_to',
                'death_time',
                'death_cause_id',
                'death_within_id',
                'death_place',
                'main_cause',
                'surgery',
                'tdl_hein_medi_org_code',
                'tdl_hein_medi_org_name',
                'tdl_patient_code',
                'tdl_patient_name',
                'tdl_patient_dob',
                'tdl_patient_address',
                'tdl_patient_gender_name',
                'tdl_patient_career_name',
                'tdl_patient_work_place',
                'tdl_patient_work_place_name',
                'tdl_patient_ethnic_name',
                'tdl_patient_relative_type',
                'tdl_patient_relative_name',
                'tdl_patient_mobile',
                'tdl_patient_phone',
                'tdl_hein_card_from_time',
                'tdl_hein_card_to_time',
                'tdl_patient_relative_address',
                'tdl_patient_relative_mobile',
                'tdl_patient_relative_phone',
                'tdl_patient_mother_name',
                'tdl_patient_father_name',
                'end_department_head_loginname',
                'end_department_head_username',
                'hospital_director_loginname',
                'hospital_director_username',
                'end_dept_subs_head_loginname',
                'end_dept_subs_head_username',
                'hosp_subs_director_loginname',
                'hosp_subs_director_username',
                'in_code',
            ]);
    }
    public function applyWithParam($query, $tab = null)
    {
        if($tab == 'info') return $query->with([
            'beds' => function ($query) {
                $query               
                ->where('his_bed.is_delete', 0)
                ->select([
                    'his_bed.id',
                    'his_bed.bed_room_id',
                    'his_bed.bed_name',
                    'his_bed.bed_code'
                ])                
                ->orderBy('his_treatment_bed_room.add_time', 'desc');
            },
            'beds.bedRoom:id,bed_room_code,bed_room_name',
        ]);
        return $query->with([
            'department_trans' => function ($query) {
                $query               
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->select([
                    'id',
                    'department_id',
                    'previous_id',
                    'department_in_time',
                    'request_time',
                    'treatment_id',
                    'is_hospitalized',
                ]);
            },
            'department_trans.department:id,department_name,department_code',
            'service_req_KH' => function ($query) {
                $query               
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->select([
                    'id',
                    'service_req_code',
                    'service_req_type_id',
                    'service_req_stt_id',
                    'treatment_id',
                    'intruction_time',
                    'intruction_date',
                    'request_loginname',
                    'request_username',
                    'execute_loginname',
                    'execute_username',
                    'start_time',
                    'finish_time',
                    'icd_code',
                    'icd_name',
                    'icd_sub_code',
                    'icd_text',
                    'icd_cause_code',
                    'icd_cause_name',
                    'hospitalization_reason',
                    'pathological_process',
                    'pathological_history',
                    'pathological_history_family',
                    'full_exam',
                    'part_exam',
                    'part_exam_circulation',
                    'part_exam_respiratory',
                    'part_exam_digestion',
                    'part_exam_kidney_urology',
                    'part_exam_neurological',
                    'part_exam_muscle_bone',
                    'part_exam_ent',
                    'part_exam_ear',
                    'part_exam_nose',
                    'part_exam_throat',
                    'part_exam_stomatology',
                    'part_exam_eye',
                    'part_exam_eye_tension_left',
                    'part_exam_eye_tension_right',
                    'part_exam_eyesight_left',
                    'part_exam_eyesight_right',
                    'part_exam_eyesight_glass_left',
                    'part_exam_eyesight_glass_right',
                    'part_exam_oend',
                    'sick_day',
                    'part_exam_mental',
                    'part_exam_obstetric',
                    'part_exam_nutrition',
                    'part_exam_motion',
                    'traditional_icd_code',
                    'traditional_icd_name',
                    'traditional_icd_sub_code',
                    'traditional_icd_text',
                    'tdl_ksk_is_required_approval',
                    'tdl_is_ksk_approve',
                    'treat_eye_tension_left',
                    'treat_eye_tension_right',
                    'treat_eyesight_left',
                    'treat_eyesight_right',
                    'treat_eyesight_glass_left',
                    'treat_eyesight_glass_right',
                    'is_first_optometrist',
                    'optometrist_time',
                    'foresight_right_eye',
                    'foresight_left_eye',
                    'foresight_right_glass_hole',
                    'foresight_left_glass_hole',
                    'foresight_right_using_glass',
                    'foresight_left_using_glass',
                    'refactometry_right_eye',
                    'refactometry_left_eye',
                    'before_light_reflection_right',
                    'before_light_reflection_left',
                    'after_light_reflection_right',
                    'after_light_reflection_left',
                    'ajustable_glass_foresight',
                    'ajustable_glass_foresight_r',
                    'ajustable_glass_foresight_l',
                    'nearsight_glass_right_eye',
                    'nearsight_glass_left_eye',
                    'nearsight_glass_reading_dist',
                    'nearsight_glass_pupil_dist',
                    'reoptometrist_appointment',
                    'foresight_using_glass_degree_r',
                    'foresight_using_glass_degree_l',
                    'result_approver_loginname',
                    'result_approver_username',
                    'exam_end_type',
                    'consultant_loginname',
                    'consultant_username',
                    'tdl_patient_classify_id',
                    'assigned_execute_loginname',
                    'assigned_execute_username',
                    'part_exam_ear_right_normal',
                    'part_exam_ear_right_whisper',
                    'part_exam_ear_left_normal',
                    'part_exam_ear_left_whisper',
                    'part_exam_upper_jaw',
                    'part_exam_lower_jaw',
                    'part_exam_horizontal_sight',
                    'part_exam_vertical_sight',
                    'part_exam_eye_blind_color',
                    'request_user_title',
                    'execute_user_title',
                    'appointment_time',
                    'appointment_desc',
                    'appointment_code',
                    'conclusion_clinical',
                    'conclusion_subclinical',
                    'occupational_disease',
                    'conclusion_consultation',
                    'exam_conclusion',
                    'conclusion',
                    'surgery_note',
                    'part_exam_hole_glass_left',
                    'part_exam_hole_glass_right',
                    'part_exam_eye_tension',
                    'part_exam_eye_st_plus',
                    'part_exam_eye_st_minus',
                    'part_exam_eye_count_finger',
                    'part_eye_glass_old_sph_left',
                    'part_eye_glass_old_sph_right',
                    'part_eye_glass_old_cyl_left',
                    'part_eye_glass_old_cyl_right',
                    'part_eye_glass_old_axe_left',
                    'part_eye_glass_old_axe_right',
                    'part_eyesight_glass_old_left',
                    'part_eyesight_glass_old_right',
                    'part_eye_glass_old_add_left',
                    'part_eye_glass_old_add_right',
                    'part_eye_glass_sph_left',
                    'part_eye_glass_sph_right',
                    'part_eye_glass_cyl_left',
                    'part_eye_glass_cyl_right',
                    'part_eye_glass_axe_left',
                    'part_eye_glass_axe_right',
                    'part_eye_glass_add_left',
                    'part_eye_glass_add_right',
                    'part_eye_glass_old_kcdt_left',
                    'part_eye_glass_old_kcdt_right',
                    'part_eye_glass_kcdt_left',
                    'part_eye_glass_kcdt_right',
                ]);
            },
            'dhsts'=> function ($query) {
                $query
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->select([
                    'id',
                    'treatment_id',
                    'dhst_sum_id',
                    'execute_time',
                    'temperature',
                    'breath_rate',
                    'weight',
                    'height',
                    'chest',
                    'belly',
                    'blood_pressure_max',
                    'blood_pressure_min',
                    'pulse',
                    'vir_bmi',
                    'vir_body_surface_area',
                    'spo2',
                    'capillary_blood_glucose',
                    'note',
                    'infution_into',
                    'infution_out',
                    'vaccination_exam_id',
                    'urine',
                ])
                ->orderBy('execute_time', 'desc');
            },
            'beds' => function ($query) {
                $query               
                ->where('his_bed.is_delete', 0)
                ->select([
                    'his_bed.id',
                    'his_bed.bed_room_id',
                    'his_bed.bed_name',
                    'his_bed.bed_code'
                ])
                ->orderBy('his_treatment_bed_room.add_time', 'desc');
            },
            'beds.bedRoom:id,bed_room_code,bed_room_name',
        ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(('tdl_patient_name'), 'like', '%'. $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(('is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(('is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyDepartmentCodeFilter($query, $code)
    {
        if ($code !== null) {
            $query->where(('department_code'), $code);
        }
        return $query;
    }
    public function applyAddLoginnameFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('add_loginname'), $param);
        }
        return $query;
    }
    public function applyBedRoomIdsFilter($query, $ids)
    {
        if ($ids != null) {
            $query->whereIn(('bed_room_id'), $ids);
        }
        return $query;
    }
    public function applyTreatmentTypeIdsFilter($query, $ids)
    {
        if ($ids != null) {
            $query->whereIn(('in_treatment_type_id'), $ids);
        }
        return $query;
    }
    public function applyPatientClassifyIdsFilter($query, $ids)
    {
        if ($ids != null) {
            $query->whereIn(('tdl_patient_classify_id'), $ids);
        }
        return $query;
    }
    public function applyIsInBedFilter($query, $param)
    {
        if ($param !== null) {
            if($param){
                $query->whereNotNull(('bed_id'))
                ->whereNull(('remove_time'));
            }else{
                $query->whereNull(('bed_id'));
            }
        }
        return $query;
    }
    public function applyIsOutFilter($query, $param)
    {
        if ($param !== null) {
            if($param){
                $query->whereNotNull(('out_time'))
                ->whereNotNull(('remove_time'));
            }else{
                $query->whereNull(('out_time'));
            }
        }
        return $query;
    }
    public function applyIsCoTreatDepartmentFilter($query, $param)
    {
        if ($param !== null) {
            if($param){
                $query->whereNotNull(('co_department_ids'));
            }else{
                $query->whereNull(('co_department_ids'));
            }
        }
        return $query;
    }
    public function applyAddTimeFromFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                $query->where('add_time', '>=', $param);
            });
        }
        return $query;
    }
    public function applyAddTimeToFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                $query->where('add_time', '<=', $param);
            });
        }
        return $query;
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
        return $this->medicalCaseCoverListVView->find($id);
    }

}