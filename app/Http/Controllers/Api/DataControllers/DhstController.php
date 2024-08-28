<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Resources\DhstResource;
use App\Models\HIS\AntibioticRequest;
use App\Models\HIS\Dhst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DhstController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->dhst = new Dhst();
        $this->antibiotic_request = new AntibioticRequest();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->antibiotic_request);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if($this->cursor === 0){
                $this->dhst_last_id = $this->dhst->max('id');
                $this->cursor = $this->dhst_last_id;
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
    public function dhst_get(Request $request)
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
            "TREATMENT_ID",
            "EXECUTE_ROOM_ID",
            "EXECUTE_LOGINNAME",
            "EXECUTE_USERNAME",
            "EXECUTE_TIME",
            "TEMPERATURE",
            "BREATH_RATE",
            "WEIGHT",
            "HEIGHT",
            "BLOOD_PRESSURE_MAX",
            "BLOOD_PRESSURE_MIN",
            "PULSE",
            "VIR_BMI",
            "VIR_BODY_SURFACE_AREA",
        ];
        $param = [
            'antibiotic_request',
            'cares',
            'ksk_generals',
            'ksk_occupationals',
            'service_reqs',
        ];
        $keyword = $this->keyword;
        $data = $this->dhst
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.execute_loginname'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_dhst.execute_username'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.is_active'), $this->is_active);
            });
        }
        if ($this->dhst_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_dhst.' . $key, $item);
                }
            }
            $data = $data->with($param);
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.id'), $this->dhst_id);
            });
            $data = $data->with($param);
            $data = $data
                ->first();
        }
        $param_return = [
            $this->start_name => $this->start,
            $this->limit_name => $this->limit,
            $this->count_name => $count ?? null,
            $this->is_include_deleted_name => $this->is_include_deleted ?? false,
            $this->is_active_name => $this->is_active,
            $this->dhst_id_name => $this->dhst_id,
            $this->keyword_name => $this->keyword,
            $this->order_by_name => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function dhst_get_v2(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "his_dhst.ID",
            "his_dhst.CREATE_TIME",
            "his_dhst.MODIFY_TIME",
            "his_dhst.CREATOR",
            "his_dhst.MODIFIER",
            "his_dhst.APP_CREATOR",
            "his_dhst.APP_MODIFIER",
            "his_dhst.IS_ACTIVE",
            "his_dhst.IS_DELETE",
            "his_dhst.TREATMENT_ID",
            "his_dhst.EXECUTE_ROOM_ID",
            "his_dhst.EXECUTE_LOGINNAME",
            "his_dhst.EXECUTE_USERNAME",
            "his_dhst.EXECUTE_TIME",
            "his_dhst.TEMPERATURE",
            "his_dhst.BREATH_RATE",
            "his_dhst.WEIGHT",
            "his_dhst.HEIGHT",
            "his_dhst.BLOOD_PRESSURE_MAX",
            "his_dhst.BLOOD_PRESSURE_MIN",
            "his_dhst.PULSE",
            "his_dhst.VIR_BMI",
            "his_dhst.VIR_BODY_SURFACE_AREA",

        ];
        $param = [
            'antibiotic_request',
            'cares',
            'ksk_generals',
            'ksk_occupationals',
            'service_reqs',
        ];
        $keyword = $this->keyword;
        $data = $this->dhst

            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.execute_loginname'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_dhst.execute_username'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.is_active'), $this->is_active);
            });
        }
        if ($this->dhst_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_dhst.' . $key, $item);
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
            $data = DhstResource::collection($data);
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.id'), $this->dhst_id);
            });
            $data = $data->with($param);
            $data = $data
                ->first();
        }
        $param_return = [
            $this->start_name => $this->start,
            $this->limit_name => $this->limit,
            $this->count_name => $count ?? null,
            $this->is_include_deleted_name => $this->is_include_deleted ?? false,
            $this->is_active_name => $this->is_active,
            $this->dhst_id_name => $this->dhst_id,
            $this->keyword_name => $this->keyword,
            $this->order_by_name => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function dhst_get_v3(Request $request)
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
            "TREATMENT_ID",
            "EXECUTE_ROOM_ID",
            "EXECUTE_LOGINNAME",
            "EXECUTE_USERNAME",
            "EXECUTE_TIME",
            "TEMPERATURE",
            "BREATH_RATE",
            "WEIGHT",
            "HEIGHT",
            "BLOOD_PRESSURE_MAX",
            "BLOOD_PRESSURE_MIN",
            "PULSE",
            "VIR_BMI",
            "VIR_BODY_SURFACE_AREA",
        ];
        $param = [
            'antibiotic_request',
            'cares',
            'ksk_generals',
            'ksk_occupationals',
            'service_reqs',
        ];
        $keyword = $this->keyword;
        try {
            $data = $this->dhst
                ->select($select);
            $data_id = $this->dhst
                ->select("HIS_DHST.ID");
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.execute_loginname'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_dhst.execute_username'), 'like', $keyword . '%');
                });
                $data_id = $data_id->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.execute_loginname'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_dhst.execute_username'), 'like', $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.is_active'), $this->is_active);
                });
            }
            if ($this->dhst_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_dhst.' . $key, $this->sub_order_by ?? $item);
                    }
                }
                // Chuyển truy vấn sang chuỗi sql
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
                $data = DhstResource::collection($data);
                if(isset($data[0])){
                    if(($data[0]->id != $this->dhst->max('id')) && ($data[0]->id != $this->dhst->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)){
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
                    $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.id'), $this->dhst_id);
                });
                $data = $data->with($param);
                $data = $data
                    ->first();
            }
            $param_return = [
                $this->prev_cursor_name => $this->prev_cursor ?? null,
                $this->limit_name => $this->limit,
                $this->next_cursor_name => $this->next_cursor ?? null,
                $this->is_include_deleted_name => $this->is_include_deleted ?? false,
                $this->is_active_name => $this->is_active,
                $this->dhst_id_name => $this->dhst_id,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
}
