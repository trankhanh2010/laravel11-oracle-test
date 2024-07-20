<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Models\HIS\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreatmentController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->treatment = new Treatment();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if (!$this->treatment->getConnection()->getSchemaBuilder()->hasColumn($this->treatment->getTable(), $key)) {
                        unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                        unset($this->order_by[$key]);
                    }
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
        // Kiểm tra giá trị tối đa của limit
        if($this->limit > 100){
            $this->limit = 10;
        }
    }
   public function treatment_get_L_view(Request $request)
   {
       $select = [
           "ID",
           "CREATE_TIME",
           "TREATMENT_CODE",
           "TDL_PATIENT_CODE",
           "TDL_PATIENT_NAME",
           "TDL_PATIENT_DOB",
           "TDL_PATIENT_GENDER_NAME",
           "ICD_CODE",
           "ICD_NAME",
           "ICD_SUB_CODE",
           "ICD_TEXT",
           "IN_TIME",
           "IS_ACTIVE",
           "IN_DATE",
       ];
       $keyword = mb_strtolower($this->keyword, 'UTF-8');
       $data = $this->treatment
           ->select($select);
       if ($keyword != null) {
           $data = $data->where(function ($query) use ($keyword) {
               $query = $query->where(DB::connection('oracle_his')->raw('FUN_CONVERT_TO_UNSIGN(lower(his_treatment.tdl_patient_name))'), 'like', '%' . $keyword . '%')
                   ->orWhere(DB::connection('oracle_his')->raw('FUN_CONVERT_TO_UNSIGN(lower(his_treatment.tdl_patient_code))'), 'like', '%' . $keyword . '%');
           });
       }
       if (!$this->is_include_deleted) {
           $data = $data->where(function ($query) {
               $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_delete'), 0);
           });
       }
       if ($this->is_active !== null) {
           $data = $data->where(function ($query) {
               $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_active'), $this->is_active);
           });
       }
       if ($this->patient_code__exact != null) {
           $data = $data->where(function ($query) {
               $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.tdl_patient_code'), $this->patient_code__exact);
           });
       }


       if ($this->treatment_id == null) {
           $count = $data->count();
           if ($this->order_by != null) {
               foreach ($this->order_by as $key => $item) {
                   $data->orderBy('his_treatment.' . $key, $item);
               }
           }
           $data = $data
               ->skip($this->start)
               ->take($this->limit)
               ->get();
       } else {
           $data = $data->where(function ($query) {
               $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.id'), $this->treatment_id);
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
           'treatment_id' => $this->treatment_id,
           'patient_code__exact' => $this->patient_code__exact,
           'keyword' => $this->keyword,
           'order_by' => $this->order_by_request
       ];
       return return_data_success($param_return, $data);

   }

   public function treatment_get_treatment_with_patient_type_info_sdo(Request $request)
   {
       $select = [
           "his_treatment.ID",
           "his_treatment.CREATE_TIME",
           "his_treatment.MODIFY_TIME",
           "his_treatment.CREATOR",
           "his_treatment.MODIFIER",
           "his_treatment.APP_CREATOR",
           "his_treatment.APP_MODIFIER",
           "his_treatment.IS_ACTIVE",
           "his_treatment.IS_DELETE",
           "his_treatment.TREATMENT_CODE",
           "his_treatment.PATIENT_ID",
           "his_treatment.BRANCH_ID",
           "his_treatment.ICD_CODE",
           "his_treatment.ICD_NAME",
           "his_treatment.ICD_SUB_CODE",
           "his_treatment.ICD_TEXT",
           "his_treatment.IN_TIME",
           "his_treatment.IN_DATE",
           "his_treatment.CLINICAL_IN_TIME",
           "his_treatment.IN_CODE",
           "his_treatment.IN_ROOM_ID",
           "his_treatment.IN_DEPARTMENT_ID",
           "his_treatment.IN_LOGINNAME",
           "his_treatment.IN_USERNAME",
           "his_treatment.IN_TREATMENT_TYPE_ID",
           "his_treatment.IN_ICD_CODE",
           "his_treatment.IN_ICD_NAME",
           "his_treatment.IN_ICD_SUB_CODE",
           "his_treatment.IN_ICD_TEXT",
           "his_treatment.HOSPITALIZATION_REASON",
           "his_treatment.DOCTOR_LOGINNAME",
           "his_treatment.DOCTOR_USERNAME",
           "his_treatment.IS_CHRONIC",
           "his_treatment.JSON_PRINT_ID",
           "his_treatment.IS_EMERGENCY",
           "his_treatment.SUBCLINICAL_RESULT",
           "his_treatment.TDL_FIRST_EXAM_ROOM_ID",
           "his_treatment.TDL_TREATMENT_TYPE_ID",
           "his_treatment.TDL_PATIENT_TYPE_ID",
           "his_treatment.FUND_CUSTOMER_NAME",
           "his_treatment.TDL_PATIENT_CODE",
           "his_treatment.TDL_PATIENT_NAME",
           "his_treatment.TDL_PATIENT_FIRST_NAME",
           "his_treatment.TDL_PATIENT_LAST_NAME",
           "his_treatment.TDL_PATIENT_DOB",
           "his_treatment.TDL_PATIENT_ADDRESS",
           "his_treatment.TDL_PATIENT_GENDER_ID",
           "his_treatment.TDL_PATIENT_GENDER_NAME",
           "his_treatment.TDL_PATIENT_CAREER_NAME",
           "his_treatment.TDL_PATIENT_DISTRICT_CODE",
           "his_treatment.TDL_PATIENT_PROVINCE_CODE",
           "his_treatment.TDL_PATIENT_COMMUNE_CODE",
           "his_treatment.TDL_PATIENT_NATIONAL_NAME",
           "his_treatment.TDL_PATIENT_RELATIVE_TYPE",
           "his_treatment.TDL_PATIENT_RELATIVE_NAME",
           "his_treatment.DEPARTMENT_IDS",
           "his_treatment.CO_DEPARTMENT_IDS",
           "his_treatment.LAST_DEPARTMENT_ID",
           "his_treatment.TDL_PATIENT_PHONE",
           "his_treatment.IS_SYNC_EMR",
           "his_treatment.VIR_IN_MONTH",
           "his_treatment.IN_CODE_SEED_CODE",
           "his_treatment.VIR_IN_YEAR",
           "his_treatment.EMR_COVER_TYPE_ID",
           "his_treatment.HOSPITALIZE_DEPARTMENT_ID",
           "his_treatment.TDL_PATIENT_RELATIVE_MOBILE",
           "his_treatment.TDL_PATIENT_NATIONAL_CODE",
           "his_treatment.TDL_PATIENT_PROVINCE_NAME",
           "his_treatment.TDL_PATIENT_DISTRICT_NAME",
           "his_treatment.TDL_PATIENT_COMMUNE_NAME",
           "his_treatment.TDL_PATIENT_UNSIGNED_NAME",
           "his_treatment.TDL_PATIENT_ETHNIC_NAME",
           "his_treatment.IS_TUBERCULOSIS",
           "his_treatment.TDL_HEIN_MEDI_ORG_CODE",
           "his_treatment.TDL_HEIN_CARD_NUMBER",
           "his_treatment.TDL_HEIN_CARD_FROM_TIME",
           "his_treatment.TDL_HEIN_CARD_TO_TIME",
       ];
       $param = [
           'patient_type:id,patient_type_code,patient_type_name,IS_COPAYMENT',
           'treatment_type:id,treatment_type_code,treatment_type_name,HEIN_TREATMENT_TYPE_CODE',
           'accident_hurts',
           'adrs',
           'allergy_cards',
           'antibiotic_requests',
           'appointment_servs',
           'babys',
           'cares',
           'care_sums',
           'carer_card_borrows',
           'debates',
           'department_trans',
           'deposit_reqs',
           'dhsts',
           'exp_mest_maty_reqs',
           'exp_mest_mety_reqs',
           'hein_approvals:id,treatment_id,LEVEL_CODE,RIGHT_ROUTE_CODE,RIGHT_ROUTE_TYPE_CODE,ADDRESS',
           'hiv_treatments',
           'hold_returns',
           'imp_mest_mate_reqs',
           'imp_mest_medi_reqs',
           'infusion_sums',
           'medi_react_sums',
           'medical_assessments',
           'medicine_interactives',
           'mr_check_summarys',
           'obey_contraindis',
           'patient_type_alters',
           'prepares',
           'reha_sums',
           'sere_servs',
           'service_reqs',
           'severe_illness_infos',
           'trackings',
           'trans_reqs',
           'transactions',
           'transfusion_sums',
           'treatment_bed_rooms',
           'treatment_borrows',
           'treatment_files',
           'treatment_loggings',
           'treatment_unlimits',
           'tuberculosis_treats'
       ];

       $data = $this->treatment
           ->select($select);
       if (!$this->is_include_deleted) {
           $data = $data->where(function ($query) {
               $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_delete'), 0);
           });
       }
       if ($this->is_active !== null) {
           $data = $data->where(function ($query) {
               $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.is_active'), $this->is_active);
           });
       }

       if ($this->treatment_id != null) {
        $data = $data->where(function ($query) {
            $query = $query->where(DB::connection('oracle_his')->raw('his_treatment.id'), $this->treatment_id);
        });
        $data = $data
            ->skip($this->start)
            ->take($this->limit)
            ->first();
       } else {
        $data = [];
       }

       $param_return = [
           'start' => $this->start,
           'limit' => $this->limit,
           'is_include_deleted' => $this->is_include_deleted ?? false,
           'is_active' => $this->is_active,
           'treatment_id' => $this->treatment_id,
           'keyword' => $this->keyword,
           'order_by' => $this->order_by_request
       ];
       return return_data_success($param_return, $data);
   }
}
