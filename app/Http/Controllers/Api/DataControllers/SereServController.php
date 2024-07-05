<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Models\HIS\SereServ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SereServController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->sere_serv = new SereServ();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if (!$this->sere_serv->getConnection()->getSchemaBuilder()->hasColumn($this->sere_serv->getTable(), $key)) {
                        unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                        unset($this->order_by[$key]);
                    }
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
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

        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if (($this->sere_serv_id == null) && (($keyword != null) || (!$this->is_include_deleted) || ($this->service_req_ids != null) || ($this->service_type_id != null) || ($this->treatment_id != null))) {
            $data = $this->sere_serv
            ->select($select);
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('lower(his_sere_serv.tdl_Service_Code)'), 'like', '%' . $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('lower(his_sere_serv.tdl_Service_name)'), 'like', '%' . $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_delete'), 0);
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
        }else{
            $data = $this->sere_serv
            ->select($select);
            if ($this->sere_serv_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv.id'), $this->sere_serv_id);
                });
            }
            $data = $data->with($param);
            $data = $data
            ->first();
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? null,
            'is_include_deleted' => $this->is_include_deleted,
            'service_req_ids' => $this->service_req_ids,
            'service_type_id' => $this->service_type_id,
            'treatment_id' => $this->treatment_id,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

}
