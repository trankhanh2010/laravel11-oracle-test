<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Controllers\Controller;
use App\Http\Resources\SereServExtResource;
use App\Models\HIS\SereServExt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SereServExtController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->sere_serv_ext = new SereServExt();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            // foreach ($this->order_by as $key => $item) {
            //     if (!in_array($key, $this->order_by_join)) {
            //         if (!$this->sere_serv_ext->getConnection()->getSchemaBuilder()->hasColumn($this->sere_serv_ext->getTable(), $key)) {
            //             unset($this->order_by_request[camelCaseFromUnderscore($key)]);
            //             unset($this->order_by[$key]);
            //         }
            //     }
            // }
            $columns = Cache::remember('columns_' . $this->sere_serv_ext_name, $this->columns_time, function () {
                return  Schema::connection('oracle_his')->getColumnListing($this->sere_serv_ext->getTable()) ?? [];

            });
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if ((!in_array($key, $columns))) {
                        $this->errors[snakeToCamel($key)] = $this->mess_order_by_name;
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
                $this->sere_serv_ext_last_id = $this->sere_serv_ext->max('id');
                $this->cursor = $this->sere_serv_ext_last_id;
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
    public function sere_serv_ext(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "ID",
            "CREATE_TIME",
            "MODIFY_TIME",
            "MODIFIER",
            "APP_MODIFIER",
            "IS_ACTIVE",
            "IS_DELETE",
            "SERE_SERV_ID",
            "CONCLUDE",
            "JSON_PRINT_ID",
            "DESCRIPTION_SAR_PRINT_ID",
            "MACHINE_CODE",
            "MACHINE_ID",
            "NUMBER_OF_FILM",
            "BEGIN_TIME",
            "END_TIME",
            "TDL_SERVICE_REQ_ID",
            "TDL_TREATMENT_ID",
            "FILM_SIZE_ID",
            "SUBCLINICAL_PRES_USERNAME",
            "SUBCLINICAL_PRES_LOGINNAME",
            "SUBCLINICAL_RESULT_USERNAME",
            "SUBCLINICAL_RESULT_LOGINNAME",
            "SUBCLINICAL_PRES_ID",
            "DESCRIPTION",
        ];
        $keyword = $this->keyword;
        $data = $this->sere_serv_ext
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_ext.SUBCLINICAL_RESULT_USERNAME'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv_ext.SUBCLINICAL_RESULT_LOGINNAME'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_ext.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_ext.is_active'), $this->is_active);
            });
        }
        if ($this->sere_serv_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv_ext.sere_serv_id'), $this->sere_serv_ids);
            });
        }

        if ($this->sere_serv_ext_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_sere_serv_ext.' . $key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_ext.id'), $this->sere_serv_ext_id);
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
            'sere_serv_ext_id' => $this->sere_serv_ext_id,
            'sere_serv_ids' => $this->sere_serv_ids,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function sere_serv_ext_v2(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
        
        $select = [
            "ID",
            "CREATE_TIME",
            "MODIFY_TIME",
            "CREATOR",
            "APP_CREATOR",
            "MODIFIER",
            "APP_MODIFIER",
            "IS_ACTIVE",
            "IS_DELETE",
            "SERE_SERV_ID",
            "CONCLUDE",
            "JSON_PRINT_ID",
            "DESCRIPTION_SAR_PRINT_ID",
            "MACHINE_CODE",
            "MACHINE_ID",
            "NUMBER_OF_FILM",
            "BEGIN_TIME",
            "END_TIME",
            "TDL_SERVICE_REQ_ID",
            "TDL_TREATMENT_ID",
            "FILM_SIZE_ID",
            "SUBCLINICAL_PRES_USERNAME",
            "SUBCLINICAL_PRES_LOGINNAME",
            "SUBCLINICAL_RESULT_USERNAME",
            "SUBCLINICAL_RESULT_LOGINNAME",
            "SUBCLINICAL_PRES_ID",
            "DESCRIPTION",
        ];
        $keyword = $this->keyword;
        try {
            $data = $this->sere_serv_ext
                ->select($select);
            $data_id = $this->sere_serv_ext
                ->select("HIS_SERE_SERV_EXT.ID");
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_ext.SUBCLINICAL_RESULT_USERNAME'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv_ext.SUBCLINICAL_RESULT_LOGINNAME'), 'like', $keyword . '%');
                });
                $data_id = $data_id->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_ext.SUBCLINICAL_RESULT_USERNAME'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv_ext.SUBCLINICAL_RESULT_LOGINNAME'), 'like', $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_ext.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_ext.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_ext.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_ext.is_active'), $this->is_active);
                });
            }
            if ($this->sere_serv_ids != null) {
                $data = $data->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv_ext.sere_serv_id'), $this->sere_serv_ids);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv_ext.sere_serv_id'), $this->sere_serv_ids);
                });
            }

            if ($this->sere_serv_ext_id == null) {
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_sere_serv_ext.' . $key, $this->sub_order_by ?? $item);
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
                $data = SereServExtResource::collection($data);
                if (isset($data[0])) {
                    if (($data[0]->id != $this->sere_serv_ext->max('id')) && ($data[0]->id != $this->sere_serv_ext->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)) {
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
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_ext.id'), $this->sere_serv_ext_id);
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
                'sere_serv_ext_id' => $this->sere_serv_ext_id,
                'sere_serv_ids' => $this->sere_serv_ids,
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
