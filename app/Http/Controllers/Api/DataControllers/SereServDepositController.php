<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Resources\SereServDepositGetResource;
use App\Models\HIS\SereServDeposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SereServDepositController  extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->sere_serv_deposit = new SereServDeposit();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->sere_serv_deposit);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if ($this->cursor === 0) {
                $this->sere_serv_deposit_last_id = $this->sere_serv_deposit->max('id');
                $this->cursor = $this->sere_serv_deposit_last_id;
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

    public function sere_serv_deposit_get_view(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
        
        $select = [
            "his_sere_serv_deposit.ID",
            "his_sere_serv_deposit.CREATE_TIME",
            "his_sere_serv_deposit.MODIFY_TIME",
            "his_sere_serv_deposit.CREATOR",
            "his_sere_serv_deposit.MODIFIER",
            "his_sere_serv_deposit.APP_CREATOR",
            "his_sere_serv_deposit.APP_MODIFIER",
            "his_sere_serv_deposit.IS_ACTIVE",
            "his_sere_serv_deposit.IS_DELETE",
            "his_sere_serv_deposit.SERE_SERV_ID",
            "his_sere_serv_deposit.DEPOSIT_ID",
            "his_sere_serv_deposit.AMOUNT",
            "his_sere_serv_deposit.TDL_TREATMENT_ID",
            "his_sere_serv_deposit.TDL_SERVICE_REQ_ID",
            "his_sere_serv_deposit.TDL_SERVICE_ID",
            "his_sere_serv_deposit.TDL_SERVICE_CODE",
            "his_sere_serv_deposit.TDL_SERVICE_NAME",
            "his_sere_serv_deposit.TDL_SERVICE_TYPE_ID",
            "his_sere_serv_deposit.TDL_SERVICE_UNIT_ID",
            "his_sere_serv_deposit.TDL_PATIENT_TYPE_ID",
            "his_sere_serv_deposit.TDL_HEIN_SERVICE_TYPE_ID",
            "his_sere_serv_deposit.TDL_REQUEST_DEPARTMENT_ID",
            "his_sere_serv_deposit.TDL_EXECUTE_DEPARTMENT_ID",
            "his_sere_serv_deposit.TDL_AMOUNT",
            "his_sere_serv_deposit.TDL_HEIN_LIMIT_PRICE",
            "his_sere_serv_deposit.TDL_VIR_PRICE",
            "his_sere_serv_deposit.TDL_VIR_PRICE_NO_ADD_PRICE",
            "his_sere_serv_deposit.TDL_VIR_HEIN_PRICE",
            "his_sere_serv_deposit.TDL_VIR_TOTAL_PRICE",
            "his_sere_serv_deposit.TDL_VIR_TOTAL_HEIN_PRICE",
            "his_sere_serv_deposit.TDL_VIR_TOTAL_PATIENT_PRICE",

            "V_HIS_SERE_SERV_DEPOSIT.SERVICE_REQ_STT_ID",
            "V_HIS_SERE_SERV_DEPOSIT.SERVICE_REQ_TYPE_ID",
            "V_HIS_SERE_SERV_DEPOSIT.SERVICE_REQ_CODE",
            "V_HIS_SERE_SERV_DEPOSIT.INTRUCTION_TIME",
            "V_HIS_SERE_SERV_DEPOSIT.SERVICE_TYPE_CODE",
            "V_HIS_SERE_SERV_DEPOSIT.SERVICE_TYPE_NAME",
            "V_HIS_SERE_SERV_DEPOSIT.TRANSACTION_CODE",
            "V_HIS_SERE_SERV_DEPOSIT.PAY_FORM_ID",
            "V_HIS_SERE_SERV_DEPOSIT.PAY_FORM_CODE",
            "V_HIS_SERE_SERV_DEPOSIT.PAY_FORM_NAME",

        ];
        $param = [];

        $keyword = $this->keyword;
        try {
            $data = $this->sere_serv_deposit
            ->leftJoin('V_HIS_SERE_SERV_DEPOSIT ', 'his_sere_serv_deposit.id', '=', 'V_HIS_SERE_SERV_DEPOSIT.id')
                ->select($select);
            $data_id = $this->sere_serv_deposit
            ->leftJoin('V_HIS_SERE_SERV_DEPOSIT ', 'his_sere_serv_deposit.id', '=', 'V_HIS_SERE_SERV_DEPOSIT.id')
                ->select("his_sere_serv_deposit.ID");
            if ($keyword != null) {

            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_deposit.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_deposit.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_deposit.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_deposit.is_active'), $this->is_active);
                });
            }
            if ($this->tdl_treatment_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_deposit.tdl_treatment_id'), $this->tdl_treatment_id);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_deposit.tdl_treatment_id'), $this->tdl_treatment_id);
                });
            }
            if ($this->sere_serv_deposit_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_sere_serv_deposit.' . $key, $this->sub_order_by ?? $item);
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
                $data = SereServDepositGetResource::collection($data);
                if (isset($data[0])) {
                    if (($data[0]->id != $this->sere_serv_deposit->max('id')) && ($data[0]->id != $this->sere_serv_deposit->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)) {
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
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_deposit.id'), $this->sere_serv_deposit_id);
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
                $this->sere_serv_deposit_id_name => $this->sere_serv_deposit_id,
                $this->tdl_treatment_id_name => $this->tdl_treatment_id,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return returnDataSuccess($param_return, $data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return500Error($e->getMessage());
        }
    }
}
