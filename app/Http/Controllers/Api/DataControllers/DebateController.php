<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Resources\DebateGetViewResource;
use App\Http\Resources\DebateResource;
use App\Models\HIS\Debate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DebateController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->debate = new Debate();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->debate);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if ($this->cursor === 0) {
                $this->debate_last_id = $this->debate->max('id');
                $this->cursor = $this->debate_last_id;
                $this->equal = "<=";
            }
        }
        if($this->cursor < 0){
            $this->sub_order_by = (strtolower($this->order_by["id"]) === 'asc') ? 'desc' : 'asc';
            $this->equal = (strtolower($this->order_by["id"]) === 'desc') ? '>' : '<';

            $this->sub_order_by_string = ' ORDER BY ID '.$this->order_by["id"];
            $this->cursor = abs($this->cursor);
        }
    }

    public function debate_get()
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            'his_debate.id',
            'his_debate.create_time',
            'his_debate.modify_time',
            'his_debate.creator',
            'his_debate.modifier',
            'his_debate.app_creator',
            'his_debate.app_modifier',
            'his_debate.is_active',
            'his_debate.is_delete',
            'his_debate.icd_code',
            'his_debate.icd_name',
            'his_debate.icd_sub_code',
            'his_debate.icd_text',
            'his_debate.debate_time',
            'his_debate.request_loginname',
            'his_debate.request_username',
            'his_debate.treatment_tracking',
            'his_debate.treatment_from_time',
            'his_debate.treatment_method',
            'his_debate.location',
            'his_debate.pathological_history',
            'his_debate.hospitalization_state',
            'his_debate.before_diagnostic',
            'his_debate.diagnostic',
            'his_debate.care_method',
            'his_debate.conclusion',
            'his_debate.discussion',
            'his_debate.medicine_use_form_name',
            'his_debate.medicine_type_name',
            'his_debate.treatment_id',
            'his_debate.icd_id__delete',
            'his_debate.debate_type_id',
            'his_debate.department_id',
            'his_debate.surgery_service_id',
            'his_debate.emotionless_method_id',
            'his_debate.pttt_method_id',
            'his_debate.tracking_id',
            'his_debate.service_id',
            'his_debate.debate_reason_id',
            'his_debate.medicine_type_ids',
            'his_debate.active_ingredient_ids',
        ];

        $param = [
            // 'icddelete:id,icd_name,icd_code',
            // 'surgery_service:id,service_name,service_code',
            // 'emotionless_method:id,emotionless_method_name,emotionless_method_code',
            // 'pttt_method:id,pttt_method_name,pttt_method_code',
            // 'tracking:id,medical_instruction,content',
            // 'service:id,service_name,service_code',
            // 'debate_reason:id,debate_reason_name',

            'debate_ekip_users:id,debate_id,loginname,username,execute_role_id,department_id',
            'debate_ekip_users.execute_role:id,execute_role_name,execute_role_code',
            'debate_ekip_users.department:id,department_name,department_code',
            'debate_invite_users:id,debate_id,loginname,username,execute_role_id',
            'debate_invite_users.execute_role:id,execute_role_name,execute_role_code',
            'debate_users:id,debate_id,loginname,username,execute_role_id',
            'debate_users.execute_role:id,execute_role_name,execute_role_code'
        ];
        $keyword = $this->keyword;
        $data = $this->debate
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate.icd_code'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_debate.icd_name'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_debate.icd_sub_code'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate.is_active'), $this->is_active);
            });
        }
        if ($this->treatment_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate.treatment_id'), $this->treatment_id);
            });
        }


        if ($this->debate_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_debate.' . $key, $item);
                }
            }
            $data = $data->with($param);
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate.id'), $this->debate_id);
            });
            $data = $data->with($param);
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->first();
        }

        $param_return = [
            $this->start_name => $this->start,
            $this->limit_name => $this->limit,
            $this->count_name => $count ?? null,
            $this->is_include_deleted_name => $this->is_include_deleted ?? false,
            $this->is_active_name => $this->is_active,
            $this->debate_id_name => $this->debate_id,
            $this->treatment_id_name => $this->treatment_id,
            $this->keyword_name => $this->keyword,
            $this->order_by_name => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function debate_get_v2()
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            'his_debate.id',
            'his_debate.create_time',
            'his_debate.modify_time',
            'his_debate.creator',
            'his_debate.modifier',
            'his_debate.app_creator',
            'his_debate.app_modifier',
            'his_debate.is_active',
            'his_debate.is_delete',
            'his_debate.icd_code',
            'his_debate.icd_name',
            'his_debate.icd_sub_code',
            'his_debate.icd_text',
            'his_debate.debate_time',
            'his_debate.request_loginname',
            'his_debate.request_username',
            'his_debate.treatment_tracking',
            'his_debate.treatment_from_time',
            'his_debate.treatment_method',
            'his_debate.location',
            'his_debate.pathological_history',
            'his_debate.hospitalization_state',
            'his_debate.before_diagnostic',
            'his_debate.diagnostic',
            'his_debate.care_method',
            'his_debate.conclusion',
            'his_debate.discussion',
            'his_debate.medicine_use_form_name',
            'his_debate.medicine_type_name',
            'his_debate.treatment_id',
            'his_debate.icd_id__delete',
            'his_debate.debate_type_id',
            'his_debate.department_id',
            'his_debate.surgery_service_id',
            'his_debate.emotionless_method_id',
            'his_debate.pttt_method_id',
            'his_debate.tracking_id',
            'his_debate.service_id',
            'his_debate.debate_reason_id',
            'his_debate.medicine_type_ids',
            'his_debate.active_ingredient_ids',
        ];

        $param = [
            // 'icddelete:id,icd_name,icd_code',
            // 'surgery_service:id,service_name,service_code',
            // 'emotionless_method:id,emotionless_method_name,emotionless_method_code',
            // 'pttt_method:id,pttt_method_name,pttt_method_code',
            // 'tracking:id,medical_instruction,content',
            // 'service:id,service_name,service_code',
            // 'debate_reason:id,debate_reason_name',

            'debate_ekip_users:id,debate_id,loginname,username,execute_role_id,department_id',
            'debate_ekip_users.execute_role:id,execute_role_name,execute_role_code',
            'debate_ekip_users.department:id,department_name,department_code',
            'debate_invite_users:id,debate_id,loginname,username,execute_role_id',
            'debate_invite_users.execute_role:id,execute_role_name,execute_role_code',
            'debate_users:id,debate_id,loginname,username,execute_role_id',
            'debate_users.execute_role:id,execute_role_name,execute_role_code'
        ];
        $keyword = $this->keyword;
        try {
            $data = $this->debate
            ->select($select);
            $data_id = $this->debate
            ->select("HIS_DEBATE.ID");
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.icd_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_debate.icd_name'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_debate.icd_sub_code'), 'like', $keyword . '%');
                });
                $data_id = $data_id->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.icd_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_debate.icd_name'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_debate.icd_sub_code'), 'like', $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.is_active'), $this->is_active);
                });
            }
            if ($this->treatment_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.treatment_id'), $this->treatment_id);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.treatment_id'), $this->treatment_id);
                });
            }


            if ($this->debate_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_debate.' . $key, $this->sub_order_by ?? $item);
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
                $fullSql = 'SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (' . $sql . ') a WHERE ROWNUM <= ' . ($this->limit + $this->start) . ' AND ID ' . $this->equal . $this->cursor . $this->sub_order_by_string. ') WHERE rnum > ' . $this->start;
                $data = DB::connection('oracle_his')->select($fullSql, $bindings);
                $data = DebateResource::collection($data);
                if(isset($data[0])){
                    if(($data[0]->id != $this->debate->max('id')) && ($data[0]->id != $this->debate->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)){
                        $this->prev_cursor = '-'.$data[0]->id;
                    }else{
                        $this->prev_cursor = null;
                    }
                    if(((count($data) === 1) && ($this->order_by["id"] == 'desc') && ($data[0]->id == $id_min_sql)) 
                    || ((count($data) === 1) && ($this->order_by["id"] == 'asc') && ($data[0]->id == $id_max_sql))){
                        $this->prev_cursor = '-'.$data[0]->id;
                    }
                    if($this->raw_cursor == 0){
                        $this->prev_cursor = null;
                    }
                    $this->next_cursor = $data[($this->limit - 1)]->id ?? null;
                    if(($this->next_cursor == $id_max_sql && ($this->order_by["id"] == 'asc') ) || ($this->next_cursor == $id_min_sql && ($this->order_by["id"] == 'desc'))){
                        $this->next_cursor = null;
                    }
                }
            } else {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.id'), $this->debate_id);
                });
                $data = $data->with($param);
                $data = $data
                    ->first();
            }

            $param_return = [
                $this->prev_cursor_name => $this->prev_cursor ?? null,
                $this->limit_name => $this->limit,
                $this->next_cursor_name => $this->next_cursor ?? null,
                $this->is_include_deleted_name => $this->is_include_deleted ?? false,
                $this->is_active_name => $this->is_active,
                $this->debate_id_name => $this->debate_id,
                $this->treatment_id_name => $this->treatment_id,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }


    public function debate_get_view()
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "his_debate.ID",
            "his_debate.CREATE_TIME",
            "his_debate.MODIFY_TIME",
            "his_debate.CREATOR",
            "his_debate.MODIFIER",
            "his_debate.APP_CREATOR",
            "his_debate.APP_MODIFIER",
            "his_debate.IS_ACTIVE",
            "his_debate.IS_DELETE",
            "his_debate.TREATMENT_ID",
            "his_debate.ICD_CODE",
            "his_debate.ICD_NAME",
            "his_debate.ICD_SUB_CODE",
            "his_debate.ICD_TEXT",
            "his_debate.DEPARTMENT_ID",
            "his_debate.DEBATE_TIME",
            "his_debate.REQUEST_LOGINNAME",
            "his_debate.REQUEST_USERNAME",
            "his_debate.TREATMENT_TRACKING",
            "his_debate.TREATMENT_FROM_TIME",
            "his_debate.TREATMENT_TO_TIME",
            "his_debate.LOCATION",
            "his_debate.CONCLUSION",
            "his_debate.DEBATE_TYPE_ID",
            "his_debate.CONTENT_TYPE",
            "his_debate.SUBCLINICAL_PROCESSES",
            "his_debate.EMOTIONLESS_METHOD_ID",
            "his_debate.SURGERY_TIME",
            "his_debate.PTTT_METHOD_ID",

            "treatment.patient_id",
            "treatment.treatment_code",
            "treatment.tdl_patient_first_name",
            "treatment.tdl_patient_last_name",
            "treatment.tdl_patient_name",
            "treatment.tdl_patient_dob",
            "treatment.tdl_patient_address",
            "treatment.tdl_patient_gender_name",

            "department.department_code",
            "department.department_name",

            "debate_type.debate_type_code",
            "debate_type.debate_type_name",

            "emotionless_method.emotionless_method_code",
            "emotionless_method.emotionless_method_name",

            "debate_reason.debate_reason_code",
            "debate_reason.debate_reason_name",
        ];

        $param = [
            'treatment:id,patient_id,treatment_code,tdl_patient_first_name,tdl_patient_last_name,tdl_patient_name,tdl_patient_dob,tdl_patient_address,tdl_patient_gender_name',
            'department:id,department_code,department_name',
            'debate_type:id,debate_type_name,debate_type_code',
            'emotionless_method:id,emotionless_method_name,emotionless_method_code',
            'pttt_method:id,pttt_method_name,pttt_method_code',
            'debate_reason:id,debate_reason_name,debate_reason_code',

        ];

        $keyword = $this->keyword;
        $data = $this->debate
            ->leftJoin('his_treatment as treatment', 'treatment.id', '=', 'his_debate.treatment_id')
            ->leftJoin('his_department as department', 'department.id', '=', 'his_debate.department_id')
            ->leftJoin('his_debate_type as debate_type', 'debate_type.id', '=', 'his_debate.debate_type_id')
            ->leftJoin('his_emotionless_method as emotionless_method', 'emotionless_method.id', '=', 'his_debate.emotionless_method_id')
            ->leftJoin('his_debate_reason as debate_reason', 'debate_reason.id', '=', 'his_debate.debate_reason_id')

            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate.icd_code'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_debate.icd_name'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_debate.icd_sub_code'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate.is_active'), $this->is_active);
            });
        }
        if ($this->treatment_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate.treatment_id'), $this->treatment_id);
            });
        }
        if ($this->treatment_code != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('treatment.treatment_code'), $this->treatment_code);
            });
        }
        if ($this->department_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_debate.department_id'), $this->department_ids);
            });
        }

        if ($this->debate_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_debate.' . $key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate.id'), $this->debate_id);
            });
            $data = $data->first();
        }

        $param_return = [
            $this->start_name => $this->start,
            $this->limit_name => $this->limit,
            $this->count_name => $count ?? null,
            $this->is_include_deleted_name => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active ?? null,
            $this->debate_id_name => $this->debate_id,
            $this->treatment_id_name => $this->treatment_id,
            'treatment_code' => $this->treatment_code,
            'department_ids' => $this->department_ids,
            $this->keyword_name => $this->keyword,
            $this->order_by_name => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function debate_get_view_v2()
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "his_debate.ID",
            "his_debate.CREATE_TIME",
            "his_debate.MODIFY_TIME",
            "his_debate.CREATOR",
            "his_debate.MODIFIER",
            "his_debate.APP_CREATOR",
            "his_debate.APP_MODIFIER",
            "his_debate.IS_ACTIVE",
            "his_debate.IS_DELETE",
            "his_debate.TREATMENT_ID",
            "his_debate.ICD_CODE",
            "his_debate.ICD_NAME",
            "his_debate.ICD_SUB_CODE",
            "his_debate.ICD_TEXT",
            "his_debate.DEPARTMENT_ID",
            "his_debate.DEBATE_TIME",
            "his_debate.REQUEST_LOGINNAME",
            "his_debate.REQUEST_USERNAME",
            "his_debate.TREATMENT_TRACKING",
            "his_debate.TREATMENT_FROM_TIME",
            "his_debate.TREATMENT_TO_TIME",
            "his_debate.LOCATION",
            "his_debate.CONCLUSION",
            "his_debate.DEBATE_TYPE_ID",
            "his_debate.CONTENT_TYPE",
            "his_debate.SUBCLINICAL_PROCESSES",
            "his_debate.EMOTIONLESS_METHOD_ID",
            "his_debate.SURGERY_TIME",
            "his_debate.PTTT_METHOD_ID",

            "treatment.patient_id",
            "treatment.treatment_code",
            "treatment.tdl_patient_first_name",
            "treatment.tdl_patient_last_name",
            "treatment.tdl_patient_name",
            "treatment.tdl_patient_dob",
            "treatment.tdl_patient_address",
            "treatment.tdl_patient_gender_name",

            "department.department_code",
            "department.department_name",

            "debate_type.debate_type_code",
            "debate_type.debate_type_name",

            "emotionless_method.emotionless_method_code",
            "emotionless_method.emotionless_method_name",

            "debate_reason.debate_reason_code",
            "debate_reason.debate_reason_name",
        ];

        $param = [
            'treatment:id,patient_id,treatment_code,tdl_patient_first_name,tdl_patient_last_name,tdl_patient_name,tdl_patient_dob,tdl_patient_address,tdl_patient_gender_name',
            'department:id,department_code,department_name',
            'debate_type:id,debate_type_name,debate_type_code',
            'emotionless_method:id,emotionless_method_name,emotionless_method_code',
            'pttt_method:id,pttt_method_name,pttt_method_code',
            'debate_reason:id,debate_reason_name,debate_reason_code',

        ];

        $keyword = $this->keyword;
        try {
            $data = $this->debate
                ->leftJoin('his_treatment as treatment', 'treatment.id', '=', 'his_debate.treatment_id')
                ->leftJoin('his_department as department', 'department.id', '=', 'his_debate.department_id')
                ->leftJoin('his_debate_type as debate_type', 'debate_type.id', '=', 'his_debate.debate_type_id')
                ->leftJoin('his_emotionless_method as emotionless_method', 'emotionless_method.id', '=', 'his_debate.emotionless_method_id')
                ->leftJoin('his_debate_reason as debate_reason', 'debate_reason.id', '=', 'his_debate.debate_reason_id')

                ->select($select);
            $data_id = $this->debate
                ->leftJoin('his_treatment as treatment', 'treatment.id', '=', 'his_debate.treatment_id')
                ->leftJoin('his_department as department', 'department.id', '=', 'his_debate.department_id')
                ->leftJoin('his_debate_type as debate_type', 'debate_type.id', '=', 'his_debate.debate_type_id')
                ->leftJoin('his_emotionless_method as emotionless_method', 'emotionless_method.id', '=', 'his_debate.emotionless_method_id')
                ->leftJoin('his_debate_reason as debate_reason', 'debate_reason.id', '=', 'his_debate.debate_reason_id')

                ->select("HIS_DEBATE.ID");
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.icd_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_debate.icd_name'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_debate.icd_sub_code'), 'like', $keyword . '%');
                });
                $data_id = $data_id->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.icd_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_debate.icd_name'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_debate.icd_sub_code'), 'like', $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.is_active'), $this->is_active);
                });
            }
            if ($this->treatment_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.treatment_id'), $this->treatment_id);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.treatment_id'), $this->treatment_id);
                });
            }
            if ($this->treatment_code != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('treatment.treatment_code'), $this->treatment_code);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('treatment.treatment_code'), $this->treatment_code);
                });
            }
            if ($this->department_ids != null) {
                $data = $data->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_debate.department_id'), $this->department_ids);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_debate.department_id'), $this->department_ids);
                });
            }

            if ($this->debate_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_debate.' . $key, $this->sub_order_by ?? $item);
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
                $fullSql = 'SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (' . $sql . ') a WHERE ROWNUM <= ' . ($this->limit + $this->start) . ' AND ID ' . $this->equal . $this->cursor . $this->sub_order_by_string. ') WHERE rnum > ' . $this->start;
                $data = DB::connection('oracle_his')->select($fullSql, $bindings);
                $data = DebateGetViewResource::collection($data);
                if(isset($data[0])){
                    if(($data[0]->id != $this->debate->max('id')) && ($data[0]->id != $this->debate->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)){
                        $this->prev_cursor = '-'.$data[0]->id;
                    }else{
                        $this->prev_cursor = null;
                    }
                    if(((count($data) === 1) && ($this->order_by["id"] == 'desc') && ($data[0]->id == $id_min_sql)) 
                    || ((count($data) === 1) && ($this->order_by["id"] == 'asc') && ($data[0]->id == $id_max_sql))){
                        $this->prev_cursor = '-'.$data[0]->id;
                    }
                    if($this->raw_cursor == 0){
                        $this->prev_cursor = null;
                    }
                    $this->next_cursor = $data[($this->limit - 1)]->id ?? null;
                    if(($this->next_cursor == $id_max_sql && ($this->order_by["id"] == 'asc') ) || ($this->next_cursor == $id_min_sql && ($this->order_by["id"] == 'desc'))){
                        $this->next_cursor = null;
                    }
                 }
            } else {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate.id'), $this->debate_id);
                });
                $data = $data->first();
            }

            $param_return = [
                $this->prev_cursor_name => $this->prev_cursor ?? null,
                $this->limit_name => $this->limit,
                $this->next_cursor_name => $this->next_cursor ?? null,
                $this->is_include_deleted_name => $this->is_include_deleted ?? false,
                'is_active' => $this->is_active ?? null,
                $this->debate_id_name => $this->debate_id,
                $this->treatment_id_name => $this->treatment_id,
                'treatment_code' => $this->treatment_code,
                'department_ids' => $this->department_ids,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
}
