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








    // /// Hospitalize Reason
    // public function hospitalize_reason($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->hospitalize_reason_name;
    //         $param = [];
    //     } else {
    //         $name = $this->hospitalize_reason_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->hospitalize_reason, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Exp Mest Reason
    // public function exp_mest_reason($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->exp_mest_reason_name;
    //         $param = [];
    //     } else {
    //         $name = $this->exp_mest_reason_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->exp_mest_reason, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }


    // /// Career Title
    // public function career_title($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->career_title_name;
    //         $param = [];
    //     } else {
    //         $name = $this->career_title_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->career_title, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Accident Hurt Type
    // public function accident_hurt_type($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->accident_hurt_type_name;
    //         $param = [];
    //     } else {
    //         $name = $this->accident_hurt_type_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->accident_hurt_type, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }


    // /// Supplier
    // public function supplier($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->supplier_name;
    //         $param = [];
    //     } else {
    //         $name = $this->supplier_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->supplier, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Processing
    // public function processing_method($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->processing_method_name;
    //         $param = [];
    //     } else {
    //         $name = $this->processing_method_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->processing_method, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Death Within
    // public function death_within($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->death_within_name;
    //         $param = [];
    //     } else {
    //         $name = $this->death_within_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->death_within, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Location Treatment
    // public function location_treatment($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->location_store_name;
    //         $param = [];
    //     } else {
    //         $name = $this->location_store_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->location_store, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Accident Care
    // public function accident_care($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->accident_care_name;
    //         $param = [];
    //     } else {
    //         $name = $this->accident_care_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->accident_care, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Pttt Table
    // public function pttt_table($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->pttt_table_name;
    //         $param = [
    //             'execute_room:id,execute_room_name,execute_room_code'
    //         ];
    //     } else {
    //         $name = $this->pttt_table_name . '_' . $id;
    //         $param = [
    //             'execute_room'
    //         ];
    //     }
    //     $data = get_cache_full($this->pttt_table, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
    



    // /// Emotionless Method
    // public function emotionless_method($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->emotionless_method_name;
    //         $param = [];
    //     } else {
    //         $name = $this->emotionless_method_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->emotionless_method, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Pttt Catastrophe
    // public function pttt_catastrophe($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->pttt_catastrophe_name;
    //         $param = [];
    //     } else {
    //         $name = $this->pttt_catastrophe_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->pttt_catastrophe, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Pttt Condition
    // public function pttt_condition($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->pttt_condition_name;
    //         $param = [];
    //     } else {
    //         $name = $this->pttt_condition_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->pttt_condition, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Awareness
    // public function awareness($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->awareness_name;
    //         $param = [];
    //     } else {
    //         $name = $this->awareness_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->awareness, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Medicine Line
    // public function medicine_line($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medicine_line_name;
    //         $param = [];
    //     } else {
    //         $name = $this->medicine_line_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->medicine_line, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Blood Volume
    // public function blood_volume($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->blood_volume_name;
    //         $param = [];
    //     } else {
    //         $name = $this->blood_volume_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->blood_volume, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Medicine Use Form
    // public function medicine_use_form($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medicine_use_form_name;
    //         $param = [];
    //     } else {
    //         $name = $this->medicine_use_form_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->medicine_use_form, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Bid Type
    // public function bid_type($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->bid_type_name;
    //         $param = [];
    //     } else {
    //         $name = $this->bid_type_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->bid_type, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Medicine Type Active Ingredient
    // public function medicine_type_acin($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medicine_type_acin_name;
    //         $param = [
    //             'medicine_type:id,medicine_type_name,medicine_type_code',
    //             'active_ingredient:id,active_ingredient_name,active_ingredient_code'
    //         ];
    //     } else {
    //         $name = $this->medicine_type_acin_name . '_' . $id;
    //         $param = [
    //             'medicine_type',
    //             'active_ingredient'
    //         ];
    //     }
    //     $data = get_cache_full($this->medicine_type_acin, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function medicine_type_with_active_ingredient($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medicine_type_name . '_with_' . $this->active_ingredient_name;
    //         $param = [
    //             'active_ingredients:id,active_ingredient_name,active_ingredient_code'
    //         ];
    //     } else {
    //         $name = $this->medicine_type_name . '_' . $id . '_with_' . $this->active_ingredient_name;
    //         $param = [
    //             'active_ingredients'
    //         ];
    //     }
    //     $data = get_cache_full($this->medicine_type, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function active_ingredient_with_medicine_type($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->active_ingredient_name . '_with_' . $this->medicine_type_name;
    //         $param = [
    //             'medicine_types:id,medicine_type_name,medicine_type_code'
    //         ];
    //     } else {
    //         $name = $this->active_ingredient_name . '_' . $id . '_with_' . $this->medicine_type_name;
    //         $param = [
    //             'medicine_types'
    //         ];
    //     }
    //     $data = get_cache_full($this->active_ingredient, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Atc Group
    // public function atc_group($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->atc_group_name;
    //         $param = [];
    //     } else {
    //         $name = $this->atc_group_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->atc_group, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Blood Group
    // public function blood_group($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->blood_group_name;
    //         $param = [];
    //     } else {
    //         $name = $this->blood_group_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->blood_group, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }


    // /// Medicine Group
    // public function medicine_group($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medicine_group_name;
    //         $param = [];
    //     } else {
    //         $name = $this->medicine_group_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->medicine_group, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Test Index
    // public function test_index($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->test_index_name;
    //         $param = [
    //             'test_service_type:id,service_name,service_code',
    //             'test_index_unit:id,test_index_unit_name,test_index_unit_code',
    //             'test_index_group:id,test_index_group_name,test_index_group_code',
    //             'material_type:id,material_type_name,material_type_code'
    //         ];
    //     } else {
    //         $name = $this->test_index_name . '_' . $id;
    //         $param = [
    //             'test_service_type',
    //             'test_index_unit',
    //             'test_index_group',
    //             'material_type'
    //         ];
    //     }
    //     $data = get_cache_full($this->test_index, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // /// Test Index Unit
    // public function test_index_unit($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->test_index_unit_name;
    //         $param = [];
    //     } else {
    //         $name = $this->test_index_unit_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->test_index_unit, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }



    // /// User Room


    // /// Debate
  

    // /// Debate User

    // /// Debate Ekip User
    // /// Debate Type
    // public function debate_type($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->debate_type_name;
    //         $param = [
    //             'debates:id,debate_type_id,icd_name,icd_code,icd_sub_code'
    //         ];
    //     } else {
    //         $name = $this->debate_type_name . '_' . $id;
    //         $param = [
    //             'debates'
    //         ];
    //     }
    //     $data = get_cache_full($this->debate_type, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

  
}
