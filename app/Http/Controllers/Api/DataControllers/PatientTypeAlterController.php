<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Models\HIS\PatientTypeAlter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientTypeAlterController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->patient_type_alter = new PatientTypeAlter();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if (!$this->patient_type_alter->getConnection()->getSchemaBuilder()->hasColumn($this->patient_type_alter->getTable(), $key)) {
                        unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                        unset($this->order_by[$key]);
                    }
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function patient_type_alter_get_view(Request $request)
    {
        $select = [
            "his_patient_type_alter.ID",
            "his_patient_type_alter.CREATE_TIME",
            "his_patient_type_alter.MODIFY_TIME",
            "his_patient_type_alter.CREATOR",
            "his_patient_type_alter.MODIFIER",
            "his_patient_type_alter.APP_CREATOR",
            "his_patient_type_alter.APP_MODIFIER",
            "his_patient_type_alter.IS_ACTIVE",
            "his_patient_type_alter.IS_DELETE",
            "his_patient_type_alter.DEPARTMENT_TRAN_ID",
            "his_patient_type_alter.TREATMENT_TYPE_ID",
            "his_patient_type_alter.PATIENT_TYPE_ID",
            "his_patient_type_alter.LOG_TIME",
            "his_patient_type_alter.TREATMENT_ID",
            "his_patient_type_alter.TDL_PATIENT_ID",
            "his_patient_type_alter.EXECUTE_ROOM_ID",
            "his_patient_type_alter.LEVEL_CODE",
            "his_patient_type_alter.RIGHT_ROUTE_CODE",
            "his_patient_type_alter.RIGHT_ROUTE_TYPE_CODE",
            "his_patient_type_alter.HEIN_MEDI_ORG_CODE",
            "his_patient_type_alter.HEIN_MEDI_ORG_NAME",
            "his_patient_type_alter.HAS_BIRTH_CERTIFICATE",
            "his_patient_type_alter.HEIN_CARD_NUMBER",
            "his_patient_type_alter.HEIN_CARD_FROM_TIME",
            "his_patient_type_alter.HEIN_CARD_TO_TIME",
            "his_patient_type_alter.ADDRESS",
            "his_patient_type_alter.JOIN_5_YEAR",
            "his_patient_type_alter.PAID_6_MONTH",
            "his_patient_type_alter.PRIMARY_PATIENT_TYPE_ID",

            "patient_type.patient_type_code",
            "patient_type.patient_type_name",
            "patient_type.IS_COPAYMENT",

            "treatment_type.treatment_type_code",
            "treatment_type.treatment_type_name",
            "treatment_type.HEIN_TREATMENT_TYPE_CODE"
        ];
        $param = [
            'patient_type:id,patient_type_code,patient_type_name,IS_COPAYMENT',
            'treatment_type:id,treatment_type_code,treatment_type_name,HEIN_TREATMENT_TYPE_CODE'
        ];
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        $data = $this->patient_type_alter
            ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_patient_type_alter.patient_type_id')
            ->leftJoin('his_treatment_type as treatment_type', 'treatment_type.id', '=', 'his_patient_type_alter.treatment_type_id')
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('lower(his_patient_type_alter.HAS_BIRTH_CERTIFICATE)'), 'like', '%' . $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('lower(his_patient_type_alter.HEIN_CARD_NUMBER)'), 'like', '%' . $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.is_active'), $this->is_active);
            });
        }
        if ($this->treatment_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.treatment_id'), $this->treatment_id);
            });
        }
        if ($this->log_time_to != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.log_time'), '<=', $this->log_time_to);
            });
        }
        if ($this->patient_type_alter_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_patient_type_alter.' . $key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_alter.id'), $this->patient_type_alter_id);
            });
            $data = $data
                ->first();
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? null,
            'is_include_deleted' => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active,
            'patient_type_alter_id' => $this->patient_type_alter_id,
            'treatment_id' => $this->treatment_id,
            'log_time_to' => $this->log_time_to,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }
}
