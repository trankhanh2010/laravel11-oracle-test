<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Controllers\Controller;
use App\Http\Resources\TreatmentBedRoomResource;
use App\Models\HIS\Treatment;
use App\Models\HIS\TreatmentBedRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TreatmentBedRoomController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->treatment_bed_room = new TreatmentBedRoom();
        $this->treatment = new Treatment();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->treatment_bed_room);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }

        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if($this->cursor === 0){
                $this->treatment_bed_room_last_id = $this->treatment_bed_room->max('id');
                $this->cursor = $this->treatment_bed_room_last_id;
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
    public function treatment_bed_room_get_L_view(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "his_treatment_bed_room.ID",
            "his_treatment_bed_room.TREATMENT_ID",
            "his_treatment_bed_room.CO_TREATMENT_ID",
            "his_treatment_bed_room.ADD_TIME",
            "his_treatment_bed_room.REMOVE_TIME",
            "his_treatment_bed_room.BED_ROOM_ID",

            "treatment.TDL_PATIENT_FIRST_NAME",
            'treatment.TREATMENT_CODE',
            'treatment.TDL_PATIENT_LAST_NAME',
            'treatment.TDL_PATIENT_NAME',
            'treatment.TDL_PATIENT_DOB',
            'treatment.TDL_PATIENT_GENDER_NAME',
            'treatment.TDL_PATIENT_CODE',
            'treatment.TDL_PATIENT_ADDRESS',
            'treatment.TDL_HEIN_CARD_NUMBER',
            'treatment.TDL_HEIN_MEDI_ORG_CODE',
            'treatment.ICD_CODE',
            'treatment.ICD_NAME',
            'treatment.ICD_TEXT',
            'treatment.ICD_SUB_CODE',
            'treatment.TDL_PATIENT_GENDER_ID',
            'treatment.TDL_HEIN_MEDI_ORG_NAME',
            'treatment.TDL_TREATMENT_TYPE_ID',
            'treatment.EMR_COVER_TYPE_ID',
            'treatment.CLINICAL_IN_TIME',
            'treatment.CO_TREAT_DEPARTMENT_IDS',
            'treatment.LAST_DEPARTMENT_ID',
            'treatment.TDL_PATIENT_UNSIGNED_NAME',
            'treatment.TREATMENT_METHOD',
            'treatment.TDL_HEIN_CARD_FROM_TIME',
            'treatment.TDL_HEIN_CARD_TO_TIME',

            'patient_type.patient_type_code',
            'patient_type.patient_type_name',

            'last_department.department_code',
            'last_department.department_name',

            'patient.note',

            'bed_room.bed_room_name',

        ];
        $param = [
            'treatment',
            'treatment.patient_type:id,patient_type_code,patient_type_name',
            'treatment.last_department:id,department_code,department_name',
            'treatment.patient:id,note',
            'bed_room:id,bed_room_name'
        ];
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        $data = $this->treatment_bed_room
            ->leftJoin('his_treatment as treatment', 'treatment.id', '=', 'his_treatment_bed_room.treatment_id')
            ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'treatment.tdl_patient_type_id')
            ->leftJoin('his_department as last_department', 'last_department.id', '=', 'treatment.last_department_id')
            ->leftJoin('his_patient as patient', 'patient.id', '=', 'treatment.patient_id')
            ->leftJoin('his_bed_room as bed_room', 'bed_room.id', '=', 'his_treatment_bed_room.bed_room_id')
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('treatment.TDL_PATIENT_LAST_NAME'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('treatment.TREATMENT_CODE'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.is_active'), $this->is_active);
            });
        }
        if ($this->bed_room_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_treatment_bed_room.bed_room_id'), $this->bed_room_ids);
            });
        }
        if ($this->add_time_to != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.add_time'), '>=', $this->add_time_to);
            });
        }
        if (!$this->is_in_room) {
            if ($this->add_time_from != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.remove_time'), '<=', $this->add_time_from);
                });
            }
        }
        if ($this->treatment_bed_room_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_treatment_bed_room.' . $key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.id'), $this->treatment_bed_room_id);
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
            'treatment_bed_room_id' => $this->treatment_bed_room_id,
            'is_in_room' => $this->is_in_room,
            'add_time_to' => $this->add_time_to,
            'add_time_from' => $this->add_time_from,
            'bed_room_ids' => $this->bed_room_ids,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function treatment_bed_room_get_L_view_v2(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "his_treatment_bed_room.ID",
            "his_treatment_bed_room.TREATMENT_ID",
            "his_treatment_bed_room.CO_TREATMENT_ID",
            "his_treatment_bed_room.ADD_TIME",
            "his_treatment_bed_room.REMOVE_TIME",
            "his_treatment_bed_room.BED_ROOM_ID",

            "treatment.TDL_PATIENT_FIRST_NAME",
            'treatment.TREATMENT_CODE',
            'treatment.TDL_PATIENT_LAST_NAME',
            'treatment.TDL_PATIENT_NAME',
            'treatment.TDL_PATIENT_DOB',
            'treatment.TDL_PATIENT_GENDER_NAME',
            'treatment.TDL_PATIENT_CODE',
            'treatment.TDL_PATIENT_ADDRESS',
            'treatment.TDL_HEIN_CARD_NUMBER',
            'treatment.TDL_HEIN_MEDI_ORG_CODE',
            'treatment.ICD_CODE',
            'treatment.ICD_NAME',
            'treatment.ICD_TEXT',
            'treatment.ICD_SUB_CODE',
            'treatment.TDL_PATIENT_GENDER_ID',
            'treatment.TDL_HEIN_MEDI_ORG_NAME',
            'treatment.TDL_TREATMENT_TYPE_ID',
            'treatment.EMR_COVER_TYPE_ID',
            'treatment.CLINICAL_IN_TIME',
            'treatment.CO_TREAT_DEPARTMENT_IDS',
            'treatment.LAST_DEPARTMENT_ID',
            'treatment.TDL_PATIENT_UNSIGNED_NAME',
            'treatment.TREATMENT_METHOD',
            'treatment.TDL_HEIN_CARD_FROM_TIME',
            'treatment.TDL_HEIN_CARD_TO_TIME',

            'patient_type.patient_type_code',
            'patient_type.patient_type_name',

            'last_department.department_code',
            'last_department.department_name',

            'patient.note',

            'bed_room.bed_room_name',

        ];
        $param = [
            'treatment',
            'treatment.patient_type:id,patient_type_code,patient_type_name',
            'treatment.last_department:id,department_code,department_name',
            'treatment.patient:id,note',
            'bed_room:id,bed_room_name'
        ];
        $keyword = $this->keyword;
        try {
            $data = $this->treatment_bed_room
                ->leftJoin('his_treatment as treatment', 'treatment.id', '=', 'his_treatment_bed_room.treatment_id')
                ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'treatment.tdl_patient_type_id')
                ->leftJoin('his_department as last_department', 'last_department.id', '=', 'treatment.last_department_id')
                ->leftJoin('his_patient as patient', 'patient.id', '=', 'treatment.patient_id')
                ->leftJoin('his_bed_room as bed_room', 'bed_room.id', '=', 'his_treatment_bed_room.bed_room_id')
                ->select($select);
            $data_id = $this->treatment_bed_room
                ->leftJoin('his_treatment as treatment', 'treatment.id', '=', 'his_treatment_bed_room.treatment_id')
                ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'treatment.tdl_patient_type_id')
                ->leftJoin('his_department as last_department', 'last_department.id', '=', 'treatment.last_department_id')
                ->leftJoin('his_patient as patient', 'patient.id', '=', 'treatment.patient_id')
                ->leftJoin('his_bed_room as bed_room', 'bed_room.id', '=', 'his_treatment_bed_room.bed_room_id')
                ->select("His_treatment_bed_room.id");
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('treatment.TDL_PATIENT_LAST_NAME'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('treatment.TREATMENT_CODE'), 'like', $keyword . '%');
                });
                $data_id = $data_id->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('treatment.TDL_PATIENT_LAST_NAME'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('treatment.TREATMENT_CODE'), 'like', $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.is_active'), $this->is_active);
                });
            }
            if ($this->bed_room_ids != null) {
                $data = $data->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_treatment_bed_room.bed_room_id'), $this->bed_room_ids);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_treatment_bed_room.bed_room_id'), $this->bed_room_ids);
                });
            }
            if ($this->add_time_to != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.add_time'), '>=', $this->add_time_to);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.add_time'), '>=', $this->add_time_to);
                });
            }
            if (!$this->is_in_room) {
                if ($this->add_time_from != null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.remove_time'), '<=', $this->add_time_from);
                    });
                    $data_id = $data_id->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.remove_time'), '<=', $this->add_time_from);
                    });
                }
            }
            if ($this->treatment_bed_room_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_treatment_bed_room.' . $key, $this->sub_order_by ?? $item);
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
                $data = TreatmentBedRoomResource::collection($data);
                if (isset($data[0])) {
                    if (($data[0]->id != $this->treatment_bed_room->max('id')) && ($data[0]->id != $this->treatment_bed_room->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)) {
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
                    $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.id'), $this->treatment_bed_room_id);
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
                'treatment_bed_room_id' => $this->treatment_bed_room_id,
                'is_in_room' => $this->is_in_room,
                'add_time_to' => $this->add_time_to,
                'add_time_from' => $this->add_time_from,
                'bed_room_ids' => $this->bed_room_ids,
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
