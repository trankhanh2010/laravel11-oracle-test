<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Resources\PatientTypeAlterResource;
use App\Models\HIS\PatientTypeAlter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PatientTypeAlterController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->patient_type_alter = new PatientTypeAlter();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->patient_type_alter);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if ($this->cursor === 0) {
                $this->patient_type_alter_last_id = $this->patient_type_alter->max('id');
                $this->cursor = $this->patient_type_alter_last_id;
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
    public function patient_type_alter_get_view(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "his_patient_type_alter.ID",
            "his_patient_type_alter.CREATE_TIME",
            "his_patient_type_alter.MODIFY_TIME",
            "his_patient_type_alter.CREATOR",
            "his_patient_type_alter.MODIFIER",
            "his_patient_type_alter.APP_CREATOR",
            "his_patient_type_alter.APP_MODIFIER",
            "his_patient_type_alter.IS_ACTIVE",
            "his_patient_type_alter.IS_DELETE",
            "his_patient_type_alter.DEPARTMENT_TRAN_ID",
            "his_patient_type_alter.TREATMENT_TYPE_ID",
            "his_patient_type_alter.PATIENT_TYPE_ID",
            "his_patient_type_alter.LOG_TIME",
            "his_patient_type_alter.TREATMENT_ID",
            "his_patient_type_alter.TDL_PATIENT_ID",
            "his_patient_type_alter.EXECUTE_ROOM_ID",
            "his_patient_type_alter.LEVEL_CODE",
            "his_patient_type_alter.RIGHT_ROUTE_CODE",
            "his_patient_type_alter.RIGHT_ROUTE_TYPE_CODE",
            "his_patient_type_alter.HEIN_MEDI_ORG_CODE",
            "his_patient_type_alter.HEIN_MEDI_ORG_NAME",
            "his_patient_type_alter.HAS_BIRTH_CERTIFICATE",
            "his_patient_type_alter.HEIN_CARD_NUMBER",
            "his_patient_type_alter.HEIN_CARD_FROM_TIME",
            "his_patient_type_alter.HEIN_CARD_TO_TIME",
            "his_patient_type_alter.ADDRESS",
            "his_patient_type_alter.JOIN_5_YEAR",
            "his_patient_type_alter.PAID_6_MONTH",
            "his_patient_type_alter.PRIMARY_PATIENT_TYPE_ID",

            "patient_type.patient_type_code",
            "patient_type.patient_type_name",
            "patient_type.IS_COPAYMENT",

            "treatment_type.treatment_type_code",
            "treatment_type.treatment_type_name",
            "treatment_type.HEIN_TREATMENT_TYPE_CODE"
        ];
        $param = [
            'patient_type:id,patient_type_code,patient_type_name,IS_COPAYMENT',
            'treatment_type:id,treatment_type_code,treatment_type_name,HEIN_TREATMENT_TYPE_CODE'
        ];
        $keyword = $this->keyword;
        $data = $this->patient_type_alter
            ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_patient_type_alter.patient_type_id')
            ->leftJoin('his_treatment_type as treatment_type', 'treatment_type.id', '=', 'his_patient_type_alter.treatment_type_id')
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.HAS_BIRTH_CERTIFICATE'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_patient_type_alter.HEIN_CARD_NUMBER'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.is_active'), $this->is_active);
            });
        }
        if ($this->treatment_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.treatment_id'), $this->treatment_id);
            });
        }
        if ($this->log_time_to != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.log_time'), '<=', $this->log_time_to);
            });
        }
        if ($this->patient_type_alter_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_patient_type_alter.' . $key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.id'), $this->patient_type_alter_id);
            });
            $data = $data
                ->first();
        }
        $param_return = [
            $this->start_name => $this->start,
            $this->limit_name => $this->limit,
            $this->count_name => $count ?? null,
            $this->is_include_deleted_name => $this->is_include_deleted ?? false,
            $this->is_active_name => $this->is_active,
            $this->patient_type_alter_id_name => $this->patient_type_alter_id,
            $this->treatment_id_name => $this->treatment_id,
            $this->log_time_to_name => $this->log_time_to,
            $this->keyword_name => $this->keyword,
            $this->order_by_name => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function patient_type_alter_get_view_v2(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
        
        $select = [
            "his_patient_type_alter.ID",
            "his_patient_type_alter.CREATE_TIME",
            "his_patient_type_alter.MODIFY_TIME",
            "his_patient_type_alter.CREATOR",
            "his_patient_type_alter.MODIFIER",
            "his_patient_type_alter.APP_CREATOR",
            "his_patient_type_alter.APP_MODIFIER",
            "his_patient_type_alter.IS_ACTIVE",
            "his_patient_type_alter.IS_DELETE",
            "his_patient_type_alter.DEPARTMENT_TRAN_ID",
            "his_patient_type_alter.TREATMENT_TYPE_ID",
            "his_patient_type_alter.PATIENT_TYPE_ID",
            "his_patient_type_alter.LOG_TIME",
            "his_patient_type_alter.TREATMENT_ID",
            "his_patient_type_alter.TDL_PATIENT_ID",
            "his_patient_type_alter.EXECUTE_ROOM_ID",
            "his_patient_type_alter.LEVEL_CODE",
            "his_patient_type_alter.RIGHT_ROUTE_CODE",
            "his_patient_type_alter.RIGHT_ROUTE_TYPE_CODE",
            "his_patient_type_alter.HEIN_MEDI_ORG_CODE",
            "his_patient_type_alter.HEIN_MEDI_ORG_NAME",
            "his_patient_type_alter.HAS_BIRTH_CERTIFICATE",
            "his_patient_type_alter.HEIN_CARD_NUMBER",
            "his_patient_type_alter.HEIN_CARD_FROM_TIME",
            "his_patient_type_alter.HEIN_CARD_TO_TIME",
            "his_patient_type_alter.ADDRESS",
            "his_patient_type_alter.JOIN_5_YEAR",
            "his_patient_type_alter.PAID_6_MONTH",
            "his_patient_type_alter.PRIMARY_PATIENT_TYPE_ID",

            "patient_type.patient_type_code",
            "patient_type.patient_type_name",
            "patient_type.IS_COPAYMENT",

            "treatment_type.treatment_type_code",
            "treatment_type.treatment_type_name",
            "treatment_type.HEIN_TREATMENT_TYPE_CODE"
        ];
        $param = [
            'patient_type:id,patient_type_code,patient_type_name,IS_COPAYMENT',
            'treatment_type:id,treatment_type_code,treatment_type_name,HEIN_TREATMENT_TYPE_CODE'
        ];
        $keyword = $this->keyword;
        try {

            $data = $this->patient_type_alter
                ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_patient_type_alter.patient_type_id')
                ->leftJoin('his_treatment_type as treatment_type', 'treatment_type.id', '=', 'his_patient_type_alter.treatment_type_id')
                ->select($select);
            $data_id = $this->patient_type_alter
                ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_patient_type_alter.patient_type_id')
                ->leftJoin('his_treatment_type as treatment_type', 'treatment_type.id', '=', 'his_patient_type_alter.treatment_type_id')
                ->select("HIS_PATIENT_TYPE_ALTER.ID");
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.HAS_BIRTH_CERTIFICATE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_patient_type_alter.HEIN_CARD_NUMBER'), 'like', $keyword . '%');
                });
                $data_id = $data_id->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.HAS_BIRTH_CERTIFICATE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_patient_type_alter.HEIN_CARD_NUMBER'), 'like', $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.is_active'), $this->is_active);
                });
            }
            if ($this->treatment_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.treatment_id'), $this->treatment_id);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.treatment_id'), $this->treatment_id);
                });
            }
            if ($this->log_time_to != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.log_time'), '<=', $this->log_time_to);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.log_time'), '<=', $this->log_time_to);
                });
            }
            if ($this->patient_type_alter_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_patient_type_alter.' . $key,  $this->sub_order_by ?? $item);
                    }
                }

                // Chuyển truy vấn sang chuỗi sql
                $data = $data->with($param);
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
                $data = PatientTypeAlterResource::collection($data);
                if (isset($data[0])) {
                    if (($data[0]->id != $this->patient_type_alter->max('id')) && ($data[0]->id != $this->patient_type_alter->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)) {
                        $this->prev_cursor = '-' . $data[0]->id;
                    } else {
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
                    $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.id'), $this->patient_type_alter_id);
                });
                $data = $data
                    ->first();
            }
            $param_return = [
                $this->prev_cursor_name => $this->prev_cursor ?? null,
                $this->limit_name => $this->limit,
                $this->next_cursor_name => $this->next_cursor ?? null,
                $this->is_include_deleted_name => $this->is_include_deleted ?? false,
                $this->is_active_name => $this->is_active,
                $this->patient_type_alter_id_name => $this->patient_type_alter_id,
                $this->treatment_id_name => $this->treatment_id,
                $this->log_time_to_name => $this->log_time_to,
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
