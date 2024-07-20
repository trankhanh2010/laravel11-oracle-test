<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Models\HIS\Care;
use App\Models\HIS\Dhst;
use App\Models\HIS\ExpMest;
use App\Models\HIS\ExpMestMaterial;
use App\Models\HIS\ExpMestMedicine;
use App\Models\HIS\ImpMest;
use App\Models\HIS\SereServ;
use App\Models\HIS\SereServExt;
use App\Models\HIS\ServiceReq;
use App\Models\HIS\Tracking;
use App\Models\HIS\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->tracking = new Tracking();
        $this->treatment = new Treatment();
        $this->exp_mest = new ExpMest();
        $this->imp_mest = new ImpMest();
        $this->exp_mest_medicine = new ExpMestMedicine();
        $this->care = new Care();
        $this->exp_mest_material = new ExpMestMaterial();
        $this->service_req = new ServiceReq();
        $this->sere_serv_ext = new SereServExt();
        $this->sere_serv = new SereServ();
        $this->dhst = new Dhst();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if (!$this->tracking->getConnection()->getSchemaBuilder()->hasColumn($this->tracking->getTable(), $key)) {
                        unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                        unset($this->order_by[$key]);
                    }
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
        // Kiểm tra giá trị tối đa của limit
        if($this->limit > 500){
            $this->limit = 10;
        }
    }
    public function tracking_get(Request $request)
    {
        $select = [
            'his_tracking.id',
            'his_tracking.create_time',
            'his_tracking.modify_time',
            'his_tracking.creator',
            'his_tracking.modifier',
            'his_tracking.app_creator',
            'his_tracking.app_modifier',
            'his_tracking.is_active',
            'his_tracking.is_delete',
            'his_tracking.treatment_id',
            'his_tracking.tracking_time',
            'his_tracking.icd_code',
            'his_tracking.icd_name',
            'his_tracking.department_id',
            'his_tracking.care_instruction',
            'his_tracking.room_id',
            'his_tracking.emr_document_stt_id',
            'his_tracking.content',
        ];
        $param = [
            'cares',
            'debates',
            'Dhsts',
            'service_reqs'
        ];

        $keyword = create_slug(mb_strtolower($this->keyword, 'UTF-8'));
        $data = $this->tracking
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('FUN_CONVERT_TO_UNSIGN(lower(his_tracking.icd_code))'), 'like', '%' . $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('FUN_CONVERT_TO_UNSIGN(lower(his_tracking.icd_name))'), 'like', '%' . $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_tracking.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_tracking.is_active'), $this->is_active);
            });
        }
        if ($this->treatment_ids != null) {
            $data = $data->where(function ($query) {
                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_tracking.treatment_id'), $this->treatment_ids);
            });
        }
        if ($this->create_time_to != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_tracking.create_time'), '>=', $this->create_time_to);
            });
        }


        if ($this->tracking_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_tracking.' . $key, $item);
                }
            }
            $data = $data->with($param)
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_tracking.id'), $this->tracking_id);
            });
            $data = $data->with($param)
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
            'tracking_id' => $this->tracking_id,
            'treatment_ids' => $this->treatment_ids,
            'create_time_to' => $this->create_time_to,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function tracking_get_data(Request $request)
    {
        // Khai báo các trường cần select
        $select_treatment = [
            'id',
            'create_time',
            'modify_time',
            'creator',
            'modifier',
            'app_creator',
            'app_modifier',
            'is_active',
            'is_delete',
            'icd_code',
            'icd_name',
            'icd_sub_code',
            'icd_text',
            'treatment_code',
            'patient_id',
            'branch_id',
            'is_pause',
            'is_lock_hein',
            'fee_lock_time',
            'fee_lock_order',
            'fee_lock_room_id',
            'fee_lock_department_id',
            'in_time',
            'in_date',
            'clinical_in_time',
            'out_time',
            'in_code',
            'in_room_id',
            'in_department_id',
            'in_loginname',
            'in_username',
            'in_treatment_type_id',
            'in_icd_code',
            'in_icd_name',
            'hospitalization_reason',
            'end_loginname',
            'end_username',
            'end_room_id',
            'end_department_id',
            'end_code',
            'treatment_day_count',
            'treatment_result_id',
            'treatment_end_type_id',
            'advise',
            'out_date',
            'store_time',
            'data_store_id',
            'store_code',
            'tdl_hein_card_number',
            'clinical_note',
            'subclinical_result',
            'treatment_method',
            'tdl_first_exam_room_id',
            'tdl_treatment_type_id',
            'tdl_patient_type_id',
            'tdl_hein_medi_org_code',
            'tdl_hein_medi_org_name',
            'tdl_patient_code',
            'tdl_patient_name',
            'tdl_patient_first_name',
            'tdl_patient_last_name',
            'tdl_patient_dob',
            'tdl_patient_is_has_not_day_dob',
            'tdl_patient_address',
            'tdl_patient_gender_id',
            'tdl_patient_gender_name',
            'tdl_patient_national_name',
            'tdl_patient_relative_name',
            'medi_record_type_id',
            'department_ids',
            'co_department_ids',
            'last_department_id',
            'medi_record_id',
            'is_sync_emr',
            'tdl_hein_card_from_time',
            'tdl_hein_card_to_time',
            'vir_in_month',
            'vir_out_month',
            'in_code_seed_code',
            'vir_in_year',
            'vir_out_year',
            'fee_lock_loginname',
            'fee_lock_username',
            'emr_cover_type_id',
            'hospitalize_department_id',
            'tdl_patient_national_code',
            'is_bhyt_holded',
            'tdl_patient_unsigned_name',
            'tdl_patient_ethnic_name',
            'IS_TUBERCULOSIS',
            'EXIT_DEPARTMENT_ID',
            'STORE_BORDEREAU_CODE',
            'HEIN_LOCK_TIME',
            'RECEPTION_FORM',
            'VIR_STORE_BORDEREAU_CODE',
            'STORE_BORDEREAU_TIME',
            'STORE_BORDEREAU_SEED_CODE'
        ];
        $select_service_req = [
            'ID',
            'CREATE_TIME',
            'MODIFY_TIME',
            'CREATOR',
            'MODIFIER',
            'APP_CREATOR',
            'APP_MODIFIER',
            'IS_ACTIVE',
            'IS_DELETE',
            'SERVICE_REQ_CODE',
            'SERVICE_REQ_TYPE_ID',
            'SERVICE_REQ_STT_ID',
            'TREATMENT_ID',
            'INTRUCTION_TIME',
            'INTRUCTION_DATE',
            'REQUEST_ROOM_ID',
            'REQUEST_DEPARTMENT_ID',
            'REQUEST_LOGINNAME',
            'REQUEST_USERNAME',
            'EXECUTE_ROOM_ID',
            'EXECUTE_DEPARTMENT_ID',
            'EXECUTE_LOGINNAME',
            'EXECUTE_USERNAME',
            'START_TIME',
            'FINISH_TIME',
            'ICD_CODE',
            'ICD_NAME',
            'NUM_ORDER',
            'PRIORITY',
            'TRACKING_ID',
            'TREATMENT_TYPE_ID',
            'SESSION_CODE',
            'TDL_TREATMENT_CODE',
            'TDL_HEIN_CARD_NUMBER',
            'TDL_PATIENT_ID',
            'TDL_PATIENT_CODE',
            'TDL_PATIENT_NAME',
            'TDL_PATIENT_FIRST_NAME',
            'TDL_PATIENT_LAST_NAME',
            'TDL_PATIENT_DOB',
            'TDL_PATIENT_IS_HAS_NOT_DAY_DOB',
            'TDL_PATIENT_ADDRESS',
            'TDL_PATIENT_GENDER_ID',
            'TDL_PATIENT_GENDER_NAME',
            'TDL_PATIENT_NATIONAL_NAME',
            'TDL_HEIN_MEDI_ORG_CODE',
            'TDL_HEIN_MEDI_ORG_NAME',
            'TDL_TREATMENT_TYPE_ID',
            'VIR_KIDNEY',
            'TDL_PATIENT_TYPE_ID',
            'IS_SEND_BARCODE_TO_LIS',
            'CONSULTANT_LOGINNAME',
            'CONSULTANT_USERNAME',
            'IS_NOT_IN_DEBT',
            'VIR_INTRUCTION_MONTH',
            'BARCODE_LENGTH',
            'TDL_SERVICE_IDS',
            'TDL_PATIENT_NATIONAL_CODE',
            'TDL_PATIENT_UNSIGNED_NAME',
            'VIR_CREATE_DATE',
        ];
        $select_exp_mest = [
            'ID',
            'CREATE_TIME',
            'MODIFY_TIME',
            'CREATOR',
            'MODIFIER',
            'APP_CREATOR',
            'APP_MODIFIER',
            'IS_ACTIVE',
            'IS_DELETE',
            'EXP_MEST_CODE',
            'EXP_MEST_TYPE_ID',
            'EXP_MEST_STT_ID',
            'MEDI_STOCK_ID',
            'REQ_LOGINNAME',
            'REQ_USERNAME',
            'REQ_ROOM_ID',
            'REQ_DEPARTMENT_ID',
            'CREATE_DATE',
            'LAST_EXP_LOGINNAME',
            'LAST_EXP_USERNAME',
            'LAST_EXP_TIME',
            'FINISH_TIME',
            'FINISH_DATE',
            'SERVICE_REQ_ID',
            'TDL_TOTAL_PRICE',
            'TDL_SERVICE_REQ_CODE',
            'TDL_INTRUCTION_TIME',
            'TDL_INTRUCTION_DATE',
            'TDL_TREATMENT_ID',
            'TDL_TREATMENT_CODE',
            'IS_EXPORT_EQUAL_APPROVE',
            'TDL_PATIENT_ID',
            'TDL_PATIENT_CODE',
            'TDL_PATIENT_NAME',
            'TDL_PATIENT_FIRST_NAME',
            'TDL_PATIENT_LAST_NAME',
            'TDL_PATIENT_DOB',
            'TDL_PATIENT_IS_HAS_NOT_DAY_DOB',
            'TDL_PATIENT_ADDRESS',
            'TDL_PATIENT_GENDER_ID',
            'TDL_PATIENT_GENDER_NAME',
            'TDL_PATIENT_TYPE_ID',
            'TDL_HEIN_CARD_NUMBER',
            'EXP_MEST_SUB_CODE',
            'LAST_APPROVAL_LOGINNAME',
            'LAST_APPROVAL_USERNAME',
            'LAST_APPROVAL_TIME',
            'LAST_APPROVAL_DATE',
            'TDL_PATIENT_NATIONAL_NAME',
            'VIR_CREATE_MONTH',
            'ICD_CODE',
            'ICD_NAME',
            'EXP_MEST_SUB_CODE_2',
            'VIR_CREATE_YEAR',
            'VIR_HEIN_CARD_PREFIX',
            'PRIORITY',
        ];
        $select_imp_mest = [];
        $select_exp_mest_medicine = [
            'ID',
            'CREATE_TIME',
            'MODIFY_TIME',
            'CREATOR',
            'MODIFIER',
            'APP_CREATOR',
            'APP_MODIFIER',
            'IS_ACTIVE',
            'IS_DELETE',
            'EXP_MEST_ID',
            'MEDICINE_ID',
            'TDL_MEDI_STOCK_ID',
            'TDL_MEDICINE_TYPE_ID',
            'IS_EXPORT',
            'AMOUNT',
            'PRICE',
            'VAT_RATIO',
            'NUM_ORDER',
            'APPROVAL_LOGINNAME',
            'APPROVAL_USERNAME',
            'APPROVAL_TIME',
            'APPROVAL_DATE',
            'EXP_LOGINNAME',
            'EXP_USERNAME',
            'EXP_TIME',
            'EXP_DATE',
            'PATIENT_TYPE_ID',
            'USE_TIME_TO',
            'TUTORIAL',
            'TDL_SERVICE_REQ_ID',
            'TDL_TREATMENT_ID',
            'VIR_PRICE',
            'MORNING',
            'EVENING',
        ];
        $select_exp_mest_material = [
            'ID',
            'CREATE_TIME',
            'MODIFY_TIME',
            'CREATOR',
            'MODIFIER',
            'APP_CREATOR',
            'APP_MODIFIER',
            'IS_ACTIVE',
            'IS_DELETE',
            'EXP_MEST_ID',
            'MATERIAL_ID',
            'TDL_MEDI_STOCK_ID',
            'TDL_MATERIAL_TYPE_ID',
            'TDL_AGGR_EXP_MEST_ID',
            'IS_EXPORT',
            'AMOUNT',
            'PRICE',
            'VAT_RATIO',
            'NUM_ORDER',
            'APPROVAL_LOGINNAME',
            'APPROVAL_USERNAME',
            'APPROVAL_TIME',
            'APPROVAL_DATE',
            'EXP_LOGINNAME',
            'EXP_USERNAME',
            'EXP_TIME',
            'EXP_DATE',
            'PATIENT_TYPE_ID',
            'TDL_SERVICE_REQ_ID',
            'TDL_TREATMENT_ID',
            'EQUIPMENT_SET_ORDER',
            'VIR_PRICE',
        ];
        $select_sere_serv = [
            "ID",
            "CREATE_TIME",
            "MODIFY_TIME",
            "CREATOR",
            "APP_CREATOR",
            "APP_MODIFIER",
            "IS_ACTIVE",
            "IS_DELETE",
            "SERVICE_ID",
            "SERVICE_REQ_ID",
            "PATIENT_TYPE_ID",
            "PRIMARY_PATIENT_TYPE_ID",
            "PRIMARY_PRICE",
            "LIMIT_PRICE",
            "HEIN_APPROVAL_ID",
            "JSON_PATIENT_TYPE_ALTER",
            "AMOUNT",
            "PRICE",
            "ORIGINAL_PRICE",
            "HEIN_PRICE",
            "HEIN_RATIO",
            "HEIN_LIMIT_PRICE",
            "VAT_RATIO",
            "HEIN_CARD_NUMBER",
            "TDL_INTRUCTION_TIME",
            "TDL_INTRUCTION_DATE",
            "TDL_PATIENT_ID",
            "TDL_TREATMENT_ID",
            "TDL_TREATMENT_CODE",
            "TDL_SERVICE_CODE",
            "TDL_SERVICE_NAME",
            "TDL_HEIN_SERVICE_BHYT_CODE",
            "TDL_HEIN_SERVICE_BHYT_NAME",
            "TDL_SERVICE_TYPE_ID",
            "TDL_SERVICE_UNIT_ID",
            "TDL_HEIN_SERVICE_TYPE_ID",
            "TDL_SERVICE_REQ_CODE",
            "TDL_REQUEST_ROOM_ID",
            "TDL_REQUEST_DEPARTMENT_ID",
            "TDL_REQUEST_LOGINNAME",
            "TDL_REQUEST_USERNAME",
            "TDL_EXECUTE_ROOM_ID",
            "TDL_EXECUTE_DEPARTMENT_ID",
            "TDL_EXECUTE_BRANCH_ID",
            "TDL_SERVICE_REQ_TYPE_ID",
            "TDL_HST_BHYT_CODE",
            "TDL_IS_MAIN_EXAM",
            "VIR_PRICE",
            "VIR_PRICE_NO_ADD_PRICE",
            "VIR_PRICE_NO_EXPEND",
            "VIR_HEIN_PRICE",
            "VIR_PATIENT_PRICE",
            "VIR_PATIENT_PRICE_BHYT",
            "VIR_TOTAL_PRICE",
            "VIR_TOTAL_PRICE_NO_ADD_PRICE",
            "VIR_TOTAL_PRICE_NO_EXPEND",
            "VIR_TOTAL_HEIN_PRICE",
            "VIR_TOTAL_PATIENT_PRICE",
            "VIR_TOTAL_PATIENT_PRICE_BHYT",
            "VIR_TOTAL_PATIENT_PRICE_NO_DC",
            "VIR_TOTAL_PATIENT_PRICE_TEMP",
        ];
        $select_sere_serv_ext = [
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
            "NUMBER_OF_FILM",
            "BEGIN_TIME",
            "END_TIME",
            "TDL_SERVICE_REQ_ID",
            "TDL_TREATMENT_ID",
            "SUBCLINICAL_RESULT_USERNAME",
            "SUBCLINICAL_RESULT_LOGINNAME",
            "DESCRIPTION"
        ];
        $select_dhst = [
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
            "WEIGHT",
            "HEIGHT",
            "BLOOD_PRESSURE_MAX",
            "BLOOD_PRESSURE_MIN",
            "PULSE",
            "VIR_BMI",
            "VIR_BODY_SURFACE_AREA",
        ];
        $select_care = [];

        // Khởi tạo, gán các model vào các biến 
        $tracking = $this->tracking;
        $treatment = $this->treatment::select($select_treatment);
        $service_req = $this->service_req::select($select_service_req);
        $exp_mest = $this->exp_mest::select($select_exp_mest);
        $imp_mest = $this->imp_mest::select();
        $exp_mest_medicine = $this->exp_mest_medicine::select($select_exp_mest_medicine);
        if ($this->include_material) {
            $exp_mest_material = $this->exp_mest_material::select($select_exp_mest_material);
        }
        $sere_serv = $this->sere_serv::select($select_sere_serv);
        $sere_serv_ext = $this->sere_serv_ext::select($select_sere_serv_ext);
        $dhst = $this->dhst::select($select_dhst);
        $care = $this->care::select();
        // Kiểm tra các điều kiện từ json param
        if (($this->treatment_id != null) || ($this->tracking_id != null)) {
            // Nếu có Tracking_id thì lấy Treatment_id từ Tracking_id
            if (($this->tracking_id != null)) {
                $this->treatment_id = $tracking::find($this->tracking_id)->treatment_id;
            }
            $treatment->find($this->treatment_id);
            $service_req->where('treatment_id', $this->treatment_id);
            $exp_mest->where('tdl_treatment_id', $this->treatment_id);
            $imp_mest->where('tdl_treatment_id', $this->treatment_id);
            $exp_mest_medicine->where('tdl_treatment_id', $this->treatment_id);
            if ($this->include_material) {
                $exp_mest_material->where('tdl_treatment_id', $this->treatment_id);
            }
            $sere_serv->where('tdl_treatment_id', $this->treatment_id);
            $sere_serv_ext->where('tdl_treatment_id', $this->treatment_id);
            $dhst->where('treatment_id', $this->treatment_id);
            $care->where('treatment_id', $this->treatment_id);
        }

        // Khai báo các bảng liên kết dùng cho with()
        $param_treatment = [
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
            'hein_approvals',
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
            'tuberculosis_treats',
        ];
        $param_service_req = [
            'bed_logs',
            'drug_interventions',
            'exam_sere_dires',
            'exp_mests',
            'ksk_drivers',
            'his_ksk_driver_cars',
            'ksk_generals',
            'ksk_occupationals',
            'ksk_others',
            'ksk_over_eighteens',
            'ksk_period_drivers',
            'ksk_under_eighteens',
            'sere_servs',
            'sere_serv_exts',
            'sere_serv_rations',
            'service_req_matys',
            'service_req_metys'
        ];
        $param_exp_mest = [
            'exp_blty_services',
            'exp_mest_bloods',
            'exp_mest_blty_reqs',
            'exp_mest_materials',
            'exp_mest_maty_reqs',
            'exp_mest_medicines',
            'exp_mest_mety_reqs',
            'exp_mest_users',
            'sere_serv_teins',
            'transaction_exps',
            'vitamin_as'
        ];
        $param_imp_mest = [];
        $param_exp_mest_medicine = [
            'bcs_mety_req_dts',
            'imp_mest_medi_reqs',
            'medicine_beans'
        ];
        $param_exp_mest_material = [
            'bcs_maty_req_dts',
            'imp_mest_mate_reqs',
            'material_beans'
        ];
        $param_sere_serv = [
            'exp_mest_bloods',
            'exp_mest_materials',
            'exp_mest_medicines',
            'sere_serv_bills',
            'sere_serv_debts',
            'sere_serv_deposits',
            'sere_serv_files',
            'sere_serv_matys',
            'sere_serv_pttts',
            'sere_serv_rehas',
            'sere_serv_suins',
            'sere_serv_teins',
            'service_change_reqs',
            'sese_depo_repays',
            'sese_trans_reqs'
        ];
        $param_sere_serv_ext = [];
        $param_dhst = [
            'antibiotic_request',
            'cares',
            'ksk_generals',
            'ksk_occupationals',
            'service_reqs'
        ];
        $param_care = [];
        // Lấy dữ liệu
        $data_treatment = $treatment->with($param_treatment)->first();
        $data_service_req = $service_req->with($param_service_req)->get();
        $data_exp_mest = $exp_mest->with($param_exp_mest)->get();
        $data_imp_mest = $imp_mest->with($param_imp_mest)->get();
        $data_exp_mest_medicine = $exp_mest_medicine->with($param_exp_mest_medicine)->get();
        if ($this->include_material) {
            $data_exp_mest_material = $exp_mest_material->with($param_exp_mest_material)->get();
            $data_imp_mest_material  = $this->treatment::find($this->treatment_id)->imp_mest_materials()->get();
        }
        if ($this->include_blood_pres) {
            $data_imp_mest_blood  = $this->treatment::find($this->treatment_id)->imp_mest_bloods()->get();
        }
        $data_imp_mest_medicine  = $this->treatment::find($this->treatment_id)->imp_mest_medicines()->get();
        $data_service_req_mety  = $this->treatment::find($this->treatment_id)->service_req_metys()->get();
        $data_service_req_maty  = $this->treatment::find($this->treatment_id)->service_req_matys()->get();
        $data_sere_serv_ration = $this->treatment::find($this->treatment_id)->sere_serv_rations()->get();
        $data_sere_serv = $sere_serv->with($param_sere_serv)->get();
        $data_sere_serv_ext = $sere_serv_ext->with($param_sere_serv_ext)->get();
        $data_exp_mest_blty_req = $this->treatment::find($this->treatment_id)->exp_mest_blty_reqs()->get();
        $data_dhst = $dhst->with($param_dhst)->get();
        $data_care = $care->with($param_care)->get();
        $data_care_detail  = $this->treatment::find($this->treatment_id)->care_details()->get();

        // Trả về dữ liệu
            $data['data'] = [
                'Treatment' => $data_treatment,
                'ServiceReqs' => $data_service_req,
                'ExpMests' => $data_exp_mest,
                'ImpMests' => $data_imp_mest,
                'ExpMestMedicines' => $data_exp_mest_medicine,
                'ExpMestMaterials' => $data_exp_mest_material ?? null,
                'ImpMestMedicines' => $data_imp_mest_medicine,
                'ImpMestMaterials' => $data_imp_mest_material ?? null,
                'ImpMestBloods' => $data_imp_mest_blood ?? null,
                'ServiceReqMetys' => $data_service_req_mety,
                'ServiceReqMatys' => $data_service_req_maty,
                'SereServRations' => $data_sere_serv_ration,
                'ExpMestBltyReqs' => $data_exp_mest_blty_req,
                'SereServs' => $data_sere_serv,
                'SereServExt' => $data_sere_serv_ext,
                'DHSTs' => $data_dhst,
                'Cares' => $data_care,
                'CareDetails' => $data_care_detail,
            ];

            $param_return = [
                'start' => $this->start,
                'limit' => $this->limit,
                'count' => null,
                'is_include_deleted' => $this->is_include_deleted ?? false,
                'is_active' => $this->is_active,
                'include_material' => $this->include_material,
                'include_blood_pres' => $this->include_blood_pres,
                'tracking_id' => $this->tracking_id,
                'treatment_id' => $this->treatment_id
            ];
            return return_data_success($param_return, $data);
    }
}
