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

    /// Machine
    public function machine()
    {
        $param = [
            'department:id,department_name',

        ];
        $data = get_cache_full($this->machine, $param, $this->machine_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function machine_id($id)
    {
        $data = get_cache($this->machine, $this->machine_name, $id, $this->time);
        $data1 = get_cache_1_n_with_ids($this->machine, "room", $this->machine_name, $id, $this->time);
        return response()->json(['data' => [
            'machine' => $data,
            'rooms' => $data1,
        ]], 200);
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


    /// Bed
    public function bed($id = null)
    {
        if ($id == null) {
            $name = $this->bed_name;
            $param = [
                'bed_type:id,bed_type_name',
                'bed_room:id,bed_room_name,room_id',
                'bed_room.room:id,department_id',
                'bed_room.room.department:id,department_name'
            ];
        } else {
            $name = $this->bed_name . '_' . $id;
            $param = [
                'bed_type',
                'bed_room',
                'bed_room.room',
                'bed_room.room.department'
            ];
        }
        $data = get_cache_full($this->bed, $param, $name, $id, $this->time);
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

    /// Bed Type List
    public function bed_type()
    {
        $data = get_cache($this->bed_type, $this->bed_type_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function bed_type_id($id)
    {
        $data = get_cache($this->bed_type, $this->bed_type_name, $id, $this->time);

        return response()->json(['data' => [
            'bed_type' => $data

        ]], 200);
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

    /// Employee User
    public function emp_user()
    {
        $param = [
            'department:id,department_name',
            'gender:id,gender_name',
            'career_title:id,career_title_name,career_title_code'
        ];
        $data = get_cache_full($this->emp_user, $param, $this->emp_user_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function emp_user_id($id)
    {
        $data = get_cache($this->emp_user, $this->emp_user_name, $id, $this->time);
        $data1 = get_cache_1_1($this->emp_user, "department", $this->emp_user_name, $id, $this->time);
        $data2 = get_cache_1_1($this->emp_user, "gender", $this->emp_user_name, $id, $this->time);
        $data3 = get_cache_1_1($this->emp_user, "branch", $this->emp_user_name, $id, $this->time);
        $data4 = get_cache_1_1($this->emp_user, "career_title", $this->emp_user_name, $id, $this->time);
        $data5 = get_cache_1_n_with_ids($this->emp_user, "default_medi_stock", $this->emp_user_name, $id, $this->time);

        return response()->json(['data' => [
            'emp_user' => $data,
            'department' => $data1,
            'genderr' => $data2,
            'branch' => $data3,
            'career_title' => $data4,
            'default_medi_stock' => $data5,

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

    /// Role
    public function role($id = null)
    {
        if ($id == null) {
            $name = $this->role_name;
            $param = [
                'modules:id,module_name'
            ];
        } else {
            $name = $this->role_name . '_' . $id;
            $param = [
                'modules'
            ];
        }
        $data = get_cache_full($this->role, $param, $name, $id, $this->time);
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


    /// Ethnic
    public function ethnic()
    {
        $data = get_cache($this->ethnic, $this->ethnic_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function ethnic_id($id)
    {
        $data = get_cache($this->module, $this->ethnic_name, $id, $this->time);
        return response()->json(['data' => [
            'ethnic' => $data
        ]], 200);
    }


    
    /// Priority Type
    public function priority_type()
    {
        $data = get_cache($this->priority_type, $this->priority_type_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function priority_type_id($id)
    {
        $data = get_cache($this->priority_type, $this->priority_type_name, $id, $this->time);
        return response()->json(['data' => [
            'patient_type' => $data
        ]], 200);
    }

    /// Career
    public function career()
    {
        $data = get_cache($this->career, $this->career_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function career_id($id)
    {
        $data = get_cache($this->career, $this->career_name, $id, $this->time);
        return response()->json(['data' => [
            'career' => $data
        ]], 200);
    }
    
    
    /// Religion
    public function religion()
    {
        $data = get_cache($this->religion, $this->religion_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function religion_id($id)
    {
        $data = get_cache($this->religion, $this->religion_name, $id, $this->time);
        return response()->json(['data' => [
            'religion' => $data
        ]], 200);
    }



    /// ServiceReq Type
    public function service_req_type()
    {
        $data = get_cache($this->service_req_type, $this->service_req_type_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function service_req_type_id($id)
    {
        $data = get_cache($this->service_req_type, $this->service_req_type_name, $id, $this->time);
        return response()->json(['data' => [
            'service_req_type' => $data,
        ]], 200);
    }

    /// ServiceReq Type
    public function ration_time()
    {
        $data = get_cache($this->ration_time, $this->ration_time_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function ration_time_id($id)
    {
        $data = get_cache($this->ration_time, $this->ration_time_name, $id, $this->time);
        return response()->json(['data' => [
            'ration_time' => $data,
        ]], 200);
    }

    /// ServiceReq Type
    public function relation_list()
    {
        $data = get_cache($this->relation_list, $this->relation_list_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function relation_list_id($id)
    {
        $data = get_cache($this->relation_list, $this->relation_list_name, $id, $this->time);
        return response()->json(['data' => [
            'relation_list' => $data,
        ]], 200);
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

    /// Sale Profit CFG
    public function sale_profit_cfg($id = null)
    {
        if ($id == null) {
            $name = $this->sale_profit_cfg_name;
            $param = [];
        } else {
            $name = $this->sale_profit_cfg_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->sale_profit_cfg, $param, $name, $id, $this->time);
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
    public function bhyt_whitelist($id = null)
    {
        if ($id == null) {
            $name = $this->bhyt_whitelist_name;
            $param = [
                'career'
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->bhyt_whitelist->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->bhyt_whitelist_name . '_' . $id;
            $param = [
                'career'
            ];
        }
        $data = get_cache_full($this->bhyt_whitelist, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }

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

    /// Service Req
    public function service_req_get_L_view($id = null, Request $request)
    {
        // Khai báo các biến từ json param
        $request_execute_room_id = $this->param_request['ApiData']['EXECUTE_ROOM_ID'] ?? null;
        $request_service_req_stt_ids = $this->param_request['ApiData']['SERVICE_REQ_STT_IDs'] ?? null;
        $request_not_in_service_req_type_ids = $this->param_request['ApiData']['NOT_IN_SERVICE_REQ_TYPE_IDs'] ?? null;
        $request_tdl_patient_type_ids = $this->param_request['ApiData']['TDL_PATIENT_TYPE_IDs'] ?? null;
        $request_intruction_date__equal = $this->param_request['ApiData']['INTRUCTION_DATE__EQUAL'] ?? null;
        $request_intruction_time_from = $this->param_request['ApiData']['INTRUCTION_TIME_FROM'] ?? null;
        $request_intruction_time_to = $this->param_request['ApiData']['INTRUCTION_TIME_TO'] ?? null;
        $request_has_execute = $this->param_request['ApiData']['HAS_EXECUTE'] ?? null;
        $request_is_not_ksk_requried_aproval__or__is_ksk_approve = $this->param_request['ApiData']['IS_NOT_KSK_REQURIED_APROVAL__OR__IS_KSK_APPROVE'] ?? null;
        $request_order_field = $this->param_request['ApiData']['ORDER_FIELD'] ?? null;
        $request_order_direction = $this->param_request['ApiData']['ORDER_DIRECTION'] ?? null;
        $request_order_field1 = $this->param_request['ApiData']['ORDER_FIELD1'] ?? null;
        $request_order_direction1 = $this->param_request['ApiData']['ORDER_DIRECTION1'] ?? null;
        $request_order_field2 = $this->param_request['ApiData']['ORDER_FIELD2'] ?? null;
        $request_order_direction2 = $this->param_request['ApiData']['ORDER_DIRECTION2'] ?? null;
        $request_order_field3 = $this->param_request['ApiData']['ORDER_FIELD3'] ?? null;
        $request_order_direction3 = $this->param_request['ApiData']['ORDER_DIRECTION3'] ?? null;
        // Kiểm tra xem User có quyền xem execute_room không
        if ($request_execute_room_id != null) {
            if (!view_service_req($request_execute_room_id, $request->bearerToken(), $this->time)) {
                return response()->json(['message' => '403'], 403);
            }
        }

        // Khai báo các trường cần select
        $select = [
            'id',
            'service_req_code',
            'tdl_patient_code',
            'tdl_patient_name',
            'tdl_patient_gender_name',
            'tdl_patient_dob',
            'tdl_patient_address',
            'treatment_id',
            'tdl_patient_avatar_url',
            'service_req_stt_id',
            'parent_id',
            'execute_room_id',
            'exe_service_module_id',
            'request_department_id',
            'tdl_treatment_code',
            'dhst_id',
            'priority',
            'request_room_id',
            'intruction_time',
            'num_order',
            'service_req_type_id',
            'tdl_hein_card_number',
            'tdl_treatment_type_id',
            'intruction_date',
            'execute_loginname',
            'execute_username',
            'tdl_patient_type_id',
            'is_not_in_debt',
            'is_no_execute',
            'vir_intruction_month',
            'has_child',
            'tdl_patient_phone',
            'resulting_time',
            'tdl_service_ids',
            'call_count',
            'tdl_patient_unsigned_name',
            'start_time',
            'note',
            'tdl_patient_id',
            'icd_code',
            'icd_name',
            'icd_sub_code',
            'icd_text',
            // 'order_time'
        ];

        // Khởi tạo model
        $model = $this->service_req::select($select);

        // Lọc theo điều kiện của json param
        if ($request_service_req_stt_ids != null) {
            $model->whereIn('service_req_stt_id', $request_service_req_stt_ids);
        }
        if ($request_not_in_service_req_type_ids != null) {
            $model->whereNotIn('service_req_stt_id', $request_not_in_service_req_type_ids);
        }
        if ($request_tdl_patient_type_ids != null) {
            $model->whereIn('tdl_patient_type_id', $request_tdl_patient_type_ids);
        }
        if ($request_intruction_date__equal != null) {
            $model->where('intruction_time', '=',  $request_intruction_date__equal);
        } else {
            if (($request_intruction_time_from != null) && ($request_intruction_time_to != null)) {
                $model->whereBetween('intruction_time', [$request_intruction_time_from, $request_intruction_time_to]);
            }
        }
        if ($request_service_req_stt_ids != null) {
            $model->whereIn('service_req_stt_id', $request_service_req_stt_ids);
        }
        if ($request_execute_room_id != null) {
            $model->where('execute_room_id', '=', $request_execute_room_id);
        }
        if ($request_has_execute != null) {
            $model->where('is_no_execute', '=', null);
        } else {
            $model->where('is_no_execute', '=', 1);
        }
        // if ($request_is_not_ksk_requried_aproval__or__is_ksk_approve) {
        //     $model->Where(function ($query) {
        //         $query->where('tdl_ksk_is_required_approval', '!=', null)
        //             ->orWhere('tdl_is_ksk_approve', '=', null);
        //     });
        // }
        if (($request_order_field != null) && ($request_order_direction != null)) {
            $model->orderBy($request_order_field, $request_order_direction);
        }
        if (($request_order_field1 != null) && ($request_order_direction1 != null)) {
            $model->orderBy($request_order_field1, $request_order_direction1);
        }
        if (($request_order_field2 != null) && ($request_order_direction2 != null)) {
            $model->orderBy($request_order_field2, $request_order_direction2);
        }
        if (($request_order_field3 != null) && ($request_order_direction3 != null)) {
            $model->orderBy($request_order_field3, $request_order_direction3);
        }

        // Khai báo các bảng liên kết
        $param = [];

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

    // Tracking
    public function tracking(Request $request)
    {
        // Khai báo các biến lấy từ json param
        $request_treatment_ids = $this->param_request['ApiData']['TREATMENT_IDs'] ?? null;
        $request_treatment_id = $this->param_request['ApiData']['TREATMENT_ID'] ?? null;
        $request_create_time_to = $this->param_request['ApiData']['CREATE_TIME_TO'] ?? null;
        $request_is_include_deleted = $this->param_request['ApiData']['IS_INCLUDE_DELETED'] ?? null;
        $request_order_field = $this->param_request['ApiData']['ORDER_FIELD'] ?? null;
        $request_order_direction = $this->param_request['ApiData']['ORDER_DIRECTION'] ?? null;

        // Khai báo các trường cần select
        $select = [
            'id',
            'create_time',
            'modify_time',
            'creator',
            'modifier',
            'app_creator',
            'app_modifier',
            'is_active',
            'is_delete',
            'treatment_id',
            'tracking_time',
            'icd_code',
            'icd_name',
            'department_id',
            'care_instruction',
            'room_id',
            'emr_document_stt_id',
            'content',
        ];

        // Khởi tạo, gán các model vào các biến 
        $model = $this->tracking::select($select);

        // Kiểm tra các điều kiện từ json param
        if ($request_treatment_ids != null) {
            $model->whereIn('treatment_id',  $request_treatment_ids);
        } else {
            if ($request_treatment_id != null) {
                $model->where('treatment_id', $request_treatment_id);
            }
        }
        if ($request_create_time_to != null) {
            $model->where('create_time', $request_create_time_to);
        }
        if (!$request_is_include_deleted) {
            $model->where('is_delete', 0);
        }
        if (($request_order_field != null) && ($request_order_direction != null)) {
            $model->orderBy($request_order_field, $request_order_direction);
        }

        // Khai báo các bảng liên kết dùng cho with()
        $param = [
            'cares',
            'debates',
            'Dhsts',
            'service_reqs'
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

    public function tracking_get_data(Request $request)
    {
        // Khai báo các biến lấy từ json param
        $request_treatment_id = $this->param_request['ApiData']['TreatmentId'] ?? null;
        $request_tracking_id = $this->param_request['ApiData']['TrackingId'] ?? null;
        $request_include_material = $this->param_request['ApiData']['IncludeMaterial'] ?? null;
        $request_include_blood_pres = $this->param_request['ApiData']['IncludeBloodPres'] ?? null;

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
        if ($request_include_material) {
            $exp_mest_material = $this->exp_mest_material::select($select_exp_mest_material);
        }
        $sere_serv = $this->sere_serv::select($select_sere_serv);
        $sere_serv_ext = $this->sere_serv_ext::select($select_sere_serv_ext);
        $dhst = $this->dhst::select($select_dhst);
        $care = $this->care::select();
        // Kiểm tra các điều kiện từ json param
        if (($request_treatment_id != null) || ($request_tracking_id != null)) {
            // Nếu có Tracking_id thì lấy Treatment_id từ Tracking_id
            if (($request_tracking_id != null)) {
                $request_treatment_id = $tracking::find($request_tracking_id)->treatment_id;
            }
            $treatment->find($request_treatment_id);
            $service_req->where('treatment_id', $request_treatment_id);
            $exp_mest->where('tdl_treatment_id', $request_treatment_id);
            $imp_mest->where('tdl_treatment_id', $request_treatment_id);
            $exp_mest_medicine->where('tdl_treatment_id', $request_treatment_id);
            if ($request_include_material) {
                $exp_mest_material->where('tdl_treatment_id', $request_treatment_id);
            }
            $sere_serv->where('tdl_treatment_id', $request_treatment_id);
            $sere_serv_ext->where('tdl_treatment_id', $request_treatment_id);
            $dhst->where('treatment_id', $request_treatment_id);
            $care->where('treatment_id', $request_treatment_id);
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
            'antibiotic_requests',
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
        if ($request_include_material) {
            $data_exp_mest_material = $exp_mest_material->with($param_exp_mest_material)->get();
            $data_imp_mest_material  = $this->treatment::find($request_treatment_id)->imp_mest_materials()->get();
        }
        if ($request_include_blood_pres) {
            $data_imp_mest_blood  = $this->treatment::find($request_treatment_id)->imp_mest_bloods()->get();
        }
        $data_imp_mest_medicine  = $this->treatment::find($request_treatment_id)->imp_mest_medicines()->get();
        $data_service_req_mety  = $this->treatment::find($request_treatment_id)->service_req_metys()->get();
        $data_service_req_maty  = $this->treatment::find($request_treatment_id)->service_req_matys()->get();
        $data_sere_serv_ration = $this->treatment::find($request_treatment_id)->sere_serv_rations()->get();
        $data_sere_serv = $sere_serv->with($param_sere_serv)->get();
        $data_sere_serv_ext = $sere_serv_ext->with($param_sere_serv_ext)->get();
        $data_exp_mest_blty_req = $this->treatment::find($request_treatment_id)->exp_mest_blty_reqs()->get();
        $data_dhst = $dhst->with($param_dhst)->get();
        $data_care = $care->with($param_care)->get();
        $data_care_detail  = $this->treatment::find($request_treatment_id)->care_details()->get();

        // Trả về dữ liệu
        return response()->json([
            'Data' => [
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
                'Param' => [
                    'TrackingId' => $request_tracking_id,
                    'TreatmentId' => $request_treatment_id,
                ]
            ]
        ], 200);
    }

    // Sere Serv
  
    // Patient Type Alter


    // Treatment
    public function treatment_get_L_view(Request $request)
    {
        // Khai báo các biến lấy từ json param
        $request_order_field = $this->param_request['ApiData']['ORDER_FIELD'] ?? null;
        $request_order_direction = $this->param_request['ApiData']['ORDER_DIRECTION'] ?? null;
        $request_key_word = $this->param_request['ApiData']['KEY_WORD'] ?? null;
        $request_patient_code__exact = $this->param_request['ApiData']['PATIENT_CODE__EXACT'] ?? null;
        // Khai báo các trường cần select
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

        // Khởi tạo, gán các model vào các biến 
        $model = $this->treatment::select($select);

        // Kiểm tra các điều kiện từ json param

        if (($request_order_field != null) && ($request_order_direction != null)) {
            $model->orderBy($request_order_field, $request_order_direction);
        }
        if ($request_patient_code__exact != null) {
            $model->where('tdl_patient_code', $request_patient_code__exact);
        } else {
            if ($request_key_word != null) {
                $model->where('tdl_patient_name', 'like', '%' . $request_key_word . '%');
            }
        }

        // Khai báo các bảng liên kết dùng cho with()
        $param = [];

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

    public function treatment_get_treatment_with_patient_type_info_sdo(Request $request)
    {
        // Khai báo các biến lấy từ json param
        $request_treatment_id = $this->param_request['ApiData']['TREATMENT_ID'] ?? null;
        $request_intruction_time = $this->param_request['ApiData']['INTRUCTION_TIME'] ?? null;
        // Khai báo các trường cần select
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
            "TREATMENT_CODE",
            "PATIENT_ID",
            "BRANCH_ID",
            "ICD_CODE",
            "ICD_NAME",
            "ICD_SUB_CODE",
            "ICD_TEXT",
            "IN_TIME",
            "IN_DATE",
            "CLINICAL_IN_TIME",
            "IN_CODE",
            "IN_ROOM_ID",
            "IN_DEPARTMENT_ID",
            "IN_LOGINNAME",
            "IN_USERNAME",
            "IN_TREATMENT_TYPE_ID",
            "IN_ICD_CODE",
            "IN_ICD_NAME",
            "IN_ICD_SUB_CODE",
            "IN_ICD_TEXT",
            "HOSPITALIZATION_REASON",
            "DOCTOR_LOGINNAME",
            "DOCTOR_USERNAME",
            "IS_CHRONIC",
            "JSON_PRINT_ID",
            "IS_EMERGENCY",
            "SUBCLINICAL_RESULT",
            "TDL_FIRST_EXAM_ROOM_ID",
            "TDL_TREATMENT_TYPE_ID",
            "TDL_PATIENT_TYPE_ID",
            "FUND_CUSTOMER_NAME",
            "TDL_PATIENT_CODE",
            "TDL_PATIENT_NAME",
            "TDL_PATIENT_FIRST_NAME",
            "TDL_PATIENT_LAST_NAME",
            "TDL_PATIENT_DOB",
            "TDL_PATIENT_ADDRESS",
            "TDL_PATIENT_GENDER_ID",
            "TDL_PATIENT_GENDER_NAME",
            "TDL_PATIENT_CAREER_NAME",
            "TDL_PATIENT_DISTRICT_CODE",
            "TDL_PATIENT_PROVINCE_CODE",
            "TDL_PATIENT_COMMUNE_CODE",
            "TDL_PATIENT_NATIONAL_NAME",
            "TDL_PATIENT_RELATIVE_TYPE",
            "TDL_PATIENT_RELATIVE_NAME",
            "DEPARTMENT_IDS",
            "CO_DEPARTMENT_IDS",
            "LAST_DEPARTMENT_ID",
            "TDL_PATIENT_PHONE",
            "IS_SYNC_EMR",
            "VIR_IN_MONTH",
            "IN_CODE_SEED_CODE",
            "VIR_IN_YEAR",
            "EMR_COVER_TYPE_ID",
            "HOSPITALIZE_DEPARTMENT_ID",
            "TDL_PATIENT_RELATIVE_MOBILE",
            "TDL_PATIENT_NATIONAL_CODE",
            "TDL_PATIENT_PROVINCE_NAME",
            "TDL_PATIENT_DISTRICT_NAME",
            "TDL_PATIENT_COMMUNE_NAME",
            "TDL_PATIENT_UNSIGNED_NAME",
            "TDL_PATIENT_ETHNIC_NAME",
            "IS_TUBERCULOSIS",
            "TDL_HEIN_MEDI_ORG_CODE",
            "TDL_HEIN_CARD_NUMBER",
            "TDL_HEIN_CARD_FROM_TIME",
            "TDL_HEIN_CARD_TO_TIME",
        ];

        // Khởi tạo, gán các model vào các biến 
        $model = $this->treatment::select($select);

        // Kiểm tra các điều kiện từ json param
        if ($request_treatment_id != null) {
            $model->where('id', $request_treatment_id);
        }
        if ($request_intruction_time != null) {
            $model->where('in_time', $request_intruction_time);
        }
        // Khai báo các bảng liên kết dùng cho with()
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

    // Treatment Bed Room
    public function treatment_bed_room_get_L_view(Request $request)
    {
        // Khai báo các biến lấy từ json param
        $request_is_in_room = $this->param_request['ApiData']['IS_IN_ROOM'] ?? null;
        $request_add_time_to = $this->param_request['ApiData']['ADD_TIME_TO'] ?? null;
        $request_add_time_from = $this->param_request['ApiData']['ADD_TIME_FROM'] ?? null;
        $request_bed_room_ids = $this->param_request['ApiData']['BED_ROOM_IDs'] ?? null;
        $request_order_field = $this->param_request['ApiData']['ORDER_FIELD'] ?? null;
        $request_order_direction = $this->param_request['ApiData']['ORDER_DIRECTION'] ?? null;
        $request_is_include_deleted = $this->param_request['ApiData']['IS_INCLUDE_DELETED'] ?? null;

        // Khai báo các trường cần select
        $select = [
            "his_treatment_bed_room.ID",
            "his_treatment_bed_room.TREATMENT_ID",
            "his_treatment_bed_room.CO_TREATMENT_ID",
            "his_treatment_bed_room.ADD_TIME",
            "his_treatment_bed_room.BED_ROOM_ID",
            "his_treatment.TDL_PATIENT_FIRST_NAME"
        ];

        // Khởi tạo, gán các model vào các biến 
        $model = $this->treatment_bed_room::join('his_treatment', 'his_treatment_bed_room.treatment_id', '=', 'his_treatment.id')->select($select);

        // Kiểm tra các điều kiện từ json param
        if ($request_bed_room_ids != null) {
            $model->whereIn('his_treatment_bed_room.bed_room_id', $request_bed_room_ids);
        }
        if ($request_is_in_room) {
            if ($request_add_time_from != null) {
                $model->where('his_treatment_bed_room.add_time', '>=', $request_add_time_from);
            }
        } else {
            if (($request_add_time_from != null) && ($request_add_time_to != null)) {
                $model->whereBetween('his_treatment_bed_room.add_time', [$request_add_time_from, $request_add_time_to]);
            }
        }
        if (!$request_is_include_deleted) {
            $model->where('his_treatment_bed_room.is_delete', 0);
        }
        if (($request_order_field != null) && ($request_order_direction != null)) {
            $model->orderBy('his_treatment.' . $request_order_field, $request_order_direction);
        }

        // Khai báo các bảng liên kết dùng cho with()
        $param = [
            'treatment'
            => function ($query) use ($request_order_field, $request_order_direction) {
                $query->select('id', 'tdl_patient_type_id', 'PATIENT_ID', 'TREATMENT_CODE', 'TDL_PATIENT_LAST_NAME', 'TDL_PATIENT_NAME', 'TDL_PATIENT_DOB', 'TDL_PATIENT_GENDER_NAME', 'TDL_PATIENT_CODE', 'TDL_PATIENT_ADDRESS', 'TDL_HEIN_CARD_NUMBER', 'TDL_HEIN_MEDI_ORG_CODE', 'ICD_CODE', 'ICD_NAME', 'ICD_TEXT', 'ICD_SUB_CODE', 'TDL_PATIENT_GENDER_ID', 'TDL_HEIN_MEDI_ORG_NAME', 'TDL_TREATMENT_TYPE_ID', 'EMR_COVER_TYPE_ID', 'CLINICAL_IN_TIME', 'CO_TREAT_DEPARTMENT_IDS', 'LAST_DEPARTMENT_ID', 'TDL_PATIENT_UNSIGNED_NAME', 'TREATMENT_METHOD', 'TDL_HEIN_CARD_FROM_TIME', 'TDL_HEIN_CARD_TO_TIME')
                    ->orderBy($request_order_field, $request_order_direction);
            },
            'treatment.patient_type:id,patient_type_code,patient_type_name',
            'treatment.last_department:id,department_code,department_name',
            'treatment.patient:id,note',
            'bed_room:id,bed_room_name'
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

    // DHST
   

    // Sere Serv Ext
    public function sere_serv_ext(Request $request)
    {
        // Khai báo các biến lấy từ json param
        $request_sere_serv_id = $this->param_request['ApiData']['SERE_SERV_ID'] ?? null;
        $request_sere_serv_ids = $this->param_request['ApiData']['SERE_SERV_IDs'] ?? null;
        $request_is_include_deleted = $this->param_request['ApiData']['IS_INCLUDE_DELETED'] ?? null;
        $request_is_active = $this->param_request['ApiData']['IS_ACTIVE'] ?? null;

        // Khai báo các trường cần select
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

        // Khởi tạo, gán các model vào các biến 
        $model = $this->sere_serv_ext::select($select);

        // Kiểm tra các điều kiện từ json param
        if ($request_sere_serv_id != null) {
            $model->where('sere_serv_id', $request_sere_serv_id);
        } else {
            if ($request_sere_serv_ids != null) {
                $model->whereIn('sere_serv_id', $request_sere_serv_ids);
            }
        }
        if (!$request_is_include_deleted) {
            $model->where('is_delete', 0);
        }
        if ($request_is_active) {
            $model->where('is_active', 1);
        } else {
            $model->where('is_active', 0);
        }

        // Khai báo các bảng liên kết dùng cho with()
        $param = [];

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

    // Sere Serv Tein
    public function sere_serv_tein_get(Request $request)
    {
        // Khai báo các biến lấy từ json param
        $request_test_index_ids = $this->param_request['ApiData']['TEST_INDEX_IDs'] ?? null;
        $request_tdl_treatment_id = $this->param_request['ApiData']['TDL_TREATMENT_ID'] ?? null;
        $request_is_include_deleted = $this->param_request['ApiData']['IS_INCLUDE_DELETED'] ?? null;

        // Khai báo các trường cần select
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
            "SERE_SERV_ID",
            "TEST_INDEX_ID",
            "VALUE",
            "RESULT_CODE",
            "TDL_TREATMENT_ID",
            "TDL_SERVICE_REQ_ID",
            "RESULT_DESCRIPTION",
        ];

        // Khởi tạo, gán các model vào các biến 
        $model = $this->sere_serv_tein::select($select);

        // Kiểm tra các điều kiện từ json param
        if ($request_test_index_ids != null) {
            $model->whereIn('test_index_id', $request_test_index_ids);
        }
        if ($request_tdl_treatment_id != null) {
            $model->where('tdl_treatment_id', $request_tdl_treatment_id);
        }
        if (!$request_is_include_deleted) {
            $model->where('is_delete', 0);
        }

        // Khai báo các bảng liên kết dùng cho with()
        $param = [];

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

    public function sere_serv_tein_get_view(Request $request)
    {
        // Khai báo các biến lấy từ json param
        $request_sere_serv_ids = $this->param_request['ApiData']['SERE_SERV_IDs'] ?? null;
        $request_is_include_deleted = $this->param_request['ApiData']['IS_INCLUDE_DELETED'] ?? null;
        $request_is_active = $this->param_request['ApiData']['IS_ACTIVE'] ?? null;

        // Khai báo các trường cần select
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
            "SERE_SERV_ID",
            "TEST_INDEX_ID",
            "VALUE",
            "TDL_TREATMENT_ID",
            "MACHINE_ID",
            "NOTE",
            "LEAVEN",
            "TDL_SERVICE_REQ_ID",
        ];

        // Khởi tạo, gán các model vào các biến 
        $model = $this->sere_serv_tein::select($select);

        // Kiểm tra các điều kiện từ json param
        if ($request_sere_serv_ids != null) {
            $model->whereIn('sere_serv_id', $request_sere_serv_ids);
        }
        if (!$request_is_include_deleted) {
            $model->where('is_delete', 0);
        }
        if ($request_is_active) {
            $model->where('is_active', 1);
        } else {
            $model->where('is_active', 0);
        }

        // Khai báo các bảng liên kết dùng cho with()
        $param = [
            'machine:id,machine_group_code,source_code,serial_number,MACHINE_NAME,MACHINE_CODE',
            'test_index:id,test_index_unit_id,TEST_INDEX_NAME,TEST_INDEX_CODE,IS_NOT_SHOW_SERVICE',
            'test_index.test_index_unit:id,TEST_INDEX_UNIT_NAME,TEST_INDEX_UNIT_CODE',
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
}
