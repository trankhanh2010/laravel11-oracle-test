<?php

namespace App\Http\Controllers\BaseControllers;

use App\Http\Controllers\Controller;
use App\Models\HIS\AccountBook;
use App\Models\HIS\BedRoom;
use App\Models\HIS\Branch;
use App\Models\HIS\CashierRoom;
use App\Models\HIS\Debate;
use App\Models\HIS\DebateEkipUser;
use App\Models\HIS\DebateUser;
use App\Models\HIS\Department;
use App\Models\HIS\Dhst;
use App\Models\HIS\ExecuteRoom;
use App\Models\HIS\PatientType;
use App\Models\HIS\PatientTypeAlter;
use App\Models\HIS\SereServ;
use App\Models\HIS\SereServBill;
use App\Models\HIS\SereServDeposit;
use App\Models\HIS\SereServExt;
use App\Models\HIS\SereServTein;
use App\Models\HIS\ServiceReq;
use App\Models\HIS\ServiceReqStt;
use App\Models\HIS\ServiceReqType;
use App\Models\HIS\ServiceType;
use App\Models\HIS\SeseDepoRepay;
use App\Models\HIS\TestIndex;
use App\Models\HIS\Tracking;
use App\Models\HIS\Treatment;
use App\Models\HIS\TreatmentBedRoom;
use App\Models\HIS\TreatmentType;
use Illuminate\Http\Request;

class BaseApiDataController extends Controller
{
    protected $errors = [];
    protected $data = [];
    protected $time;
    protected $columns_time;
    protected $time_id;
    protected $start;
    protected $start_name = 'Start';
    protected $limit;
    protected $limit_name = 'Limit';
    protected $cursor;
    protected $raw_cursor;
    protected $next_cursor;
    protected $prev_cursor;
    protected $sub_order_by = null;
    protected $sub_order_by_string ='';
    protected $order_by;
    protected $order_by_name = 'OrderBy';
    protected $order_by_tring;
    protected $order_by_request;
    protected $order_by_join;
    protected $only_active;
    protected $only_active_name = 'OnlyActive';


    // Các biến cho phân trang con trỏ
    protected $equal;
    protected $sql_id;
    protected $sere_serv_last_id;
    protected $sere_serv_first_id = 0;
    protected $debate_last_id;
    protected $debate_user_last_id;
    protected $dhst_last_id;
    protected $patient_type_alter_last_id;
    protected $service_req_last_id;
    protected $sere_serv_ext_last_id;
    protected $sere_serv_tein_last_id;
    protected $tracking_last_id;
    protected $treatment_bed_room_last_id;
    protected $treatment_last_id;
    protected $user_room_last_id;
    protected $sere_serv_bill_last_id;
    protected $sere_serv_deposit_last_id;
    protected $sese_depo_repay_last_id;

    // Tham số
    protected $service_type_ids;
    protected $service_type_ids_name = 'ServiceTypeIds';
    protected $patient_type_ids;
    protected $patient_type_ids_name = 'PatientTypeIds';
    protected $tdl_treatment_type_ids;
    protected $tdl_treatment_type_ids_name = 'TdlTreatmentTypeIds';
    protected $branch_id;
    protected $branch_id_name = 'BranchId';
    protected $in_date_from;
    protected $in_date_from_name = 'InDateFrom';
    protected $in_date_to;
    protected $in_date_to_name = 'InDateTo';
    protected $is_approve_store;
    protected $is_approve_store_name = 'IsApproveStore';
    protected $for_deposit;
    protected $for_deposit_name = 'ForDeposit';
    protected $loginname;
    protected $loginname_name = 'Loginname';
    protected $service_id;
    protected $service_id_name = 'ServiceId';
    protected $package_id;
    protected $package_id_name = 'PackageId';
    protected $department_id;
    protected $department_id_name = 'DepartmentId';
    protected $keyword;
    protected $keyword_name = 'Keyword';
    protected $per_page;
    protected $per_page_name = 'PerPage';
    protected $page;
    protected $page_name = 'Page';
    protected $param_request;
    protected $param_request_name = 'ParamRequest';
    protected $is_include_deleted;
    protected $is_include_deleted_name = 'IsIncludeDeleted';
    protected $is_active;
    protected $is_active_name = 'IsActive';
    protected $debate_id;
    protected $debate_id_name = 'DebateId';
    protected $treatment_id;
    protected $treatment_id_name = 'TreatmentId';
    protected $treatment_code;
    protected $treatment_code_name = 'TreatmentCode';
    protected $department_ids;
    protected $department_ids_name = 'DepartmentIds';
    protected $debate_user_id;
    protected $debate_user_id_name = 'DebateUserId';
    protected $debate_ekip_user_id;
    protected $debate_ekip_user_id_name = 'DebateEkipUserId';
    protected $dhst_id;
    protected $dhst_id_name = 'DhstId';
    protected $patient_type_alter_id;
    protected $patient_type_alter_id_name = 'PatientTypeAlterId';
    protected $log_time_to;
    protected $log_time_to_name = 'LogTimeTo';
    protected $sere_serv_id;
    protected $sere_serv_id_name = 'SereServId';
    protected $service_req_ids;
    protected $service_req_ids_name = 'ServiceReqIds';
    protected $service_type_id;
    protected $service_type_id_name = 'ServiceTypeId';
    protected $execute_room_id;
    protected $execute_room_id_name = 'ExecuteRoomId';
    protected $service_req_stt_ids;
    protected $service_req_stt_ids_name = 'ServiceReqSttIds';
    protected $not_in_service_req_type_ids;
    protected $not_in_service_req_type_ids_name = 'NotInServiceReqTypeIds';
    protected $tdl_patient_type_ids;
    protected $tdl_patient_type_ids_name = 'TdlPatientTypeIds';
    protected $intruction_time_from;
    protected $intruction_time_from_name = 'IntructionTimeFrom';
    protected $intruction_time_to;
    protected $intruction_time_to_name = 'IntructionTimeTo';
    protected $has_execute;
    protected $has_execute_name = 'HasExecute';
    protected $is_not_ksk_requried_aproval__or__is_ksk_approve;
    protected $is_not_ksk_requried_aproval__or__is_ksk_approve_name = 'IsNotKskRequriedAprovalOrIsKskApprove';
    protected $service_req_id;
    protected $service_req_id_name = 'ServiceReqId';
    protected $sere_serv_ext_id;
    protected $sere_serv_ext_id_name = 'SereServExtId';
    protected $sere_serv_ids;
    protected $sere_serv_ids_name = 'SereServIds';
    protected $sere_serv_tein_id;
    protected $sere_serv_tein_id_name = 'SereServTeinId';
    protected $test_index_ids;
    protected $test_index_ids_name = 'TestIndexIds';
    protected $tdl_treatment_id;
    protected $tdl_treatment_id_name = 'TdlTreatmentId';
    protected $treatment_ids;
    protected $treatment_ids_name = 'TreatmentIds';
    protected $create_time_to;
    protected $create_time_to_name = 'CreateTimeTo';
    protected $tracking_id;
    protected $tracking_id_name = 'TrackingId';
    protected $sere_serv_bill_id;
    protected $sere_serv_bill_id_name = 'SereServBillId';
    protected $sere_serv_deposit_id;
    protected $sere_serv_deposit_id_name = 'SereServDepositId';
    protected $sese_depo_repay_id;
    protected $sese_depo_repay_id_name = 'SeseDepoRepayId';
    protected $cashier_room_id;
    protected $cashier_room_id_name = 'CashierRoomId';
    protected $account_book_id;
    protected $account_book_id_name = 'AccountBookId';
    protected $include_material;
    protected $include_material_name = 'IncludeMaterial';
    protected $include_blood_pres;
    protected $include_blood_pres_name = 'IncludeBloodPres';
    protected $patient_code__exact;
    protected $patient_code__exact_name = 'PatientCodeExact';
    protected $is_in_room;
    protected $is_in_room_name = 'IsInRoom';
    protected $add_time_to;
    protected $add_time_to_name = 'AddTimeTo';
    protected $add_time_from;
    protected $add_time_from_name = 'AddTimeFrom';
    protected $bed_room_ids;
    protected $bed_room_ids_name = 'BedRoomIds';
    protected $treatment_bed_room_id;
    protected $treatment_bed_room_id_name = 'TreatmentBedRoomId';
    protected $is_out_of_bill;
    protected $is_out_of_bill_name = 'IsOutOfBill';

    // Khai báo các biến mặc định model
    protected $app_creator = "MOS_v2";
    protected $app_modifier = "MOS_v2";
    // Khai báo các biến model
    protected $debate;
    protected $debate_name = 'debate';
    protected $debate_user;
    protected $debate_user_name = 'debate_user';
    protected $debate_ekip_user;
    protected $debate_ekip_user_name = 'debate_ekip_user';
    protected $dhst;
    protected $dhst_name = 'dhst';
    protected $patient_type_alter;
    protected $patient_type_alter_name = 'patient_type_alter';
    protected $sere_serv;
    protected $sere_serv_name = 'sere_serv';
    protected $service_req;
    protected $service_req_name = 'service_req';
    protected $sere_serv_ext;
    protected $sere_serv_ext_name = 'sere_serv_ext';
    protected $sere_serv_tein;
    protected $sere_serv_tein_name = 'sere_serv_tein';
    protected $tracking;
    protected $tracking_name = 'tracking';
    protected $treatment;
    protected $treatment_name = 'treatment';
    protected $exp_mest;
    protected $exp_mest_name = 'exp_mest';
    protected $imp_mest;
    protected $imp_mest_name = 'imp_mest';
    protected $exp_mest_medicine;
    protected $exp_mest_medicine_name = 'exp_mest_medicine';
    protected $care;
    protected $care_name = 'care';
    protected $exp_mest_material;
    protected $exp_mest_material_name = 'exp_mest_material';
    protected $treatment_bed_room;
    protected $treatment_bed_room_name = 'treatment_bed_room';
    protected $bhyt_whiteList;
    protected $bhyt_whiteList_name = 'bhyt_whiteList';
    protected $antibiotic_request;
    protected $antibiotic_request_name = 'antibiotic_request';
    protected $user_room;
    protected $user_room_name = 'user_room';
    protected $sere_serv_bill;
    protected $sere_serv_bill_name = 'sere_serv_bill';
    protected $sere_serv_deposit;
    protected $sere_serv_deposit_name = 'sere_serv_deposit';
    protected $sese_depo_repay;
    protected $sese_depo_repay_name = 'sese_depo_repay';
    protected $account_book;
    protected $account_book_name = 'account_book';

    // Thông báo lỗi
    protected $mess_format;
    protected $mess_order_by_name;
    protected $mess_record_id;
    protected $mess_decode_param;

    // Function kiểm tra lỗi và lấy thông báo lỗi
    protected function has_errors()
    {
        return !empty($this->errors);
    }

    protected function get_errors()
    {
        return $this->errors;
    }

    protected function check_param(){
        if ($this->has_errors()) {
            return return_400($this->get_errors());
        }
        return null;

    }
    public function __construct(Request $request)
    {
        // Khai báo các biến
        // Thời gian tồn tại của cache
        $this->time = now()->addMinutes(10080);
        $this->time_id = now()->addMinutes(60);
        $this->columns_time = now()->addMinutes(20000);


        // Thông báo lỗi 
        $this->mess_format = config('keywords')['error']['format'];
        $this->mess_order_by_name = config('keywords')['error']['order_by_name'];
        $this->mess_record_id = config('keywords')['error']['record_id'];
        $this->mess_decode_param = config('keywords')['error']['decode_param'];


        // Param json gửi từ client
        if($request->input('param') !== null){
            $this->param_request = json_decode(base64_decode($request->input('param')), true) ?? null;
            if($this->param_request === null){
                $this->errors['param'] = $this->mess_decode_param;
            }
        }

        // Gán và kiểm tra các tham số được gửi lên
        $this->per_page = $request->query('perPage', 10);
        $this->page = $request->query('page', 1);
        $this->start = $this->param_request['CommonParam']['Start'] ?? intval($request->start) ?? 0;
        $this->limit = $this->param_request['CommonParam']['Limit'] ?? intval($request->limit) ?? 10;
        if($this->limit <= 0){
            $this->limit = 10;
        }
        $this->cursor = $this->param_request['CommonParam']['Cursor'] ?? intval($request->cursor) ?? 0;
        $this->raw_cursor = $this->param_request['CommonParam']['Cursor'] ?? intval($request->cursor) ?? 0;
        if (($this->limit < 10) || (!in_array($this->limit, [10, 20, 50, 100, 500, 1000, 2000, 4000]))) {
            $this->errors[$this->limit_name] = $this->mess_format;
            $this->limit = 10;
        }
        if ($this->start != null) {
            if ((!is_numeric($this->start)) || (!is_int($this->start)) || ($this->start < 0)) {
                $this->errors[$this->start_name] = $this->mess_format;
                $this->start = 0;
            }
        }
        // if (($this->limit != null) || ($this->start != null)) {
        //     if ((!is_numeric($this->limit)) || (!is_int($this->limit)) || ($this->limit > 4000) || ($this->limit <= 0)) {
        //         $this->errors[$this->limit_name] = $this->mess_format;
        //         $this->limit = 100;
        //     }
        // }
        $this->keyword = $this->param_request['ApiData']['KeyWord'] ?? $request->keyword ?? "";
        if($this->keyword !== null){
            if (!is_string ($this->keyword)) {
                $this->errors[$this->keyword_name] = $this->mess_format;
                $this->keyword = null;
            }
        }

        $this->order_by = $this->param_request['ApiData']['OrderBy'] ?? null;
        $this->order_by_request = $this->param_request['ApiData']['OrderBy'] ?? null;
        if ($this->order_by != null) {
            $this->order_by = convertArrayKeysToSnakeCase($this->order_by);
            foreach($this->order_by as $key => $item){
                if(!in_array($item, ['asc', 'desc'])){
                    $this->errors[$this->order_by_name] = $this->mess_format;
                }
            }
        }

        $this->is_active = $this->param_request['ApiData']['IsActive'] ?? null;
        if($this->is_active !== null){
            if (!in_array ($this->is_active, [0,1])) {
                $this->errors[$this->is_active_name] = $this->mess_format;
                $this->is_active = 1;
            }
        }

        $this->only_active = $this->param_request['ApiData']['OnlyActive'] ?? false;
        if (!is_bool ($this->only_active)) {
            $this->errors[$this->only_active_name] = $this->mess_format;
            $this->only_active = false;
        }

        $this->is_include_deleted = $this->param_request['ApiData']['IsIncludeDeleted'] ?? false;
        if (!is_bool ($this->is_include_deleted)) {
            $this->errors[$this->is_include_deleted_name] = $this->mess_format;
            $this->is_include_deleted = false;
        }

        $this->include_material = $this->param_request['ApiData']['IncludeMaterial'] ?? true;
        if (!is_bool ($this->include_material)) {
            $this->errors[$this->include_material_name] = $this->mess_format;
            $this->include_material = true;
        }

        $this->include_blood_pres = $this->param_request['ApiData']['IncludeBloodPres'] ?? true;
        if (!is_bool ($this->include_blood_pres)) {
            $this->errors[$this->include_blood_pres_name] = $this->mess_format;
            $this->include_blood_pres = true;
        }

        $this->has_execute = $this->param_request['ApiData']['HasExecute'] ?? true;
        if (!is_bool ($this->has_execute)) {
            $this->errors[$this->has_execute_name] = $this->mess_format;
            $this->has_execute = true;
        }

        $this->is_not_ksk_requried_aproval__or__is_ksk_approve = $this->param_request['ApiData']['IsNotKskRequriedAproval_Or_IsKskApprove'] ?? true;
        if (!is_bool ($this->is_not_ksk_requried_aproval__or__is_ksk_approve)) {
            $this->errors[$this->is_not_ksk_requried_aproval__or__is_ksk_approve_name] = $this->mess_format;
            $this->is_not_ksk_requried_aproval__or__is_ksk_approve = true;
        }

        $this->debate_id = $this->param_request['ApiData']['DebateId'] ?? null;
        if ($this->debate_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->debate_id)) {
                $this->errors[$this->debate_id_name] = $this->mess_format;
                $this->debate_id = null;
            } else {
                if (!Debate::where('id', $this->debate_id)->exists()) {
                    $this->errors[$this->debate_id_name] = $this->mess_record_id;
                    $this->debate_id = null;
                }
            }
        }

        $this->treatment_id = $this->param_request['ApiData']['TreatmentId'] ?? null;
        if ($this->treatment_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->treatment_id)) {
                $this->errors[$this->treatment_id_name] = $this->mess_format;
                $this->treatment_id = null;
            } else {
                if (!Treatment::where('id', $this->treatment_id)->exists()) {
                    $this->errors[$this->treatment_id_name] = $this->mess_record_id;
                    $this->treatment_id = null;
                }
            }
        }

        $this->treatment_code = $this->param_request['ApiData']['TreatmentCode'] ?? null;
        if($this->treatment_code !== null){
            if (!is_string ($this->treatment_code)) {
                $this->errors[$this->treatment_code_name] = $this->mess_format;
                $this->treatment_code = null;
            }
        }

        $this->department_ids = $this->param_request['ApiData']['DepartmentIds'] ?? null;
        if ($this->department_ids != null) {
            foreach ($this->department_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->department_ids_name] = $this->mess_format;
                    unset($this->department_ids[$key]);
                } else {
                    if (!Department::where('id', $item)->exists()) {
                        $this->errors[$this->department_ids_name] = $this->mess_record_id;
                        unset($this->department_ids[$key]);
                    }
                }
            }
        }

        $this->debate_user_id = $this->param_request['ApiData']['DebateUserId'] ?? null;
        if ($this->debate_user_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->debate_user_id)) {
                $this->errors[$this->debate_user_id_name] = $this->mess_format;
                $this->debate_user_id = null;
            } else {
                if (!DebateUser::where('id', $this->debate_user_id)->exists()) {
                    $this->errors[$this->debate_user_id_name] = $this->mess_record_id;
                    $this->debate_user_id = null;
                }
            }
        }

        $this->debate_ekip_user_id = $this->param_request['ApiData']['DebateEkipUserId'] ?? null;
        if ($this->debate_ekip_user_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->debate_ekip_user_id)) {
                $this->errors[$this->debate_ekip_user_id_name] = $this->mess_format;
                $this->debate_ekip_user_id = null;
            } else {
                if (!DebateEkipUser::where('id', $this->debate_ekip_user_id)->exists()) {
                    $this->errors[$this->debate_ekip_user_id_name] = $this->mess_record_id;
                    $this->debate_ekip_user_id = null;
                }
            }
        }

        $this->dhst_id = $this->param_request['ApiData']['DhstId'] ?? null;
        if ($this->dhst_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->dhst_id)) {
                $this->errors[$this->dhst_id_name] = $this->mess_format;
                $this->dhst_id = null;
            } else {
                if (!Dhst::where('id', $this->dhst_id)->exists()) {
                    $this->errors[$this->dhst_id_name] = $this->mess_record_id;
                    $this->dhst_id = null;
                }
            }
        }

        $this->patient_type_alter_id = $this->param_request['ApiData']['PatientTypeAlterId'] ?? null;
        if ($this->patient_type_alter_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->patient_type_alter_id)) {
                $this->errors[$this->patient_type_alter_id_name] = $this->mess_format;
                $this->patient_type_alter_id = null;
            } else {
                if (!PatientTypeAlter::where('id', $this->patient_type_alter_id)->exists()) {
                    $this->errors[$this->patient_type_alter_id_name] = $this->mess_record_id;
                    $this->patient_type_alter_id = null;
                }
            }
        }

        $this->log_time_to = $this->param_request['ApiData']['LogTimeTo'] ?? null;
        if($this->log_time_to != null){
            if(!preg_match('/^\d{14}$/',  $this->log_time_to)){
                $this->errors[$this->log_time_to_name] = $this->mess_format;
                $this->log_time_to = null;
            }
        }

        $this->sere_serv_id = $this->param_request['ApiData']['SereServId'] ?? null;
        if ($this->sere_serv_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->sere_serv_id)) {
                $this->errors[$this->sere_serv_id_name] = $this->mess_format;
                $this->sere_serv_id = null;
            } else {
                if (!SereServ::where('id', $this->sere_serv_id)->exists()) {
                    $this->errors[$this->sere_serv_id_name] = $this->mess_record_id;
                    $this->sere_serv_id = null;
                }
            }
        }

        $this->service_req_ids = $this->param_request['ApiData']['ServiceReqIds'] ?? null;
        if ($this->service_req_ids != null) {
            foreach ($this->service_req_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->service_req_ids_name] = $this->mess_format;
                    unset($this->service_req_ids[$key]);
                } else {
                    if (!ServiceReq::where('id', $item)->exists()) {
                        $this->errors[$this->service_req_ids_name] = $this->mess_record_id;
                        unset($this->service_req_ids[$key]);
                    }
                }
            }
        }

        $this->service_type_id = $this->param_request['ApiData']['ServiceTypeId'] ?? null;
        if ($this->service_type_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->service_type_id)) {
                $this->errors[$this->service_type_id_name] = $this->mess_format;
                $this->service_type_id = null;
            } else {
                if (!ServiceType::where('id', $this->service_type_id)->exists()) {
                    $this->errors[$this->service_type_id_name] = $this->mess_record_id;
                    $this->service_type_id = null;
                }
            }
        }

        $this->execute_room_id = $this->param_request['ApiData']['ExecuteRoomId'] ?? null;
        if ($this->execute_room_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->execute_room_id)) {
                $this->errors[$this->execute_room_id_name] = $this->mess_format;
                $this->execute_room_id = null;
            } else {
                if (!ExecuteRoom::where('id', $this->execute_room_id)->exists()) {
                    $this->errors[$this->execute_room_id_name] = $this->mess_record_id;
                    $this->execute_room_id = null;
                }
            }
        }

        $this->service_req_stt_ids = $this->param_request['ApiData']['ServiceReqSttIds'] ?? null;
        if ($this->service_req_stt_ids != null) {
            foreach ($this->service_req_stt_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->service_req_stt_ids_name] = $this->mess_format;
                    unset($this->service_req_stt_ids[$key]);
                } else {
                    if (!ServiceReqStt::where('id', $item)->exists()) {
                        $this->errors[$this->service_req_stt_ids_name] = $this->mess_record_id;
                        unset($this->service_req_stt_ids[$key]);
                    }
                }
            }
        }

        $this->not_in_service_req_type_ids = $this->param_request['ApiData']['NotInServiceReqTypeIds'] ?? null;
        if ($this->not_in_service_req_type_ids != null) {
            foreach ($this->not_in_service_req_type_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->not_in_service_req_type_ids_name] = $this->mess_format;
                    unset($this->not_in_service_req_type_ids[$key]);
                } else {
                    if (!ServiceReqType::where('id', $item)->exists()) {
                        $this->errors[$this->not_in_service_req_type_ids_name] = $this->mess_record_id;
                        unset($this->not_in_service_req_type_ids[$key]);
                    }
                }
            }
        }

        $this->tdl_patient_type_ids = $this->param_request['ApiData']['TdlPatientTypeIds'] ?? null;
        if ($this->tdl_patient_type_ids != null) {
            foreach ($this->tdl_patient_type_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->tdl_patient_type_ids_name] = $this->mess_format;
                    unset($this->tdl_patient_type_ids[$key]);
                } else {
                    if (!PatientType::where('id', $item)->exists()) {
                        $this->errors[$this->tdl_patient_type_ids_name] = $this->mess_record_id;
                        unset($this->tdl_patient_type_ids[$key]);
                    }
                }
            }
        }

        $this->intruction_time_from = $this->param_request['ApiData']['IntructionTimeFrom'] ?? null;
        if($this->intruction_time_from != null){
            if(!preg_match('/^\d{14}$/',  $this->intruction_time_from)){
                $this->errors[$this->intruction_time_from_name] = $this->mess_format;
                $this->intruction_time_from = null;
            }
        }

        $this->intruction_time_to = $this->param_request['ApiData']['IntructionTimeTo'] ?? null;
        if($this->intruction_time_to != null){
            if(!preg_match('/^\d{14}$/',  $this->intruction_time_to)){
                $this->errors[$this->intruction_time_to_name] = $this->mess_format;
                $this->intruction_time_to = null;
            }
        }

        $this->service_req_id = $this->param_request['ApiData']['ServiceReqId'] ?? null;
        if ($this->service_req_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->service_req_id)) {
                $this->errors[$this->service_req_id_name] = $this->mess_format;
                $this->service_req_id = null;
            } else {
                if (!ServiceReq::where('id', $this->service_req_id)->exists()) {
                    $this->errors[$this->service_req_id_name] = $this->mess_record_id;
                    $this->service_req_id = null;
                }
            }
        }

        $this->sere_serv_ext_id = $this->param_request['ApiData']['SereServExtId'] ?? null;
        if ($this->sere_serv_ext_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->sere_serv_ext_id)) {
                $this->errors[$this->sere_serv_ext_id_name] = $this->mess_format;
                $this->sere_serv_ext_id = null;
            } else {
                if (!SereServExt::where('id', $this->sere_serv_ext_id)->exists()) {
                    $this->errors[$this->sere_serv_ext_id_name] = $this->mess_record_id;
                    $this->sere_serv_ext_id = null;
                }
            }
        }

        $this->sere_serv_ids = $this->param_request['ApiData']['SereServIds'] ?? null;
        if ($this->sere_serv_ids != null) {
            foreach ($this->sere_serv_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->sere_serv_ids_name] = $this->mess_format;
                    unset($this->sere_serv_ids[$key]);
                } else {
                    if (!SereServ::where('id', $item)->exists()) {
                        $this->errors[$this->sere_serv_ids_name] = $this->mess_record_id;
                        unset($this->sere_serv_ids[$key]);
                    }
                }
            }
        }

        $this->sere_serv_tein_id = $this->param_request['ApiData']['SereServTeinId'] ?? null;
        if ($this->sere_serv_tein_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->sere_serv_tein_id)) {
                $this->errors[$this->sere_serv_tein_id_name] = $this->mess_format;
                $this->sere_serv_tein_id = null;
            } else {
                if (!SereServTein::where('id', $this->sere_serv_tein_id)->exists()) {
                    $this->errors[$this->sere_serv_tein_id_name] = $this->mess_record_id;
                    $this->sere_serv_tein_id = null;
                }
            }
        }

        $this->test_index_ids = $this->param_request['ApiData']['TestIndexIds'] ?? null;
        if ($this->test_index_ids != null) {
            foreach ($this->test_index_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->test_index_ids_name] = $this->mess_format;
                    unset($this->test_index_ids[$key]);
                } else {
                    if (!TestIndex::where('id', $item)->exists()) {
                        $this->errors[$this->test_index_ids_name] = $this->mess_record_id;
                        unset($this->test_index_ids[$key]);
                    }
                }
            }
        }

        $this->tdl_treatment_id = $this->param_request['ApiData']['TdlTreatmentId'] ?? null;
        if ($this->tdl_treatment_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->tdl_treatment_id)) {
                $this->errors[$this->tdl_treatment_id_name] = $this->mess_format;
                $this->tdl_treatment_id = null;
            } else {
                if (!Treatment::where('id', $this->tdl_treatment_id)->exists()) {
                    $this->errors[$this->tdl_treatment_id_name] = $this->mess_record_id;
                    $this->tdl_treatment_id = null;
                }
            }
        }

        $this->treatment_ids = $this->param_request['ApiData']['TreatmentIds'] ?? null;
        if ($this->treatment_ids != null) {
            foreach ($this->treatment_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->treatment_ids_name] = $this->mess_format;
                    unset($this->treatment_ids[$key]);
                } else {
                    if (!Treatment::where('id', $item)->exists()) {
                        $this->errors[$this->treatment_ids_name] = $this->mess_record_id;
                        unset($this->treatment_ids[$key]);
                    }
                }
            }
        }

        $this->create_time_to = $this->param_request['ApiData']['CreateTimeTo'] ?? null;
        if($this->create_time_to != null){
            if(!preg_match('/^\d{14}$/',  $this->create_time_to)){
                $this->errors[$this->create_time_to_name] = $this->mess_format;
                $this->create_time_to = null;
            }
        }

        $this->tracking_id = $this->param_request['ApiData']['TrackingId'] ?? null;
        if ($this->tracking_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->tracking_id)) {
                $this->errors[$this->tracking_id_name] = $this->mess_format;
                $this->tracking_id = null;
            } else {
                if (!Tracking::where('id', $this->tracking_id)->exists()) {
                    $this->errors[$this->tracking_id_name] = $this->mess_record_id;
                    $this->tracking_id = null;
                }
            }
        }

        $this->patient_code__exact = $this->param_request['ApiData']['PatientCode_Exact'] ?? null;
        if($this->patient_code__exact != null){
            if(!preg_match('/^.{0,10}$/',  $this->patient_code__exact)){
                $this->errors[$this->patient_code__exact_name] = $this->mess_format;
                $this->patient_code__exact = null;
            }
        }

        $this->is_in_room = $this->param_request['ApiData']['IsInRoom'] ?? false;
        if (!is_bool ($this->is_in_room)) {
            $this->errors[$this->is_in_room_name] = $this->mess_format;
            $this->is_in_room = false;
        }

        $this->add_time_to = $this->param_request['ApiData']['AddTimeTo'] ?? null;
        if($this->add_time_to != null){
            if(!preg_match('/^\d{14}$/',  $this->add_time_to)){
                $this->errors[$this->add_time_to_name] = $this->mess_format;
                $this->add_time_to = null;
            }
        }

        $this->add_time_from = $this->param_request['ApiData']['AddTimeFrom'] ?? null;
        if($this->add_time_from != null){
            if(!preg_match('/^\d{14}$/',  $this->add_time_from)){
                $this->errors[$this->add_time_from_name] = $this->mess_format;
                $this->add_time_from = null;
            }
            if($this->is_in_room){
                $this->add_time_from = null;
            }
        }

        $this->bed_room_ids = $this->param_request['ApiData']['BedRoomIds'] ?? null;
        if ($this->bed_room_ids != null) {
            foreach ($this->bed_room_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->bed_room_ids_name] = $this->mess_format;
                    unset($this->bed_room_ids[$key]);
                } else {
                    if (!BedRoom::where('id', $item)->exists()) {
                        $this->errors[$this->bed_room_ids_name] = $this->mess_record_id;
                        unset($this->bed_room_ids[$key]);
                    }
                }
            }
        }

        $this->treatment_bed_room_id = $this->param_request['ApiData']['TreatmentBedRoomId'] ?? null;
        if ($this->treatment_bed_room_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->treatment_bed_room_id)) {
                $this->errors[$this->treatment_bed_room_id_name] = $this->mess_format;
                $this->treatment_bed_room_id = null;
            } else {
                if (!TreatmentBedRoom::where('id', $this->treatment_bed_room_id)->exists()) {
                    $this->errors[$this->treatment_bed_room_id_name] = $this->mess_record_id;
                    $this->treatment_bed_room_id = null;
                }
            }
        }

        $this->tdl_treatment_type_ids = $this->param_request['ApiData']['TdlTreatmentTypeIds'] ?? null;
        if ($this->tdl_treatment_type_ids != null) {
            foreach ($this->tdl_treatment_type_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->tdl_treatment_type_ids_name] = $this->mess_format;
                    unset($this->tdl_treatment_type_ids_name);
                } else {
                    if (!TreatmentType::where('id', $item)->exists()) {
                        $this->errors[$this->tdl_treatment_type_ids_name] = $this->mess_record_id;
                        unset($this->tdl_treatment_type_ids_name);
                    }
                }
            }
        }
        $this->branch_id = $this->param_request['ApiData']['BranchId'] ?? null;
        if ($this->branch_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->branch_id)) {
                $this->errors[$this->branch_id_name] = $this->mess_format;
                $this->branch_id = null;
            } else {
                if (!Branch::where('id', $this->branch_id)->exists()) {
                    $this->errors[$this->branch_id_name] = $this->mess_record_id;
                    $this->branch_id = null;
                }
            }
        }

        $this->in_date_from = $this->param_request['ApiData']['InDateFrom'] ?? null;
        if($this->in_date_from != null){
            if(!preg_match('/^\d{14}$/',  $this->in_date_from)){
                $this->errors[$this->in_date_from_name] = $this->mess_format;
                $this->in_date_from = null;
            }
        }

        $this->in_date_to = $this->param_request['ApiData']['InDateTo'] ?? null;
        if($this->in_date_to != null){
            if(!preg_match('/^\d{14}$/',  $this->in_date_to)){
                $this->errors[$this->in_date_to_name] = $this->mess_format;
                $this->in_date_to = null;
            }
        }

        $this->is_approve_store = $this->param_request['ApiData']['IsApproveStore'] ?? null;
        if($this->is_approve_store !== null){
            if (!is_bool ($this->is_approve_store)) {
                $this->errors[$this->is_approve_store_name] = $this->mess_format;
                $this->is_approve_store = null;
            }
        }

        $this->sere_serv_bill_id = $this->param_request['ApiData']['SereServBillId'] ?? null;
        if ($this->sere_serv_bill_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->sere_serv_bill_id)) {
                $this->errors[$this->sere_serv_bill_id_name] = $this->mess_format;
                $this->sere_serv_bill_id = null;
            } else {
                if (!SereServBill::where('id', $this->sere_serv_bill_id)->exists()) {
                    $this->errors[$this->sere_serv_bill_id_name] = $this->mess_record_id;
                    $this->sere_serv_bill_id = null;
                }
            }
        }

        $this->sere_serv_deposit_id = $this->param_request['ApiData']['SereServDepositId'] ?? null;
        if ($this->sere_serv_deposit_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->sere_serv_deposit_id)) {
                $this->errors[$this->sere_serv_deposit_id_name] = $this->mess_format;
                $this->sere_serv_deposit_id = null;
            } else {
                if (!SereServDeposit::where('id', $this->sere_serv_deposit_id)->exists()) {
                    $this->errors[$this->sere_serv_deposit_id_name] = $this->mess_record_id;
                    $this->sere_serv_deposit_id = null;
                }
            }
        }

        $this->sese_depo_repay_id = $this->param_request['ApiData']['SeseDepoRepayId'] ?? null;
        if ($this->sese_depo_repay_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->sese_depo_repay_id)) {
                $this->errors[$this->sese_depo_repay_id_name] = $this->mess_format;
                $this->sese_depo_repay_id = null;
            } else {
                if (!SeseDepoRepay::where('id', $this->sese_depo_repay_id)->exists()) {
                    $this->errors[$this->sese_depo_repay_id_name] = $this->mess_record_id;
                    $this->sese_depo_repay_id = null;
                }
            }
        }

        $this->is_out_of_bill = $this->param_request['ApiData']['IsOutOfBill'] ?? null;
        if($this->is_out_of_bill !== null){
            if (!is_bool ($this->is_out_of_bill)) {
                $this->errors[$this->is_out_of_bill_name] = $this->mess_format;
                $this->is_out_of_bill = null;
            }
        }

        $this->for_deposit = $this->param_request['ApiData']['ForDeposit'] ?? null;
        if($this->for_deposit !== null){
            if (!is_bool ($this->for_deposit)) {
                $this->errors[$this->for_deposit_name] = $this->mess_format;
                $this->for_deposit = null;
            }
        }

        $this->loginname = $this->param_request['ApiData']['Loginname'] ?? null;
        if($this->loginname !== null){
            if (!is_string ($this->loginname)) {
                $this->errors[$this->loginname_name] = $this->mess_format;
                $this->loginname = null;
            }
        }

        $this->cashier_room_id = $this->param_request['ApiData']['CashierRoomId'] ?? null;
        if ($this->cashier_room_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->cashier_room_id)) {
                $this->errors[$this->cashier_room_id_name] = $this->mess_format;
                $this->cashier_room_id = null;
            } else {
                if (!CashierRoom::where('id', $this->cashier_room_id)->exists()) {
                    $this->errors[$this->cashier_room_id_name] = $this->mess_record_id;
                    $this->cashier_room_id = null;
                }
            }
        }
        $this->account_book_id = $this->param_request['ApiData']['AccountBookId'] ?? null;
        if ($this->account_book_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->account_book_id)) {
                $this->errors[$this->account_book_id_name] = $this->mess_format;
                $this->account_book_id = null;
            } else {
                if (!AccountBook::where('id', $this->account_book_id)->exists()) {
                    $this->errors[$this->account_book_id_name] = $this->mess_record_id;
                    $this->account_book_id = null;
                }
            }
        }
    }
}
