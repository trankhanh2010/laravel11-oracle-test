<?php

namespace App\Http\Controllers\BaseControllers;

use App\Http\Controllers\Controller;
use App\Models\ACS\Module;
use App\Models\ACS\Role;
use App\Models\HIS\ActiveIngredient;
use App\Models\HIS\Bed;
use App\Models\HIS\Department;
use App\Models\HIS\Employee;
use App\Models\HIS\ExecuteRole;
use App\Models\HIS\ExecuteRoom;
use App\Models\HIS\Machine;
use App\Models\HIS\MaterialType;
use App\Models\HIS\MedicineType;
use App\Models\HIS\MediStock;
use App\Models\HIS\Package;
use App\Models\HIS\PatientType;
use App\Models\HIS\Room;
use App\Models\HIS\RoomType;
use App\Models\HIS\Service;
use Illuminate\Http\Request;
use App\Models\HIS\ServiceType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class BaseApiCacheController extends Controller
{
    protected $errors = [];
    protected $data = [];
    protected $time;
    protected $columns_time;
    protected $arr_limit;
    protected $start;
    protected $start_name = 'Start';
    protected $limit;
    protected $limit_name = 'Limit';
    protected $order_by;
    protected $order_by_name = 'OrderBy';
    protected $order_by_tring;
    protected $order_by_request;
    protected $order_by_join;
    protected $only_active;
    protected $only_active_name = 'OnlyActive';
    protected $service_type_ids;
    protected $service_type_ids_name = 'ServiceTypeIds';
    protected $patient_type_ids;
    protected $patient_type_ids_name = 'PatientTypeIds';
    protected $service_ids;
    protected $service_ids_name = 'ServiceIds';
    protected $service_ids_string;
    protected $machine_ids;
    protected $machine_ids_name = 'MachineIds';
    protected $machine_ids_string;
    protected $room_ids;
    protected $room_ids_name = 'RoomIds';
    protected $service_follow_ids;
    protected $service_follow_ids_name = 'ServiceFollowIds';
    protected $bed_ids;
    protected $bed_ids_name = 'BedIds';
    protected $service_id;
    protected $service_id_name = 'ServiceId';
    protected $package_id;
    protected $package_id_name = 'PackageId';
    protected $department_id;
    protected $department_id_name = 'DepartmentId';
    protected $keyword;
    protected $keyword_name = 'Keyword';
    protected $get_all;
    protected $get_all_name = 'GetAll';
    protected $count_name = 'Count';
    protected $per_page;
    protected $page;
    protected $param_request;
    protected $is_active;
    protected $is_active_name = 'IsActive';
    protected $effective;
    protected $effective_name = 'Effective';
    protected $room_type_id;
    protected $room_type_id_name = 'RoomTypeId';
    protected $is_addition;
    protected $is_addition_name = 'IsAddition';
    protected $service_type_id;
    protected $service_type_id_name = 'ServiceTypeId';
    protected $loginname;
    protected $loginname_name = 'Loginname';
    protected $execute_role_id;
    protected $execute_role_id_name = 'ExecuteRoleId';
    protected $module_id;
    protected $module_id_name = 'ModuleId';
    protected $role_id;
    protected $role_id_name = 'RoleId';
    protected $medi_stock_id;
    protected $medi_stock_id_name = 'MediStockId';
    protected $patient_type_id;
    protected $patient_type_id_name = 'PatientTypeId';
    protected $medicine_type_id;
    protected $medicine_type_id_name = 'MedicineTypeId';
    protected $material_type_id;
    protected $material_type_id_name = 'MaterialTypeId';
    protected $room_id;
    protected $room_id_name = 'RoomId';
    protected $execute_room_id;
    protected $execute_room_id_name = 'ExecuteRoomId';
    protected $patient_type_allow_id;
    protected $patient_type_allow_id_name = 'PatientTypeAllowId';
    protected $active_ingredient_id;
    protected $active_ingredient_id_name = 'ActiveIngredientId';
    protected $test_service_type_id;
    protected $test_service_type_id_name = 'TestServiceTypeId';
    protected $patient_type_ids_string;
    protected $service_type_ids_string;

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
    protected $icd_cm;
    protected $icd_cm_name = 'icd_cm';
    protected $diim_type;
    protected $diim_type_name = 'diim_type';
    protected $fuex_type;
    protected $fuex_type_name = 'fuex_type';
    protected $film_size;
    protected $film_size_name = 'film_size';
    protected $gender;
    protected $gender_name = 'gender';
    protected $body_part;
    protected $body_part_name = 'body_part';
    protected $exe_service_module;
    protected $exe_service_module_name = 'exe_service_module';
    protected $suim_index;
    protected $suim_index_name = 'suim_index';
    protected $package;
    protected $package_name = 'package';
    protected $service_condition;
    protected $service_condition_name = 'service_condition';
    protected $employee;
    protected $employee_name = 'employee';
    protected $token ;
    protected $token_name = 'token';
    protected $medi_stock_mety;
    protected $medi_stock_mety_name = 'medi_stock_mety';
    protected $medi_stock_maty;
    protected $medi_stock_maty_name = 'medi_stock_maty';
    protected $mest_room;
    protected $mest_room_name = 'mest_room';

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

    protected function check_param()
    {
        if ($this->has_errors()) {
            return return_400($this->get_errors());
        }
        return null;
    }
    protected function check_id($id, $model, $name)
    {
        if($this->is_active !== null){
            $data = Cache::remember($name . '_check_id_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id, $model) {
                return $model->where('id', $id)->where('is_active', $this->is_active)->exists();
            });
        }else{
            $data = Cache::remember($name . '_check_id_' . $id , $this->time, function () use ($id, $model) {
                return $model->where('id', $id)->exists();
            });
        }

        if (!$data) {
            return return_not_record($id);
        }
        return null;
    }
    protected function get_columns_table($table)
    {
        $parts = explode('_', $table->getTable());
        $conn = strtolower($parts[0]);
        $columns_table = Cache::remember('columns_' . $table->getTable(), $this->columns_time, function () use ($table, $conn) {
            return  Schema::connection('oracle_'.$conn)->getColumnListing($table->getTable()) ?? [];
        });
        return $columns_table;
    }
    protected function check_order_by($order_by, $columns, $order_by_join)
    {
        foreach ($order_by as $key => $item) {
            if (!in_array($key, $order_by_join)) {
                if ((!in_array($key, $columns))) {
                    $this->errors[snakeToCamel($key)] = $this->mess_order_by_name;
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                    unset($this->order_by[$key]);
                }
            }
        }
        return $order_by;
    }
    public function __construct(Request $request)
    {
        // Khai báo các biến
        // Thời gian tồn tại của cache
        $this->time = now()->addMinutes(10080);
        $this->columns_time = now()->addMinutes(20000);

        // Thông báo lỗi 
        $this->mess_format = config('keywords')['error']['format'];
        $this->mess_order_by_name = config('keywords')['error']['order_by_name'];
        $this->mess_record_id = config('keywords')['error']['record_id'];
        $this->mess_decode_param = config('keywords')['error']['decode_param'];


        // Param json gửi từ client
        if ($request->input('param') !== null) {
            $this->param_request = json_decode(base64_decode($request->input('param')), true) ?? null;
            if ($this->param_request === null) {
                $this->errors['param'] = $this->mess_decode_param;
            }
        }

        // Gán và kiểm tra các tham số được gửi lên
        $this->per_page = $request->query('perPage', 10);
        $this->page = $request->query('page', 1);
        $this->start = $this->param_request['CommonParam']['Start'] ?? intval($request->start) ?? 0;
        $this->limit = $this->param_request['CommonParam']['Limit'] ?? intval($request->limit) ?? 10;
        if ($this->limit <= 0) {
            $this->limit = 10;
        }
        $this->arr_limit = [10, 20, 50, 100, 200, 500, 1000, 2000, 4000];
        if (($this->limit < 10) || (!in_array($this->limit, $this->arr_limit))) {
            $this->errors[$this->limit_name] = $this->mess_format . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->arr_limit);
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
        if ($this->keyword !== null) {
            if (!is_string($this->keyword)) {
                $this->errors[$this->keyword_name] = $this->mess_format;
                $this->keyword = null;
            }
        }

        $this->get_all = $this->param_request['CommonParam']['GetAll'] ?? false;
        if (!is_bool($this->get_all)) {
            $this->errors[$this->get_all_name] = $this->mess_format;
            $this->get_all = false;
        }
        $this->order_by = $this->param_request['ApiData']['OrderBy'] ?? null;
        $this->order_by_request = $this->param_request['ApiData']['OrderBy'] ?? null;
        if ($this->order_by != null) {
            $this->order_by = convertArrayKeysToSnakeCase($this->order_by);
            foreach ($this->order_by as $key => $item) {
                if (!in_array($item, ['asc', 'desc'])) {
                    $this->errors[$this->order_by_name] = $this->mess_format;
                }
            }
        }

        $this->is_active = $this->param_request['ApiData']['IsActive'] ?? null;
        if ($this->is_active !== null) {
            if (!in_array($this->is_active, [0, 1])) {
                $this->errors[$this->is_active_name] = $this->mess_format;
                $this->is_active = 1;
            }
        }

        $this->only_active = $this->param_request['ApiData']['OnlyActive'] ?? false;
        if (!is_bool($this->only_active)) {
            $this->errors[$this->only_active_name] = $this->mess_format;
            $this->only_active = false;
        }

        $this->service_type_ids = $this->param_request['ApiData']['ServiceTypeIds'] ?? null;
        if ($this->service_type_ids != null) {
            foreach ($this->service_type_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->service_type_ids_name] = $this->mess_format;
                    unset($this->service_type_ids[$key]);
                } else {
                    if (!ServiceType::where('id', $item)->exists()) {
                        $this->errors[$this->service_type_ids_name] = $this->mess_record_id;
                        unset($this->service_type_ids[$key]);
                    }
                }
            }
        }
        if ($this->service_type_ids != null) {
            $this->service_type_ids_string = arrayToCustomStringNotKey($this->service_type_ids);
        }
        $this->patient_type_ids = $this->param_request['ApiData']['PatientTypeIds'] ?? null;
        if ($this->patient_type_ids != null) {
            foreach ($this->patient_type_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->patient_type_ids_name] = $this->mess_format;
                    unset($this->patient_type_ids[$key]);
                } else {
                    if (!PatientType::where('id', $item)->exists()) {
                        $this->errors[$this->patient_type_ids_name] = $this->mess_record_id;
                        unset($this->patient_type_ids[$key]);
                    }
                }
            }
        }
        if ($this->patient_type_ids !=  null) {
            $this->patient_type_ids_string = arrayToCustomStringNotKey($this->patient_type_ids);
        }
        $this->service_id = $this->param_request['ApiData']['ServiceId'] ?? null;
        if ($this->service_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->service_id)) {
                $this->errors[$this->service_id_name] = $this->mess_format;
                $this->service_id = null;
            } else {
                if (!Service::where('id', $this->service_id)->exists()) {
                    $this->errors[$this->service_id_name] = $this->mess_record_id;
                    $this->service_id = null;
                }
            }
        }
        $this->package_id = $this->param_request['ApiData']['PackageId'] ?? null;
        if ($this->package_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->package_id)) {
                $this->errors[$this->package_id_name] = $this->mess_format;
                $this->package_id = null;
            } else {
                if (!Package::where('id', $this->package_id)->exists()) {
                    $this->errors[$this->package_id_name] = $this->mess_record_id;
                    $this->package_id = null;
                }
            }
        }
        $this->department_id = $this->param_request['ApiData']['DepartmentId'] ?? null;
        if ($this->department_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->department_id)) {
                $this->errors[$this->department_id_name] = $this->mess_format;
                $this->department_id = null;
            } else {
                if (!Department::where('id', $this->department_id)->exists()) {
                    $this->errors[$this->department_id_name] = $this->mess_record_id;
                    $this->department_id = null;
                }
            }
        }
        $this->is_active = $this->param_request['ApiData']['IsActive'] ?? null;
        if ($this->is_active !== null) {
            if (!in_array($this->is_active, [0, 1])) {
                $this->errors[$this->is_active_name] = $this->mess_format;
                $this->is_active = 1;
            }
        }
        $this->effective = $this->param_request['ApiData']['Effective'] ?? false;
        if (!is_bool($this->effective)) {
            $this->errors[$this->effective_name] = $this->mess_format;
            $this->effective = false;
        }
        $this->room_type_id = $this->param_request['ApiData']['RoomTypeId'] ?? null;
        if ($this->room_type_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->room_type_id)) {
                $this->errors[$this->room_type_id_name] = $this->mess_format;
                $this->room_type_id = null;
            } else {
                if (!RoomType::where('id', $this->room_type_id)->exists()) {
                    $this->errors[$this->room_type_id_name] = $this->mess_record_id;
                    $this->room_type_id = null;
                }
            }
        }
        $this->is_addition = $this->param_request['ApiData']['IsAddition'] ?? null;
        if ($this->is_addition !== null) {
            if (!in_array($this->is_addition, [0, 1])) {
                $this->errors[$this->is_addition_name] = $this->mess_format;
                $this->is_addition = 1;
            }
        }
        $this->service_type_id = $this->param_request['ApiData']['ServiceTypeId'] ?? null;
        if ($this->service_type_id !== null) {
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
        $this->service_ids = $this->param_request['ApiData']['ServiceIds'] ?? null;
        if ($this->service_ids != null) {
            foreach ($this->service_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->service_ids_name] = $this->mess_format;
                    unset($this->service_ids[$key]);
                } else {
                    if (!Service::where('id', $item)->exists()) {
                        $this->errors[$this->service_ids_name] = $this->mess_record_id;
                        unset($this->service_ids[$key]);
                    }
                }
            }
        }
        if($this->service_ids != null){
            $this->service_ids_string = arrayToCustomStringNotKey($this->service_ids);
        }
        $this->machine_ids = $this->param_request['ApiData']['MachineIds'] ?? null;
        if ($this->machine_ids != null) {
            foreach ($this->machine_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->machine_ids_name] = $this->mess_format;
                    unset($this->machine_ids[$key]);
                } else {
                    if (!Machine::where('id', $item)->exists()) {
                        $this->errors[$this->machine_ids_name] = $this->mess_record_id;
                        unset($this->machine_ids[$key]);
                    }
                }
            }
        }
        if($this->machine_ids != null){
            $this->machine_ids_string = arrayToCustomStringNotKey($this->machine_ids);
        }
        $this->room_ids = $this->param_request['ApiData']['RoomIds'] ?? null;
        if ($this->room_ids != null) {
            foreach ($this->room_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->room_ids_name] = $this->mess_format;
                    unset($this->room_ids[$key]);
                } else {
                    if (!Room::where('id', $item)->exists()) {
                        $this->errors[$this->room_ids_name] = $this->mess_record_id;
                        unset($this->room_ids[$key]);
                    }
                }
            }
        }
        $this->service_follow_ids = $this->param_request['ApiData']['ServiceFollowIds'] ?? null;
        if ($this->service_follow_ids != null) {
            foreach ($this->service_follow_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->service_follow_ids_name] = $this->mess_format;
                    unset($this->service_follow_ids[$key]);
                } else {
                    if (!Service::where('id', $item)->exists()) {
                        $this->errors[$this->service_follow_ids_name] = $this->mess_record_id;
                        unset($this->service_follow_ids[$key]);
                    }
                }
            }
        }
        $this->bed_ids = $this->param_request['ApiData']['BedIds'] ?? null;
        if ($this->bed_ids != null) {
            foreach ($this->bed_ids as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->bed_ids_name] = $this->mess_format;
                    unset($this->bed_ids[$key]);
                } else {
                    if (!Bed::where('id', $item)->exists()) {
                        $this->errors[$this->bed_ids_name] = $this->mess_record_id;
                        unset($this->bed_ids[$key]);
                    }
                }
            }
        }
        $this->loginname = $this->param_request['ApiData']['Loginname'] ?? null;
        if ($this->loginname !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_string($this->loginname)) {
                $this->errors[$this->loginname_name] = $this->mess_format;
                $this->loginname = null;
            } else {
                if (!Employee::where('loginname', $this->loginname)->exists()) {
                    $this->errors[$this->loginname_name] = $this->mess_record_id;
                    $this->loginname = null;
                }
            }
        }
        $this->execute_role_id = $this->param_request['ApiData']['ExecuteRoleId'] ?? null;
        if ($this->execute_role_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->execute_role_id)) {
                $this->errors[$this->execute_role_id_name] = $this->mess_format;
                $this->execute_role_id = null;
            } else {
                if (!ExecuteRole::where('id', $this->execute_role_id)->exists()) {
                    $this->errors[$this->execute_role_id_name] = $this->mess_record_id;
                    $this->execute_role_id = null;
                }
            }
        }
        $this->module_id = $this->param_request['ApiData']['ModuleId'] ?? null;
        if ($this->module_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->module_id)) {
                $this->errors[$this->module_id_name] = $this->mess_format;
                $this->module_id = null;
            } else {
                if (!Module::where('id', $this->module_id)->exists()) {
                    $this->errors[$this->module_id_name] = $this->mess_record_id;
                    $this->module_id = null;
                }
            }
        }
        $this->role_id = $this->param_request['ApiData']['RoleId'] ?? null;
        if ($this->role_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->role_id)) {
                $this->errors[$this->role_id_name] = $this->mess_format;
                $this->role_id = null;
            } else {
                if (!Role::where('id', $this->role_id)->exists()) {
                    $this->errors[$this->role_id_name] = $this->mess_record_id;
                    $this->role_id = null;
                }
            }
        }
        $this->medi_stock_id = $this->param_request['ApiData']['MediStockId'] ?? null;
        if ($this->medi_stock_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->medi_stock_id)) {
                $this->errors[$this->medi_stock_id_name] = $this->mess_format;
                $this->medi_stock_id = null;
            } else {
                if (!MediStock::where('id', $this->medi_stock_id)->exists()) {
                    $this->errors[$this->medi_stock_id_name] = $this->mess_record_id;
                    $this->medi_stock_id = null;
                }
            }
        }
        $this->patient_type_id = $this->param_request['ApiData']['PatientTypeId'] ?? null;
        if ($this->patient_type_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->patient_type_id)) {
                $this->errors[$this->patient_type_id_name] = $this->mess_format;
                $this->patient_type_id = null;
            } else {
                if (!PatientType::where('id', $this->patient_type_id)->exists()) {
                    $this->errors[$this->patient_type_id_name] = $this->mess_record_id;
                    $this->patient_type_id = null;
                }
            }
        }
        $this->medicine_type_id = $this->param_request['ApiData']['MedicineTypeId'] ?? null;
        if ($this->medicine_type_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->medicine_type_id)) {
                $this->errors[$this->medicine_type_id_name] = $this->mess_format;
                $this->medicine_type_id = null;
            } else {
                if (!MedicineType::where('id', $this->medicine_type_id)->exists()) {
                    $this->errors[$this->medicine_type_id_name] = $this->mess_record_id;
                    $this->medicine_type_id = null;
                }
            }
        }
        $this->material_type_id = $this->param_request['ApiData']['MaterialTypeId'] ?? null;
        if ($this->material_type_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->material_type_id)) {
                $this->errors[$this->material_type_id_name] = $this->mess_format;
                $this->material_type_id = null;
            } else {
                if (!MaterialType::where('id', $this->material_type_id)->exists()) {
                    $this->errors[$this->material_type_id_name] = $this->mess_record_id;
                    $this->material_type_id = null;
                }
            }
        }
        $this->room_id = $this->param_request['ApiData']['RoomId'] ?? null;
        if ($this->room_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->room_id)) {
                $this->errors[$this->room_id_name] = $this->mess_format;
                $this->room_id = null;
            } else {
                if (!Room::where('id', $this->room_id)->exists()) {
                    $this->errors[$this->room_id_name] = $this->mess_record_id;
                    $this->room_id = null;
                }
            }
        }
        $this->execute_room_id = $this->param_request['ApiData']['ExecuteRoomId'] ?? null;
        if ($this->execute_room_id !== null) {
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
        $this->patient_type_allow_id = $this->param_request['ApiData']['PatientTypeAllowId'] ?? null;
        if ($this->patient_type_allow_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->patient_type_allow_id)) {
                $this->errors[$this->patient_type_allow_id_name] = $this->mess_format;
                $this->patient_type_allow_id = null;
            } else {
                if (!PatientType::where('id', $this->patient_type_allow_id)->exists()) {
                    $this->errors[$this->patient_type_allow_id_name] = $this->mess_record_id;
                    $this->patient_type_allow_id = null;
                }
            }
        }
        $this->active_ingredient_id = $this->param_request['ApiData']['ActiveIngredientId'] ?? null;
        if ($this->active_ingredient_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->active_ingredient_id)) {
                $this->errors[$this->active_ingredient_id_name] = $this->mess_format;
                $this->active_ingredient_id = null;
            } else {
                if (!ActiveIngredient::where('id', $this->active_ingredient_id)->exists()) {
                    $this->errors[$this->active_ingredient_id_name] = $this->mess_record_id;
                    $this->active_ingredient_id = null;
                }
            }
        }
        $this->test_service_type_id = $this->param_request['ApiData']['TestServiceTypeId'] ?? null;
        if ($this->test_service_type_id !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->test_service_type_id)) {
                $this->errors[$this->test_service_type_id_name] = $this->mess_format;
                $this->test_service_type_id = null;
            } else {
                if (!Service::
                        leftJoin('his_service_type as service_type', 'service_type.id', '=', 'his_service.service_type_id')
                        ->where('his_service.id', $this->test_service_type_id)
                        ->where('service_type.service_type_code', 'XN')->exists()) {
                    $this->errors[$this->test_service_type_id_name] = $this->mess_record_id;
                    $this->test_service_type_id = null;
                }
            }
        }
    }
}
