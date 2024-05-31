<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Department;
use App\Models\BedRoom;
use App\Models\ExecuteRoom;
use App\Models\Room;
use App\Models\Speciality;
use App\Models\TreatmentType;
use App\Models\MediOrg;
use App\Models\Branch;
use App\Models\District;
use App\Models\MediStock;
use App\Models\ReceptionRoom;
use App\Models\Area;
use App\Models\Refectory;
use App\Models\ExecuteGroup;
use App\Models\CashierRoom;
use App\Models\National;
use App\Models\Province;
use App\Models\DataStore;
use App\Models\ExecuteRole;
use App\Models\Commune;
use App\Models\Service;
use App\Models\Servive;
use App\Models\ServicePaty;
use App\Models\ServiceMachine;
use App\Models\Machine;
use App\Models\ServiceRoom;

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
        $this->service = new Servive();
        $this->service_paty = new ServicePaty();
        $this->service_machine = new ServiceMachine();
        $this->machine = new Machine();
        $this->service_room = new ServiceRoom();
    }

    /// Department
    public function department()
    {
        $data = get_cache($this->department, $this->department_name, null, $this->time);
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
        $data = get_cache($this->bed_room, $this->bed_room_name, null, $this->time);
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

    public function bed_room_get_room($id)
    {
        $data = get_cache_1_1($this->bed_room, "room", $this->bed_room_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function bed_room_get_department($id)
    {
        $data = get_cache_1_1_1($this->bed_room, "room.department", $this->bed_room_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function bed_room_get_area($id)
    {
        $data = get_cache_1_1_1_1($this->bed_room, "room.department.area", $this->bed_room_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Execute Room
    public function execute_room()
    {
        $data = get_cache($this->execute_room, $this->execute_room_name, null, $this->time);
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

    public function execute_room_get_room($id)
    {
        $data = get_cache_1_1($this->execute_room, "room", $this->execute_room_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }
    public function execute_room_get_department($id)
    {
        $data = get_cache_1_1_1($this->execute_room, "room.department", $this->execute_room_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
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
        $data = get_cache($this->district, $this->district_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function district_id($id)
    {
        $data = get_cache($this->district, $this->district_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Medi Stock
    public function medi_stock()
    {
        $data = get_cache($this->medi_stock, $this->medi_stock_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function medi_stock_id($id)
    {
        $data = get_cache($this->medi_stock, $this->medi_stock_name, $id, $this->time);
        $data1 = get_cache_1_1($this->medi_stock, "room", $this->medi_stock_name, $id, $this->time);
        $data2 = get_cache_1_1_1($this->medi_stock, "room.room_type", $this->medi_stock_name, $id, $this->time);
        $data3 = get_cache_1_1_1($this->medi_stock, "room.department", $this->medi_stock_name, $id, $this->time);

        return response()->json(['data' => [
            'medi_stock' => $data,
            'room' => $data1,
            'room_type' => $data2,
            'department' => $data3
        ]], 200);
    }

    public function medi_stock_get_room($id)
    {
        $data = get_cache_1_1($this->medi_stock, "room", $this->medi_stock_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function medi_stock_get_room_type($id)
    {
        $data = get_cache_1_1_1($this->medi_stock, "room.room_type", $this->medi_stock_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function medi_stock_get_department($id)
    {
        $data = get_cache_1_1_1($this->medi_stock, "room.department", $this->medi_stock_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// Reception Room
    public function reception_room()
    {
        $data = get_cache($this->reception_room, $this->reception_room_name, null, $this->time);
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

    public function reception_room_get_department($id)
    {
        $data = get_cache_1_1_1($this->reception_room, "room.department", $this->reception_room_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
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
        $data = get_cache($this->refectory, $this->refectory_name, null, $this->time);
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
    public function cashier_room()
    {
        $data = get_cache($this->cashier_room, $this->cashier_room_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function cashier_room_id($id)
    {
        $data = get_cache($this->cashier_room, $this->cashier_room_name, $id, $this->time);
        $data1 = get_cache_1_1_1($this->cashier_room, "room.room_type", $this->cashier_room_name, $id, $this->time);
        $data2 = get_cache_1_1_1($this->cashier_room, "room.department", $this->cashier_room_name, $id, $this->time);
        $data3 = get_cache_1_1_1_1($this->cashier_room, "room.department.area", $this->cashier_room_name, $id, $this->time);

        return response()->json(['data' => [
            'cashier_room' => $data,
            'room_type' => $data1,
            'department' => $data2,
            'area' => $data3
        ]], 200);
    }

    public function cashier_room_get_room_type($id)
    {
        $data = get_cache_1_1_1($this->cashier_room, "room.room_type", $this->cashier_room_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function cashier_room_get_department($id)
    {
        $data = get_cache_1_1_1($this->cashier_room, "room.department", $this->cashier_room_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }
    public function cashier_room_get_area($id)
    {
        $data = get_cache_1_1_1_1($this->cashier_room, "room.department.area", $this->cashier_room_name, $id, $this->time);
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
    public function province()
    {
        $data = get_cache($this->province, $this->province_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function province_id($id)
    {
        $data = get_cache($this->province, $this->province_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    /// DataStore
    public function data_store()
    {
        $data = get_cache($this->data_store, $this->data_store_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function data_store_id($id)
    {
        $data = get_cache($this->data_store, $this->data_store_name, $id, $this->time);
        $data1 = get_cache_1_1_1($this->data_store, "room.department", $this->data_store_name, $id, $this->time);
        $data2 = get_cache_1_1($this->data_store, "department", $this->data_store_name, $id, $this->time);
        $data3 = get_cache_1_n_with_ids($this->data_store, "treatment_type", $this->data_store_name, $id, $this->time);
        $data4 = get_cache_1_n_with_ids($this->data_store, "treatment_end_type", $this->data_store_name, $id, $this->time);
        $data5 = get_cache_1_1($this->data_store, "store_room", $this->data_store_name, $id, $this->time);
        $data6 = get_cache_1_1($this->data_store, "parent", $this->data_store_name, $id, $this->time);
        return response()->json(['data' => [
            'data_store' => $data,
            'department_room' => $data1,
            'department' => $data2,
            'treatment_type' => $data3,
            'treatment_end_type' => $data4,
            'store_room' => $data5,
            'parent' => $data6
        ]], 200);
    }

    public function data_store_get_department_room($id)
    {
        $data = get_cache_1_1_1($this->data_store, "room.department", $this->data_store_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function data_store_get_department($id)
    {
        $data = get_cache_1_1($this->data_store, "department", $this->data_store_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
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
    public function commune()
    {
        $data = get_cache($this->commune, $this->commune_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function commune_id($id)
    {
        $data = get_cache($this->commune, $this->commune_name, $id, $this->time);
        return response()->json(['data' => $data], 200);
    }

    ///Service
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

    /// Service Paty
    public function service_paty()
    {
        $data = get_cache($this->service_paty, $this->service_paty_name, null, $this->time);
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

    /// Service Machine
    public function service_machine()
    {
        $data = get_cache($this->service_machine, $this->service_machine_name, null, $this->time);
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

    /// Machine
    public function machine()
    {
        $data = get_cache($this->machine, $this->machine_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function machine_id($id)
    {
        $data = get_cache($this->machine, $this->machine_name, $id, $this->time);
        return response()->json(['data' => [
            'machine' => $data
        ]], 200);
    }

    /// Room Service
    public function service_room()
    {
        $data = get_cache($this->service_room, $this->service_room_name, null, $this->time);
        return response()->json(['data' => $data], 200);
    }

    public function service_room_id($id)
    {
        $data = get_cache($this->service_room, $this->service_room_name, $id, $this->time);
        return response()->json(['data' => [
            'room_service' => $data
        ]], 200);
    }
}
