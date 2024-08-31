<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Resources\SereServBillGetResource;
use App\Models\HIS\SereServBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SereServBillController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->sere_serv_bill = new SereServBill();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->sere_serv_bill);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }

        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if ($this->cursor === 0) {
                $this->sere_serv_bill_last_id = $this->sere_serv_bill->max('id');
                $this->cursor = $this->sere_serv_bill_last_id;
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
    public function sere_serv_bill_get(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
        
        $select = [
            "his_sere_serv_bill.ID",
            "his_sere_serv_bill.CREATE_TIME",
            "his_sere_serv_bill.MODIFY_TIME",
            "his_sere_serv_bill.CREATOR",
            "his_sere_serv_bill.MODIFIER",
            "his_sere_serv_bill.APP_CREATOR",
            "his_sere_serv_bill.APP_MODIFIER",
            "his_sere_serv_bill.IS_ACTIVE",
            "his_sere_serv_bill.IS_DELETE",
            "his_sere_serv_bill.SERE_SERV_ID",
            "his_sere_serv_bill.BILL_ID",
            "his_sere_serv_bill.PRICE",
            "his_sere_serv_bill.VAT_RATIO",
            "his_sere_serv_bill.TDL_TREATMENT_ID",
            "his_sere_serv_bill.TDL_BILL_TYPE_ID",
            "his_sere_serv_bill.TDL_SERVICE_REQ_ID",
            "his_sere_serv_bill.TDL_PRIMARY_PRICE",
            "his_sere_serv_bill.TDL_AMOUNT",
            "his_sere_serv_bill.TDL_PRICE",
            "his_sere_serv_bill.TDL_ORIGINAL_PRICE",
            "his_sere_serv_bill.TDL_VAT_RATIO",
            "his_sere_serv_bill.TDL_SERVICE_TYPE_ID",
            "his_sere_serv_bill.TDL_HEIN_SERVICE_TYPE_ID",
            "his_sere_serv_bill.TDL_TOTAL_HEIN_PRICE",
            "his_sere_serv_bill.TDL_TOTAL_PATIENT_PRICE",
            "his_sere_serv_bill.TDL_TOTAL_PATIENT_PRICE_BHYT",
            "his_sere_serv_bill.TDL_SERVICE_ID",
            "his_sere_serv_bill.TDL_SERVICE_CODE",
            "his_sere_serv_bill.TDL_SERVICE_NAME",
            "his_sere_serv_bill.TDL_SERVICE_UNIT_ID",
            "his_sere_serv_bill.TDL_PATIENT_TYPE_ID",
            "his_sere_serv_bill.TDL_REQUEST_DEPARTMENT_ID",
            "his_sere_serv_bill.TDL_EXECUTE_DEPARTMENT_ID",
            "his_sere_serv_bill.TDL_REAL_PRICE",
            "his_sere_serv_bill.TDL_REAL_PATIENT_PRICE",
            "his_sere_serv_bill.TDL_REAL_HEIN_PRICE",
        ];
        $param = [];
        try {
            $data = $this->sere_serv_bill
                ->select($select);
            $data_id = $this->sere_serv_bill

                ->select("His_sere_serv_bill.ID");
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('His_sere_serv_bill.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('His_sere_serv_bill.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('His_sere_serv_bill.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('His_sere_serv_bill.is_active'), $this->is_active);
                });
            }
            if ($this->tdl_treatment_id !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('His_sere_serv_bill.tdl_treatment_id'), $this->tdl_treatment_id);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('His_sere_serv_bill.tdl_treatment_id'), $this->tdl_treatment_id);
                });
            }

            if ($this->sere_serv_bill_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('His_sere_serv_bill.' . $key, $this->sub_order_by ?? $item);
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
                $data = SereServBillGetResource::collection($data);
                if (isset($data[0])) {
                    if (($data[0]->id != $this->sere_serv_bill->max('id')) && ($data[0]->id != $this->sere_serv_bill->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)) {
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
                    $query = $query->where(DB::connection('oracle_his')->raw('His_sere_serv_bill.id'), $this->sere_serv_bill_id);
                });
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->first();
            }

            $param_return = [
                $this->prev_cursor_name => $this->prev_cursor ?? null,
                $this->limit_name => $this->limit,
                $this->next_cursor_name => $this->next_cursor ?? null,
                $this->is_include_deleted_name => $this->is_include_deleted ?? false,
                $this->is_active_name => $this->is_active,
                $this->sere_serv_bill_id_name => $this->sere_serv_bill_id,
                $this->tdl_treatment_id_name => $this->tdl_treatment_id,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }
}
