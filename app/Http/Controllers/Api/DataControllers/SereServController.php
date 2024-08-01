<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Resources\SereServResource;
use App\Models\HIS\SereServ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class SereServController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->sere_serv = new SereServ();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            // foreach ($this->order_by as $key => $item) {
            //     if (!in_array($key, $this->order_by_join)) {
            //         if ((!$this->sere_serv->getConnection()->getSchemaBuilder()->hasColumn($this->sere_serv->getTable(), $key))) {
            //             unset($this->order_by_request[camelCaseFromUnderscore($key)]);
            //             unset($this->order_by[$key]);
            //         }
            //     }
            // }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if($this->cursor === 0){
                $this->sere_serv_last_id = $this->sere_serv->max('id');
                $this->cursor = $this->sere_serv_last_id;
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
    public function sere_serv_get(Request $request)
    {
        $select = [
            "his_sere_serv.ID",
            "his_sere_serv.CREATE_TIME",
            "his_sere_serv.MODIFY_TIME",
            "his_sere_serv.CREATOR",
            "his_sere_serv.MODIFIER",
            "his_sere_serv.APP_CREATOR",
            "his_sere_serv.APP_MODIFIER",
            "his_sere_serv.IS_ACTIVE",
            "his_sere_serv.IS_DELETE",
            "his_sere_serv.SERVICE_ID",
            "his_sere_serv.SERVICE_REQ_ID",
            "his_sere_serv.PATIENT_TYPE_ID",
            "his_sere_serv.PRIMARY_PRICE",
            "his_sere_serv.AMOUNT",
            "his_sere_serv.PRICE",
            "his_sere_serv.ORIGINAL_PRICE",
            "his_sere_serv.VAT_RATIO",
            "his_sere_serv.MEDICINE_ID",
            "his_sere_serv.EXP_MEST_MEDICINE_ID",
            "his_sere_serv.TDL_INTRUCTION_TIME",
            "his_sere_serv.TDL_INTRUCTION_DATE",
            "his_sere_serv.TDL_PATIENT_ID",
            "his_sere_serv.TDL_TREATMENT_ID",
            "his_sere_serv.TDL_TREATMENT_CODE",
            "his_sere_serv.TDL_SERVICE_CODE",
            "his_sere_serv.TDL_SERVICE_NAME",
            "his_sere_serv.TDL_HEIN_SERVICE_BHYT_NAME",
            "his_sere_serv.TDL_SERVICE_TYPE_ID",
            "his_sere_serv.TDL_SERVICE_UNIT_ID",
            "his_sere_serv.TDL_HEIN_SERVICE_TYPE_ID",
            "his_sere_serv.TDL_ACTIVE_INGR_BHYT_CODE",
            "his_sere_serv.TDL_ACTIVE_INGR_BHYT_NAME",
            "his_sere_serv.TDL_MEDICINE_CONCENTRA",
            "his_sere_serv.TDL_MEDICINE_REGISTER_NUMBER",
            "his_sere_serv.TDL_MEDICINE_PACKAGE_NUMBER",
            "his_sere_serv.TDL_SERVICE_REQ_CODE",
            "his_sere_serv.TDL_REQUEST_ROOM_ID",
            "his_sere_serv.TDL_REQUEST_DEPARTMENT_ID",
            "his_sere_serv.TDL_REQUEST_LOGINNAME",
            "his_sere_serv.TDL_REQUEST_USERNAME",
            "his_sere_serv.TDL_EXECUTE_ROOM_ID",
            "his_sere_serv.TDL_EXECUTE_DEPARTMENT_ID",
            "his_sere_serv.TDL_EXECUTE_BRANCH_ID",
            "his_sere_serv.TDL_SERVICE_REQ_TYPE_ID",
            "his_sere_serv.TDL_HST_BHYT_CODE",
            "his_sere_serv.VIR_PRICE",
            "his_sere_serv.VIR_PRICE_NO_ADD_PRICE",
            "his_sere_serv.VIR_PRICE_NO_EXPEND",
            "his_sere_serv.VIR_HEIN_PRICE",
            "his_sere_serv.VIR_PATIENT_PRICE",
            "his_sere_serv.VIR_PATIENT_PRICE_BHYT",
            "his_sere_serv.VIR_TOTAL_PRICE",
            "his_sere_serv.VIR_TOTAL_PRICE_NO_ADD_PRICE",
            "his_sere_serv.VIR_TOTAL_PRICE_NO_EXPEND",
            "his_sere_serv.VIR_TOTAL_HEIN_PRICE",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_BHYT",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_NO_DC",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_TEMP",
        ];
        $param = [
            'exp_mest_bloods',
            'exp_mest_materials',
            'exp_mest_medicines',
            'sere_serv_bills',
            'sere_serv_debts',
            'sere_serv_deposits',
            'sere_serv_files',
            'sere_serv_matys',
            'sere_serv_pttts',
            'sere_serv_rehas',
            'sere_serv_suins',
            'sere_serv_teins',
            'service_change_reqs',
            'sese_depo_repays',
            'sese_trans_reqs'
        ];

        $keyword = $this->keyword;
        $data = $this->sere_serv
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_Service_Code'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv.tdl_Service_name'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_active'), $this->is_active);
            });
        }
        if ($this->service_req_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv.service_req_id'), $this->service_req_ids);
            });
        }
        if ($this->service_type_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_service_type_id'), $this->service_type_id);
            });
        }
        if ($this->treatment_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_treatment_id'), $this->treatment_id);
            });
        }
        if ($this->sere_serv_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_sere_serv.' . $key, $item);
                }
            }
            $data = $data->with($param);
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.id'), $this->sere_serv_id);
            });
            $data = $data->with($param);
            $data = $data
                ->first();
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? null,
            'is_include_deleted' => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active,
            'service_req_ids' => $this->service_req_ids,
            'service_type_id' => $this->service_type_id,
            'treatment_id' => $this->treatment_id,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function sere_serv_get_v2(Request $request)
    {
        $select = [
            "his_sere_serv.ID",
            "his_sere_serv.CREATE_TIME",
            "his_sere_serv.MODIFY_TIME",
            "his_sere_serv.CREATOR",
            "his_sere_serv.MODIFIER",
            "his_sere_serv.APP_CREATOR",
            "his_sere_serv.APP_MODIFIER",
            "his_sere_serv.IS_ACTIVE",
            "his_sere_serv.IS_DELETE",
            "his_sere_serv.SERVICE_ID",
            "his_sere_serv.SERVICE_REQ_ID",
            "his_sere_serv.PATIENT_TYPE_ID",
            "his_sere_serv.PRIMARY_PRICE",
            "his_sere_serv.AMOUNT",
            "his_sere_serv.PRICE",
            "his_sere_serv.ORIGINAL_PRICE",
            "his_sere_serv.VAT_RATIO",
            "his_sere_serv.MEDICINE_ID",
            "his_sere_serv.EXP_MEST_MEDICINE_ID",
            "his_sere_serv.TDL_INTRUCTION_TIME",
            "his_sere_serv.TDL_INTRUCTION_DATE",
            "his_sere_serv.TDL_PATIENT_ID",
            "his_sere_serv.TDL_TREATMENT_ID",
            "his_sere_serv.TDL_TREATMENT_CODE",
            "his_sere_serv.TDL_SERVICE_CODE",
            "his_sere_serv.TDL_SERVICE_NAME",
            "his_sere_serv.TDL_HEIN_SERVICE_BHYT_NAME",
            "his_sere_serv.TDL_SERVICE_TYPE_ID",
            "his_sere_serv.TDL_SERVICE_UNIT_ID",
            "his_sere_serv.TDL_HEIN_SERVICE_TYPE_ID",
            "his_sere_serv.TDL_ACTIVE_INGR_BHYT_CODE",
            "his_sere_serv.TDL_ACTIVE_INGR_BHYT_NAME",
            "his_sere_serv.TDL_MEDICINE_CONCENTRA",
            "his_sere_serv.TDL_MEDICINE_REGISTER_NUMBER",
            "his_sere_serv.TDL_MEDICINE_PACKAGE_NUMBER",
            "his_sere_serv.TDL_SERVICE_REQ_CODE",
            "his_sere_serv.TDL_REQUEST_ROOM_ID",
            "his_sere_serv.TDL_REQUEST_DEPARTMENT_ID",
            "his_sere_serv.TDL_REQUEST_LOGINNAME",
            "his_sere_serv.TDL_REQUEST_USERNAME",
            "his_sere_serv.TDL_EXECUTE_ROOM_ID",
            "his_sere_serv.TDL_EXECUTE_DEPARTMENT_ID",
            "his_sere_serv.TDL_EXECUTE_BRANCH_ID",
            "his_sere_serv.TDL_SERVICE_REQ_TYPE_ID",
            "his_sere_serv.TDL_HST_BHYT_CODE",
            "his_sere_serv.VIR_PRICE",
            "his_sere_serv.VIR_PRICE_NO_ADD_PRICE",
            "his_sere_serv.VIR_PRICE_NO_EXPEND",
            "his_sere_serv.VIR_HEIN_PRICE",
            "his_sere_serv.VIR_PATIENT_PRICE",
            "his_sere_serv.VIR_PATIENT_PRICE_BHYT",
            "his_sere_serv.VIR_TOTAL_PRICE",
            "his_sere_serv.VIR_TOTAL_PRICE_NO_ADD_PRICE",
            "his_sere_serv.VIR_TOTAL_PRICE_NO_EXPEND",
            "his_sere_serv.VIR_TOTAL_HEIN_PRICE",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_BHYT",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_NO_DC",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_TEMP",
        ];
        $param = [
            'exp_mest_bloods',
            'exp_mest_materials',
            'exp_mest_medicines',
            'sere_serv_bills',
            'sere_serv_debts',
            'sere_serv_deposits',
            'sere_serv_files',
            'sere_serv_matys',
            'sere_serv_pttts',
            'sere_serv_rehas',
            'sere_serv_suins',
            'sere_serv_teins',
            'service_change_reqs',
            'sese_depo_repays',
            'sese_trans_reqs'
        ];

        $keyword = $this->keyword;
        $data = $this->sere_serv
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_Service_Code'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv.tdl_Service_name'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_active'), $this->is_active);
            });
        }
        if ($this->service_req_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv.service_req_id'), $this->service_req_ids);
            });
        }
        if ($this->service_type_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_service_type_id'), $this->service_type_id);
            });
        }
        if ($this->treatment_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_treatment_id'), $this->treatment_id);
            });
        }
        if ($this->sere_serv_id == null) {
            // $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_sere_serv.' . $key, $item);
                }
            }
            $data = $data->with($param);
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
            // $data = SereServResource::collection($data);
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.id'), $this->sere_serv_id);
            });
            $data = $data->with($param);
            $data = $data
                ->first();
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            // 'count' => $count ?? null,
            'is_include_deleted' => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active,
            'service_req_ids' => $this->service_req_ids,
            'service_type_id' => $this->service_type_id,
            'treatment_id' => $this->treatment_id,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function sere_serv_get_count_v2(Request $request)
    {
        $select = [
            "his_sere_serv.ID",
            "his_sere_serv.CREATE_TIME",
            "his_sere_serv.MODIFY_TIME",
            "his_sere_serv.CREATOR",
            "his_sere_serv.MODIFIER",
            "his_sere_serv.APP_CREATOR",
            "his_sere_serv.APP_MODIFIER",
            "his_sere_serv.IS_ACTIVE",
            "his_sere_serv.IS_DELETE",
            "his_sere_serv.SERVICE_ID",
            "his_sere_serv.SERVICE_REQ_ID",
            "his_sere_serv.PATIENT_TYPE_ID",
            "his_sere_serv.PRIMARY_PRICE",
            "his_sere_serv.AMOUNT",
            "his_sere_serv.PRICE",
            "his_sere_serv.ORIGINAL_PRICE",
            "his_sere_serv.VAT_RATIO",
            "his_sere_serv.MEDICINE_ID",
            "his_sere_serv.EXP_MEST_MEDICINE_ID",
            "his_sere_serv.TDL_INTRUCTION_TIME",
            "his_sere_serv.TDL_INTRUCTION_DATE",
            "his_sere_serv.TDL_PATIENT_ID",
            "his_sere_serv.TDL_TREATMENT_ID",
            "his_sere_serv.TDL_TREATMENT_CODE",
            "his_sere_serv.TDL_SERVICE_CODE",
            "his_sere_serv.TDL_SERVICE_NAME",
            "his_sere_serv.TDL_HEIN_SERVICE_BHYT_NAME",
            "his_sere_serv.TDL_SERVICE_TYPE_ID",
            "his_sere_serv.TDL_SERVICE_UNIT_ID",
            "his_sere_serv.TDL_HEIN_SERVICE_TYPE_ID",
            "his_sere_serv.TDL_ACTIVE_INGR_BHYT_CODE",
            "his_sere_serv.TDL_ACTIVE_INGR_BHYT_NAME",
            "his_sere_serv.TDL_MEDICINE_CONCENTRA",
            "his_sere_serv.TDL_MEDICINE_REGISTER_NUMBER",
            "his_sere_serv.TDL_MEDICINE_PACKAGE_NUMBER",
            "his_sere_serv.TDL_SERVICE_REQ_CODE",
            "his_sere_serv.TDL_REQUEST_ROOM_ID",
            "his_sere_serv.TDL_REQUEST_DEPARTMENT_ID",
            "his_sere_serv.TDL_REQUEST_LOGINNAME",
            "his_sere_serv.TDL_REQUEST_USERNAME",
            "his_sere_serv.TDL_EXECUTE_ROOM_ID",
            "his_sere_serv.TDL_EXECUTE_DEPARTMENT_ID",
            "his_sere_serv.TDL_EXECUTE_BRANCH_ID",
            "his_sere_serv.TDL_SERVICE_REQ_TYPE_ID",
            "his_sere_serv.TDL_HST_BHYT_CODE",
            "his_sere_serv.VIR_PRICE",
            "his_sere_serv.VIR_PRICE_NO_ADD_PRICE",
            "his_sere_serv.VIR_PRICE_NO_EXPEND",
            "his_sere_serv.VIR_HEIN_PRICE",
            "his_sere_serv.VIR_PATIENT_PRICE",
            "his_sere_serv.VIR_PATIENT_PRICE_BHYT",
            "his_sere_serv.VIR_TOTAL_PRICE",
            "his_sere_serv.VIR_TOTAL_PRICE_NO_ADD_PRICE",
            "his_sere_serv.VIR_TOTAL_PRICE_NO_EXPEND",
            "his_sere_serv.VIR_TOTAL_HEIN_PRICE",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_BHYT",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_NO_DC",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_TEMP",
        ];
        $param = [
            'exp_mest_bloods',
            'exp_mest_materials',
            'exp_mest_medicines',
            'sere_serv_bills',
            'sere_serv_debts',
            'sere_serv_deposits',
            'sere_serv_files',
            'sere_serv_matys',
            'sere_serv_pttts',
            'sere_serv_rehas',
            'sere_serv_suins',
            'sere_serv_teins',
            'service_change_reqs',
            'sese_depo_repays',
            'sese_trans_reqs'
        ];

        $keyword = $this->keyword;
        $data = $this->sere_serv
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_Service_Code'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv.tdl_Service_name'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_active'), $this->is_active);
            });
        }
        if ($this->service_req_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv.service_req_id'), $this->service_req_ids);
            });
        }
        if ($this->service_type_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_service_type_id'), $this->service_type_id);
            });
        }
        if ($this->treatment_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_treatment_id'), $this->treatment_id);
            });
        }
        if ($this->sere_serv_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_sere_serv.' . $key, $item);
                }
            }
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? null,
            'is_include_deleted' => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active,
            'service_req_ids' => $this->service_req_ids,
            'service_type_id' => $this->service_type_id,
            'treatment_id' => $this->treatment_id,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function sere_serv_get_v3(Request $request)
    {
        $select = [
            "his_sere_serv.ID",
            "his_sere_serv.CREATE_TIME",
            "his_sere_serv.MODIFY_TIME",
            "his_sere_serv.CREATOR",
            "his_sere_serv.MODIFIER",
            "his_sere_serv.APP_CREATOR",
            "his_sere_serv.APP_MODIFIER",
            "his_sere_serv.IS_ACTIVE",
            "his_sere_serv.IS_DELETE",
            "his_sere_serv.SERVICE_ID",
            "his_sere_serv.SERVICE_REQ_ID",
            "his_sere_serv.PATIENT_TYPE_ID",
            "his_sere_serv.PRIMARY_PRICE",
            "his_sere_serv.AMOUNT",
            "his_sere_serv.PRICE",
            "his_sere_serv.ORIGINAL_PRICE",
            "his_sere_serv.VAT_RATIO",
            "his_sere_serv.MEDICINE_ID",
            "his_sere_serv.EXP_MEST_MEDICINE_ID",
            "his_sere_serv.TDL_INTRUCTION_TIME",
            "his_sere_serv.TDL_INTRUCTION_DATE",
            "his_sere_serv.TDL_PATIENT_ID",
            "his_sere_serv.TDL_TREATMENT_ID",
            "his_sere_serv.TDL_TREATMENT_CODE",
            "his_sere_serv.TDL_SERVICE_CODE",
            "his_sere_serv.TDL_SERVICE_NAME",
            "his_sere_serv.TDL_HEIN_SERVICE_BHYT_NAME",
            "his_sere_serv.TDL_SERVICE_TYPE_ID",
            "his_sere_serv.TDL_SERVICE_UNIT_ID",
            "his_sere_serv.TDL_HEIN_SERVICE_TYPE_ID",
            "his_sere_serv.TDL_ACTIVE_INGR_BHYT_CODE",
            "his_sere_serv.TDL_ACTIVE_INGR_BHYT_NAME",
            "his_sere_serv.TDL_MEDICINE_CONCENTRA",
            "his_sere_serv.TDL_MEDICINE_REGISTER_NUMBER",
            "his_sere_serv.TDL_MEDICINE_PACKAGE_NUMBER",
            "his_sere_serv.TDL_SERVICE_REQ_CODE",
            "his_sere_serv.TDL_REQUEST_ROOM_ID",
            "his_sere_serv.TDL_REQUEST_DEPARTMENT_ID",
            "his_sere_serv.TDL_REQUEST_LOGINNAME",
            "his_sere_serv.TDL_REQUEST_USERNAME",
            "his_sere_serv.TDL_EXECUTE_ROOM_ID",
            "his_sere_serv.TDL_EXECUTE_DEPARTMENT_ID",
            "his_sere_serv.TDL_EXECUTE_BRANCH_ID",
            "his_sere_serv.TDL_SERVICE_REQ_TYPE_ID",
            "his_sere_serv.TDL_HST_BHYT_CODE",
            "his_sere_serv.VIR_PRICE",
            "his_sere_serv.VIR_PRICE_NO_ADD_PRICE",
            "his_sere_serv.VIR_PRICE_NO_EXPEND",
            "his_sere_serv.VIR_HEIN_PRICE",
            "his_sere_serv.VIR_PATIENT_PRICE",
            "his_sere_serv.VIR_PATIENT_PRICE_BHYT",
            "his_sere_serv.VIR_TOTAL_PRICE",
            "his_sere_serv.VIR_TOTAL_PRICE_NO_ADD_PRICE",
            "his_sere_serv.VIR_TOTAL_PRICE_NO_EXPEND",
            "his_sere_serv.VIR_TOTAL_HEIN_PRICE",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_BHYT",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_NO_DC",
            "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_TEMP",
        ];
        $param = [
            'exp_mest_bloods',
            'exp_mest_materials',
            'exp_mest_medicines',
            'sere_serv_bills',
            'sere_serv_debts',
            'sere_serv_deposits',
            'sere_serv_files',
            'sere_serv_matys',
            'sere_serv_pttts',
            'sere_serv_rehas',
            'sere_serv_suins',
            'sere_serv_teins',
            'service_change_reqs',
            'sese_depo_repays',
            'sese_trans_reqs'
        ];

        $keyword = $this->keyword;
        try {
            $data = $this->sere_serv
            ->select($select);
            $data_id = $this->sere_serv
            ->select("his_sere_serv.ID");
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.TDL_SERVICE_REQ_CODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv.TDL_SERVICE_CODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv.TDL_TREATMENT_CODE'), 'like', $keyword . '%');
                });
                $data_id = $data_id->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.TDL_SERVICE_REQ_CODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv.TDL_SERVICE_CODE'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv.TDL_TREATMENT_CODE'), 'like', $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_active'), $this->is_active);
                });
            }
            if ($this->service_req_ids != null) {
                $data = $data->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv.service_req_id'), $this->service_req_ids);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv.service_req_id'), $this->service_req_ids);
                });
            }
            if ($this->service_type_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_service_type_id'), $this->service_type_id);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_service_type_id'), $this->service_type_id);
                });
            }
            if ($this->treatment_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_treatment_id'), $this->treatment_id);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_treatment_id'), $this->treatment_id);
                });
            }
            if ($this->sere_serv_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_sere_serv.' . $key, $this->sub_order_by ?? $item);
                    }
                }
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

                $fullSql = 'SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (' . $sql . ') a WHERE ROWNUM <= ' . ($this->limit + $this->start) . ' AND ID ' . $this->equal . $this->cursor . $this->sub_order_by_string. ') WHERE rnum > ' . $this->start;
                $data = DB::connection('oracle_his')->select($fullSql, $bindings);
                $data = SereServResource::collection($data);
                if(isset($data[0])){
                    if(($data[0]->id != $this->sere_serv->max('id')) && ($data[0]->id != $this->sere_serv->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)){
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
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.id'), $this->sere_serv_id);
                });
                $data = $data->with($param);
                $data = $data
                    ->first();
            }
            $param_return = [
                'prev_cursor' => $this->prev_cursor ?? null,
                'limit' => $this->limit,
                'next_cursor' => $this->next_cursor ?? null,
                'is_include_deleted' => $this->is_include_deleted ?? false,
                'is_active' => $this->is_active,
                'service_req_ids' => $this->service_req_ids,
                'service_type_id' => $this->service_type_id,
                'treatment_id' => $this->treatment_id,
                'keyword' => $this->keyword,
                'order_by' => $this->order_by_request
            ];
            return return_data_success($param_return, $data);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }

    public function sere_serv_get_count_v3(Request $request)
    {
        $keyword = $this->keyword;
        $data = $this->sere_serv;
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.TDL_SERVICE_REQ_CODE'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv.TDL_SERVICE_CODE'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv.TDL_TREATMENT_CODE'), 'like', $keyword . '%');
                // ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv.tdl_Service_name'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_active'), $this->is_active);
            });
        }
        if ($this->service_req_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv.service_req_id'), $this->service_req_ids);
            });
        }
        if ($this->service_type_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_service_type_id'), $this->service_type_id);
            });
        }
        if ($this->treatment_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_treatment_id'), $this->treatment_id);
            });
        }
        if ($this->sere_serv_id == null) {
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.id'), $this->sere_serv_id);
            });

        }
        $count = $data->count();

        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? null,
            'is_include_deleted' => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active,
            'service_req_ids' => $this->service_req_ids,
            'service_type_id' => $this->service_type_id,
            'treatment_id' => $this->treatment_id,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }
}
