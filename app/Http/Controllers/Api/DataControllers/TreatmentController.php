<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Resources\TreatmentGetFeeViewResource;
use App\Http\Resources\TreatmentResource;
use App\Models\HIS\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TreatmentController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->treatment = new Treatment();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->treatment);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }

        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if ($this->cursor === 0) {
                $this->treatment_last_id = $this->treatment->max('id');
                $this->cursor = $this->treatment_last_id;
                $this->equal = "<=";
            }
        }
        if ($this->cursor < 0) {
            $this->sub_order_by = (strtolower($this->order_by["id"]) === 'asc') ? 'desc' : 'asc';
            $this->equal = (strtolower($this->order_by["id"]) === 'desc') ? '>' : '<';

            $this->sub_order_by_string = ' ORDER BY ID ' . $this->order_by["id"];
            $this->cursor = abs($this->cursor);
        }
    }
    public function treatment_get_L_view(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "ID",
            "CREATE_TIME",
            "TREATMENT_CODE",
            "TDL_PATIENT_CODE",
            "TDL_PATIENT_NAME",
            "TDL_PATIENT_DOB",
            "TDL_PATIENT_GENDER_NAME",
            "ICD_CODE",
            "ICD_NAME",
            "ICD_SUB_CODE",
            "ICD_TEXT",
            "IN_TIME",
            "IS_ACTIVE",
            "IN_DATE",
        ];
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        $data = $this->treatment
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.tdl_patient_name'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_treatment.tdl_patient_code'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_active'), $this->is_active);
            });
        }
        if ($this->patient_code__exact != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.tdl_patient_code'), $this->patient_code__exact);
            });
        }


        if ($this->treatment_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_treatment.' . $key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.id'), $this->treatment_id);
            });
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->first();
        }

        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? null,
            'is_include_deleted' => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active,
            'treatment_id' => $this->treatment_id,
            'patient_code__exact' => $this->patient_code__exact,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function treatment_get_L_view_v2(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "ID",
            "CREATE_TIME",
            "TREATMENT_CODE",
            "TDL_PATIENT_CODE",
            "TDL_PATIENT_NAME",
            "TDL_PATIENT_DOB",
            "TDL_PATIENT_GENDER_NAME",
            "ICD_CODE",
            "ICD_NAME",
            "ICD_SUB_CODE",
            "ICD_TEXT",
            "IN_TIME",
            "IS_ACTIVE",
            "IN_DATE",
        ];
        $keyword = $this->keyword;
        try {
            $data = $this->treatment
                ->select($select);
            $data_id = $this->treatment
                ->select("id");
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.tdl_patient_name'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_treatment.tdl_patient_code'), 'like', $keyword . '%');
                });
                $data_id = $data_id->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.tdl_patient_name'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_treatment.tdl_patient_code'), 'like', $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_active'), $this->is_active);
                });
            }
            if ($this->patient_code__exact != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.tdl_patient_code'), $this->patient_code__exact);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.tdl_patient_code'), $this->patient_code__exact);
                });
            }


            if ($this->treatment_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_treatment.' . $key, $this->sub_order_by ?? $item);
                    }
                }
                // Chuyển truy vấn sang chuỗi sql
                $sql = $data->toSql();
                $sql_id = $data_id->toSql();
                // Truyền tham số qua binding tránh SQL Injection
                $bindings = $data->getBindings();
                $bindings_id = $data_id->getBindings();
                $id_max_sql = DB::connection('oracle_his')->select('SELECT a.ID, ROWNUM  FROM (' . $sql_id . ' order by ID desc) a  WHERE ROWNUM = 1 ', $bindings_id);
                $id_min_sql = DB::connection('oracle_his')->select('SELECT a.ID, ROWNUM  FROM (' . $sql_id . ' order by ID asc) a  WHERE ROWNUM = 1 ', $bindings_id);
                $id_max_sql = intval($id_max_sql[0]->id ?? null);
                $id_min_sql = intval($id_min_sql[0]->id ?? null);

                $fullSql = 'SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (' . $sql . ') a WHERE ROWNUM <= ' . ($this->limit + $this->start) . ' AND ID ' . $this->equal . $this->cursor . $this->sub_order_by_string . ') WHERE rnum > ' . $this->start;
                $data = DB::connection('oracle_his')->select($fullSql, $bindings);
                $data = TreatmentResource::collection($data);
                if (isset($data[0])) {
                    if (($data[0]->id != $this->treatment->max('id')) && ($data[0]->id != $this->treatment->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)) {
                        $this->prev_cursor = '-' . $data[0]->id;
                    } else {
                        $this->prev_cursor = null;
                    }
                    if (((count($data) === 1) && ($this->order_by["id"] == 'desc') && ($data[0]->id == $id_min_sql))
                        || ((count($data) === 1) && ($this->order_by["id"] == 'asc') && ($data[0]->id == $id_max_sql))
                    ) {
                        $this->prev_cursor = '-' . $data[0]->id;
                    }
                    if ($this->raw_cursor == 0) {
                        $this->prev_cursor = null;
                    }
                    $this->next_cursor = $data[($this->limit - 1)]->id ?? null;
                    if (($this->next_cursor == $id_max_sql && ($this->order_by["id"] == 'asc')) || ($this->next_cursor == $id_min_sql && ($this->order_by["id"] == 'desc'))) {
                        $this->next_cursor = null;
                    }
                }
            } else {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.id'), $this->treatment_id);
                });
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->first();
            }

            $param_return = [
                'prev_cursor' => $this->prev_cursor ?? null,
                'limit' => $this->limit,
                'next_cursor' => $this->next_cursor ?? null,
                'is_include_deleted' => $this->is_include_deleted ?? false,
                'is_active' => $this->is_active,
                'treatment_id' => $this->treatment_id,
                'patient_code__exact' => $this->patient_code__exact,
                'keyword' => $this->keyword,
                'order_by' => $this->order_by_request
            ];
            return return_data_success($param_return, $data);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }

    public function treatment_get_treatment_with_patient_type_info_sdo(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
    
        $select = [
            "his_treatment.ID",
            "his_treatment.CREATE_TIME",
            "his_treatment.MODIFY_TIME",
            "his_treatment.CREATOR",
            "his_treatment.MODIFIER",
            "his_treatment.APP_CREATOR",
            "his_treatment.APP_MODIFIER",
            "his_treatment.IS_ACTIVE",
            "his_treatment.IS_DELETE",
            "his_treatment.TREATMENT_CODE",
            "his_treatment.PATIENT_ID",
            "his_treatment.BRANCH_ID",
            "his_treatment.ICD_CODE",
            "his_treatment.ICD_NAME",
            "his_treatment.ICD_SUB_CODE",
            "his_treatment.ICD_TEXT",
            "his_treatment.IN_TIME",
            "his_treatment.IN_DATE",
            "his_treatment.CLINICAL_IN_TIME",
            "his_treatment.IN_CODE",
            "his_treatment.IN_ROOM_ID",
            "his_treatment.IN_DEPARTMENT_ID",
            "his_treatment.IN_LOGINNAME",
            "his_treatment.IN_USERNAME",
            "his_treatment.IN_TREATMENT_TYPE_ID",
            "his_treatment.IN_ICD_CODE",
            "his_treatment.IN_ICD_NAME",
            "his_treatment.IN_ICD_SUB_CODE",
            "his_treatment.IN_ICD_TEXT",
            "his_treatment.HOSPITALIZATION_REASON",
            "his_treatment.DOCTOR_LOGINNAME",
            "his_treatment.DOCTOR_USERNAME",
            "his_treatment.IS_CHRONIC",
            "his_treatment.JSON_PRINT_ID",
            "his_treatment.IS_EMERGENCY",
            "his_treatment.SUBCLINICAL_RESULT",
            "his_treatment.TDL_FIRST_EXAM_ROOM_ID",
            "his_treatment.TDL_TREATMENT_TYPE_ID",
            "his_treatment.TDL_PATIENT_TYPE_ID",
            "his_treatment.FUND_CUSTOMER_NAME",
            "his_treatment.TDL_PATIENT_CODE",
            "his_treatment.TDL_PATIENT_NAME",
            "his_treatment.TDL_PATIENT_FIRST_NAME",
            "his_treatment.TDL_PATIENT_LAST_NAME",
            "his_treatment.TDL_PATIENT_DOB",
            "his_treatment.TDL_PATIENT_ADDRESS",
            "his_treatment.TDL_PATIENT_GENDER_ID",
            "his_treatment.TDL_PATIENT_GENDER_NAME",
            "his_treatment.TDL_PATIENT_CAREER_NAME",
            "his_treatment.TDL_PATIENT_DISTRICT_CODE",
            "his_treatment.TDL_PATIENT_PROVINCE_CODE",
            "his_treatment.TDL_PATIENT_COMMUNE_CODE",
            "his_treatment.TDL_PATIENT_NATIONAL_NAME",
            "his_treatment.TDL_PATIENT_RELATIVE_TYPE",
            "his_treatment.TDL_PATIENT_RELATIVE_NAME",
            "his_treatment.DEPARTMENT_IDS",
            "his_treatment.CO_DEPARTMENT_IDS",
            "his_treatment.LAST_DEPARTMENT_ID",
            "his_treatment.TDL_PATIENT_PHONE",
            "his_treatment.IS_SYNC_EMR",
            "his_treatment.VIR_IN_MONTH",
            "his_treatment.IN_CODE_SEED_CODE",
            "his_treatment.VIR_IN_YEAR",
            "his_treatment.EMR_COVER_TYPE_ID",
            "his_treatment.HOSPITALIZE_DEPARTMENT_ID",
            "his_treatment.TDL_PATIENT_RELATIVE_MOBILE",
            "his_treatment.TDL_PATIENT_NATIONAL_CODE",
            "his_treatment.TDL_PATIENT_PROVINCE_NAME",
            "his_treatment.TDL_PATIENT_DISTRICT_NAME",
            "his_treatment.TDL_PATIENT_COMMUNE_NAME",
            "his_treatment.TDL_PATIENT_UNSIGNED_NAME",
            "his_treatment.TDL_PATIENT_ETHNIC_NAME",
            "his_treatment.IS_TUBERCULOSIS",
            "his_treatment.TDL_HEIN_MEDI_ORG_CODE",
            "his_treatment.TDL_HEIN_CARD_NUMBER",
            "his_treatment.TDL_HEIN_CARD_FROM_TIME",
            "his_treatment.TDL_HEIN_CARD_TO_TIME",
        ];
        $param = [
            'patient_type:id,patient_type_code,patient_type_name,IS_COPAYMENT',
            'treatment_type:id,treatment_type_code,treatment_type_name,HEIN_TREATMENT_TYPE_CODE',
            'accident_hurts',
            'adrs',
            'allergy_cards',
            'antibiotic_requests',
            'appointment_servs',
            'babys',
            'cares',
            'care_sums',
            'carer_card_borrows',
            'debates',
            'department_trans',
            'deposit_reqs',
            'dhsts',
            'exp_mest_maty_reqs',
            'exp_mest_mety_reqs',
            'hein_approvals:id,treatment_id,LEVEL_CODE,RIGHT_ROUTE_CODE,RIGHT_ROUTE_TYPE_CODE,ADDRESS',
            'hiv_treatments',
            'hold_returns',
            'imp_mest_mate_reqs',
            'imp_mest_medi_reqs',
            'infusion_sums',
            'medi_react_sums',
            'medical_assessments',
            'medicine_interactives',
            'mr_check_summarys',
            'obey_contraindis',
            'patient_type_alters',
            'prepares',
            'reha_sums',
            'sere_servs',
            'service_reqs',
            'severe_illness_infos',
            'trackings',
            'trans_reqs',
            'transactions',
            'transfusion_sums',
            'treatment_bed_rooms',
            'treatment_borrows',
            'treatment_files',
            'treatment_loggings',
            'treatment_unlimits',
            'tuberculosis_treats'
        ];

        $data = $this->treatment
            ->select($select);
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_active'), $this->is_active);
            });
        }

        if ($this->treatment_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.id'), $this->treatment_id);
            });
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->first();
        } else {
            $data = [];
        }

        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'is_include_deleted' => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active,
            'treatment_id' => $this->treatment_id,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function treatment_get_treatment_with_patient_type_info_sdo_v2(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "his_treatment.ID",
            "his_treatment.CREATE_TIME",
            "his_treatment.MODIFY_TIME",
            "his_treatment.CREATOR",
            "his_treatment.MODIFIER",
            "his_treatment.APP_CREATOR",
            "his_treatment.APP_MODIFIER",
            "his_treatment.IS_ACTIVE",
            "his_treatment.IS_DELETE",
            "his_treatment.TREATMENT_CODE",
            "his_treatment.PATIENT_ID",
            "his_treatment.BRANCH_ID",
            "his_treatment.ICD_CODE",
            "his_treatment.ICD_NAME",
            "his_treatment.ICD_SUB_CODE",
            "his_treatment.ICD_TEXT",
            "his_treatment.IN_TIME",
            "his_treatment.IN_DATE",
            "his_treatment.CLINICAL_IN_TIME",
            "his_treatment.IN_CODE",
            "his_treatment.IN_ROOM_ID",
            "his_treatment.IN_DEPARTMENT_ID",
            "his_treatment.IN_LOGINNAME",
            "his_treatment.IN_USERNAME",
            "his_treatment.IN_TREATMENT_TYPE_ID",
            "his_treatment.IN_ICD_CODE",
            "his_treatment.IN_ICD_NAME",
            "his_treatment.IN_ICD_SUB_CODE",
            "his_treatment.IN_ICD_TEXT",
            "his_treatment.HOSPITALIZATION_REASON",
            "his_treatment.DOCTOR_LOGINNAME",
            "his_treatment.DOCTOR_USERNAME",
            "his_treatment.IS_CHRONIC",
            "his_treatment.JSON_PRINT_ID",
            "his_treatment.IS_EMERGENCY",
            "his_treatment.SUBCLINICAL_RESULT",
            "his_treatment.TDL_FIRST_EXAM_ROOM_ID",
            "his_treatment.TDL_TREATMENT_TYPE_ID",
            "his_treatment.TDL_PATIENT_TYPE_ID",
            "his_treatment.FUND_CUSTOMER_NAME",
            "his_treatment.TDL_PATIENT_CODE",
            "his_treatment.TDL_PATIENT_NAME",
            "his_treatment.TDL_PATIENT_FIRST_NAME",
            "his_treatment.TDL_PATIENT_LAST_NAME",
            "his_treatment.TDL_PATIENT_DOB",
            "his_treatment.TDL_PATIENT_ADDRESS",
            "his_treatment.TDL_PATIENT_GENDER_ID",
            "his_treatment.TDL_PATIENT_GENDER_NAME",
            "his_treatment.TDL_PATIENT_CAREER_NAME",
            "his_treatment.TDL_PATIENT_DISTRICT_CODE",
            "his_treatment.TDL_PATIENT_PROVINCE_CODE",
            "his_treatment.TDL_PATIENT_COMMUNE_CODE",
            "his_treatment.TDL_PATIENT_NATIONAL_NAME",
            "his_treatment.TDL_PATIENT_RELATIVE_TYPE",
            "his_treatment.TDL_PATIENT_RELATIVE_NAME",
            "his_treatment.DEPARTMENT_IDS",
            "his_treatment.CO_DEPARTMENT_IDS",
            "his_treatment.LAST_DEPARTMENT_ID",
            "his_treatment.TDL_PATIENT_PHONE",
            "his_treatment.IS_SYNC_EMR",
            "his_treatment.VIR_IN_MONTH",
            "his_treatment.IN_CODE_SEED_CODE",
            "his_treatment.VIR_IN_YEAR",
            "his_treatment.EMR_COVER_TYPE_ID",
            "his_treatment.HOSPITALIZE_DEPARTMENT_ID",
            "his_treatment.TDL_PATIENT_RELATIVE_MOBILE",
            "his_treatment.TDL_PATIENT_NATIONAL_CODE",
            "his_treatment.TDL_PATIENT_PROVINCE_NAME",
            "his_treatment.TDL_PATIENT_DISTRICT_NAME",
            "his_treatment.TDL_PATIENT_COMMUNE_NAME",
            "his_treatment.TDL_PATIENT_UNSIGNED_NAME",
            "his_treatment.TDL_PATIENT_ETHNIC_NAME",
            "his_treatment.IS_TUBERCULOSIS",
            "his_treatment.TDL_HEIN_MEDI_ORG_CODE",
            "his_treatment.TDL_HEIN_CARD_NUMBER",
            "his_treatment.TDL_HEIN_CARD_FROM_TIME",
            "his_treatment.TDL_HEIN_CARD_TO_TIME",
        ];
        $param = [
            'patient_type:id,patient_type_code,patient_type_name,IS_COPAYMENT',
            'treatment_type:id,treatment_type_code,treatment_type_name,HEIN_TREATMENT_TYPE_CODE',
            'accident_hurts',
            'adrs',
            'allergy_cards',
            'antibiotic_requests',
            'appointment_servs',
            'babys',
            'cares',
            'care_sums',
            'carer_card_borrows',
            'debates',
            'department_trans',
            'deposit_reqs',
            'dhsts',
            'exp_mest_maty_reqs',
            'exp_mest_mety_reqs',
            'hein_approvals:id,treatment_id,LEVEL_CODE,RIGHT_ROUTE_CODE,RIGHT_ROUTE_TYPE_CODE,ADDRESS',
            'hiv_treatments',
            'hold_returns',
            'imp_mest_mate_reqs',
            'imp_mest_medi_reqs',
            'infusion_sums',
            'medi_react_sums',
            'medical_assessments',
            'medicine_interactives',
            'mr_check_summarys',
            'obey_contraindis',
            'patient_type_alters',
            'prepares',
            'reha_sums',
            'sere_servs',
            'service_reqs',
            'severe_illness_infos',
            'trackings',
            'trans_reqs',
            'transactions',
            'transfusion_sums',
            'treatment_bed_rooms',
            'treatment_borrows',
            'treatment_files',
            'treatment_loggings',
            'treatment_unlimits',
            'tuberculosis_treats'
        ];

        $data = $this->treatment
            ->select($select);
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_active'), $this->is_active);
            });
        }

        if ($this->treatment_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.id'), $this->treatment_id);
            });
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->first();
        } else {
            $data = [];
        }

        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'is_include_deleted' => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active,
            'treatment_id' => $this->treatment_id,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function treatment_get_fee_view(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "his_treatment.ID",
            "his_treatment.CREATE_TIME",
            "his_treatment.MODIFY_TIME",
            "his_treatment.CREATOR",
            "his_treatment.MODIFIER",
            "his_treatment.APP_CREATOR",
            "his_treatment.APP_MODIFIER",
            "his_treatment.IS_ACTIVE",
            "his_treatment.IS_DELETE",
            "his_treatment.TREATMENT_CODE",
            "his_treatment.PATIENT_ID",
            "his_treatment.BRANCH_ID",
            "his_treatment.IS_PAUSE",
            "his_treatment.IS_LOCK_HEIN",
            "his_treatment.ICD_CODE",
            "his_treatment.ICD_NAME",
            "his_treatment.ICD_SUB_CODE",
            "his_treatment.ICD_TEXT",
            "his_treatment.FEE_LOCK_TIME",
            "his_treatment.FEE_LOCK_ORDER",
            "his_treatment.FEE_LOCK_ROOM_ID",
            "his_treatment.FEE_LOCK_DEPARTMENT_ID",
            "his_treatment.IN_TIME",
            "his_treatment.IN_DATE",
            "his_treatment.OUT_TIME",
            "his_treatment.HOSPITALIZATION_REASON",
            "his_treatment.DOCTOR_LOGINNAME",
            "his_treatment.DOCTOR_USERNAME",
            "his_treatment.END_LOGINNAME",
            "his_treatment.END_USERNAME",
            "his_treatment.END_ROOM_ID",
            "his_treatment.END_DEPARTMENT_ID",
            "his_treatment.END_CODE",
            "his_treatment.TREATMENT_DAY_COUNT",
            "his_treatment.TREATMENT_RESULT_ID",
            "his_treatment.TREATMENT_END_TYPE_ID",
            "his_treatment.APPOINTMENT_TIME",
            "his_treatment.OUT_DATE",
            "his_treatment.TDL_HEIN_CARD_NUMBER",
            "his_treatment.CLINICAL_NOTE",
            "his_treatment.TDL_FIRST_EXAM_ROOM_ID",
            "his_treatment.TDL_TREATMENT_TYPE_ID",
            "his_treatment.TDL_PATIENT_TYPE_ID",
            "his_treatment.TDL_HEIN_MEDI_ORG_CODE",
            "his_treatment.TDL_HEIN_MEDI_ORG_NAME",
            "his_treatment.XML4210_URL",
            "his_treatment.TDL_PATIENT_CODE",
            "his_treatment.TDL_PATIENT_NAME",
            "his_treatment.TDL_PATIENT_FIRST_NAME",
            "his_treatment.TDL_PATIENT_LAST_NAME",
            "his_treatment.TDL_PATIENT_DOB",
            "his_treatment.TDL_PATIENT_IS_HAS_NOT_DAY_DOB",
            "his_treatment.TDL_PATIENT_AVATAR_URL",
            "his_treatment.TDL_PATIENT_ADDRESS",
            "his_treatment.TDL_PATIENT_GENDER_ID",
            "his_treatment.TDL_PATIENT_GENDER_NAME",
            "his_treatment.TDL_PATIENT_CAREER_NAME",
            "his_treatment.TDL_PATIENT_DISTRICT_CODE",
            "his_treatment.TDL_PATIENT_PROVINCE_CODE",
            "his_treatment.TDL_PATIENT_COMMUNE_CODE",
            "his_treatment.TDL_PATIENT_NATIONAL_NAME",
            "his_treatment.APPOINTMENT_EXAM_ROOM_IDS",
            "his_treatment.DEPARTMENT_IDS",
            "his_treatment.LAST_DEPARTMENT_ID",
            "his_treatment.PROVISIONAL_DIAGNOSIS",
            "his_treatment.TDL_PATIENT_PHONE",
            "his_treatment.IS_SYNC_EMR",
            "his_treatment.XML4210_RESULT",
            "his_treatment.COLLINEAR_XML4210_URL",
            "his_treatment.COLLINEAR_XML4210_RESULT",
            "his_treatment.TDL_HEIN_CARD_FROM_TIME",
            "his_treatment.TDL_HEIN_CARD_TO_TIME",
            "his_treatment.APPOINTMENT_DATE",
            "his_treatment.VIR_IN_MONTH",
            "his_treatment.VIR_OUT_MONTH",
            "his_treatment.VIR_IN_YEAR",
            "his_treatment.VIR_OUT_YEAR",
            "his_treatment.FEE_LOCK_LOGINNAME",
            "his_treatment.FEE_LOCK_USERNAME",
            "his_treatment.APPOINTMENT_EXAM_SERVICE_ID",
            "his_treatment.TDL_PATIENT_CCCD_NUMBER",
            "his_treatment.TDL_PATIENT_CCCD_DATE",
            "his_treatment.TDL_PATIENT_RELATIVE_MOBILE",
            "his_treatment.TDL_PATIENT_NATIONAL_CODE",
            "his_treatment.TDL_PATIENT_PROVINCE_NAME",
            "his_treatment.TDL_PATIENT_DISTRICT_NAME",
            "his_treatment.TDL_PATIENT_COMMUNE_NAME",
            "his_treatment.IS_BHYT_HOLDED",
            "his_treatment.SHOW_ICD_CODE",
            "his_treatment.SHOW_ICD_NAME",
            "his_treatment.SHOW_ICD_SUB_CODE",
            "his_treatment.SHOW_ICD_TEXT",
            "his_treatment.TDL_PATIENT_UNSIGNED_NAME",
            "his_treatment.TDL_PATIENT_ETHNIC_NAME",
            "his_treatment.IS_TUBERCULOSIS",
            "his_treatment.STORE_BORDEREAU_CODE",
            "his_treatment.HEIN_LOCK_TIME",
            "his_treatment.RECEPTION_FORM",
            "his_treatment.HOSPITAL_DIRECTOR_LOGINNAME",
            "his_treatment.HOSPITAL_DIRECTOR_USERNAME",
            "his_treatment.HAS_CARD",

            "V_HIS_TREATMENT_FEE.TOTAL_BILL_AMOUNT",
            "V_HIS_TREATMENT_FEE.TOTAL_BILL_OTHER_AMOUNT",
            "V_HIS_TREATMENT_FEE.TOTAL_BILL_TRANSFER_AMOUNT",
            "V_HIS_TREATMENT_FEE.TOTAL_BILL_EXEMPTION",
            "V_HIS_TREATMENT_FEE.TOTAL_BILL_FUND",
            "V_HIS_TREATMENT_FEE.TOTAL_DEPOSIT_AMOUNT",
            "V_HIS_TREATMENT_FEE.TOTAL_REPAY_AMOUNT",
            "V_HIS_TREATMENT_FEE.TOTAL_PRICE",
            "V_HIS_TREATMENT_FEE.TOTAL_HEIN_PRICE",
            "V_HIS_TREATMENT_FEE.TOTAL_OTHER_COPAID_PRICE",
            "V_HIS_TREATMENT_FEE.TOTAL_PATIENT_PRICE",
            "V_HIS_TREATMENT_FEE.TOTAL_DISCOUNT",
            "V_HIS_TREATMENT_FEE.TOTAL_PRICE_EXPEND",
            "V_HIS_TREATMENT_FEE.COUNT_HEIN_APPROVAL",
            "V_HIS_TREATMENT_FEE.TOTAL_DEBT_AMOUNT",
            "V_HIS_TREATMENT_FEE.TOTAL_PATIENT_PRICE_BHYT",
            "V_HIS_TREATMENT_FEE.TOTAL_OTHER_SOURCE_PRICE",
            "V_HIS_TREATMENT_FEE.LAST_DEPOSIT_TIME",
            "V_HIS_TREATMENT_FEE.TOTAL_SERVICE_DEPOSIT_AMOUNT",
            "V_HIS_TREATMENT_FEE.TDL_TREATMENT_TYPE_CODE",
            "V_HIS_TREATMENT_FEE.TDL_TREATMENT_TYPE_NAME",
            "V_HIS_TREATMENT_FEE.HEIN_TREATMENT_TYPE_CODE",
            "V_HIS_TREATMENT_FEE.LOCKING_AMOUNT",

        ];
        $param = [
            'patient_type:id,patient_type_code,patient_type_name,IS_COPAYMENT',
            'treatment_type:id,treatment_type_code,treatment_type_name,HEIN_TREATMENT_TYPE_CODE',
            'accident_hurts',
            'adrs',
            'allergy_cards',
            'antibiotic_requests',
            'appointment_servs',
            'babys',
            'cares',
            'care_sums',
            'carer_card_borrows',
            'debates',
            'department_trans',
            'deposit_reqs',
            'dhsts',
            'exp_mest_maty_reqs',
            'exp_mest_mety_reqs',
            'hein_approvals:id,treatment_id,LEVEL_CODE,RIGHT_ROUTE_CODE,RIGHT_ROUTE_TYPE_CODE,ADDRESS',
            'hiv_treatments',
            'hold_returns',
            'imp_mest_mate_reqs',
            'imp_mest_medi_reqs',
            'infusion_sums',
            'medi_react_sums',
            'medical_assessments',
            'medicine_interactives',
            'mr_check_summarys',
            'obey_contraindis',
            'patient_type_alters',
            'prepares',
            'reha_sums',
            'sere_servs',
            'service_reqs',
            'severe_illness_infos',
            'trackings',
            'trans_reqs',
            'transactions',
            'transfusion_sums',
            'treatment_bed_rooms',
            'treatment_borrows',
            'treatment_files',
            'treatment_loggings',
            'treatment_unlimits',
            'tuberculosis_treats'
        ];
        try {
            $data = $this->treatment
            ->leftJoin('V_HIS_TREATMENT_FEE ', 'his_treatment.id', '=', 'V_HIS_TREATMENT_FEE.id')

            ->select($select);
            $data_id = $this->treatment
            
            ->select("His_treatment.ID");
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_active'), $this->is_active);
                });
            }
            if ($this->tdl_treatment_type_ids != null) {
                $data = $data->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_treatment.tdl_treatment_type_id'), $this->tdl_treatment_type_ids);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_treatment.tdl_treatment_type_id'), $this->tdl_treatment_type_ids);
                });
            }
            if ($this->tdl_patient_type_ids != null) {
                $data = $data->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_treatment.tdl_patient_type_id'), $this->tdl_patient_type_ids);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_treatment.tdl_patient_type_id'), $this->tdl_patient_type_ids);
                });
            }
            if ($this->branch_id !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.branch_id'), $this->branch_id);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.branch_id'), $this->branch_id);
                });
            }
            if ($this->in_date_from != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.in_date'), '>=', $this->in_date_from);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.in_date'), '>=', $this->in_date_from);
                });
            }
            if ($this->in_date_to != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.in_date'), '<=', $this->in_date_to);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.in_date'), '<=', $this->in_date_to);
                });
            }
           if($this->is_approve_store != null){
            if ($this->is_approve_store) {
                $data = $data->where(function ($query) {
                    $query = $query->whereNotNull(DB::connection('oracle_his')->raw('his_treatment.approval_store_stt_id'));
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->whereNotNull(DB::connection('oracle_his')->raw('his_treatment.approval_store_stt_id'));
                });
            }else{
                $data = $data->where(function ($query) {
                    $query = $query->whereNull(DB::connection('oracle_his')->raw('his_treatment.approval_store_stt_id'));
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->whereNull(DB::connection('oracle_his')->raw('his_treatment.approval_store_stt_id'));
                });
            }
           }

            if ($this->treatment_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_treatment.' . $key, $this->sub_order_by ?? $item);
                    }
                }
                // Chuyển truy vấn sang chuỗi sql
                $sql = $data->toSql();
                $sql_id = $data_id->toSql();
                // Truyền tham số qua binding tránh SQL Injection
                $bindings = $data->getBindings();
                $bindings_id = $data_id->getBindings();
                $id_max_sql = DB::connection('oracle_his')->select('SELECT a.ID, ROWNUM  FROM (' . $sql_id . ' order by ID desc) a  WHERE ROWNUM = 1 ', $bindings_id);
                $id_min_sql = DB::connection('oracle_his')->select('SELECT a.ID, ROWNUM  FROM (' . $sql_id . ' order by ID asc) a  WHERE ROWNUM = 1 ', $bindings_id);
                $id_max_sql = intval($id_max_sql[0]->id ?? null);
                $id_min_sql = intval($id_min_sql[0]->id ?? null);

                $fullSql = 'SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (' . $sql . ') a WHERE ROWNUM <= ' . ($this->limit + $this->start) . ' AND ID ' . $this->equal . $this->cursor . $this->sub_order_by_string . ') WHERE rnum > ' . $this->start;
                $data = DB::connection('oracle_his')->select($fullSql, $bindings);
                $data = TreatmentGetFeeViewResource::collection($data);
                if (isset($data[0])) {
                    if (($data[0]->id != $this->treatment->max('id')) && ($data[0]->id != $this->treatment->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)) {
                        $this->prev_cursor = '-' . $data[0]->id;
                    } else {
                        $this->prev_cursor = null;
                    }
                    if (((count($data) === 1) && ($this->order_by["id"] == 'desc') && ($data[0]->id == $id_min_sql))
                        || ((count($data) === 1) && ($this->order_by["id"] == 'asc') && ($data[0]->id == $id_max_sql))
                    ) {
                        $this->prev_cursor = '-' . $data[0]->id;
                    }
                    if ($this->raw_cursor == 0) {
                        $this->prev_cursor = null;
                    }
                    $this->next_cursor = $data[($this->limit - 1)]->id ?? null;
                    if (($this->next_cursor == $id_max_sql && ($this->order_by["id"] == 'asc')) || ($this->next_cursor == $id_min_sql && ($this->order_by["id"] == 'desc'))) {
                        $this->next_cursor = null;
                    }
                }
            } else {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.id'), $this->treatment_id);
                });
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->first();
            }

            $param_return = [
                'prev_cursor' => $this->prev_cursor ?? null,
                'limit' => $this->limit,
                'next_cursor' => $this->next_cursor ?? null,
                'is_include_deleted' => $this->is_include_deleted ?? false,
                'is_active' => $this->is_active,
                'treatment_id' => $this->treatment_id,
                'tdl_treatment_type_ids' => $this->tdl_treatment_type_ids,
                'tdl_patient_type_ids' => $this->tdl_patient_type_ids,
                'branch_id' => $this->branch_id,
                'in_date_from' => $this->in_date_from,
                'in_date_to' => $this->in_date_to,
                'is_approve_store' => $this->is_approve_store,
                'keyword' => $this->keyword,
                'order_by' => $this->order_by_request
            ];
            return return_data_success($param_return, $data);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
}
