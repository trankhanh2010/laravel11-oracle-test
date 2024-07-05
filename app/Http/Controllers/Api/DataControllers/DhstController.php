<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Models\HIS\Dhst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DhstController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->dhst = new Dhst();
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
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if (($this->dhst_id == null) && (($keyword != null) || (!$this->is_include_deleted))) {
            $data = $this->dhst
            ->select($select);
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('lower(his_dhst.execute_loginname)'), 'like', '%' . $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('lower(his_dhst.execute_username)'), 'like', '%' . $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.is_delete'), 0);
                });
            }
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
        }else{
            $data = $this->dhst;
            if ($this->dhst_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_dhst.id'), $this->dhst_id);
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
            'dhst_id' => $this->dhst_id,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);       

    }
}
