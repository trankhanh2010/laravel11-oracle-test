<?php

namespace App\Http\Controllers\Api\CacheControllers;

use Illuminate\Http\Request;
use App\Models\HIS\Service;
use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ServiceType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ServiceController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->service = new Service();
        $this->service_type = new ServiceType();
    }

    
    public function service($id)
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
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        $param_data = [
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
        ];
        return return_data_success($param_return, $param_data);
    }


    // public function service_by_code($type_id)
    // {
    //     $param = [];
    //     $data = get_cache_by_code($this->service, $this->service_name, $param, 'service_code', $type_id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
    public function service_by_service_type($id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->service_type->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $param =[];
        $data = get_cache_by_code($this->service, $this->service_name, $param, 'service_type_id', $id, $this->time);

        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
    
}
