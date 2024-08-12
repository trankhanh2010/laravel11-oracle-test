<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Resources\ServicereqResource;
use App\Models\HIS\ServiceReq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ServiceReqController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->service_req = new ServiceReq();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->service_req);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }

        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if ($this->cursor === 0) {
                $this->service_req_last_id = $this->service_req->max('id');
                $this->cursor = $this->service_req_last_id;
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
    public function service_req_get_L_view(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        // Kiểm tra xem User có quyền xem execute_room không
        if ($this->execute_room_id != null) {
            if (!view_service_req($this->execute_room_id, $request->bearerToken(), $this->time)) {
                return return_403();
            }
        } else {
            if ($this->service_req_id == null) {
                return return_400('Thiếu execute_room_id!');
            }
        }

        // Khai báo các trường cần select
        $select = [
            'his_service_req.id',
            'his_service_req.service_req_code',
            'his_service_req.tdl_patient_code',
            'his_service_req.tdl_patient_name',
            'his_service_req.tdl_patient_gender_name',
            'his_service_req.tdl_patient_dob',
            'his_service_req.tdl_patient_address',
            'his_service_req.treatment_id',
            'his_service_req.tdl_patient_avatar_url',
            'his_service_req.service_req_stt_id',
            'his_service_req.parent_id',
            'his_service_req.execute_room_id',
            'his_service_req.exe_service_module_id',
            'his_service_req.request_department_id',
            'his_service_req.tdl_treatment_code',
            'his_service_req.dhst_id',
            'his_service_req.priority',
            'his_service_req.request_room_id',
            'his_service_req.intruction_time',
            'his_service_req.num_order',
            'his_service_req.service_req_type_id',
            'his_service_req.tdl_hein_card_number',
            'his_service_req.tdl_treatment_type_id',
            'his_service_req.intruction_date',
            'his_service_req.execute_loginname',
            'his_service_req.execute_username',
            'his_service_req.tdl_patient_type_id',
            'his_service_req.is_not_in_debt',
            'his_service_req.is_no_execute',
            'his_service_req.vir_intruction_month',
            'his_service_req.has_child',
            'his_service_req.tdl_patient_phone',
            'his_service_req.resulting_time',
            'his_service_req.tdl_service_ids',
            'his_service_req.call_count',
            'his_service_req.tdl_patient_unsigned_name',
            'his_service_req.start_time',
            'his_service_req.note',
            'his_service_req.tdl_patient_id',
            'his_service_req.icd_code',
            'his_service_req.icd_name',
            'his_service_req.icd_sub_code',
            'his_service_req.icd_text',
            // 'order_time'
        ];

        $keyword = $this->keyword;
        $data = $this->service_req
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.service_req_code'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_service_req.tdl_patient_code'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.is_active'), $this->is_active);
            });
        }
        if ($this->service_req_stt_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_req.service_req_stt_id'), $this->service_req_stt_ids);
            });
        }
        if ($this->not_in_service_req_type_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereNotIn(DB::connection('oracle_his')->raw('his_service_req.service_req_type_id'), $this->not_in_service_req_type_ids);
            });
        }
        if ($this->tdl_patient_type_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_req.tdl_patient_type_id'), $this->tdl_patient_type_ids);
            });
        }
        if ($this->execute_room_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.execute_room_id'), $this->execute_room_id);
            });
        }
        if ($this->intruction_time_from != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.intruction_time'), '>=', $this->intruction_time_from);
            });
        }
        if ($this->intruction_time_to != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.intruction_time'), '<=', $this->intruction_time_to);
            });
        }
        if (!$this->has_execute) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.is_no_execute'), 1);
            });
        }
        if ($this->is_not_ksk_requried_aproval__or__is_ksk_approve) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.TDL_KSK_IS_REQUIRED_APPROVAL'), null);
                $query = $query->orWhere(DB::connection('oracle_his')->raw('his_service_req.TDL_IS_KSK_APPROVE'), 0);
                $query = $query->orwhere(DB::connection('oracle_his')->raw('his_service_req.TDL_IS_KSK_APPROVE'), 1);
            });
        }
        if ($this->service_req_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_service_req.' . $key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.id'), $this->service_req_id);
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
            $this->service_req_stt_ids_name => $this->service_req_stt_ids,
            $this->not_in_service_req_type_ids_name => $this->not_in_service_req_type_ids,
            $this->tdl_patient_type_ids_name => $this->tdl_patient_type_ids,
            $this->execute_room_id_name => $this->execute_room_id,
            $this->intruction_time_from_name => $this->intruction_time_from,
            $this->intruction_time_to_name => $this->intruction_time_to,
            $this->has_execute_name => $this->has_execute,
            $this->is_not_ksk_requried_aproval__or__is_ksk_approve_name => $this->is_not_ksk_requried_aproval__or__is_ksk_approve,
            $this->keyword_name => $this->keyword,
            $this->order_by_name => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function service_req_get_L_view_v2(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        //Kiểm tra xem User có quyền xem execute_room không
        if ($this->execute_room_id != null) {
            if (!view_service_req($this->execute_room_id, $request->bearerToken(), $this->time)) {
                return return_403();
            }
        } else {
            if ($this->service_req_id == null) {
                return return_400('Thiếu execute_room_id!');
            }
        }

        // Khai báo các trường cần select
        $select = [
            'his_service_req.id',
            'his_service_req.service_req_code',
            'his_service_req.tdl_patient_code',
            'his_service_req.tdl_patient_name',
            'his_service_req.tdl_patient_gender_name',
            'his_service_req.tdl_patient_dob',
            'his_service_req.tdl_patient_address',
            'his_service_req.treatment_id',
            'his_service_req.tdl_patient_avatar_url',
            'his_service_req.service_req_stt_id',
            'his_service_req.parent_id',
            'his_service_req.execute_room_id',
            'his_service_req.exe_service_module_id',
            'his_service_req.request_department_id',
            'his_service_req.tdl_treatment_code',
            'his_service_req.dhst_id',
            'his_service_req.priority',
            'his_service_req.request_room_id',
            'his_service_req.intruction_time',
            'his_service_req.num_order',
            'his_service_req.service_req_type_id',
            'his_service_req.tdl_hein_card_number',
            'his_service_req.tdl_treatment_type_id',
            'his_service_req.intruction_date',
            'his_service_req.execute_loginname',
            'his_service_req.execute_username',
            'his_service_req.tdl_patient_type_id',
            'his_service_req.is_not_in_debt',
            'his_service_req.is_no_execute',
            'his_service_req.vir_intruction_month',
            'his_service_req.has_child',
            'his_service_req.tdl_patient_phone',
            'his_service_req.resulting_time',
            'his_service_req.tdl_service_ids',
            'his_service_req.call_count',
            'his_service_req.tdl_patient_unsigned_name',
            'his_service_req.start_time',
            'his_service_req.note',
            'his_service_req.tdl_patient_id',
            'his_service_req.icd_code',
            'his_service_req.icd_name',
            'his_service_req.icd_sub_code',
            'his_service_req.icd_text',
            // 'order_time'
        ];

        $keyword = $this->keyword;
        $data = $this->service_req
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.service_req_code'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_service_req.tdl_patient_code'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.is_active'), $this->is_active);
            });
        }
        if ($this->service_req_stt_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_req.service_req_stt_id'), $this->service_req_stt_ids);
            });
        }
        if ($this->not_in_service_req_type_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereNotIn(DB::connection('oracle_his')->raw('his_service_req.service_req_type_id'), $this->not_in_service_req_type_ids);
            });
        }
        if ($this->tdl_patient_type_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_req.tdl_patient_type_id'), $this->tdl_patient_type_ids);
            });
        }
        if ($this->execute_room_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.execute_room_id'), $this->execute_room_id);
            });
        }
        if ($this->intruction_time_from != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.intruction_time'), '>=', $this->intruction_time_from);
            });
        }
        if ($this->intruction_time_to != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.intruction_time'), '<=', $this->intruction_time_to);
            });
        }
        if (!$this->has_execute) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.is_no_execute'), 1);
            });
        }
        if ($this->is_not_ksk_requried_aproval__or__is_ksk_approve) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.TDL_KSK_IS_REQUIRED_APPROVAL'), null);
                $query = $query->orWhere(DB::connection('oracle_his')->raw('his_service_req.TDL_IS_KSK_APPROVE'), 0);
                $query = $query->orwhere(DB::connection('oracle_his')->raw('his_service_req.TDL_IS_KSK_APPROVE'), 1);
            });
        }
        if ($this->service_req_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_service_req.' . $key, $item);
                }
            }
            // Chuyển truy vấn sang chuỗi sql
            $sql = $data->toSql();
            $bindings = $data->getBindings();

            // Thêm dấu nháy đơn cho các giá trị chuỗi
            foreach ($bindings as $key => $value) {
                if (is_string($value)) {
                    $bindings[$key] = "'" . $value . "'";
                }
            }
            $fullSql = Str::replaceArray('?', $bindings, $sql);
            $fullSql = $fullSql . ' OFFSET ' . $this->start . ' ROWS FETCH NEXT ' . $this->limit . ' ROWS ONLY';
            $data = DB::connection('oracle_his')->select($fullSql);
            $data = ServicereqResource::collection($data);
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.id'), $this->service_req_id);
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
            $this->service_req_stt_ids_name => $this->service_req_stt_ids,
            $this->not_in_service_req_type_ids_name => $this->not_in_service_req_type_ids,
            $this->tdl_patient_type_ids_name => $this->tdl_patient_type_ids,
            $this->execute_room_id_name => $this->execute_room_id,
            $this->intruction_time_from_name => $this->intruction_time_from,
            $this->intruction_time_to_name => $this->intruction_time_to,
            $this->has_execute_name => $this->has_execute,
            $this->is_not_ksk_requried_aproval__or__is_ksk_approve_name => $this->is_not_ksk_requried_aproval__or__is_ksk_approve,
            $this->keyword_name => $this->keyword,
            $this->order_by_name => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function service_req_get_L_view_v3(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
        
        //Kiểm tra xem User có quyền xem execute_room không
        if ($this->execute_room_id != null) {
            if (!view_service_req($this->execute_room_id, $request->bearerToken(), $this->time)) {
                return return_403();
            }
        } else {
            if ($this->service_req_id == null) {
                return return_400('Thiếu execute_room_id!');
            }
        }

        // Khai báo các trường cần select
        $select = [
            'his_service_req.id',
            'his_service_req.service_req_code',
            'his_service_req.tdl_patient_code',
            'his_service_req.tdl_patient_name',
            'his_service_req.tdl_patient_gender_name',
            'his_service_req.tdl_patient_dob',
            'his_service_req.tdl_patient_address',
            'his_service_req.treatment_id',
            'his_service_req.tdl_patient_avatar_url',
            'his_service_req.service_req_stt_id',
            'his_service_req.parent_id',
            'his_service_req.execute_room_id',
            'his_service_req.exe_service_module_id',
            'his_service_req.request_department_id',
            'his_service_req.tdl_treatment_code',
            'his_service_req.dhst_id',
            'his_service_req.priority',
            'his_service_req.request_room_id',
            'his_service_req.intruction_time',
            'his_service_req.num_order',
            'his_service_req.service_req_type_id',
            'his_service_req.tdl_hein_card_number',
            'his_service_req.tdl_treatment_type_id',
            'his_service_req.intruction_date',
            'his_service_req.execute_loginname',
            'his_service_req.execute_username',
            'his_service_req.tdl_patient_type_id',
            'his_service_req.is_not_in_debt',
            'his_service_req.is_no_execute',
            'his_service_req.vir_intruction_month',
            'his_service_req.has_child',
            'his_service_req.tdl_patient_phone',
            'his_service_req.resulting_time',
            'his_service_req.tdl_service_ids',
            'his_service_req.call_count',
            'his_service_req.tdl_patient_unsigned_name',
            'his_service_req.start_time',
            'his_service_req.note',
            'his_service_req.tdl_patient_id',
            'his_service_req.icd_code',
            'his_service_req.icd_name',
            'his_service_req.icd_sub_code',
            'his_service_req.icd_text',
            // 'order_time'
        ];

        $keyword = $this->keyword;
        try {
            $data = $this->service_req
                ->select($select);
            $data_id = $this->service_req
                ->select("HIS_SERVICE_REQ.ID");
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.TDL_TREATMENT_CODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_service_req.TDL_PATIENT_CODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_service_req.BARCODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_service_req.EXECUTE_LOGINNAME'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_service_req.SESSION_CODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_service_req.ASSIGN_TURN_CODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_service_req.SERVICE_REQ_CODE'), 'like', $keyword . '%');
                });
                $data_id = $data_id->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.TDL_TREATMENT_CODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_service_req.TDL_PATIENT_CODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_service_req.BARCODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_service_req.EXECUTE_LOGINNAME'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_service_req.SESSION_CODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_service_req.ASSIGN_TURN_CODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_service_req.SERVICE_REQ_CODE'), 'like', $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.is_active'), $this->is_active);
                });
            }
            if ($this->service_req_stt_ids != null) {
                $data = $data->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_req.service_req_stt_id'), $this->service_req_stt_ids);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_req.service_req_stt_id'), $this->service_req_stt_ids);
                });
            }
            if ($this->not_in_service_req_type_ids != null) {
                $data = $data->where(function ($query) {
                    $query = $query->whereNotIn(DB::connection('oracle_his')->raw('his_service_req.service_req_type_id'), $this->not_in_service_req_type_ids);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->whereNotIn(DB::connection('oracle_his')->raw('his_service_req.service_req_type_id'), $this->not_in_service_req_type_ids);
                });
            }
            if ($this->tdl_patient_type_ids != null) {
                $data = $data->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_req.tdl_patient_type_id'), $this->tdl_patient_type_ids);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_req.tdl_patient_type_id'), $this->tdl_patient_type_ids);
                });
            }
            if ($this->execute_room_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.execute_room_id'), $this->execute_room_id);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.execute_room_id'), $this->execute_room_id);
                });
            }
            if ($this->intruction_time_from != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.intruction_time'), '>=', $this->intruction_time_from);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.intruction_time'), '>=', $this->intruction_time_from);
                });
            }
            if ($this->intruction_time_to != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.intruction_time'), '<=', $this->intruction_time_to);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.intruction_time'), '<=', $this->intruction_time_to);
                });
            }
            if (!$this->has_execute) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.is_no_execute'), 1);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.is_no_execute'), 1);
                });
            }
            if ($this->is_not_ksk_requried_aproval__or__is_ksk_approve) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.TDL_KSK_IS_REQUIRED_APPROVAL'), null);
                    $query = $query->orWhere(DB::connection('oracle_his')->raw('his_service_req.TDL_IS_KSK_APPROVE'), 0);
                    $query = $query->orwhere(DB::connection('oracle_his')->raw('his_service_req.TDL_IS_KSK_APPROVE'), 1);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.TDL_KSK_IS_REQUIRED_APPROVAL'), null);
                    $query = $query->orWhere(DB::connection('oracle_his')->raw('his_service_req.TDL_IS_KSK_APPROVE'), 0);
                    $query = $query->orwhere(DB::connection('oracle_his')->raw('his_service_req.TDL_IS_KSK_APPROVE'), 1);
                });
            }
            if ($this->service_req_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_service_req.' . $key, $this->sub_order_by ?? $item);
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
                $data = ServicereqResource::collection($data);
                if(isset($data[0])){
                    if(($data[0]->id != $this->service_req->max('id')) && ($data[0]->id != $this->service_req->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)){
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
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.id'), $this->service_req_id);
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
                $this->service_req_stt_ids_name => $this->service_req_stt_ids,
                $this->not_in_service_req_type_ids_name => $this->not_in_service_req_type_ids,
                $this->tdl_patient_type_ids_name => $this->tdl_patient_type_ids,
                $this->execute_room_id_name => $this->execute_room_id,
                $this->intruction_time_from_name => $this->intruction_time_from,
                $this->intruction_time_to_name => $this->intruction_time_to,
                $this->has_execute_name => $this->has_execute,
                $this->is_not_ksk_requried_aproval__or__is_ksk_approve_name => $this->is_not_ksk_requried_aproval__or__is_ksk_approve,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
}
