<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Controllers\Controller;
use App\Models\HIS\SereServExt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SereServExtController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->sere_serv_ext = new SereServExt();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if (!$this->sere_serv_ext->getConnection()->getSchemaBuilder()->hasColumn($this->sere_serv_ext->getTable(), $key)) {
                        unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                        unset($this->order_by[$key]);
                    }
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function sere_serv_ext(Request $request)
    {
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
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        $data = $this->sere_serv_ext
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('lower(his_sere_serv_ext.SUBCLINICAL_RESULT_USERNAME)'), 'like', '%' . $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('lower(his_sere_serv_ext.SUBCLINICAL_RESULT_LOGINNAME)'), 'like', '%' . $keyword . '%');
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
}
