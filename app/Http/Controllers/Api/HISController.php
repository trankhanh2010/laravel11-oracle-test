<?php

namespace App\Http\Controllers\Api;

use App\Events\Cache\DeleteCache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;

// use Model

use App\Models\HIS\Room;
use App\Models\HIS\ServicePaty;
use App\Models\HIS\ServiceMachine;
use App\Models\HIS\Machine;
use App\Models\HIS\ServiceRoom;
use App\Models\HIS\ServiceFollow;
use App\Models\HIS\Bed;
use App\Models\HIS\BedBsty;
use App\Models\HIS\BedType;
use App\Models\HIS\ServSegr;
use App\Models\HIS\ServiceGroup;
use App\Models\HIS\Employee;
use App\Models\HIS\ExecuteRoleUser;
use App\Models\ACS\Role;
use App\Models\ACS\Module;
use App\Models\SDA\Ethnic;
use App\Models\HIS\PatientType;
use App\Models\HIS\PriorityType;
use App\Models\HIS\Career;
use App\Models\SDA\Religion;
use App\Models\HIS\ServiceUnit;
use App\Models\HIS\ServiceType;
use App\Models\HIS\RationGroup;
use App\Models\HIS\ServiceReqType;
use App\Models\HIS\RationTime;
use App\Models\EMR\Relation;
use App\Models\ACS\ModuleRole;
use App\Models\HIS\MestPatientType;
use App\Models\HIS\MediStockMety;
use App\Models\ACS\User;
use App\Models\HIS\MedicineType;
use App\Models\HIS\MediStockMaty;
use App\Models\HIS\MaterialType;
use App\Models\HIS\MestRoom;
use App\Models\HIS\ExroRoom;
use App\Models\HIS\PatientTypeRoom;
use App\Models\HIS\SaleProfitCFG;
use App\Models\HIS\PatientTypeAllow;
use App\Models\HIS\Position;
use App\Models\HIS\WorkPlace;
use App\Models\HIS\BornPosition;
use App\Models\HIS\PatientCase;
use App\Models\HIS\BHYTWhitelist;
use App\Models\HIS\HeinServiceType;
use App\Models\HIS\BHYTParam;
use App\Models\HIS\BHYTBlacklist;
use App\Models\HIS\MedicinePaty;
use App\Models\HIS\AccidentBodyPart;
use App\Models\HIS\PreparationsBlood;
use App\Models\HIS\Contraindication;
use App\Models\HIS\DosageForm;
use App\Models\HIS\AccidentLocation;
use App\Models\HIS\LicenseClass;
use App\Models\HIS\Manufacturer;
use App\Models\HIS\Icd;
use App\Models\HIS\MediRecordType;
use App\Models\HIS\FileType;
use App\Models\HIS\TreatmentEndType;
use App\Models\HIS\TranPatiTech;
use App\Models\HIS\DebateReason;
use App\Models\HIS\CancelReason;
use App\Models\HIS\InteractionReason;
use App\Models\HIS\UnlimitReason;
use App\Models\HIS\HospitalizeReason;
use App\Models\HIS\ExpMestReason;
use App\Models\HIS\CareerTitle;
use App\Models\HIS\AccidentHurtType;
use App\Models\HIS\Supplier;
use App\Models\HIS\ProcessingMethod;
use App\Models\HIS\DeathWithin;
use App\Models\HIS\LocationStore;
use App\Models\HIS\AccidentCare;
use App\Models\HIS\PtttTable;
use App\Models\HIS\PtttGroup;
use App\Models\HIS\PtttMethod;
use App\Models\HIS\EmotionlessMethod;
use App\Models\HIS\PtttCatastrophe;
use App\Models\HIS\PtttCondition;
use App\Models\HIS\Awareness;
use App\Models\HIS\MedicineLine;
use App\Models\HIS\BloodVolume;
use App\Models\HIS\MedicineUseForm;
use App\Models\HIS\BidType;
use App\Models\HIS\MedicineTypeAcIn;
use App\Models\HIS\ActiveIngredient;
use App\Models\HIS\AtcGroup;
use App\Models\HIS\BloodGroup;
use App\Models\HIS\MedicineGroup;
use App\Models\HIS\TestIndex;
use App\Models\HIS\TestIndexUnit;
use App\Models\HIS\TestSampleType;
use App\Models\HIS\UserRoom;
use App\Models\HIS\Debate;
use App\Models\HIS\DebateUser;
use App\Models\HIS\DebateEkipUser;
use App\Models\HIS\DebateType;
use App\Models\HIS\Treatment;
use App\Models\HIS\Tracking;
use App\Models\HIS\DebateInviteUser;
use App\Models\HIS\ServiceReq;
use App\Models\HIS\ExpMest;
use App\Models\HIS\ExpMestMedicine;
use App\Models\HIS\ExpMestMaterial;
use App\Models\HIS\ImpMest;
use App\Models\HIS\SereServExt;
use App\Models\HIS\SereServ;
use App\Models\HIS\Dhst;
use App\Models\HIS\Care;
use App\Models\HIS\PatientTypeAlter;
use App\Models\HIS\TreatmentBedRoom;
use App\Models\HIS\SereServTein;
use App\Models\ACS\ModuleGroup;
// use Request






class HISController extends Controller
{
    protected $data = [];
    protected $time;
    protected $start;
    protected $limit;
    protected $per_page;
    protected $page;
    protected $param_request;
    protected $order_by;
    // Khai báo các biến mặc định model
    protected $app_creator = "MOS_v2";
    protected $app_modifier = "MOS_v2";
    // Khai báo các biến model
    protected $department;
    protected $department_name = "department";
    protected $bed_room;
    protected $bed_room_name = "bed_room";
    protected $execute_room;
    protected $execute_room_name = "execute_room";
    protected $room;
    protected $room_name = "room";
    protected $speciality;
    protected $speciality_name = "speciality";
    protected $treatment_type;
    protected $treatment_type_name = "treatment_type";
    protected $medi_org;
    protected $medi_org_name = "medi_org";
    protected $branch;
    protected $branch_name = "branch";
    protected $district;
    protected $district_name = "district";
    protected $medi_stock;
    protected $medi_stock_name = "medi_stock";
    protected $reception_room;
    protected $reception_room_name = "reception_room";
    protected $area;
    protected $area_name = "area";
    protected $refectory;
    protected $refectory_name = "refectory";
    protected $execute_group;
    protected $execute_group_name = "execute_group";
    protected $cashier_room;
    protected $cashier_room_name = "cashier_room";
    protected $national;
    protected $national_name = "national";
    protected $province;
    protected $province_name = "province";
    protected $data_store;
    protected $data_store_name = "data_store";
    protected $execute_role;
    protected $execute_role_name = "execute_role";
    protected $commune;
    protected $commune_name = "commune";
    protected $service;
    protected $service_name = "service";
    protected $service_paty;
    protected $service_paty_name = 'service_paty';
    protected $service_machine;
    protected $service_machine_name = 'service_machine';
    protected $machine;
    protected $machine_name = 'machine';
    protected $service_room;
    protected $service_room_name = 'service_room';
    protected $service_follow;
    protected $service_follow_name = 'service_follow';
    protected $bed;
    protected $bed_name = 'bed';
    protected $bed_bsty;
    protected $bed_bsty_name = 'bed_bsty';
    protected $bed_type;
    protected $bed_type_name = 'bed_type';
    protected $serv_segr;
    protected $serv_segr_name = 'serv_segr';
    protected $service_group;
    protected $service_group_name = 'service_group';
    protected $emp_user;
    protected $emp_user_name = 'employee';
    protected $execute_role_user;
    protected $execute_role_user_name = 'execute_role_user';
    protected $role;
    protected $role_name = 'role';
    protected $module;
    protected $module_name = 'module';
    protected $ethnic;
    protected $ethnic_name = 'ethnic';
    protected $patient_type;
    protected $patient_type_name = 'patient_type';
    protected $priority_type;
    protected $priority_type_name = 'priority_type';
    protected $career;
    protected $career_name = 'career';
    protected $patient_classify;
    protected $patient_classify_name = 'patient_classify';
    protected $religion;
    protected $religion_name = 'religion';
    protected $service_unit;
    protected $service_unit_name = 'service_unit';
    protected $service_type;
    protected $service_type_name = 'service_type';
    protected $ration_group;
    protected $ration_group_name = 'ration_group';
    protected $service_req_type;
    protected $service_req_type_name = 'service_req_type';
    protected $ration_time;
    protected $ration_time_name = 'ration_time';
    protected $relation_list;
    protected $relation_list_name = 'relation_list';
    protected $module_role;
    protected $module_role_name = 'module_role';
    protected $mest_patient_type;
    protected $mest_patient_type_name = 'mest_patient_type';
    protected $medi_stock_mety_list;
    protected $medi_stock_mety_list_name = 'medi_stock_mety';
    protected $user;
    protected $user_name = 'user';
    protected $medicine_type;
    protected $medicine_type_name = 'medicine_type';
    protected $medi_stock_maty_list;
    protected $medi_stock_maty_list_name = 'medi_stock_maty';
    protected $material_type;
    protected $material_type_name = 'material_type';
    protected $mest_export_room;
    protected $mest_export_room_name = 'mest_export_room';
    protected $exro_room;
    protected $exro_room_name = 'exro_room';
    protected $patient_type_room;
    protected $patient_type_room_name = 'patient_type_room';
    protected $sale_profit_cfg;
    protected $sale_profit_cfg_name = 'sale_profit_cfg';
    protected $patient_type_allow;
    protected $patient_type_allow_name = 'patient_type_allow';
    protected $position;
    protected $position_name = 'position';
    protected $work_place;
    protected $work_place_name = 'work_place';
    protected $born_position;
    protected $born_position_name = 'born_position';
    protected $patient_case;
    protected $patient_case_name = 'patient_case';
    protected $bhyt_whitelist;
    protected $bhyt_whitelist_name = 'bhyt_whitelist';
    protected $hein_service_type;
    protected $hein_service_type_name = 'hein_service_type';
    protected $bhyt_param;
    protected $bhyt_param_name = 'bhyt_param';
    protected $bhyt_blacklist;
    protected $bhyt_blacklist_name = 'bhyt_blacklist';
    protected $medicine_paty;
    protected $medicine_paty_name = 'medicine_paty';
    protected $accident_body_part;
    protected $accident_body_part_name = 'accident_body_part';
    protected $preparations_blood;
    protected $preparations_blood_name = 'preparations_blood';
    protected $contraindication;
    protected $contraindication_name = 'contraindication';
    protected $dosage_form;
    protected $dosage_form_name = 'dosage_form';
    protected $accident_location;
    protected $accident_location_name = 'accident_location';
    protected $license_class;
    protected $license_class_name = 'license_class';
    protected $manufacturer;
    protected $manufacturer_name = 'manufacturer';
    protected $icd;
    protected $icd_name = 'icd';
    protected $medi_record_type;
    protected $medi_record_type_name = 'medi_record_type';
    protected $file_type;
    protected $file_type_name = 'file_type';
    protected $treatment_end_type;
    protected $treatment_end_type_name = 'treatment_end_type';
    protected $tran_pati_tech;
    protected $tran_pati_tech_name = 'tran_pati_tech';
    protected $debate_reason;
    protected $debate_reason_name = 'debate_reason';
    protected $cancel_reason;
    protected $cancel_reason_name = 'cancel_reason';
    protected $interaction_reason;
    protected $interaction_reason_name = 'interaction_reason';
    protected $unlimit_reason;
    protected $unlimit_reason_name = 'unlimit_reason';
    protected $hospitalize_reason;
    protected $hospitalize_reason_name = 'hospitalize_reason';
    protected $exp_mest_reason;
    protected $exp_mest_reason_name = 'exp_mest_reason';
    protected $career_title;
    protected $career_title_name = 'career_title';
    protected $accident_hurt_type;
    protected $accident_hurt_type_name = 'accident_hurt_type';
    protected $supplier;
    protected $supplier_name = 'supplier';
    protected $processing_method;
    protected $processing_method_name = 'processing_method';
    protected $death_within;
    protected $death_within_name = 'death_within';
    protected $location_store;
    protected $location_store_name = 'location_store';
    protected $accident_care;
    protected $accident_care_name = 'accident_care';
    protected $pttt_table;
    protected $pttt_table_name = 'pttt_table';
    protected $pttt_group;
    protected $pttt_group_name = 'pttt_group';
    protected $pttt_method;
    protected $pttt_method_name = 'pttt_method';
    protected $emotionless_method;
    protected $emotionless_method_name = 'emotionless_method';
    protected $pttt_catastrophe;
    protected $pttt_catastrophe_name = 'pttt_catastrophe';
    protected $pttt_condition;
    protected $pttt_condition_name = 'pttt_condition';
    protected $awareness;
    protected $awareness_name = 'awareness';
    protected $medicine_line;
    protected $medicine_line_name = 'medicine_line';
    protected $blood_volume;
    protected $blood_volume_name = 'blood_volume';
    protected $medicine_use_form;
    protected $medicine_use_form_name = 'medicine_use_form';
    protected $bid_type;
    protected $bid_type_name = 'bid_type';
    protected $medicine_type_acin;
    protected $medicine_type_acin_name = 'medicine_type_acin';
    protected $active_ingredient;
    protected $active_ingredient_name = 'active_ingredient';
    protected $atc_group;
    protected $atc_group_name = 'atc_group';
    protected $blood_group;
    protected $blood_group_name = 'blood_group';
    protected $medicine_group;
    protected $medicine_group_name = 'medicine_group';
    protected $test_index;
    protected $test_index_name = 'test_index';
    protected $test_index_unit;
    protected $test_index_unit_name = 'test_index_unit';
    protected $test_sample_type;
    protected $test_sample_type_name = 'test_sample_type';
    protected $user_room;
    protected $user_room_name = 'user_room';
    protected $debate;
    protected $debate_name = 'debate';
    protected $debate_user;
    protected $debate_user_name = 'debate_user';
    protected $debate_ekip_user;
    protected $debate_ekip_user_name = 'debate_ekip_user';
    protected $debate_type;
    protected $debate_type_name = 'debate_type';
    protected $treatment;
    protected $treatment_name = 'treatment';
    protected $tracking;
    protected $tracking_name = 'tracking';
    protected $debate_invite_user;
    protected $debate_invite_user_name = 'debate_invite_user';
    protected $service_req;
    protected $service_req_name = 'service_req';
    protected $exp_mest;
    protected $exp_mest_name = 'exp_mest';
    protected $exp_mest_medicine;
    protected $exp_mest_medicine_name = 'exp_mest_medicine';
    protected $exp_mest_material;
    protected $exp_mest_material_name = 'exp_mest_material';
    protected $imp_mest;
    protected $imp_mest_name = 'imp_mest';
    protected $sere_serv_ext;
    protected $sere_serv_ext_name = 'sere_serv_ext';
    protected $sere_serv;
    protected $sere_serv_name = 'sere_serv';
    protected $dhst;
    protected $dhst_name = 'dhst';
    protected $care;
    protected $care_name = 'care';
    protected $patient_type_alter;
    protected $patient_type_alter_name = 'patient_type_alter';
    protected $treatment_bed_room;
    protected $treatment_bed_room_name = 'treatment_bed_room';
    protected $sere_serv_tein;
    protected $sere_serv_tein_name = 'sere_serv_tein';
    protected $group;
    protected $group_name = 'group';
    protected $room_type;
    protected $room_type_name = 'room_type';
    protected $test_type;
    protected $test_type_name = 'test_type';
    protected $room_group;
    protected $room_group_name = 'room_group';
    protected $module_group;
    protected $module_group_name = 'module_group';
    protected $other_pay_source;
    protected $other_pay_source_name = 'other_pay_source';
    protected $military_rank;
    protected $military_rank_name = 'military_rank';
    public function __construct(Request $request)
    {
        // Khai báo các biến
        $this->time = now()->addMinutes(10080);
        $this->param_request = json_decode(base64_decode($request->input('param')), true);
        $this->per_page = $request->query('perPage', 50);
        $this->page = $request->query('page', 1);
        $this->start = $this->param_request['CommonParam']['Start'] ?? 0;
        $this->limit = $this->param_request['CommonParam']['Limit'] ?? 100;
        if ($this->start < 0) {
            $this->start = 0;
        }
        if ($this->limit > 100) {
            $this->limit = 100;
        }

        // Khởi tạo các model
        $this->room = new Room();
        $this->service_paty = new ServicePaty();
        $this->service_machine = new ServiceMachine();
        $this->machine = new Machine();
        $this->service_room = new ServiceRoom();
        $this->service_follow = new ServiceFollow();
        $this->bed = new Bed();
        $this->bed_bsty = new BedBsty();
        $this->bed_type = new BedType();
        $this->serv_segr = new ServSegr();
        $this->service_group = new ServiceGroup();
        $this->emp_user = new Employee();
        $this->execute_role_user = new ExecuteRoleUser();
        $this->role = new Role();
        $this->module = new Module();
        $this->ethnic = new Ethnic();
        $this->patient_type = new PatientType();
        $this->priority_type = new PriorityType();
        $this->career = new Career();
        $this->religion = new Religion();
        $this->service_unit = new ServiceUnit();
        $this->service_type = new ServiceType();
        $this->ration_group = new RationGroup();
        $this->service_req_type = new ServiceReqType();
        $this->ration_time = new RationTime();
        $this->relation_list = new Relation();
        $this->module_role = new ModuleRole();
        $this->mest_patient_type = new MestPatientType();
        $this->medi_stock_mety_list = new MediStockMety();
        $this->user = new User();
        $this->medicine_type = new MedicineType();
        $this->medi_stock_maty_list = new MediStockMaty();
        $this->material_type = new MaterialType();
        $this->mest_export_room = new MestRoom();
        $this->exro_room = new ExroRoom();
        $this->patient_type_room = new PatientTypeRoom();
        $this->sale_profit_cfg = new SaleProfitCFG();
        $this->patient_type_allow = new PatientTypeAllow();
        $this->position = new Position();
        $this->work_place = new WorkPlace();
        $this->born_position = new BornPosition();
        $this->patient_case = new PatientCase();
        $this->bhyt_whitelist = new BHYTWhitelist();
        $this->hein_service_type = new HeinServiceType();
        $this->bhyt_param = new BHYTParam();
        $this->bhyt_blacklist = new BHYTBlacklist();
        $this->medicine_paty = new MedicinePaty();
        $this->accident_body_part = new AccidentBodyPart();
        $this->preparations_blood = new PreparationsBlood();
        $this->contraindication = new Contraindication();
        $this->dosage_form = new DosageForm();
        $this->accident_location = new AccidentLocation();
        $this->license_class = new LicenseClass();
        $this->manufacturer = new Manufacturer();
        $this->icd = new Icd();
        $this->medi_record_type = new MediRecordType();
        $this->file_type = new FileType();
        $this->treatment_end_type = new TreatmentEndType();
        $this->tran_pati_tech = new TranPatiTech();
        $this->debate_reason = new DebateReason();
        $this->cancel_reason = new CancelReason();
        $this->interaction_reason = new InteractionReason();
        $this->unlimit_reason = new UnlimitReason();
        $this->hospitalize_reason = new HospitalizeReason();
        $this->exp_mest_reason = new ExpMestReason();
        $this->career_title = new CareerTitle();
        $this->accident_hurt_type = new AccidentHurtType();
        $this->supplier = new Supplier();
        $this->processing_method = new ProcessingMethod();
        $this->death_within = new DeathWithin();
        $this->location_store = new LocationStore();
        $this->accident_care = new AccidentCare();
        $this->pttt_table = new PtttTable();
        $this->pttt_group = new PtttGroup();
        $this->pttt_method = new PtttMethod();
        $this->emotionless_method = new EmotionlessMethod();
        $this->pttt_catastrophe = new PtttCatastrophe();
        $this->pttt_condition = new PtttCondition();
        $this->awareness = new Awareness();
        $this->medicine_line = new MedicineLine();
        $this->blood_volume = new BloodVolume();
        $this->medicine_use_form = new MedicineUseForm();
        $this->bid_type = new BidType();
        $this->medicine_type_acin = new MedicineTypeAcIn();
        $this->active_ingredient = new ActiveIngredient();
        $this->atc_group = new AtcGroup();
        $this->blood_group = new BloodGroup();
        $this->medicine_group = new MedicineGroup();
        $this->test_index = new TestIndex();
        $this->test_index_unit = new TestIndexUnit();
        $this->test_sample_type = new TestSampleType();
        $this->user_room = new UserRoom();
        $this->debate = new Debate();
        $this->debate_user = new DebateUser();
        $this->debate_ekip_user = new DebateEkipUser();
        $this->debate_type = new DebateType();
        $this->treatment = new Treatment();
        $this->tracking = new Tracking();
        $this->debate_invite_user = new DebateInviteUser();
        $this->service_req = new ServiceReq();
        $this->exp_mest = new ExpMest();
        $this->exp_mest_medicine = new ExpMestMedicine();
        $this->exp_mest_material = new ExpMestMaterial();
        $this->imp_mest = new ImpMest();
        $this->sere_serv_ext = new SereServExt();
        $this->sere_serv = new SereServ();
        $this->dhst = new Dhst();
        $this->care = new Care();
        $this->patient_type_alter = new PatientTypeAlter();
        $this->treatment_bed_room = new TreatmentBedRoom();
        $this->sere_serv_tein = new SereServTein();
        $this->module_group = new ModuleGroup();
    }
 





   

    /// Service Machine
    public function service_machine()
    {
        $param = [
            'service:id,service_name,service_type_id',
            'service.service_type:id,service_type_name,service_type_code',
            'machine:id,machine_name,machine_code,machine_group_code',
        ];
        $data = get_cache_full($this->service_machine, $param, $this->service_machine_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function service_machine_id($id)
    {
        $data = get_cache($this->service_machine, $this->service_machine_name, $id, $this->time);
        $data1 = get_cache_1_1($this->service_machine, "service", $this->service_machine_name, $id, $this->time);
        $data2 = get_cache_1_1_1($this->service_machine, "service.service_type", $this->service_machine_name, $id, $this->time);
        $data3 = get_cache_1_1($this->service_machine, "machine", $this->service_machine_name, $id, $this->time);

        return response()->json(['data' => [
            'service_machine' => $data,
            'service' => $data1,
            'service_type' => $data2,
            'machine' => $data3
        ]], 200);
    }

    public function service_with_machine($id = null)
    {
        if ($id == null) {
            $name = $this->service_name . '_with_' . $this->machine_name;
            $param = [
                'machines:id,machine_name,machine_code',
            ];
        } else {
            $name = $this->service_name . '_' . $id . '_with_' . $this->machine_name;
            $param = [
                'machines',
            ];
        }
        $data = get_cache_full($this->service, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function machine_with_service($id = null)
    {
        if ($id == null) {
            $name = $this->machine_name . '_with_' . $this->service_name;
            $param = [
                'services:id,service_name,service_code',
            ];
        } else {
            $name = $this->machine_name . '_' . $id . '_with_' . $this->service_name;
            $param = [
                'services',
            ];
        }
        $data = get_cache_full($this->machine, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }



    /// Service Room
    public function service_room($id = null)
    {
        if ($id == null) {
            $name = $this->service_room_name;
            $param = [
                'service:id,service_name,service_type_id',
                'service.service_type:id,service_type_name,service_type_code',
                'room:id,room_type_id,department_id',
                'room.execute_room:id,room_id,execute_room_name,execute_room_code',
                'room.room_type:id,room_type_name',
                'room.department:id,department_name',
            ];
        } else {
            $name = $this->service_room_name . '_' . $id;
            $param = [
                'service',
                'service.service_type',
                'room',
                'room.execute_room',
                'room.room_type',
                'room.department',
            ];
        }
        $data = get_cache_full($this->service_room, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }


    public function service_with_room($id = null)
    {
        if ($id == null) {
            $name = $this->service_name . '_with_' . $this->execute_room_name;
            $param = [
                'execute_rooms:id,room_id,execute_room_name,execute_room_code',
                'execute_rooms.room:id,department_id,room_type_id',
                'execute_rooms.room.department:id,department_name,department_code',
                'execute_rooms.room.room_type:id,room_type_name,room_type_code'
            ];
        } else {
            $name = $this->service_name . '_' . $id . '_with_' . $this->execute_room_name;
            $param = [
                'execute_rooms',
                'execute_rooms.room:id,department_id,room_type_id',
                'execute_rooms.room.department',
                'execute_rooms.room.room_type'
            ];
        }
        $data = get_cache_full($this->service, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function room_with_service($id = null)
    {
        if ($id == null) {
            $name = $this->execute_room_name . '_with_' . $this->service_name;
            $param = [
                'services:id,service_name,service_code',
                'room:id,department_id,room_type_id',
                'room.department:id,department_name,department_code',
                'room.room_type:id,room_type_name,room_type_code',
                'room.execute_room:id,room_id,execute_room_name,execute_room_code'
            ];
        } else {
            $name = $this->execute_room_name . '_' . $id . '_with_' . $this->service_name;
            $param = [
                'services',
                'room:id,department_id,room_type_id',
                'room.department:id,department_name,department_code',
                'room.room_type:id,room_type_name,room_type_code',
                'room.execute_room'
            ];
        }
        $data = get_cache_full($this->execute_room, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Room


    /// Service Follow
    public function service_follow()
    {
        $param = [
            'service:id,service_name,service_type_id',
            'service.service_type:id,service_type_name,service_type_code',
            'follow:id,service_name,service_type_id',
            'follow.service_type:id,service_type_name,service_type_code',
        ];
        $data = get_cache_full($this->service_follow, $param, $this->service_follow_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function service_follow_id($id)
    {
        $data = get_cache($this->service_follow, $this->service_follow_name, $id, $this->time);
        $data1 = get_cache_1_1($this->service_follow, "service", $this->service_follow_name, $id, $this->time);
        $data2 = get_cache_1_1_1($this->service_follow, "service.service_type", $this->service_follow_name, $id, $this->time);
        $data3 = get_cache_1_1($this->service_follow, "follow", $this->service_follow_name, $id, $this->time);
        $data4 = get_cache_1_1_1($this->service_follow, "follow.service_type", $this->service_follow_name, $id, $this->time);
        $data5 = get_cache_1_n_with_ids($this->service_follow, "treatment_type", $this->service_follow_name, $id, $this->time);

        return response()->json(['data' => [
            'service_follow' => $data,
            'service' => $data1,
            'service_type' => $data2,
            'follow' => $data3,
            'follow_type' => $data4,
            'treatment_type' => $data5
        ]], 200);
    }

    public function service_with_follow($id = null)
    {
        if ($id == null) {
            $name = $this->service_name . '_with_follow' . $this->service_name;
            $param = [
                'follows:id,service_name,service_code',
            ];
        } else {
            $name = $this->service_name . '_' . $id . '_with_' . $this->machine_name;
            $param = [
                'follows',
            ];
        }
        $data = get_cache_full($this->service, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function follow_with_service($id = null)
    {
        if ($id == null) {
            $name = $this->service_name . '_follow_with_' . $this->service_name;
            $param = [
                'services:id,service_name,service_code',
            ];
        } else {
            $name = $this->service_name . '_' . $id . '_with_' . $this->service_name;
            $param = [
                'services',
            ];
        }
        $data = get_cache_full($this->service, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }


    /// BedBsty
    public function bed_bsty($id = null)
    {
        if ($id == null) {
            $name = $this->bed_bsty_name;
            $param = [
                'bed:id,bed_name,bed_room_id',
                'bed.bed_room:id,bed_room_name',
                'bed.bed_room.room:id,department_id',
                'bed.bed_room.room.department:id,department_name',
                'bed_service_type:id,service_name,service_code'
            ];
        } else {
            $name = $this->bed_bsty_name . '_' . $id;
            $param = [
                'bed',
                'bed.bed_room',
                'bed.bed_room.room',
                'bed.bed_room.room.department',
                'bed_service_type'
            ];
        }
        $data = get_cache_full($this->bed_bsty, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function service_with_bed($id = null)
    {
        if ($id == null) {
            $name = $this->service_name . '_with_' . $this->bed_name;
            $param = [
                'beds:id,bed_name,bed_room_id',
                'beds.bed_room:id,bed_room_name,room_id',
                'beds.bed_room.room:id,department_id',
                'beds.bed_room.room.department:id,department_name,department_code',
            ];
        } else {
            $name = $this->service_name . '_' . $id . '_with_' . $this->bed_name;
            $param = [
                'beds',
                'beds.bed_room:id,bed_room_name,room_id',
                'beds.bed_room.room:id,department_id',
                'beds.bed_room.room.department:id,department_name,department_code',
            ];
        }
        $data = get_cache_full($this->service, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function bed_with_service($id = null)
    {
        if ($id == null) {
            $name = $this->bed_name . '_with_' . $this->service_name;
            $param = [
                'bed_room:id,bed_room_name,room_id',
                'bed_room.room:id,department_id',
                'bed_room.room.department:id,department_name,department_code',
                'services:id,service_name,service_code'
            ];
        } else {
            $name = $this->bed_name . '_' . $id . '_with_' . $this->service_name;
            $param = [
                'bed_room',
                'bed_room.room:id,department_id',
                'bed_room.room.department:id,department_name,department_code',
                'services'
            ];
        }
        $data = get_cache_full($this->bed, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }


    /// Serv Segr
    public function serv_segr($id = null)
    {
        if ($id == null) {
            $name = $this->serv_segr_name;
            $param = [
                'service:id,service_name,service_type_id',
                'service.service_type:id,service_type_name,service_type_code',
                'service_group:id,service_group_name',
            ];
        } else {
            $name = $this->serv_segr_name . '_' . $id;
            $param = [
                'service',
                'service.service_type',
                'service_group',
            ];
        }
        $data = get_cache_full($this->serv_segr, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Service Group
    public function service_group()
    {
        $data = get_cache($this->service_group, $this->service_group_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function service_group_id($id)
    {
        $data = get_cache($this->service_group, $this->service_group_name, $id, $this->time);
        return response()->json(['data' => [
            'service_group' => $data
        ]], 200);
    }



    /// Info User
    public function info_user_id($id)
    {
        $data = get_cache($this->emp_user, $this->emp_user_name, $id, $this->time);
        $data1 = get_cache_1_1($this->emp_user, "department", $this->emp_user_name, $id, $this->time);
        $data2 = get_cache_1_1($this->emp_user, "gender", $this->emp_user_name, $id, $this->time);
        $data3 = get_cache_1_1($this->emp_user, "branch", $this->emp_user_name, $id, $this->time);
        $data4 = get_cache_1_1($this->emp_user, "career_title", $this->emp_user_name, $id, $this->time);
        $data5 = get_cache_1_n_with_ids($this->emp_user, "default_medi_stock", $this->emp_user_name, $id, $this->time);

        return response()->json(['data' => [
            'info_user' => $data,
            'department' => $data1,
            'genderr' => $data2,
            'branch' => $data3,
            'career_title' => $data4,
            'default_medi_stock' => $data5,

        ]], 200);
    }

    /// Execute Role User
    public function execute_role_user($id = null)
    {
        if ($id == null) {
            $name = $this->execute_role_user_name;
            $param = [
                'execute_role:id,execute_role_name',
            ];
        } else {
            $name = $this->execute_role_user_name . '_' . $id;
            $param = [
                'execute_role',
            ];
        }
        $data = get_cache_full($this->execute_role_user, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function execute_role_with_user($id = null)
    {
        if ($id == null) {
            $name = $this->execute_role_name . '_with_' . $this->emp_user_name;
            $param = [
                'employees:id,loginname,tdl_username,department_id',
                'employees.department:id,department_name,department_code'
            ];
        } else {
            $name = $this->execute_role_name . '_' . $id . '_with_' . $this->emp_user_name;
            $param = [
                'employees',
                'employees.department'
            ];
        }
        $data = get_cache_full($this->execute_role, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function user_with_execute_role($id = null)
    {
        if ($id == null) {
            $name = $this->emp_user_name . '_with_' . $this->execute_role_name;
            $param = [
                'execute_roles:id,execute_role_name,execute_role_code',
                'department:id,department_code,department_name'
            ];
        } else {
            $name = $this->emp_user_name . '_' . $id . '_with_' . $this->execute_role_name;
            $param = [
                'execute_roles',
                'department'
            ];
        }
        $data = get_cache_full($this->emp_user, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }


    /// Module
    public function module_role($id = null)
    {
        if ($id == null) {
            $name = $this->module_role_name;
            $param = [
                'module:id,module_name',
                'role:id,role_name,role_code',
            ];
        } else {
            $name = $this->module_role_name . '_' . $id;
            $param = [
                'module',
                'role',
            ];
        }
        $data = get_cache_full($this->module_role, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Mest Patient Type

    public function mest_patient_type($id = null)
    {
        if ($id == null) {
            $name = $this->mest_patient_type_name;
            $param = [
                'medi_stock:id,medi_stock_name,medi_stock_code',
                'patient_type:id,patient_type_name,patient_type_code'
            ];
        } else {
            $name = $this->mest_patient_type_name . '_' . $id;
            $param = [
                'medi_stock',
                'patient_type'
            ];
        }
        $data = get_cache_full($this->mest_patient_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function medi_stock_with_patient_type($id = null)
    {
        if ($id == null) {
            $name = $this->medi_stock_name . '_with_' . $this->patient_type_name;
            $param = [
                'patient_types:id,patient_type_name,patient_type_code'
            ];
        } else {
            $name = $this->medi_stock_name . '_' . $id . '_with_' . $this->patient_type_name;
            $param = [
                'patient_types'
            ];
        }
        $data = get_cache_full($this->medi_stock, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function patient_type_with_medi_stock($id = null)
    {
        if ($id == null) {
            $name = $this->patient_type_name . '_with_' . $this->medi_stock_name;
            $param = [
                'medi_stocks:id,medi_stock_name,medi_stock_code'
            ];
        } else {
            $name = $this->patient_type_name . '_' . $id . '_with_' . $this->medi_stock_name;
            $param = [
                'medi_stocks'
            ];
        }
        $data = get_cache_full($this->patient_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Medi Stock Mety List

    public function medi_stock_mety_list($id = null)
    {
        if ($id == null) {
            $name = $this->medi_stock_mety_list_name;
            $param = [
                'medi_stock:id,medi_stock_name,medi_stock_code',
                'medicine_type:id,medicine_type_name,medicine_type_code',
                'exp_medi_stock:id,medi_stock_name,medi_stock_code'
            ];
        } else {
            $name = $this->medi_stock_mety_list_name . '_' . $id;
            $param = [
                'medi_stock',
                'medicine_type',
                'exp_medi_stock'
            ];
        }
        $data = get_cache_full($this->medi_stock_mety_list, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function medi_stock_with_medicine_type($id = null)
    {
        if ($id == null) {
            $name = $this->medi_stock_name . '_with_' . $this->medicine_type_name;
            $param = [
                'medicine_types:id,medicine_type_name,medicine_type_code,tdl_service_unit_id',
                'medicine_types.service_unit:id,service_unit_name,service_unit_code'
            ];
        } else {
            $name = $this->medi_stock_name . '_' . $id . '_with_' . $this->medicine_type_name;
            $param = [
                'medicine_types',
                'medicine_types.service_unit'

            ];
        }
        $data = get_cache_full($this->medi_stock, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function medicine_type_with_medi_stock($id = null)
    {
        if ($id == null) {
            $name = $this->medicine_type_name . '_with_' . $this->medi_stock_name;
            $param = [
                'medi_stocks:id,medi_stock_name,medi_stock_code',
                'service_unit:id,service_unit_name,service_unit_code'
            ];
        } else {
            $name = $this->medicine_type_name . '_' . $id . '_with_' . $this->medi_stock_name;
            $param = [
                'medi_stocks',
                'service_unit'
            ];
        }
        $data = get_cache_full($this->medicine_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Medi Stock Maty List
    public function medi_stock_maty_list($id = null)
    {
        if ($id == null) {
            $name = $this->medi_stock_maty_list_name;
            $param = [
                'medi_stock:id,medi_stock_name,medi_stock_code',
                'material_type:id,material_type_name,material_type_code',
                'exp_medi_stock:id,medi_stock_name,medi_stock_code'
            ];
        } else {
            $name = $this->medi_stock_maty_list_name . '_' . $id;
            $param = [
                'medi_stock',
                'material_type',
                'exp_medi_stock'
            ];
        }
        $data = get_cache_full($this->medi_stock_maty_list, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function medi_stock_with_material_type($id = null)
    {
        if ($id == null) {
            $name = $this->medi_stock_name . '_with_' . $this->material_type_name;
            $param = [
                'material_types:id,material_type_name,material_type_code,tdl_service_unit_id',
                'material_types.service_unit:id,service_unit_name,service_unit_code'
            ];
        } else {
            $name = $this->medi_stock_name . '_' . $id . '_with_' . $this->material_type_name;
            $param = [
                'material_types',
                'material_types.service_unit'
            ];
        }
        $data = get_cache_full($this->medi_stock, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function material_type_with_medi_stock($id = null)
    {
        if ($id == null) {
            $name = $this->material_type_name . '_with_' . $this->medi_stock_name;
            $param = [
                'medi_stocks:id,medi_stock_name,medi_stock_code',
                'service_unit:id,service_unit_name,service_unit_code',
            ];
        } else {
            $name = $this->material_type_name . '_' . $id . '_with_' . $this->medi_stock_name;
            $param = [
                'medi_stocks',
                'service_unit',
            ];
        }
        $data = get_cache_full($this->material_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Mest Export Room
    public function mest_export_room($id = null)
    {
        if ($id == null) {
            $name = $this->mest_export_room_name;
            $param = [
                'medi_stock:id,medi_stock_name,medi_stock_code,is_active,is_delete,creator,modifier',
                'room:id,department_id',
                'room.execute_room:id,room_id,execute_room_name,execute_room_code',
                'room.department:id,department_name,department_code'
            ];
        } else {
            $name = $this->mest_export_room_name . '_' . $id;
            $param = [
                'medi_stock',
                'room',
                'room.execute_room',
                'room.department'
            ];
        }
        $data = get_cache_full($this->mest_export_room, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function medi_stock_with_room($id = null)
    {
        if ($id == null) {
            $name = $this->medi_stock_name . '_with_' . $this->room_name;
            $param = [
                'rooms:id,department_id,room_type_id',
                'rooms.execute_room:id,room_id,execute_room_name,execute_room_code'
            ];
        } else {
            $name = $this->medi_stock_name . '_' . $id . '_with_' . $this->room_name;
            $param = [
                'rooms',
                'rooms.execute_room',
                'rooms.department',
                'rooms.room_type'
            ];
        }
        $data = get_cache_full($this->medi_stock, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function room_with_medi_stock($id = null)
    {
        if ($id == null) {
            $name = $this->room_name . '_with_' . $this->medi_stock_name;
            $param = [
                'execute_room:id,room_id,execute_room_name,execute_room_code',
                'department:id,department_name,department_code',
                'room_type:id,room_type_name,room_type_code',
                'medi_stocks:id,medi_stock_name,medi_stock_code'
            ];
        } else {
            $name = $this->room_name . '_' . $id . '_with_' . $this->medi_stock_name;
            $param = [
                'execute_room',
                'department',
                'room_type',
                'medi_stocks'
            ];
        }
        $data = get_cache_full($this->room, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Exro Room
    public function exro_room($id = null)
    {
        if ($id == null) {
            $name = $this->exro_room_name;
            $param = [
                'room:id,department_id',
                'room.execute_room:id,room_id,execute_room_name',
                'room.department:id,department_name,department_code',
                'execute_room:id,room_id,execute_room_name,execute_room_code',
                'execute_room.room:id,department_id',
                'execute_room.room.department:id,department_name,department_code'
            ];
        } else {
            $name = $this->exro_room_name . '_' . $id;
            $param = [
                'room',
                'room.execute_room',
                'room.department',
                'execute_room',
                'execute_room.room',
                'execute_room.room.department'
            ];
        }
        $data = get_cache_full($this->exro_room, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function execute_room_with_room($id = null)
    {
        if ($id == null) {
            $name = $this->execute_room_name . '_with_' . $this->room_name;
            $param = [
                'rooms:id,department_id',
                'rooms.execute_room:id,room_id,execute_room_name',
                'rooms.department:id,department_name,department_code',
                'room.department:id,department_name,department_code',
            ];
        } else {
            $name = $this->execute_room_name . '_' . $id . '_with_' . $this->room_name;
            $param = [
                'rooms',
                'rooms.execute_room',
                'rooms.department',
                'room.department',
            ];
        }
        $data = get_cache_full($this->execute_room, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function room_with_execute_room($id = null)
    {
        if ($id == null) {
            $name = $this->room_name . '_with_' . $this->execute_room_name;
            $param = [
                'department:id,department_name,department_code',
                'execute_room:id,room_id,execute_room_name,execute_room_code',
                'execute_rooms:id,room_id,execute_room_name,execute_room_code',
                'execute_rooms.room:id,department_id',
                'execute_rooms.room.department:id,department_name,department_code',
            ];
        } else {
            $name = $this->room_name . '_' . $id . '_with_' . $this->execute_room_name;
            $param = [
                'department',
                'execute_room',
                'execute_rooms',
                'execute_room.rooms',
                'execute_room.room.departments',
            ];
        }
        $data = get_cache_full($this->room, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Patient Type Room
    public function patient_type_room($id = null)
    {
        if ($id == null) {
            $name = $this->patient_type_room_name;
            $param = [
                'room:id,department_id,room_type_id',
                'room.execute_room:id,room_id,execute_room_name,execute_room_code',
                'room.department:id,department_name,department_code',
                'room.room_type:id,room_type_name,room_type_code',
                'patient_type:id,patient_type_name,patient_type_code'
            ];
        } else {
            $name = $this->patient_type_room_name . '_' . $id;
            $param = [
                'room',
                'room.execute_room',
                'room.department',
                'room.room_type',
                'patient_type'
            ];
        }
        $data = get_cache_full($this->patient_type_room, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function patient_type_with_room($id = null)
    {
        if ($id == null) {
            $name = $this->patient_type_name . '_with_' . $this->room_name;
            $param = [
                'rooms:id,department_id,room_type_id',
                'rooms.execute_room:id,room_id,execute_room_name,execute_room_code',
                'rooms.department:id,department_name,department_code',
                'rooms.room_type:id,room_type_name,room_type_code',
            ];
        } else {
            $name = $this->patient_type_name . '_' . $id . '_with_' . $this->room_name;
            $param = [
                'rooms',
                'rooms.execute_room',
                'rooms.department',
                'rooms.room_type',
            ];
        }
        $data = get_cache_full($this->patient_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function room_with_patient_type($id = null)
    {
        if ($id == null) {
            $name = $this->room_name . '_with_' . $this->patient_type_name;
            $param = [
                'execute_room:id,room_id,execute_room_name,execute_room_code',
                'department:id,department_name,department_code',
                'room_type:id,room_type_name,room_type_code',
                'patient_types:id,patient_type_name,patient_type_code'
            ];
        } else {
            $name = $this->room_name . '_' . $id . '_with_' . $this->patient_type_name;
            $param = [
                'execute_room',
                'department',
                'room_type',
                'patient_types'
            ];
        }
        $data = get_cache_full($this->room, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Patient Type Allow
    public function patient_type_allow($id = null)
    {
        if ($id == null) {
            $name = $this->patient_type_allow_name;
            $param = [
                'patient_type',
                'patient_type_allow'
            ];
        } else {
            $name = $this->patient_type_allow_name . '_' . $id;
            $param = [
                'patient_type',
                'patient_type_allow'
            ];
        }
        $data = get_cache_full($this->patient_type_allow, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Position
    public function position($id = null)
    {
        if ($id == null) {
            $name = $this->position_name;
            $param = [];
        } else {
            $name = $this->position_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->position, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }


    /// Work Place
    public function work_place($id = null)
    {
        if ($id == null) {
            $name = $this->work_place_name;
            $param = [];
        } else {
            $name = $this->work_place_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->work_place, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Born Position
    public function born_position($id = null)
    {
        if ($id == null) {
            $name = $this->born_position_name;
            $param = [];
        } else {
            $name = $this->born_position_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->born_position, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Born Position
    public function patient_case($id = null)
    {
        if ($id == null) {
            $name = $this->patient_case_name;
            $param = [];
        } else {
            $name = $this->patient_case_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->patient_case, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }


    /// BHYT Whitelist


    /// BHYT Param
    public function bhyt_param($id = null)
    {
        if ($id == null) {
            $name = $this->bhyt_param_name;
            $param = [];
        } else {
            $name = $this->bhyt_param_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->bhyt_param, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// BHYT Blacklist
    public function bhyt_blacklist($id = null)
    {
        if ($id == null) {
            $name = $this->bhyt_blacklist_name;
            $param = [];
        } else {
            $name = $this->bhyt_blacklist_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->bhyt_blacklist, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Medicine Paty
    public function medicine_paty($id = null)
    {
        if ($id == null) {
            $name = $this->medicine_paty_name;
            $param = [
                'medicine',
                'medicine.medicine_type:id,medicine_type_name,medicine_type_code',
                'patient_type'
            ];
        } else {
            $name = $this->medicine_paty_name . '_' . $id;
            $param = [
                'medicine',
                'medicine.medicine_type',
                'patient_type'
            ];
        }
        $data = get_cache_full($this->medicine_paty, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Accident Body Part
    public function accident_body_part($id = null)
    {
        if ($id == null) {
            $name = $this->accident_body_part_name;
            $param = [];
        } else {
            $name = $this->accident_body_part_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->accident_body_part, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Preparations Blood
    public function preparations_blood($id = null)
    {
        if ($id == null) {
            $name = $this->preparations_blood_name;
            $param = [];
        } else {
            $name = $this->preparations_blood_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->preparations_blood, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Contraindication
    public function contraindication($id = null)
    {
        if ($id == null) {
            $name = $this->contraindication_name;
            $param = [];
        } else {
            $name = $this->contraindication_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->contraindication, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Dosage Form
    public function dosage_form($id = null)
    {
        if ($id == null) {
            $name = $this->dosage_form_name;
            $param = [];
        } else {
            $name = $this->dosage_form_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->dosage_form, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Accident Location
    public function accident_location($id = null)
    {
        if ($id == null) {
            $name = $this->accident_location_name;
            $param = [];
        } else {
            $name = $this->accident_location_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->accident_location, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// License Class
    public function license_class($id = null)
    {
        if ($id == null) {
            $name = $this->license_class_name;
            $param = [];
        } else {
            $name = $this->license_class_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->license_class, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Manufacturer
    public function manufacturer($id = null)
    {
        if ($id == null) {
            $name = $this->manufacturer_name;
            $param = [];
        } else {
            $name = $this->manufacturer_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->manufacturer, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// ICD
    public function icd($id = null)
    {
        if ($id == null) {
            $name = $this->icd_name;
            $param = [
                'icd_group',
                'icd_chapter',
                'gender',
                'age_type'
            ];
        } else {
            $name = $this->icd_name . '_' . $id;
            $param = [
                'icd_group',
                'icd_chapter',
                'gender',
                'age_type'
            ];
        }
        $data = get_cache_full($this->icd, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Medi Record Type
    public function medi_record_type($id = null)
    {
        if ($id == null) {
            $name = $this->medi_record_type_name;
            $param = [];
        } else {
            $name = $this->medi_record_type_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->medi_record_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// File Type
    public function file_type($id = null)
    {
        if ($id == null) {
            $name = $this->file_type_name;
            $param = [];
        } else {
            $name = $this->file_type_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->file_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Treatment End Type
    public function treatment_end_type($id = null)
    {
        if ($id == null) {
            $name = $this->treatment_end_type_name;
            $param = [];
        } else {
            $name = $this->treatment_end_type_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->treatment_end_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Tran Pati Tech
    public function tran_pati_tech($id = null)
    {
        if ($id == null) {
            $name = $this->tran_pati_tech_name;
            $param = [];
        } else {
            $name = $this->tran_pati_tech_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->tran_pati_tech, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Debate Reason
    public function debate_reason($id = null)
    {
        if ($id == null) {
            $name = $this->debate_reason_name;
            $param = [
                'debates:id,debate_reason_id,icd_name,icd_code,icd_sub_code'
            ];
        } else {
            $name = $this->debate_reason_name . '_' . $id;
            $param = [
                'debates'
            ];
        }
        $data = get_cache_full($this->debate_reason, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Cancel Reason
    public function cancel_reason($id = null)
    {
        if ($id == null) {
            $name = $this->cancel_reason_name;
            $param = [];
        } else {
            $name = $this->cancel_reason_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->cancel_reason, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Interaction Reason
    public function interaction_reason($id = null)
    {
        if ($id == null) {
            $name = $this->interaction_reason_name;
            $param = [];
        } else {
            $name = $this->interaction_reason_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->interaction_reason, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Unlimit Reason
    public function unlimit_reason($id = null)
    {
        if ($id == null) {
            $name = $this->unlimit_reason_name;
            $param = [];
        } else {
            $name = $this->unlimit_reason_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->unlimit_reason, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Hospitalize Reason
    public function hospitalize_reason($id = null)
    {
        if ($id == null) {
            $name = $this->hospitalize_reason_name;
            $param = [];
        } else {
            $name = $this->hospitalize_reason_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->hospitalize_reason, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Exp Mest Reason
    public function exp_mest_reason($id = null)
    {
        if ($id == null) {
            $name = $this->exp_mest_reason_name;
            $param = [];
        } else {
            $name = $this->exp_mest_reason_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->exp_mest_reason, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }


    /// Career Title
    public function career_title($id = null)
    {
        if ($id == null) {
            $name = $this->career_title_name;
            $param = [];
        } else {
            $name = $this->career_title_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->career_title, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Accident Hurt Type
    public function accident_hurt_type($id = null)
    {
        if ($id == null) {
            $name = $this->accident_hurt_type_name;
            $param = [];
        } else {
            $name = $this->accident_hurt_type_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->accident_hurt_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }


    /// Supplier
    public function supplier($id = null)
    {
        if ($id == null) {
            $name = $this->supplier_name;
            $param = [];
        } else {
            $name = $this->supplier_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->supplier, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Processing
    public function processing_method($id = null)
    {
        if ($id == null) {
            $name = $this->processing_method_name;
            $param = [];
        } else {
            $name = $this->processing_method_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->processing_method, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Death Within
    public function death_within($id = null)
    {
        if ($id == null) {
            $name = $this->death_within_name;
            $param = [];
        } else {
            $name = $this->death_within_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->death_within, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Location Treatment
    public function location_treatment($id = null)
    {
        if ($id == null) {
            $name = $this->location_store_name;
            $param = [];
        } else {
            $name = $this->location_store_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->location_store, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Accident Care
    public function accident_care($id = null)
    {
        if ($id == null) {
            $name = $this->accident_care_name;
            $param = [];
        } else {
            $name = $this->accident_care_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->accident_care, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Pttt Table
    public function pttt_table($id = null)
    {
        if ($id == null) {
            $name = $this->pttt_table_name;
            $param = [
                'execute_room:id,execute_room_name,execute_room_code'
            ];
        } else {
            $name = $this->pttt_table_name . '_' . $id;
            $param = [
                'execute_room'
            ];
        }
        $data = get_cache_full($this->pttt_table, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }
    



    /// Emotionless Method
    public function emotionless_method($id = null)
    {
        if ($id == null) {
            $name = $this->emotionless_method_name;
            $param = [];
        } else {
            $name = $this->emotionless_method_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->emotionless_method, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Pttt Catastrophe
    public function pttt_catastrophe($id = null)
    {
        if ($id == null) {
            $name = $this->pttt_catastrophe_name;
            $param = [];
        } else {
            $name = $this->pttt_catastrophe_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->pttt_catastrophe, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Pttt Condition
    public function pttt_condition($id = null)
    {
        if ($id == null) {
            $name = $this->pttt_condition_name;
            $param = [];
        } else {
            $name = $this->pttt_condition_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->pttt_condition, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Awareness
    public function awareness($id = null)
    {
        if ($id == null) {
            $name = $this->awareness_name;
            $param = [];
        } else {
            $name = $this->awareness_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->awareness, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Medicine Line
    public function medicine_line($id = null)
    {
        if ($id == null) {
            $name = $this->medicine_line_name;
            $param = [];
        } else {
            $name = $this->medicine_line_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->medicine_line, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Blood Volume
    public function blood_volume($id = null)
    {
        if ($id == null) {
            $name = $this->blood_volume_name;
            $param = [];
        } else {
            $name = $this->blood_volume_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->blood_volume, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Medicine Use Form
    public function medicine_use_form($id = null)
    {
        if ($id == null) {
            $name = $this->medicine_use_form_name;
            $param = [];
        } else {
            $name = $this->medicine_use_form_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->medicine_use_form, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Bid Type
    public function bid_type($id = null)
    {
        if ($id == null) {
            $name = $this->bid_type_name;
            $param = [];
        } else {
            $name = $this->bid_type_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->bid_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Medicine Type Active Ingredient
    public function medicine_type_acin($id = null)
    {
        if ($id == null) {
            $name = $this->medicine_type_acin_name;
            $param = [
                'medicine_type:id,medicine_type_name,medicine_type_code',
                'active_ingredient:id,active_ingredient_name,active_ingredient_code'
            ];
        } else {
            $name = $this->medicine_type_acin_name . '_' . $id;
            $param = [
                'medicine_type',
                'active_ingredient'
            ];
        }
        $data = get_cache_full($this->medicine_type_acin, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function medicine_type_with_active_ingredient($id = null)
    {
        if ($id == null) {
            $name = $this->medicine_type_name . '_with_' . $this->active_ingredient_name;
            $param = [
                'active_ingredients:id,active_ingredient_name,active_ingredient_code'
            ];
        } else {
            $name = $this->medicine_type_name . '_' . $id . '_with_' . $this->active_ingredient_name;
            $param = [
                'active_ingredients'
            ];
        }
        $data = get_cache_full($this->medicine_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function active_ingredient_with_medicine_type($id = null)
    {
        if ($id == null) {
            $name = $this->active_ingredient_name . '_with_' . $this->medicine_type_name;
            $param = [
                'medicine_types:id,medicine_type_name,medicine_type_code'
            ];
        } else {
            $name = $this->active_ingredient_name . '_' . $id . '_with_' . $this->medicine_type_name;
            $param = [
                'medicine_types'
            ];
        }
        $data = get_cache_full($this->active_ingredient, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Atc Group
    public function atc_group($id = null)
    {
        if ($id == null) {
            $name = $this->atc_group_name;
            $param = [];
        } else {
            $name = $this->atc_group_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->atc_group, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Blood Group
    public function blood_group($id = null)
    {
        if ($id == null) {
            $name = $this->blood_group_name;
            $param = [];
        } else {
            $name = $this->blood_group_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->blood_group, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }


    /// Medicine Group
    public function medicine_group($id = null)
    {
        if ($id == null) {
            $name = $this->medicine_group_name;
            $param = [];
        } else {
            $name = $this->medicine_group_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->medicine_group, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Test Index
    public function test_index($id = null)
    {
        if ($id == null) {
            $name = $this->test_index_name;
            $param = [
                'test_service_type:id,service_name,service_code',
                'test_index_unit:id,test_index_unit_name,test_index_unit_code',
                'test_index_group:id,test_index_group_name,test_index_group_code',
                'material_type:id,material_type_name,material_type_code'
            ];
        } else {
            $name = $this->test_index_name . '_' . $id;
            $param = [
                'test_service_type',
                'test_index_unit',
                'test_index_group',
                'material_type'
            ];
        }
        $data = get_cache_full($this->test_index, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Test Index Unit
    public function test_index_unit($id = null)
    {
        if ($id == null) {
            $name = $this->test_index_unit_name;
            $param = [];
        } else {
            $name = $this->test_index_unit_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->test_index_unit, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }



    /// User Room
    public function user_with_room()
    {
        // Khai báo các biến lấy từ json param
        $request_loginname = $this->param_request['ApiData']['LOGINNAME'] ?? null;

        // Khai báo các trường cần select
        $select = [
            "id",
            "create_Time",
            "modify_Time",
            "creator",
            "modifier",
            "app_Creator",
            "app_Modifier",
            "is_Active",
            "is_Delete",
            "group_Code",
            "loginname",
            "room_Id",
        ];

        // Khởi tạo, gán các model vào các biến 
        $model = $this->user_room::select($select);

        // Kiểm tra các điều kiện từ json param
        if ($request_loginname != null) {
            $model->where('loginname', $request_loginname);
        }

        // Khai báo các bảng liên kết dùng cho with()
        $param = [
            'room:id,department_id,room_type_id',
            'room.execute_room:id,room_id,execute_room_name,execute_room_code',
            'room.room_type:id,room_type_name,room_type_code',
            'room.department:id,branch_id,department_name,department_code',
            'room.department.branch:id,branch_name,branch_code',
        ];

        // Lấy dữ liệu
        $count = $model->count();
        $data = $model->skip($this->start)->take($this->limit)->with($param)->get();
        // Trả về dữ liệu
        return response()->json([
            'data' =>
            $data,
            'Param' => [
                'Start' => $this->start,
                'Limit' => $this->limit,
                'Count' => $count
            ]
        ], 200);
    }

    /// Debate
  

    /// Debate User

    /// Debate Ekip User
    /// Debate Type
    public function debate_type($id = null)
    {
        if ($id == null) {
            $name = $this->debate_type_name;
            $param = [
                'debates:id,debate_type_id,icd_name,icd_code,icd_sub_code'
            ];
        } else {
            $name = $this->debate_type_name . '_' . $id;
            $param = [
                'debates'
            ];
        }
        $data = get_cache_full($this->debate_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }




 

  
  
}
