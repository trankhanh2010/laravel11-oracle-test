<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\SAR\SarPrint;
use App\Models\View\SereServDetailVView;
use Illuminate\Support\Facades\DB;

class SereServDetailVViewRepository
{
    protected $sereServDetailVView;
    public function __construct(SereServDetailVView $sereServDetailVView)
    {
        $this->sereServDetailVView = $sereServDetailVView;
    }

    public function applyJoins($serviceTypeCode = null)
    {
        switch ($serviceTypeCode) {
            case 'KH':
                return $this->sereServDetailVView
                    ->select([
                        'id',
                        'service_id',
                        'service_req_id',
                        'tdl_patient_id',
                        'tdl_treatment_id',
                        'tdl_treatment_code',
                    ]);
            case 'TH':
                return $this->sereServDetailVView
                    ->select([
                        'id',
                        'service_id',
                        'service_req_id',
                        'tdl_patient_id',
                        'tdl_treatment_id',
                        'tdl_treatment_code',
                    ]);
            case 'XN':
                return $this->sereServDetailVView
                    ->select([
                        'id',
                        'service_id',
                        'service_req_id',
                        'tdl_patient_id',
                        'tdl_treatment_id',
                        'tdl_treatment_code',
                    ]);
            case 'TT':
            case 'PT':
                return $this->sereServDetailVView
                    ->select([
                        'id',
                        'service_id',
                        'service_req_id',
                        'tdl_patient_id',
                        'tdl_treatment_id',
                        'tdl_treatment_code',
                        'ekip_id',
                    ]);
            case 'SA':
            case 'NS':
            case 'CN':
            case 'HA':
                return $this->sereServDetailVView
                    ->select([
                        'id',
                        'service_id',
                        'service_req_id',
                        'tdl_patient_id',
                        'tdl_treatment_id',
                        'tdl_treatment_code',
                        'ekip_id',
                    ]);
            default:
                return $this->sereServDetailVView
                    ->select();
        }
    }
    public function applyWithParam($query, $serviceTypeCode = null)
    {
        switch ($serviceTypeCode) {
            case 'KH':
                return $query->with([
                    'service_req' => function ($query) {
                        $query->select([
                            'id',
                            'intruction_time',
                            'hospitalization_reason',
                            'sick_day',
                            'pathological_process',
                            'pathological_history',
                            'pathological_history_family',
                            'full_exam',
                            'dhst_id',
                            'part_exam',
                            'subclinical',
                            'treatment_instruction',
                            'next_treatment_instruction',
                            'note',
                            'icd_code',
                            'icd_name',
                            'icd_sub_code',
                            'icd_text',
                            'execute_username',
                            'execute_loginname',
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // TH thuốc
                    'service_req.dhst' => function ($query) {
                        $query->select([
                            'id',
                            'EXECUTE_TIME',
                            'TEMPERATURE',
                            'BREATH_RATE',
                            'WEIGHT',
                            'HEIGHT',
                            'CHEST',
                            'BELLY',
                            'BLOOD_PRESSURE_MAX',
                            'BLOOD_PRESSURE_MIN',
                            'PULSE',
                            'VIR_BMI',
                            'VIR_BODY_SURFACE_AREA',
                            'SPO2',
                            'CAPILLARY_BLOOD_GLUCOSE',
                            'NOTE',
                            'INFUTION_INTO',
                            'INFUTION_OUT',
                            'VACCINATION_EXAM_ID',
                            'URINE',

                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                ]);
            case 'TH':
                return $query->with([
                    'exp_mest_medicine' => function ($query) {
                        $query->where('is_delete', 0)->where('is_active', 1);
                    }, // TH thuốc
                ]);
            case 'XN':
                return $query->with([
                    'service_req' => function ($query) {
                        $query->select([
                            'id',
                            'intruction_time',
                            'hospitalization_reason',
                            'sick_day',
                            'pathological_process',
                            'pathological_history',
                            'pathological_history_family',
                            'full_exam',
                            'dhst_id',
                            'part_exam',
                            'subclinical',
                            'treatment_instruction',
                            'next_treatment_instruction',
                            'note',
                            'icd_code',
                            'icd_name',
                            'icd_sub_code',
                            'icd_text',
                            'execute_username',
                            'execute_loginname',
                            'parent_id',
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // XN Xét nghiệm (nếu có ekip thì lấy dựa theo role, nếu k có thì execute_username là người đọc và kỹ thuật viên luôn)
                    'service_req.machine' => function ($query) {
                        $query->select([
                            'id',
                            'machine_name',
                            'machine_code'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                    'service_req.sere_serv_details' => function ($query) {
                        $query->select([
                            'id',
                            'service_req_id',
                            'tdl_service_code',
                            'tdl_service_name',
                            'is_no_execute',
                            'tdl_intruction_date',
                            'tdl_intruction_time'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // Dịch vụ gần nhất - dịch vụ trong cùng 1 y lệnh
                    'service_req.sere_serv_details.sere_serv_teins' => function ($query) {
                        $query->select([
                            'id',
                            'sere_serv_id',
                            'test_index_id',
                            'value',
                            'result_code',
                            'description',
                            'result_description'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                    'service_req.sere_serv_details.sere_serv_teins.test_index' => function ($query) {
                        $query->select([
                            'id',
                            'test_index_code',
                            'test_index_name',
                            'test_index_unit_id'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                    'service_req.sere_serv_details.sere_serv_teins.test_index.test_index_unit' => function ($query) {
                        $query->select([
                            'id',
                            'test_index_unit_code',
                            'test_index_unit_name',
                            'test_index_unit_symbol'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                    'sere_serv_exts' => function ($query) {
                        $query->select([
                            'id',
                            'sere_serv_id',
                            'json_print_id',
                            'description_sar_print_id',
                            'begin_time',
                            'end_time',
                            'conclude',
                            'description',
                            'note',
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                ]);
            case 'TT':
            case 'PT':
                return $query->with([
                    'service_req' => function ($query) {
                        $query->select([
                            'id',
                            'intruction_time',
                            'hospitalization_reason',
                            'sick_day',
                            'pathological_process',
                            'pathological_history',
                            'pathological_history_family',
                            'full_exam',
                            'dhst_id',
                            'part_exam',
                            'subclinical',
                            'treatment_instruction',
                            'next_treatment_instruction',
                            'note',
                            'icd_code',
                            'icd_name',
                            'icd_sub_code',
                            'icd_text',
                            'execute_username',
                            'execute_loginname',
                            'parent_id',
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                    'sere_serv_exts' => function ($query) {
                        $query->select([
                            'id',
                            'sere_serv_id',
                            'json_print_id',
                            'begin_time',
                            'end_time',
                            'conclude',
                            'description',
                            'note',
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // TT thủ thuật, PT phẫu thuật Chỉ lấy is_delete = 0
                    'sere_serv_exts.machine' => function ($query) {
                        $query->select([
                            'id',
                            'machine_code',
                            'machine_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // Máy trả kết quả CLS
                    'sere_serv_exts.film_size' => function ($query) {
                        $query->select([
                            'id',
                            'film_size_code',
                            'film_size_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                    'service' => function ($query) {
                        $query->select([
                            'id',
                            'service_code',
                            'service_name',
                            'icd_cm_id'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                    'service.icd_cm' => function ($query) {
                        $query->select([
                            'id',
                            'icd_cm_code',
                            'icd_cm_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                    // 'service.machines' => function ($query) {
                    //     $query->select([
                    //         'id','machine_code','machine_name'
                    //     ])->where('is_delete', 0)->where('is_active', 1);
                    // }, // Máy thực hiện dịch vụ
                    'sere_serv_childrens' => function ($query) {
                        $query->select([
                            'id',
                            'parent_id',
                            'service_id',
                            'amount'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // dịch vụ đính kèm
                    'sere_serv_childrens.service' => function ($query) {
                        $query->select([
                            'id',
                            'service_unit_id',
                            'service_code',
                            'service_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                    'sere_serv_childrens.service.service_unit' => function ($query) {
                        $query->select([
                            'id',
                            'service_unit_code',
                            'service_unit_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },

                    'ekip_user' => function ($query) {
                        $query->select([
                            'id',
                            'department_id',
                            'ekip_id',
                            'loginname',
                            'username',
                            'execute_role_id'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // danh sách ekip
                    'ekip_user.execute_role' => function ($query) {
                        $query->select([
                            'id',
                            'execute_role_code',
                            'execute_role_name',
                            'is_surgry',
                            'is_subclinical',
                            'is_subclinical_result'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // IS_SURGRY gây mê, IS_SUBCLINICAL là kỹ thuật viên, IS_SUBCLINICAL_RESULT là người đọc kết quả
                    'ekip_user.department' => function ($query) {
                        $query->select([
                            'id',
                            'department_code',
                            'department_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                    'service_req_matys' => function ($query) {
                        $query->where('is_delete', 0)->where('is_active', 1);
                    },
                    'sere_serv_pttts' => function ($query) {
                        $query->select([
                            'id',
                            'sere_serv_id',
                            'blood_rh_id',
                            'blood_abo_id',
                            'pttt_group_id',
                            'pttt_method_id',
                            'real_pttt_method_id',
                            'pttt_condition_id',
                            'pttt_catastrophe_id',
                            'pttt_high_tech_id',
                            'pttt_priority_id',
                            'pttt_table_id',
                            'emotionless_method_id',
                            'emotionless_method_second_id',
                            'emotionless_result_id',
                            'death_within_id',
                            'icd_name',
                            'icd_code',
                            'icd_text',
                            'icd_sub_code',
                            'before_pttt_icd_name',
                            'before_pttt_icd_code',
                            'after_pttt_icd_name',
                            'after_pttt_icd_code',
                            'manner',
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // PT phẫu thuật
                    'sere_serv_pttts.pttt_group' => function ($query) {
                        $query->select([
                            'id',
                            'pttt_group_code',
                            'pttt_group_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // Phân loại
                    'sere_serv_pttts.pttt_method' => function ($query) {
                        $query->select([
                            'id',
                            'pttt_method_code',
                            'pttt_method_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // Phương pháp pttt
                    'sere_serv_pttts.real_pttt_method' => function ($query) {
                        $query->select([
                            'id',
                            'pttt_method_code',
                            'pttt_method_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // Phương pháp pttt thực tế
                    'sere_serv_pttts.pttt_condition' => function ($query) {
                        $query->select([
                            'id',
                            'pttt_condition_code',
                            'pttt_condition_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // Tình trạng pttt
                    'sere_serv_pttts.pttt_catastrophe' => function ($query) {
                        $query->select([
                            'id',
                            'pttt_catastrophe_code',
                            'pttt_catastrophe_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // Tai biến pttt
                    'sere_serv_pttts.pttt_high_tech' => function ($query) {
                        $query->select([
                            'id',
                            'pttt_high_tech_code',
                            'pttt_high_tech_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // công nghệ cao
                    'sere_serv_pttts.pttt_priority' => function ($query) {
                        $query->select([
                            'id',
                            'pttt_priority_code',
                            'pttt_priority_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // Ưu tiên
                    'sere_serv_pttts.pttt_table' => function ($query) {
                        $query->select([
                            'id',
                            'pttt_table_code',
                            'pttt_table_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // Bàn mổ
                    'sere_serv_pttts.emotionless_method' => function ($query) {
                        $query->select([
                            'id',
                            'emotionless_method_code',
                            'emotionless_method_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // Phương pháp vô cảm
                    'sere_serv_pttts.emotionless_method_second' => function ($query) {
                        $query->select([
                            'id',
                            'emotionless_method_code',
                            'emotionless_method_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // Phương pháp vô cảm 2
                    'sere_serv_pttts.emotionless_result' => function ($query) {
                        $query->select([
                            'id',
                            'emotionless_result_code',
                            'emotionless_result_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // Kết quả vô cảm
                    'sere_serv_pttts.death_within' => function ($query) {
                        $query->select([
                            'id',
                            'death_within_code',
                            'death_within_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // Tử vong trong
                    'sere_serv_pttts.blood_abo' => function ($query) {
                        $query->select([
                            'id',
                            'blood_abo_code'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // máu
                    'sere_serv_pttts.blood_rh' => function ($query) {
                        $query->select([
                            'id',
                            'blood_rh_code'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // máu

                ]);
            case 'SA':
            case 'NS':
            case 'CN':
            case 'HA':
                return $query->with([
                    'service_req' => function ($query) {
                        $query->select([
                            'id',
                            'intruction_time',
                            'hospitalization_reason',
                            'sick_day',
                            'pathological_process',
                            'pathological_history',
                            'pathological_history_family',
                            'full_exam',
                            'dhst_id',
                            'part_exam',
                            'subclinical',
                            'treatment_instruction',
                            'next_treatment_instruction',
                            'note',
                            'icd_code',
                            'icd_name',
                            'icd_sub_code',
                            'icd_text',
                            'execute_username',
                            'execute_loginname',
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                    'sere_serv_exts' => function ($query) {
                        $query->select([
                            'id',
                            'sere_serv_id',
                            'json_print_id',
                            'description_sar_print_id',
                            'begin_time',
                            'end_time',
                            'conclude',
                            'description',
                            'note',
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // TT thủ thuật, PT phẫu thuật Chỉ lấy is_delete = 0
                    'sere_serv_exts.sar_print' => function ($query) {
                        $query->where('is_delete', 0)->where('is_active', 1);
                    }, // HA hình ảnh, SA siêu âm, CN thăm dò chức năng, NS nội soi
                    'ekip_user' => function ($query) {
                        $query->select([
                            'id',
                            'department_id',
                            'ekip_id',
                            'loginname',
                            'username',
                            'execute_role_id'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // danh sách ekip
                    'ekip_user.execute_role' => function ($query) {
                        $query->select([
                            'id',
                            'execute_role_code',
                            'execute_role_name',
                            'is_surgry',
                            'is_subclinical',
                            'is_subclinical_result'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    }, // IS_SURGRY gây mê, IS_SUBCLINICAL là kỹ thuật viên, IS_SUBCLINICAL_RESULT là người đọc kết quả
                    'ekip_user.department' => function ($query) {
                        $query->select([
                            'id',
                            'department_code',
                            'department_name'
                        ])->where('is_delete', 0)->where('is_active', 1);
                    },
                ]);
            default:
                return $query->with([
                    'exp_mest_medicine', // TH thuốc

                    'service_req', // XN Xét nghiệm (nếu có ekip thì lấy dựa theo role, nếu k có thì execute_username là người đọc và kỹ thuật viên luôn)
                    'service_req.machine:id,machine_name,machine_code',
                    'service_req.sere_serv_details:id,service_req_id,tdl_service_code,tdl_service_name,is_no_execute,tdl_intruction_date,tdl_intruction_time', // Dịch vụ gần nhất - dịch vụ trong cùng 1 y lệnh
                    'service_req.sere_serv_details.sere_serv_teins:id,sere_serv_id,test_index_id,value,result_code,description,result_description',
                    'service_req.sere_serv_details.sere_serv_teins.test_index:id,test_index_code,test_index_name,test_index_unit_id',
                    'service_req.sere_serv_details.sere_serv_teins.test_index.test_index_unit:id,test_index_unit_code,test_index_unit_name,test_index_unit_symbol',

                    'sere_serv_exts' => function ($query) {
                        $query->where('is_delete', 0);
                    }, // TT thủ thuật, PT phẫu thuật Chỉ lấy is_delete = 0
                    'sere_serv_exts.machine:id,machine_code,machine_name', // Máy trả kết quả CLS
                    'sere_serv_exts.film_size:id,film_size_code,film_size_name',
                    'service:id,service_code,service_name,icd_cm_id',
                    'service.icd_cm:id,icd_cm_code,icd_cm_name',
                    // 'service.machines:id,machine_code,machine_name', // Máy thực hiện dịch vụ
                    'sere_serv_childrens:id,parent_id,service_id,amount', // dịch vụ đính kèm
                    'sere_serv_childrens.service:id,service_unit_id,service_code,service_name',
                    'sere_serv_childrens.service.service_unit:id,service_unit_code,service_unit_name',

                    'ekip_user:id,department_id,ekip_id,loginname,username,execute_role_id', // danh sách ekip
                    'ekip_user.execute_role:id,execute_role_code,execute_role_name,is_surgry,is_subclinical,is_subclinical_result', // IS_SURGRY gây mê, IS_SUBCLINICAL là kỹ thuật viên, IS_SUBCLINICAL_RESULT là người đọc kết quả
                    'ekip_user.department:id,department_code,department_name',
                    'service_req_matys',
                    'sere_serv_exts.sar_print', // HA hình ảnh, SA siêu âm, CN thăm dò chức năng, NS nội soi

                    'sere_serv_pttts' => function ($query) {
                        $query->where('is_delete', 0);
                    }, // PT phẫu thuật
                    'sere_serv_pttts.pttt_group:id,pttt_group_code,pttt_group_name', // Phân loại
                    'sere_serv_pttts.pttt_method:id,pttt_method_code,pttt_method_name', // Phương pháp pttt
                    'sere_serv_pttts.real_pttt_method:id,pttt_method_code,pttt_method_name', // Phương pháp pttt thực tế
                    'sere_serv_pttts.pttt_condition:id,pttt_condition_code,pttt_condition_name', // Tình trạng pttt
                    'sere_serv_pttts.pttt_catastrophe:id,pttt_catastrophe_code,pttt_catastrophe_name', // Tai biến pttt
                    'sere_serv_pttts.pttt_high_tech:id,pttt_high_tech_code,pttt_high_tech_name', // công nghệ cao
                    'sere_serv_pttts.pttt_priority:id,pttt_priority_code,pttt_priority_name', // Ưu tiên
                    'sere_serv_pttts.pttt_table:id,pttt_table_code,pttt_table_name', // Bàn mổ
                    'sere_serv_pttts.emotionless_method:id,emotionless_method_code,emotionless_method_name', // Phương pháp vô cảm
                    'sere_serv_pttts.emotionless_method_second:id,emotionless_method_code,emotionless_method_name', // Phương pháp vô cảm 2
                    'sere_serv_pttts.emotionless_result:id,emotionless_result_code,emotionless_result_name', // Kết quả vô cảm
                    'sere_serv_pttts.death_within:id,death_within_code,death_within_name', // Tử vong trong
                    'sere_serv_pttts.blood_abo:id,blood_abo_code', // máu
                    'sere_serv_pttts.blood_rh:id,blood_rh_code', // máu
                ]);
        }
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(('sere_serv_detail_code'), 'like', '%' . $keyword . '%')
                ->orWhere(('lower(sere_serv_detail_name)'), 'like', '%' . strtolower($keyword) . '%');
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
        return $this->sereServDetailVView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->sereServDetailVView::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'sere_serv_detail_v_view_code' => $request->sere_serv_detail_v_view_code,
    //         'sere_serv_detail_v_view_name' => $request->sere_serv_detail_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'sere_serv_detail_v_view_code' => $request->sere_serv_detail_v_view_code,
    //         'sere_serv_detail_v_view_name' => $request->sere_serv_detail_v_view_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('id');
            $maxId = $this->applyJoins()->max('id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('sere_serv_detail_v_view', 'v_his_sere_serv_detail', $startId, $endId, $batchSize);
            }
        }
    }
}
