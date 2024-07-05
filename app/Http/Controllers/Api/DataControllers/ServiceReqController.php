<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Models\HIS\ServiceReq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceReqController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->service_req = new ServiceReq();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if (!$this->service_req->getConnection()->getSchemaBuilder()->hasColumn($this->service_req->getTable(), $key)) {
                        unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                        unset($this->order_by[$key]);
                    }
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function service_req_get_L_view($id = null, Request $request)
    {
        // Kiểm tra xem User có quyền xem execute_room không
        if ($this->execute_room_id != null) {
            if (!view_service_req($this->execute_room_id, $request->bearerToken(), $this->time)) {
                return return_403();
            }
        }else{
            if($this->service_req_id == null){
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

        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        $data = $this->service_req
        ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('lower(his_service_req.service_req_code)'), 'like', '%' . $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('lower(his_service_req.tdl_patient_code)'), 'like', '%' . $keyword . '%');
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
        }else{
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_req.id'), $this->service_req_id);
                });
            $data = $data
            ->first();
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? null,
            'is_iclude_deleted' => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active,
            'service_req_stt_ids' => $this->service_req_stt_ids,
            'not_in_service_req_type_ids' => $this->not_in_service_req_type_ids,
            'tdl_patient_type_ids' => $this->tdl_patient_type_ids,
            'execute_room_id' => $this->execute_room_id,
            'intruction_time_from' => $this->intruction_time_from,
            'intruction_time_to' => $this->intruction_time_to,
            'has_execute' => $this->has_execute,
            'is_not_ksk_requried_aproval__or__is_ksk_approve' => $this->is_not_ksk_requried_aproval__or__is_ksk_approve,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);

       
    }
}
