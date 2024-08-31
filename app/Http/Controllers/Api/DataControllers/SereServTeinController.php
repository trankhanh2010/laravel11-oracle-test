<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Resources\SereServTeinGetViewResource;
use App\Http\Resources\SereServTeinResource;
use App\Models\HIS\SereServTein;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SereServTeinController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->sere_serv_tein = new SereServTein();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->sere_serv_tein);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }

        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if ($this->cursor === 0) {
                $this->sere_serv_tein_last_id = $this->sere_serv_tein->max('id');
                $this->cursor = $this->sere_serv_tein_last_id;
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
    public function sere_serv_tein_get(Request $request)
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
            "MODIFIER",
            "APP_CREATOR",
            "APP_MODIFIER",
            "IS_ACTIVE",
            "IS_DELETE",
            "SERE_SERV_ID",
            "TEST_INDEX_ID",
            "VALUE",
            "RESULT_CODE",
            "TDL_TREATMENT_ID",
            "TDL_SERVICE_REQ_ID",
            "RESULT_DESCRIPTION",
        ];
        $keyword = $this->keyword;
        $data = $this->sere_serv_tein
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.value'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv_tein.result_code'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.is_active'), $this->is_active);
            });
        }
        if ($this->test_index_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv_tein.test_index_id'), $this->test_index_ids);
            });
        }
        if ($this->tdl_treatment_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.tdl_treatment_id'), $this->tdl_treatment_id);
            });
        }


        if ($this->sere_serv_tein_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_sere_serv_tein.' . $key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.id'), $this->sere_serv_tein_id);
            });
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->first();
        }

        $param_return = [
            $this->start_name => $this->start,
            $this->limit_name => $this->limit,
            $this->count_name => $count ?? null,
            $this->is_include_deleted_name => $this->is_include_deleted ?? false,
            $this->is_active_name => $this->is_active,
            $this->sere_serv_tein_id_name => $this->sere_serv_tein_id,
            $this->test_index_ids_name => $this->test_index_ids,
            $this->tdl_treatment_id_name => $this->tdl_treatment_id,
            $this->keyword_name => $this->keyword,
            $this->order_by_name => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function sere_serv_tein_get_v2(Request $request)
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
            "MODIFIER",
            "APP_CREATOR",
            "APP_MODIFIER",
            "IS_ACTIVE",
            "IS_DELETE",
            "SERE_SERV_ID",
            "TEST_INDEX_ID",
            "VALUE",
            "RESULT_CODE",
            "TDL_TREATMENT_ID",
            "TDL_SERVICE_REQ_ID",
            "RESULT_DESCRIPTION",
        ];
        $keyword = $this->keyword;
        try {
            $data = $this->sere_serv_tein
                ->select($select);
            $data_id = $this->sere_serv_tein
                ->select("HIS_SERE_SERV_TEIN.ID");
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.value'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv_tein.result_code'), 'like', $keyword . '%');
                });
                $data_id = $data_id->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.value'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv_tein.result_code'), 'like', $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.is_active'), $this->is_active);
                });
            }
            if ($this->test_index_ids != null) {
                $data = $data->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv_tein.test_index_id'), $this->test_index_ids);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv_tein.test_index_id'), $this->test_index_ids);
                });
            }
            if ($this->tdl_treatment_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.tdl_treatment_id'), $this->tdl_treatment_id);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.tdl_treatment_id'), $this->tdl_treatment_id);
                });
            }


            if ($this->sere_serv_tein_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_sere_serv_tein.' . $key, $this->sub_order_by ?? $item);
                    }
                }
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
                $data = SereServTeinResource::collection($data);
                if (isset($data[0])) {
                    if (($data[0]->id != $this->sere_serv_tein->max('id')) && ($data[0]->id != $this->sere_serv_tein->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)) {
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
                    $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.id'), $this->sere_serv_tein_id);
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
                $this->sere_serv_tein_id_name => $this->sere_serv_tein_id,
                $this->test_index_ids_name => $this->test_index_ids,
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
    public function sere_serv_tein_get_view(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "his_sere_serv_tein.ID",
            "his_sere_serv_tein.CREATE_TIME",
            "his_sere_serv_tein.MODIFY_TIME",
            "his_sere_serv_tein.CREATOR",
            "his_sere_serv_tein.MODIFIER",
            "his_sere_serv_tein.APP_CREATOR",
            "his_sere_serv_tein.APP_MODIFIER",
            "his_sere_serv_tein.IS_ACTIVE",
            "his_sere_serv_tein.IS_DELETE",
            "his_sere_serv_tein.SERE_SERV_ID",
            "his_sere_serv_tein.TEST_INDEX_ID",
            "his_sere_serv_tein.VALUE",
            "his_sere_serv_tein.TDL_TREATMENT_ID",
            "his_sere_serv_tein.MACHINE_ID",
            "his_sere_serv_tein.NOTE",
            "his_sere_serv_tein.LEAVEN",
            "his_sere_serv_tein.TDL_SERVICE_REQ_ID",

            "machine.machine_group_code",
            "machine.source_code",
            "machine.serial_number",
            "machine.MACHINE_NAME",
            "machine.MACHINE_CODE",

            "test_index.test_index_unit_id",
            "test_index.TEST_INDEX_NAME",
            "test_index.TEST_INDEX_CODE",
            "test_index.IS_NOT_SHOW_SERVICE",

            "test_index_unit.test_index_unit_code",
            "test_index_unit.test_index_unit_name",
        ];
        $param = [
            'machine:id,machine_group_code,source_code,serial_number,MACHINE_NAME,MACHINE_CODE',
            'test_index:id,test_index_unit_id,TEST_INDEX_NAME,TEST_INDEX_CODE,IS_NOT_SHOW_SERVICE',
            'test_index.test_index_unit:id,TEST_INDEX_UNIT_NAME,TEST_INDEX_UNIT_CODE',
        ];

        $keyword = $this->keyword;
        $data = $this->sere_serv_tein
            ->leftJoin('his_machine as machine', 'machine.id', '=', 'his_sere_serv_tein.machine_id')
            ->leftJoin('his_test_index as test_index', 'test_index.id', '=', 'his_sere_serv_tein.test_index_id')
            ->leftJoin('his_test_index_unit as test_index_unit', 'test_index_unit.id', '=', 'test_index.test_index_unit_id')
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.value'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv_tein.result_code'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.is_active'), $this->is_active);
            });
        }
        if ($this->sere_serv_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv_tein.sere_serv_id'), $this->sere_serv_ids);
            });
        }


        if ($this->sere_serv_tein_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_sere_serv_tein.' . $key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.id'), $this->sere_serv_tein_id);
            });
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->first();
        }

        $param_return = [
            $this->start_name => $this->start,
            $this->limit_name => $this->limit,
            $this->count_name => $count ?? null,
            $this->is_include_deleted_name => $this->is_include_deleted ?? false,
            $this->is_active_name => $this->is_active,
            $this->sere_serv_tein_id_name => $this->sere_serv_tein_id,
            $this->sere_serv_ids_name => $this->sere_serv_ids,
            $this->keyword_name => $this->keyword,
            $this->order_by_name => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function sere_serv_tein_get_view_v2(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "his_sere_serv_tein.ID",
            "his_sere_serv_tein.CREATE_TIME",
            "his_sere_serv_tein.MODIFY_TIME",
            "his_sere_serv_tein.CREATOR",
            "his_sere_serv_tein.MODIFIER",
            "his_sere_serv_tein.APP_CREATOR",
            "his_sere_serv_tein.APP_MODIFIER",
            "his_sere_serv_tein.IS_ACTIVE",
            "his_sere_serv_tein.IS_DELETE",
            "his_sere_serv_tein.SERE_SERV_ID",
            "his_sere_serv_tein.TEST_INDEX_ID",
            "his_sere_serv_tein.VALUE",
            "his_sere_serv_tein.TDL_TREATMENT_ID",
            "his_sere_serv_tein.MACHINE_ID",
            "his_sere_serv_tein.NOTE",
            "his_sere_serv_tein.LEAVEN",
            "his_sere_serv_tein.TDL_SERVICE_REQ_ID",

            "machine.machine_group_code",
            "machine.source_code",
            "machine.serial_number",
            "machine.MACHINE_NAME",
            "machine.MACHINE_CODE",

            "test_index.test_index_unit_id",
            "test_index.TEST_INDEX_NAME",
            "test_index.TEST_INDEX_CODE",
            "test_index.IS_NOT_SHOW_SERVICE",

            "test_index_unit.test_index_unit_code",
            "test_index_unit.test_index_unit_name",
        ];
        $param = [
            'machine:id,machine_group_code,source_code,serial_number,MACHINE_NAME,MACHINE_CODE',
            'test_index:id,test_index_unit_id,TEST_INDEX_NAME,TEST_INDEX_CODE,IS_NOT_SHOW_SERVICE',
            'test_index.test_index_unit:id,TEST_INDEX_UNIT_NAME,TEST_INDEX_UNIT_CODE',
        ];

        $keyword = $this->keyword;
        try {
        $data = $this->sere_serv_tein
            ->leftJoin('his_machine as machine', 'machine.id', '=', 'his_sere_serv_tein.machine_id')
            ->leftJoin('his_test_index as test_index', 'test_index.id', '=', 'his_sere_serv_tein.test_index_id')
            ->leftJoin('his_test_index_unit as test_index_unit', 'test_index_unit.id', '=', 'test_index.test_index_unit_id')
            ->select($select);
        $data_id = $this->sere_serv_tein
            ->leftJoin('his_machine as machine', 'machine.id', '=', 'his_sere_serv_tein.machine_id')
            ->leftJoin('his_test_index as test_index', 'test_index.id', '=', 'his_sere_serv_tein.test_index_id')
            ->leftJoin('his_test_index_unit as test_index_unit', 'test_index_unit.id', '=', 'test_index.test_index_unit_id')
            ->select("HIS_SERE_SERV_TEIN.ID");
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.value'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv_tein.result_code'), 'like', $keyword . '%');
            });
            $data_id = $data_id->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.value'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_sere_serv_tein.result_code'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.is_delete'), 0);
            });
            $data_id = $data_id->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.is_active'), $this->is_active);
            });
            $data_id = $data_id->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.is_active'), $this->is_active);
            });
        }
        if ($this->sere_serv_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv_tein.sere_serv_id'), $this->sere_serv_ids);
            });
            $data_id = $data_id->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_sere_serv_tein.sere_serv_id'), $this->sere_serv_ids);
            });
        }


        if ($this->sere_serv_tein_id == null) {
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_sere_serv_tein.' . $key, $this->sub_order_by ?? $item);
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
                $data = SereServTeinGetViewResource::collection($data);
                if(isset($data[0])){
                    if(($data[0]->id != $this->sere_serv_tein->max('id')) && ($data[0]->id != $this->sere_serv_tein->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)){
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
                $query = $query->where(DB::connection('oracle_his')->raw('his_sere_serv_tein.id'), $this->sere_serv_tein_id);
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
            $this->sere_serv_tein_id_name => $this->sere_serv_tein_id,
            $this->sere_serv_ids_name => $this->sere_serv_ids,
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
