<?php

namespace App\Http\Controllers\BaseControllers;

use App\Http\Controllers\Controller;
use App\Models\HIS\Debate;
use App\Models\HIS\DebateEkipUser;
use App\Models\HIS\DebateUser;
use App\Models\HIS\Department;
use App\Models\HIS\Dhst;
use App\Models\HIS\PatientTypeAlter;
use App\Models\HIS\SereServ;
use App\Models\HIS\ServiceReq;
use App\Models\HIS\ServiceType;
use App\Models\HIS\Treatment;
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
        $this->keyword = $this->param_request['ApiData']['KeyWord'] ?? $request->keyword;

        $this->order_by = $this->param_request['ApiData']['OrderBy'] ?? null;
        $this->order_by_request = $this->param_request['ApiData']['OrderBy'] ?? null;
        if ($this->order_by != null) {
            $this->order_by = convertArrayKeysToSnakeCase($this->order_by);
        }

        $this->only_active = $this->param_request['ApiData']['OnlyActive'] ?? false;
        if (!is_bool ($this->only_active)) {
            $this->only_active = false;
        }

        $this->is_include_deleted = $this->param_request['ApiData']['IsIncludeDeleted'] ?? false;
        if (!is_bool ($this->is_include_deleted)) {
            $this->is_include_deleted = false;
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

    }
}
