<?php

namespace App\Http\Controllers\BaseControllers;

use App\Http\Controllers\Controller;
use App\Models\HIS\BedRoom;
use App\Models\HIS\Debate;
use App\Models\HIS\DebateEkipUser;
use App\Models\HIS\DebateUser;
use App\Models\HIS\Department;
use App\Models\HIS\Dhst;
use App\Models\HIS\ExecuteRoom;
use App\Models\HIS\PatientType;
use App\Models\HIS\PatientTypeAlter;
use App\Models\HIS\SereServ;
use App\Models\HIS\SereServExt;
use App\Models\HIS\SereServTein;
use App\Models\HIS\ServiceReq;
use App\Models\HIS\ServiceReqStt;
use App\Models\HIS\ServiceReqType;
use App\Models\HIS\ServiceType;
use App\Models\HIS\TestIndex;
use App\Models\HIS\Tracking;
use App\Models\HIS\Treatment;
use App\Models\HIS\TreatmentBedRoom;
use Illuminate\Http\Request;

class BaseApiDataController extends Controller
{
    protected $data = [];
    protected $time;
    protected $start;
    protected $limit;
    protected $order_by;
    protected $order_by_tring;
    protected $order_by_request;
    protected $order_by_join;
    protected $only_active;
    protected $service_type_ids;
    protected $patient_type_ids;
    protected $service_id;
    protected $package_id;
    protected $department_id;
    protected $keyword;
    protected $per_page;
    protected $page;
    protected $param_request;
    protected $is_include_deleted;
    protected $is_active;
    protected $debate_id;
    protected $treatment_id;
    protected $treatment_code;
    protected $department_ids;
    protected $debate_user_id;
    protected $debate_ekip_user_id;
    protected $dhst_id;
    protected $patient_type_alter_id;
    protected $log_time_to;
    protected $sere_serv_id;
    protected $service_req_ids;
    protected $service_type_id;
    protected $execute_room_id;
    protected $service_req_stt_ids;
    protected $not_in_service_req_type_ids;
    protected $tdl_patient_type_ids;
    protected $intruction_time_from;
    protected $intruction_time_to;
    protected $has_execute;
    protected $is_not_ksk_requried_aproval__or__is_ksk_approve;
    protected $service_req_id;
    protected $sere_serv_ext_id;
    protected $sere_serv_ids;
    protected $sere_serv_tein_id;
    protected $test_index_ids;
    protected $tdl_treatment_id;
    protected $treatment_ids;
    protected $create_time_to;
    protected $tracking_id;
    protected $include_material;
    protected $include_blood_pres;
    protected $patient_code__exact;
    protected $is_in_room;
    protected $add_time_to;
    protected $add_time_from;
    protected $bed_room_ids;
    protected $treatment_bed_room_id;

    // Khai báo các biến mặc định model
    protected $app_creator = "MOS_v2";
    protected $app_modifier = "MOS_v2";
    // Khai báo các biến model
    protected $debate;
    protected $debate_user;
    protected $debate_ekip_user;
    protected $dhst;
    protected $patient_type_alter;
    protected $sere_serv;
    protected $service_req;
    protected $sere_serv_ext;
    protected $sere_serv_tein;
    protected $tracking;
    protected $treatment;
    protected $exp_mest;
    protected $imp_mest;
    protected $exp_mest_medicine;
    protected $care;
    protected $exp_mest_material;
    protected $treatment_bed_room;
    protected $bhyt_whiteList;
    protected $antibiotic_request;

    public function __construct(Request $request)
    {
        // Khai báo các biến
        // Thời gian tồn tại của cache
        $this->time = now()->addMinutes(10080);
        // Param json gửi từ client
        $this->param_request = json_decode(base64_decode($request->input('param')), true) ?? null;

        $this->per_page = $request->query('perPage', 10);
        $this->page = $request->query('page', 1);
        $this->start = $this->param_request['CommonParam']['Start'] ?? intval($request->start) ?? 0;
        $this->limit = $this->param_request['CommonParam']['Limit'] ?? intval($request->limit) ?? 10;

        if (($this->limit <= 10) || (!in_array($this->limit, [10, 20, 50, 100, 500, 1000, 2000, 4000]))) {
            $this->limit = 10;
        }
        if ($this->start != null) {
            if ((!is_numeric($this->start)) || (!is_int($this->start)) || ($this->start < 0)) {
                $this->start = 0;
            }
        }
        if (($this->limit != null) || ($this->start != null)) {
            if ((!is_numeric($this->limit)) || (!is_int($this->limit)) || ($this->limit > 4000) || ($this->limit <= 0)) {
                $this->limit = 100;
            }
        }
        $this->keyword = $this->param_request['ApiData']['KeyWord'] ?? $request->keyword ?? "";

        $this->order_by = $this->param_request['ApiData']['OrderBy'] ?? null;
        $this->order_by_request = $this->param_request['ApiData']['OrderBy'] ?? null;
        if ($this->order_by != null) {
            $this->order_by = convertArrayKeysToSnakeCase($this->order_by);
        }

        $this->is_active = $this->param_request['ApiData']['IsActive'] ?? null;
        if($this->is_active !== null){
            if (!in_array ($this->is_active, [0,1])) {
                $this->is_active = 1;
            }
        }

        $this->only_active = $this->param_request['ApiData']['OnlyActive'] ?? false;
        if (!is_bool ($this->only_active)) {
            $this->only_active = false;
        }

        $this->is_include_deleted = $this->param_request['ApiData']['IsIncludeDeleted'] ?? false;
        if (!is_bool ($this->is_include_deleted)) {
            $this->is_include_deleted = false;
        }

        $this->include_material = $this->param_request['ApiData']['IncludeMaterial'] ?? true;
        if (!is_bool ($this->include_material)) {
            $this->include_material = true;
        }

        $this->include_blood_pres = $this->param_request['ApiData']['IncludeBloodPres'] ?? true;
        if (!is_bool ($this->include_blood_pres)) {
            $this->include_blood_pres = true;
        }

        $this->has_execute = $this->param_request['ApiData']['HasExecute'] ?? true;
        if (!is_bool ($this->has_execute)) {
            $this->has_execute = true;
        }

        $this->is_not_ksk_requried_aproval__or__is_ksk_approve = $this->param_request['ApiData']['IsNotKskRequriedAproval_Or_IsKskApprove'] ?? true;
        if (!is_bool ($this->is_not_ksk_requried_aproval__or__is_ksk_approve)) {
            $this->is_not_ksk_requried_aproval__or__is_ksk_approve = true;
        }


        $this->debate_id = $this->param_request['ApiData']['DebateId'] ?? null;
        if ($this->debate_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->debate_id)) {
                $this->debate_id = null;
            } else {
                if (!Debate::where('id', $this->debate_id)->exists()) {
                    $this->debate_id = null;
                }
            }
        }

        $this->treatment_id = $this->param_request['ApiData']['TreatmentId'] ?? null;
        if ($this->treatment_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->treatment_id)) {
                $this->treatment_id = null;
            } else {
                if (!Treatment::where('id', $this->treatment_id)->exists()) {
                    $this->treatment_id = null;
                }
            }
        }

        $this->treatment_code = $this->param_request['ApiData']['TreatmentCode'] ?? null;

        $this->department_ids = $this->param_request['ApiData']['DepartmentIds'] ?? null;
        if ($this->department_ids != null) {
            foreach ($this->department_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    unset($this->department_ids[$key]);
                } else {
                    if (!Department::where('id', $item)->exists()) {
                        unset($this->department_ids[$key]);
                    }
                }
            }
        }

        $this->debate_user_id = $this->param_request['ApiData']['DebateUserId'] ?? null;
        if ($this->debate_user_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->debate_user_id)) {
                $this->debate_user_id = null;
            } else {
                if (!DebateUser::where('id', $this->debate_user_id)->exists()) {
                    $this->debate_user_id = null;
                }
            }
        }

        $this->debate_ekip_user_id = $this->param_request['ApiData']['DebateEkipUserId'] ?? null;
        if ($this->debate_ekip_user_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->debate_ekip_user_id)) {
                $this->debate_ekip_user_id = null;
            } else {
                if (!DebateEkipUser::where('id', $this->debate_ekip_user_id)->exists()) {
                    $this->debate_ekip_user_id = null;
                }
            }
        }

        $this->dhst_id = $this->param_request['ApiData']['DhstId'] ?? null;
        if ($this->dhst_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->dhst_id)) {
                $this->dhst_id = null;
            } else {
                if (!Dhst::where('id', $this->dhst_id)->exists()) {
                    $this->dhst_id = null;
                }
            }
        }

        $this->patient_type_alter_id = $this->param_request['ApiData']['PatientTypeAlterId'] ?? null;
        if ($this->patient_type_alter_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->patient_type_alter_id)) {
                $this->patient_type_alter_id = null;
            } else {
                if (!PatientTypeAlter::where('id', $this->patient_type_alter_id)->exists()) {
                    $this->patient_type_alter_id = null;
                }
            }
        }

        $this->log_time_to = $this->param_request['ApiData']['LogTimeTo'] ?? null;
        if($this->log_time_to != null){
            if(!preg_match('/^\d{14}$/',  $this->log_time_to)){
                $this->log_time_to = null;
            }
        }

        $this->sere_serv_id = $this->param_request['ApiData']['SereServId'] ?? null;
        if ($this->sere_serv_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->sere_serv_id)) {
                $this->sere_serv_id = null;
            } else {
                if (!SereServ::where('id', $this->sere_serv_id)->exists()) {
                    $this->sere_serv_id = null;
                }
            }
        }

        $this->service_req_ids = $this->param_request['ApiData']['ServiceReqIds'] ?? null;
        if ($this->service_req_ids != null) {
            foreach ($this->service_req_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    unset($this->service_req_ids[$key]);
                } else {
                    if (!ServiceReq::where('id', $item)->exists()) {
                        unset($this->service_req_ids[$key]);
                    }
                }
            }
        }

        $this->service_type_id = $this->param_request['ApiData']['ServiceTypeId'] ?? null;
        if ($this->service_type_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->service_type_id)) {
                $this->service_type_id = null;
            } else {
                if (!ServiceType::where('id', $this->service_type_id)->exists()) {
                    $this->service_type_id = null;
                }
            }
        }

        $this->execute_room_id = $this->param_request['ApiData']['ExecuteRoomId'] ?? null;
        if ($this->execute_room_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->execute_room_id)) {
                $this->execute_room_id = null;
            } else {
                if (!ExecuteRoom::where('id', $this->execute_room_id)->exists()) {
                    $this->execute_room_id = null;
                }
            }
        }

        $this->service_req_stt_ids = $this->param_request['ApiData']['ServiceReqSttIds'] ?? null;
        if ($this->service_req_stt_ids != null) {
            foreach ($this->service_req_stt_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    unset($this->service_req_stt_ids[$key]);
                } else {
                    if (!ServiceReqStt::where('id', $item)->exists()) {
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
                    unset($this->not_in_service_req_type_ids[$key]);
                } else {
                    if (!ServiceReqType::where('id', $item)->exists()) {
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
                    unset($this->tdl_patient_type_ids[$key]);
                } else {
                    if (!PatientType::where('id', $item)->exists()) {
                        unset($this->tdl_patient_type_ids[$key]);
                    }
                }
            }
        }

        $this->intruction_time_from = $this->param_request['ApiData']['IntructionTimeFrom'] ?? null;
        if($this->intruction_time_from != null){
            if(!preg_match('/^\d{14}$/',  $this->intruction_time_from)){
                $this->intruction_time_from = null;
            }
        }

        $this->intruction_time_to = $this->param_request['ApiData']['IntructionTimeTo'] ?? null;
        if($this->intruction_time_to != null){
            if(!preg_match('/^\d{14}$/',  $this->intruction_time_to)){
                $this->intruction_time_to = null;
            }
        }

        $this->service_req_id = $this->param_request['ApiData']['ServiceReqId'] ?? null;
        if ($this->service_req_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->service_req_id)) {
                $this->service_req_id = null;
            } else {
                if (!ServiceReq::where('id', $this->service_req_id)->exists()) {
                    $this->service_req_id = null;
                }
            }
        }

        $this->sere_serv_ext_id = $this->param_request['ApiData']['SereServExtId'] ?? null;
        if ($this->sere_serv_ext_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->sere_serv_ext_id)) {
                $this->sere_serv_ext_id = null;
            } else {
                if (!SereServExt::where('id', $this->sere_serv_ext_id)->exists()) {
                    $this->sere_serv_ext_id = null;
                }
            }
        }

        $this->sere_serv_ids = $this->param_request['ApiData']['SereServIds'] ?? null;
        if ($this->sere_serv_ids != null) {
            foreach ($this->sere_serv_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    unset($this->sere_serv_ids[$key]);
                } else {
                    if (!SereServ::where('id', $item)->exists()) {
                        unset($this->sere_serv_ids[$key]);
                    }
                }
            }
        }

        $this->sere_serv_tein_id = $this->param_request['ApiData']['SereServTeinId'] ?? null;
        if ($this->sere_serv_tein_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->sere_serv_tein_id)) {
                $this->sere_serv_tein_id = null;
            } else {
                if (!SereServTein::where('id', $this->sere_serv_tein_id)->exists()) {
                    $this->sere_serv_tein_id = null;
                }
            }
        }

        $this->test_index_ids = $this->param_request['ApiData']['TestIndexIds'] ?? null;
        if ($this->test_index_ids != null) {
            foreach ($this->test_index_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    unset($this->test_index_ids[$key]);
                } else {
                    if (!TestIndex::where('id', $item)->exists()) {
                        unset($this->test_index_ids[$key]);
                    }
                }
            }
        }

        $this->tdl_treatment_id = $this->param_request['ApiData']['TdlTreatmentId'] ?? null;
        if ($this->tdl_treatment_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->tdl_treatment_id)) {
                $this->tdl_treatment_id = null;
            } else {
                if (!Treatment::where('id', $this->tdl_treatment_id)->exists()) {
                    $this->tdl_treatment_id = null;
                }
            }
        }

        $this->treatment_ids = $this->param_request['ApiData']['TreatmentIds'] ?? null;
        if ($this->treatment_ids != null) {
            foreach ($this->treatment_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    unset($this->treatment_ids[$key]);
                } else {
                    if (!Treatment::where('id', $item)->exists()) {
                        unset($this->treatment_ids[$key]);
                    }
                }
            }
        }

        $this->create_time_to = $this->param_request['ApiData']['CreateTimeTo'] ?? null;
        if($this->create_time_to != null){
            if(!preg_match('/^\d{14}$/',  $this->create_time_to)){
                $this->create_time_to = null;
            }
        }

        $this->tracking_id = $this->param_request['ApiData']['TrackingId'] ?? null;
        if ($this->tracking_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->tracking_id)) {
                $this->tracking_id = null;
            } else {
                if (!Tracking::where('id', $this->tracking_id)->exists()) {
                    $this->tracking_id = null;
                }
            }
        }

        $this->patient_code__exact = $this->param_request['ApiData']['PatientCode_Exact'] ?? null;
        if($this->patient_code__exact != null){
            if(!preg_match('/^.{0,10}$/',  $this->patient_code__exact)){
                $this->patient_code__exact = null;
            }
        }

        $this->is_in_room = $this->param_request['ApiData']['IsInRoom'] ?? false;
        if (!is_bool ($this->is_in_room)) {
            $this->is_in_room = false;
        }

        $this->add_time_to = $this->param_request['ApiData']['AddTimeTo'] ?? null;
        if($this->add_time_to != null){
            if(!preg_match('/^\d{14}$/',  $this->add_time_to)){
                $this->add_time_to = null;
            }
        }

        $this->add_time_from = $this->param_request['ApiData']['AddTimeFrom'] ?? null;
        if($this->add_time_from != null){
            if(!preg_match('/^\d{14}$/',  $this->add_time_from)){
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
                    unset($this->bed_room_ids[$key]);
                } else {
                    if (!BedRoom::where('id', $item)->exists()) {
                        unset($this->bed_room_ids[$key]);
                    }
                }
            }
        }

        $this->treatment_bed_room_id = $this->param_request['ApiData']['TreatmentBedRoomId'] ?? null;
        if ($this->treatment_bed_room_id != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->treatment_bed_room_id)) {
                $this->treatment_bed_room_id = null;
            } else {
                if (!TreatmentBedRoom::where('id', $this->treatment_bed_room_id)->exists()) {
                    $this->treatment_bed_room_id = null;
                }
            }
        }

        
    }
}
