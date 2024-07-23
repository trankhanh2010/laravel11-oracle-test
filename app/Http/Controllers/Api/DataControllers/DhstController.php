<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Resources\DhstResource;
use App\Models\HIS\AntibioticRequest;
use App\Models\HIS\Dhst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if (!$this->dhst->getConnection()->getSchemaBuilder()->hasColumn($this->dhst->getTable(), $key)) {
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
            if($this->cursor === 0){
                $this->dhst_last_id = $this->dhst->max('id');
                $this->cursor = $this->dhst_last_id;
                $this->equal = "<=";
            }
        }
    }
    public function dhst_get(Request $request)
    {
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
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? null,
            'is_include_deleted' => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active,
            'dhst_id' => $this->dhst_id,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function dhst_get_v2(Request $request)
    {
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
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? null,
            'is_include_deleted' => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active,
            'dhst_id' => $this->dhst_id,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function dhst_get_v3(Request $request)
    {
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
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_dhst.' . $key, $item);
                    }
                }
                // Chuyển truy vấn sang chuỗi sql
                $sql = $data->toSql();
                // Truyền tham số qua binding tránh SQL Injection
                $bindings = $data->getBindings();
                $fullSql = 'SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (' . $sql . ') a WHERE ROWNUM <= ' . ($this->limit + $this->start) . ' AND ID ' . $this->equal . $this->cursor . ') WHERE rnum > ' . $this->start;
                $data = DB::connection('oracle_his')->select($fullSql, $bindings);
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
                'cursor' => $data[0]->id ?? null,
                'limit' => $this->limit,
                'next_cursor' => $data[($this->limit - 1)]->id ?? null,
                'is_include_deleted' => $this->is_include_deleted ?? false,
                'is_active' => $this->is_active,
                'dhst_id' => $this->dhst_id,
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
