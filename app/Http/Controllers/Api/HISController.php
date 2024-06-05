<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\HIS\Department;
use App\Models\HIS\BedRoom;
use App\Models\HIS\ExecuteRoom;
use App\Models\HIS\Room;
use App\Models\HIS\Speciality;
use App\Models\HIS\TreatmentType;
use App\Models\HIS\MediOrg;
use App\Models\HIS\Branch;
use App\Models\SDA\District;
use App\Models\HIS\MediStock;
use App\Models\HIS\ReceptionRoom;
use App\Models\HIS\Area;
use App\Models\HIS\Refectory;
use App\Models\HIS\ExecuteGroup;
use App\Models\HIS\CashierRoom;
use App\Models\SDA\National;
use App\Models\SDA\Province;
use App\Models\HIS\DataStore;
use App\Models\HIS\ExecuteRole;
use App\Models\SDA\Commune;
use App\Models\HIS\Service;
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
use App\Models\HIS\PatientClassify;
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

class HISController extends Controller
{
    protected $time;
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
    public function __construct()
    {
        $this->time = now()->addMinutes(1440);
        $this->department = new Department();
        $this->bed_room = new BedRoom();
        $this->execute_room = new ExecuteRoom();
        $this->room = new Room();
        $this->speciality = new Speciality();
        $this->treatment_type = new TreatmentType();
        $this->medi_org = new MediOrg();
        $this->branch = new Branch();
        $this->district = new District();
        $this->medi_stock = new MediStock();
        $this->reception_room = new ReceptionRoom();
        $this->area = new Area();
        $this->refectory = new Refectory();
        $this->execute_group = new ExecuteGroup();
        $this->cashier_room = new CashierRoom();
        $this->national = new National();
        $this->province = new Province();
        $this->data_store = new DataStore();
        $this->execute_role = new ExecuteRole();
        $this->commune = new Commune();
        $this->service = new Service();
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
        $this->patient_classify = new PatientClassify();
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
    }

    /// Department
    public function department()
    {
        $param = [
            'branch:id,branch_name,branch_code',
        ];
        $data = get_cache_full($this->department, $param, $this->department_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function department_id($id)
    {
        $data = get_cache($this->department, $this->department_name, $id, $this->time);
        $data1 = get_cache_1_1($this->department, "branch", $this->department_name, $id, $this->time);
        $data2 = get_cache_1_n_with_ids($this->department, "allow_treatment_type", $this->department_name, $id, $this->time);
        $data3 = get_cache_1_1_1($this->department, "room.default_instr_patient_type", $this->department_name, $id, $this->time);
        $data4 = get_cache_1_1($this->department, "req_surg_treatment_type", $this->department_name, $id, $this->time);
        return response()->json(['data' => [
            'department' => $data,
            'branch' => $data1,
            'allow_treatment_type' => $data2,
            'default_instr_patient_type' => $data3,
            'req_surg_treatment_type' => $data4
        ]], 200);
    }

    /// Bed Room
    public function bed_room()
    {
        $param = [
            'room:id,department_id,speciality_id,default_cashier_room_id,default_instr_patient_type_id',
            'room.department:id,department_name,department_code',
            'room.department.area:id,area_name',
            'room.speciality:id,speciality_name,speciality_code',
            'room.default_cashier_room:id,cashier_room_name',
            'room.default_instr_patient_type:id,patient_type_name',

        ];
        $data = get_cache_full($this->bed_room, $param, $this->bed_room_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function bed_room_id($id)
    {
        $data = get_cache($this->bed_room, $this->bed_room_name, $id, $this->time);
        $data1 = get_cache_1_1_1($this->bed_room, "room.department", $this->bed_room_name, $id, $this->time);
        $data2 = get_cache_1_1_1_1($this->bed_room, "room.department.area", $this->bed_room_name, $id, $this->time);
        $data3 = get_cache_1_1_1($this->bed_room, "room.speciality", $this->bed_room_name, $id, $this->time);
        $data4 = get_cache_1_n_with_ids($this->bed_room, "treatment_type", $this->bed_room_name, $id, $this->time);
        $data5 = get_cache_1_1_1($this->bed_room, "room.default_cashier_room", $this->bed_room_name, $id, $this->time);
        $data6 = get_cache_1_1_1($this->bed_room, "room.default_instr_patient_type", $this->bed_room_name, $id, $this->time);
        return response()->json(['data' => [
            'bed_room' => $data,
            'department' => $data1,
            'area' => $data2,
            'speciality' => $data3,
            'treatment_type' => $data4,
            'default_cashier_room' => $data5,
            'default_instr_patient_type' => $data6

        ]], 200);
    }

    /// Execute Room
    public function execute_room()
    {
        $param = [
            'room:id,department_id',
            'room.department:id,department_name,department_code',
            'room.department.area:id,area_name,area_code'
        ];
        $data = get_cache_full($this->execute_room, $param, $this->execute_room_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function execute_room_id($id)
    {
        $data = get_cache($this->execute_room, $this->execute_room_name, $id, $this->time);
        $data1 = get_cache_1_1($this->execute_room, "room", $this->execute_room_name, $id, $this->time);
        $data2 = get_cache_1_1_1($this->execute_room, "room.department", $this->execute_room_name, $id, $this->time);
        $data3 = get_cache_1_1_1($this->execute_room, "room.room_type", $this->execute_room_name, $id, $this->time);
        $data4 = get_cache_1_1_1($this->execute_room, "room.speciality", $this->execute_room_name, $id, $this->time);
        $data5 = get_cache_1_1_1_1($this->execute_room, "room.department.area", $this->execute_room_name, $id, $this->time);
        $data6 = get_cache_1_1_1($this->execute_room, "room.default_cashier_room", $this->execute_room_name, $id, $this->time);
        $data7 = get_cache_1_1_1($this->execute_room, "room.default_instr_patient_type", $this->execute_room_name, $id, $this->time);
        $data8 = get_cache_1_1_1($this->execute_room, "room.default_service", $this->execute_room_name, $id, $this->time);
        $data9 = get_cache_1_1_n_with_ids($this->execute_room, "room.default_drug_store", $this->execute_room_name, $id, $this->time);
        $data10 = get_cache_1_1_1($this->execute_room, "room.deposit_account_book", $this->execute_room_name, $id, $this->time);
        $data11 = get_cache_1_1_1($this->execute_room, "room.bill_account_book", $this->execute_room_name, $id, $this->time);

        return response()->json(['data' => [
            'execute_room' => $data,
            'room' => $data1,
            'department' => $data2,
            'room_type' => $data3,
            'speciality' => $data4,
            'area' => $data5,
            'default_cashier_room' => $data6,
            'default_instr_patient_type' => $data7,
            'default_service' => $data8,
            'default_drug_stores' => $data9,
            'deposit_account_book' => $data10,
            'bill_account_book' => $data11
        ]], 200);
    }

    /// Speciality     
    public function speciality()
    {
        $data = get_cache($this->speciality, $this->speciality_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function speciality_id($id)
    {
        $data = get_cache($this->speciality, $this->speciality_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Treatment Type     
    public function treatment_type()
    {
        $data = get_cache($this->treatment_type, $this->treatment_type_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function treatment_type_id($id)
    {
        $data = get_cache($this->treatment_type, $this->treatment_type_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Medi Org
    public function medi_org()
    {
        $data = get_cache($this->medi_org, $this->medi_org_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function medi_org_id($id)
    {
        $data = get_cache($this->medi_org, $this->medi_org_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Branch
    public function branch()
    {
        $data = get_cache($this->branch, $this->branch_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function branch_id($id)
    {
        $data = get_cache($this->branch, $this->branch_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// District
    public function district()
    {
        $param = [
            'province:id,province_name,province_code',
        ];
        $data = get_cache_full($this->district, $param, $this->district_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function district_id($id)
    {
        $data = get_cache($this->district, $this->district_name, $id, $this->time);
        $data1 = get_cache_1_1($this->district, "province", $this->district_name, $id, $this->time);
        return response()->json(['data' => [
            'district' => $data,
            'province' => $data1
        ]], 200);
    }

    /// Medi Stock
    public function medi_stock()
    {
        $param = [
            'room:id,department_id,room_type_id',
            'room.department:id,department_name,department_code',
            'room.room_type:id,room_type_name,room_type_code',
            'parent:id,medi_stock_name,medi_stock_code'
        ];
        $data = get_cache_full($this->medi_stock, $param, $this->medi_stock_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function medi_stock_id($id)
    {
        $data = get_cache($this->medi_stock, $this->medi_stock_name, $id, $this->time);
        $data1 = get_cache_1_1($this->medi_stock, "room", $this->medi_stock_name, $id, $this->time);
        $data2 = get_cache_1_1_1($this->medi_stock, "room.room_type", $this->medi_stock_name, $id, $this->time);
        $data3 = get_cache_1_1_1($this->medi_stock, "room.department", $this->medi_stock_name, $id, $this->time);
        $data4 = get_cache_1_1($this->medi_stock, "parent", $this->medi_stock_name, $id, $this->time);

        return response()->json(['data' => [
            'medi_stock' => $data,
            'room' => $data1,
            'room_type' => $data2,
            'department' => $data3,
            'parent' => $data4
        ]], 200);
    }

    /// Reception Room
    public function reception_room()
    {
        $param = [
            'room:id,department_id',
            'room.department:id,department_name,department_code'
        ];
        $data = get_cache_full($this->reception_room, $param, $this->reception_room_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function reception_room_id($id)
    {
        $data = get_cache($this->reception_room, $this->reception_room_name, $id, $this->time);
        $data1 = get_cache_1_1_1($this->reception_room, "room.department", $this->reception_room_name, $id, $this->time);
        $data2 = get_cache_1_n_with_ids($this->reception_room, "patient_type", $this->reception_room_name, $id, $this->time);
        $data3 = get_cache_1_1_1_1($this->reception_room, "room.department.area", $this->reception_room_name, $id, $this->time);
        $data4 = get_cache_1_1_1($this->reception_room, "room.default_cashier_room", $this->reception_room_name, $id, $this->time);
        return response()->json(['data' => [
            'reception_room' => $data,
            'department' => $data1,
            'patient_type' => $data2,
            'area' => $data3,
            'default_cashier_room' => $data4,
        ]], 200);
    }

    /// Area
    public function area()
    {
        $data = get_cache($this->area, $this->area_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function area_id($id)
    {
        $data = get_cache($this->area, $this->area_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Refectory
    public function refectory()
    {
        $param = [
            'room:id,department_id',
            'room.department:id,department_name'
        ];
        $data = get_cache_full($this->refectory, $param, $this->refectory_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function refectory_id($id)
    {
        $data = get_cache($this->refectory, $this->refectory_name, $id, $this->time);
        $data1 = get_cache_1_1_1($this->refectory, "room.department", $this->refectory_name, $id, $this->time);
        return response()->json(['data' => [
            'refectory' => $data,
            'department' => $data1,
        ]], 200);
    }

    public function refectory_get_department($id)
    {
        $data = get_cache_1_1_1($this->refectory, "room.department", $this->refectory_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Execute Group
    public function execute_group()
    {
        $data = get_cache($this->execute_group, $this->execute_group_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function execute_group_id($id)
    {
        $data = get_cache($this->execute_group, $this->execute_group_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Cashier Room
    public function cashier_room($id = null)
    {
        if ($id == null) {
            $name = $this->cashier_room_name;
            $param = [
                'room:id,department_id',
                'room.department:id,department_name,department_code',
                'room.department.area'
            ];
        } else {
            $name = $this->cashier_room_name.'_'.$id;
            $param = [
                'room',
                'room.department',
                'room.department.area'
            ];
        }
        $data = get_cache_full($this->cashier_room, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// National 
    public function national()
    {
        $data = get_cache($this->national, $this->national_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function national_id($id)
    {
        $data = get_cache($this->national, $this->national_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Province
    public function province($id = null)
    {
        if ($id == null) {
            $name = $this->province_name;
            $param = [
                'national:id,national_name,national_code'
            ];
        } else {
            $name = $this->province_name.'_'.$id;
            $param = [
                'national'
            ];
        }
        $data = get_cache_full($this->province, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }


    /// DataStore
    public function data_store()
    {
        $param = [
            'room:id,department_id',
            'room.department:id,department_name,department_code',
            'stored_room:id',
            'stored_department:id,department_name,department_code'
        ];
        $data = get_cache_full($this->data_store, $param, $this->data_store_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function data_store_id($id)
    {
        $data = get_cache($this->data_store, $this->data_store_name, $id, $this->time);
        $data1 = get_cache_1_1_1($this->data_store, "room.department", $this->data_store_name, $id, $this->time);
        $data2 = get_cache_1_1($this->data_store, "stored_department", $this->data_store_name, $id, $this->time);
        $data3 = get_cache_1_n_with_ids($this->data_store, "treatment_type", $this->data_store_name, $id, $this->time);
        $data4 = get_cache_1_n_with_ids($this->data_store, "treatment_end_type", $this->data_store_name, $id, $this->time);
        $data5 = get_cache_1_1($this->data_store, "stored_room", $this->data_store_name, $id, $this->time);
        $data6 = get_cache_1_1($this->data_store, "parent", $this->data_store_name, $id, $this->time);
        return response()->json(['data' => [
            'data_store' => $data,
            'department' => $data1,
            'stored_department' => $data2,
            'treatment_type' => $data3,
            'treatment_end_type' => $data4,
            'stored_room' => $data5,
            'parent' => $data6
        ]], 200);
    }

    /// ExecuteRole
    public function execute_role()
    {
        $data = get_cache($this->execute_role, $this->execute_role_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function execute_role_id($id)
    {
        $data = get_cache($this->execute_role, $this->execute_role_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Commune
    public function commune($id = null)
    {
        if ($id == null) {
            $name = $this->commune_name;
            $param = [
                'district:id,district_name,district_code'
            ];
        } else {
            $name = $this->commune_name.'_'.$id;
            $param = [
                'district'
            ];
        }
        $data = get_cache_full($this->commune, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Service
    public function service()
    {
        $data = get_cache($this->service, $this->service_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function service_id($id)
    {
        $data = get_cache($this->service, $this->service_name, $id, $this->time);
        $data1 = get_cache_1_1($this->service, "service_type", $this->service_name, $id, $this->time);
        $data2 = get_cache_1_1($this->service, "parent", $this->service_name, $id, $this->time);
        $data3 = get_cache_1_1($this->service, "service_unit", $this->service_name, $id, $this->time);
        $data4 = get_cache_1_1($this->service, "hein_service_type", $this->service_name, $id, $this->time);
        $data5 = get_cache_1_1($this->service, "bill_patient_type", $this->service_name, $id, $this->time);
        $data6 = get_cache_1_1($this->service, "pttt_group", $this->service_name, $id, $this->time);
        $data7 = get_cache_1_1($this->service, "pttt_method", $this->service_name, $id, $this->time);
        $data8 = get_cache_1_1($this->service, "icd_cm", $this->service_name, $id, $this->time);
        $data9 = get_cache_1_1($this->service, "revenue_department", $this->service_name, $id, $this->time);
        $data10 = get_cache_1_1($this->service, "package", $this->service_name, $id, $this->time);
        $data11 = get_cache_1_1($this->service, "exe_service_module", $this->service_name, $id, $this->time);
        $data12 = get_cache_1_1($this->service, "gender", $this->service_name, $id, $this->time);
        $data13 = get_cache_1_1($this->service, "ration_group", $this->service_name, $id, $this->time);
        $data14 = get_cache_1_1($this->service, "diim_type", $this->service_name, $id, $this->time);
        $data15 = get_cache_1_1($this->service, "fuex_type", $this->service_name, $id, $this->time);
        $data16 = get_cache_1_1($this->service, "test_type", $this->service_name, $id, $this->time);
        $data17 = get_cache_1_1($this->service, "other_pay_source", $this->service_name, $id, $this->time);
        $data18 = get_cache_1_n_with_ids($this->service, "body_part", $this->service_name, $id, $this->time);
        $data19 = get_cache_1_1($this->service, "film_size", $this->service_name, $id, $this->time);
        $data20 = get_cache_1_n_with_ids($this->service, "applied_patient_type", $this->service_name, $id, $this->time);
        $data21 = get_cache_1_1($this->service, "default_patient_type", $this->service_name, $id, $this->time);
        $data22 = get_cache_1_n_with_ids($this->service, "applied_patient_classify", $this->service_name, $id, $this->time);
        $data23 = get_cache_1_n_with_ids($this->service, "min_proc_time_except_paty", $this->service_name, $id, $this->time);
        $data24 = get_cache_1_n_with_ids($this->service, "max_proc_time_except_paty", $this->service_name, $id, $this->time);
        $data25 = get_cache_1_n_with_ids($this->service, "total_time_except_paty", $this->service_name, $id, $this->time);
        return response()->json(['data' => [
            'service' => $data,
            'service_type' => $data1,
            'parent' => $data2,
            'service_unit' => $data3,
            'hein_service_type' => $data4,
            'bill_patient_type' => $data5,
            'pttt_group' => $data6,
            'pttt_method' => $data7,
            'icd_cm' => $data8,
            'revenue_department' => $data9,
            'package' => $data10,
            'exe_service_module' => $data11,
            'gender' => $data12,
            'ration_group' => $data13,
            'diim_type' => $data14,
            'fuex_type' => $data15,
            'test_type' => $data16,
            'other_pay_source' => $data17,
            'body_part' => $data18,
            'film_size' => $data19,
            'applied_patient_type' => $data20,
            'default_patient_type' => $data21,
            'applied_patient_classify' => $data22,
            'min_proc_time_except_paty' => $data23,
            'max_proc_time_except_paty' => $data24,
            'total_time_except_paty' => $data25
        ]], 200);
    }


    public function service_by_code($type_id)
    {
        $data = get_cache_by_code($this->service, $this->service_name, 'service_code', $type_id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Service Paty
    public function service_paty()
    {
        $param = [
            'service:id,service_name,service_type_id',
            'service.service_type:id,service_type_name,service_type_code',
            'patient_type:id,patient_type_name,patient_type_code',
            'branch:id,branch_name,branch_code',
            'package:id,package_name,package_code'
        ];
        $data = get_cache_full($this->service_paty, $param, $this->service_paty_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function service_paty_id($id)
    {
        $data = get_cache($this->service_paty, $this->service_paty_name, $id, $this->time);
        $data1 = get_cache_1_1($this->service_paty, "service", $this->service_paty_name, $id, $this->time);
        $data2 = get_cache_1_1($this->service_paty, "patient_type", $this->service_paty_name, $id, $this->time);
        $data3 = get_cache_1_1($this->service_paty, "branch", $this->service_paty_name, $id, $this->time);
        $data4 = get_cache_1_n_with_ids($this->service_paty, "request_room", $this->service_paty_name, $id, $this->time);
        $data5 = get_cache_1_n_with_ids($this->service_paty, "execute_room", $this->service_paty_name, $id, $this->time);
        $data6 = get_cache_1_n_with_ids($this->service_paty, "request_deparment", $this->service_paty_name, $id, $this->time);
        $data7 = get_cache_1_1($this->service_paty, "package", $this->service_paty_name, $id, $this->time);
        $data8 = get_cache_1_1($this->service_paty, "service_condition", $this->service_paty_name, $id, $this->time);
        $data9 = get_cache_1_1($this->service_paty, "patient_classify", $this->service_paty_name, $id, $this->time);
        $data10 = get_cache_1_1($this->service_paty, "ration_time", $this->service_paty_name, $id, $this->time);
        return response()->json(['data' => [
            'service_paty' => $data,
            'service' => $data1,
            'patient_type' => $data2,
            'branch' => $data3,
            'request_room' => $data4,
            'execute_room' => $data5,
            'request_deparment' => $data6,
            'package' => $data7,
            'service_condition' => $data8,
            'patient_classify' => $data9,
            'ration_time' => $data10
        ]], 200);
    }

    public function service_with_patient_type($id = null)
    {
        if ($id == null) {
            $name = $this->service_name . '_with_' . $this->patient_type_name;
            $param = [
                'patient_types:id,patient_type_name,patient_type_code',
            ];
        } else {
            $name = $this->service_name . '_' . $id . '_with_' . $this->patient_type_name;
            $param = [
                'patient_types',
            ];
        }
        $data = get_cache_full($this->service, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function patient_type_with_service($id = null)
    {
        if ($id == null) {
            $name = $this->patient_type_name . '_with_' . $this->service_name;
            $param = [
                'services:id,service_name,service_code',
            ];
        } else {
            $name = $this->patient_type_name . '_' . $id . '_with_' . $this->service_name;
            $param = [
                'services',
            ];
        }
        $data = get_cache_full($this->patient_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
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
        $data1 = get_cache_1_1($this->machine, "department", $this->machine_name, $id, $this->time);
        $data2 = get_cache_1_n_with_ids($this->machine, "execute_room", $this->machine_name, $id, $this->time);
        $data3 = get_cache_1_1($this->machine, "execute_room", $this->machine_name, $id, $this->time);
        return response()->json(['data' => [
            'machine' => $data,
            'department' => $data1,
            'execute_rooms' => $data2,
            'execute_room' => $data3
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
    public function room($id = null)
    {
        if ($id == null) {
            $name = $this->room_name;
            $param = [
                'department:id,department_name,department_code',
                'room_type:id,room_type_name,room_type_code',
                'execute_room:id,room_id,execute_room_name,execute_room_code'
            ];
        } else {
            $name = $this->room_name.'_'.$id;
            $param = [
                'department',
                'room_type',
                'execute_room'
            ];
        }
        $data = get_cache_full($this->room, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

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
            $name = $this->bed_name.'_'.$id;
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
            $name = $this->bed_bsty_name.'_'.$id;
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
            $name = $this->serv_segr_name.'_'.$id;
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
            $name = $this->execute_role_user_name.'_'.$id;
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
            $name = $this->role_name.'_'.$id;
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
            $name = $this->module_role_name.'_'.$id;
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

    /// Patient Type
    public function patient_type()
    {
        $data = get_cache($this->patient_type, $this->patient_type_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function patient_type_id($id)
    {
        $data = get_cache($this->patient_type, $this->patient_type_name, $id, $this->time);
        $data1 = get_cache_1_n_with_ids($this->patient_type, 'treatment_type', $this->patient_type_name, $id, $this->time);
        $data2 = get_cache_1_1($this->patient_type, 'base_patient_type', $this->patient_type_name, $id, $this->time);
        $data3 = get_cache_1_n_with_ids($this->patient_type, 'other_pay_source', $this->patient_type_name, $id, $this->time);

        return response()->json(['data' => [
            'patient_type' => $data,
            'treatment_type' => $data1,
            'base_patient_type' => $data2,
            'other_pay_source' => $data3
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

    /// Patient Classify
    public function patient_classify($id = null)
    {
        if ($id == null) {
            $name = $this->patient_classify_name;
            $param = [
                'patient_type',
                'other_pay_source',
                'BHYT_whitelist',
            ];
        } else {
            $name = $this->patient_classify_name.'_'.$id;
            $param = [
                'patient_type',
                'other_pay_source',
                'BHYT_whitelist'
            ];
        }
        $data = get_cache_full($this->patient_classify, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
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

    /// Service Unit
    public function service_unit($id = null)
    {
        if ($id == null) {
            $name = $this->service_unit_name;
            $param = [
                'convert:id,service_unit_name',
            ];
        } else {
            $name = $this->service_unit_name.'_'.$id;
            $param = [
                'convert',
            ];
        }
        $data = get_cache_full($this->service_unit, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Service Type
    public function service_type($id = null)
    {
        if ($id == null) {
            $name = $this->service_type_name;
            $param = [
                'exe_service_module:id,exe_service_module_name,module_link',
            ];
        } else {
            $name = $this->service_type_name.'_'.$id;
            $param = [
                'exe_service_module',
            ];
        }
        $data = get_cache_full($this->service_type, $param, $name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Ration Group
    public function ration_group()
    {
        $data = get_cache($this->ration_group, $this->ration_group_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function ration_group_id($id)
    {
        $data = get_cache($this->ration_group, $this->ration_group_name, $id, $this->time);
        return response()->json(['data' => [
            'ration_group' => $data,
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
            $name = $this->mest_patient_type_name.'_'.$id;
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
            $name = $this->medi_stock_mety_list_name.'_'.$id;
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
            $name = $this->medi_stock_maty_list_name.'_'.$id;
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
        if ($id == null ) {
            $name = $this->mest_export_room_name;
            $param = [
                'medi_stock:id,medi_stock_name,medi_stock_code,is_active,is_delete,creator,modifier',
                'room:id,department_id',
                'room.execute_room:id,room_id,execute_room_name,execute_room_code',
                'room.department:id,department_name,department_code'
            ];
        } else {
            $name = $this->mest_export_room_name.'_'.$id;
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
}
