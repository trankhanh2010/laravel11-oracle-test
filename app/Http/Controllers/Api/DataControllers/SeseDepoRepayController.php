<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Resources\SeseDepoRepayGetViewResource;
use App\Models\HIS\SeseDepoRepay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeseDepoRepayController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->sese_depo_repay = new SeseDepoRepay();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            // foreach ($this->order_by as $key => $item) {
            //     if (!in_array($key, $this->order_by_join)) {
            //         if ((!$this->sese_depo_repay->getConnection()->getSchemaBuilder()->hasColumn($this->sese_depo_repay->getTable(), $key))) {
            //             unset($this->order_by_request[camelCaseFromUnderscore($key)]);
            //             unset($this->order_by[$key]);
            //         }
            //     }
            // }
            $columns = Cache::remember('columns_' . $this->sese_depo_repay_name, $this->columns_time, function () {
                return  Schema::connection('oracle_his')->getColumnListing($this->sese_depo_repay->getTable()) ?? [];

            });
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if ((!in_array($key, $columns))) {
                        $this->errors[$key] = $this->mess_order_by_name;
                        unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                        unset($this->order_by[$key]);
                    }
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if ($this->cursor === 0) {
                $this->sese_depo_repay_last_id = $this->sese_depo_repay->max('id') ?? 0;
                $this->cursor = $this->sese_depo_repay_last_id;
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

    public function sese_depo_repay_get_view(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
        
        $select = [
            "his_sese_depo_repay.id",
            "his_sese_depo_repay.create_time",
            "his_sese_depo_repay.modify_time",
            "his_sese_depo_repay.creator",
            "his_sese_depo_repay.modifier",
            "his_sese_depo_repay.app_creator",
            "his_sese_depo_repay.app_modifier",
            "his_sese_depo_repay.is_active",
            "his_sese_depo_repay.is_delete",
            "his_sese_depo_repay.sere_serv_deposit_id",
            "his_sese_depo_repay.repay_id",
            "his_sese_depo_repay.amount",
            "his_sese_depo_repay.is_cancel",
            "his_sese_depo_repay.tdl_treatment_id",
            "his_sese_depo_repay.tdl_service_req_id",
            "his_sese_depo_repay.tdl_service_id",
            "his_sese_depo_repay.tdl_service_code",
            "his_sese_depo_repay.tdl_service_name",
            "his_sese_depo_repay.tdl_service_type_id",
            "his_sese_depo_repay.tdl_service_unit_id",
            "his_sese_depo_repay.tdl_patient_type_id",
            "his_sese_depo_repay.tdl_hein_service_type_id",
            "his_sese_depo_repay.tdl_request_department_id",
            "his_sese_depo_repay.tdl_execute_department_id",
            "his_sese_depo_repay.tdl_amount",
            "his_sese_depo_repay.tdl_vir_price",
            "his_sese_depo_repay.tdl_vir_price_no_add_price",
            "his_sese_depo_repay.tdl_vir_hein_price",
            "his_sese_depo_repay.tdl_vir_total_price",
            "his_sese_depo_repay.tdl_vir_total_hein_price",
            "his_sese_depo_repay.tdl_vir_total_patient_price",
            "V_HIS_SESE_DEPO_REPAY.sere_serv_id",

        ];
        $param = [];

        $keyword = $this->keyword;
        try {
            $data = $this->sese_depo_repay
            ->leftJoin('V_HIS_SESE_DEPO_REPAY ', 'his_sese_depo_repay.id', '=', 'V_HIS_SESE_DEPO_REPAY.id')
                ->select($select);
            $data_id = $this->sese_depo_repay
                ->select("his_sese_depo_repay.ID");
            if ($keyword != null) {
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sese_depo_repay.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sese_depo_repay.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sese_depo_repay.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sese_depo_repay.is_active'), $this->is_active);
                });
            }
            if ($this->tdl_treatment_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sese_depo_repay.tdl_treatment_id'), $this->tdl_treatment_id);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sese_depo_repay.tdl_treatment_id'), $this->tdl_treatment_id);
                });
            }
            if ($this->sese_depo_repay_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_sese_depo_repay.' . $key, $this->sub_order_by ?? $item);
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
                $data = SeseDepoRepayGetViewResource::collection($data);
                if (isset($data[0])) {
                    if (($data[0]->id != $this->sese_depo_repay->max('id')) && ($data[0]->id != $this->sese_depo_repay->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)) {
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
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sese_depo_repay.id'), $this->sese_depo_repay_id);
                });
                $data = $data
                    ->first();
            }
            $param_return = [
                'prev_cursor' => $this->prev_cursor ?? null,
                'limit' => $this->limit,
                'next_cursor' => $this->next_cursor ?? null,
                'is_include_deleted' => $this->is_include_deleted ?? false,
                'is_active' => $this->is_active,
                'sese_depo_repay_id' => $this->sese_depo_repay_id,
                'tdl_treatment_id' => $this->tdl_treatment_id,
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
