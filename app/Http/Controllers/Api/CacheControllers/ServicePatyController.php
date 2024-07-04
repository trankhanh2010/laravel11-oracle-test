<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ServicePaty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ServicePatyController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->service_paty = new ServicePaty();
        $this->order_by_join = ['service_name', 'service_code', 'patient_type_name', 'patient_type_code', 'branch_name', 'branch_code', 'package_name', 'package_code'];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if (!$this->service_paty->getConnection()->getSchemaBuilder()->hasColumn($this->service_paty->getTable(), $key)) {
                        unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                        unset($this->order_by[$key]);
                    }
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function service_paty($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if (($keyword != null) || ($this->service_type_ids != null) || ($this->patient_type_ids != null) || ($this->service_id != null) || ($this->package_id != null)) {
            $data = $this->service_paty
                ->leftJoin('his_service as service', 'service.id', '=', 'his_service_paty.service_id')
                ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_service_paty.patient_type_id')
                ->leftJoin('his_branch as branch', 'branch.id', '=', 'his_service_paty.branch_id')
                ->leftJoin('his_package as package', 'package.id', '=', 'his_service_paty.package_id')
                ->select(
                    'his_service_paty.*',
                    'service.service_name as service_name',
                    'service.service_code as service_code',
                    'service.service_type_id as service_type_id',
                    'patient_type.patient_type_name as patient_type_name',
                    'patient_type.patient_type_code as patient_type_code',
                    'branch.branch_name as branch_name',
                    'branch.branch_code as branch_code',
                    'package.package_name as package_name',
                    'package.package_code as package_code',
                );
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query->where(DB::connection('oracle_his')->raw('lower(service.service_name)'), 'like', '%' . $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('lower(service.service_code)'), 'like', '%' . $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('lower(patient_type.patient_type_name)'), 'like', '%' . $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('lower(patient_type.patient_type_code)'), 'like', '%' . $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('lower(package.package_name)'), 'like', '%' . $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('lower(package.package_code)'), 'like', '%' . $keyword . '%');
                });
            }
            if ($this->service_type_ids != null) {
                $data = $data->where(function ($query) {
                    // Khởi tạo biến cờ
                    $isFirst = true;
                    foreach ($this->service_type_ids as $key => $item) {
                        if ($isFirst) {
                            $query = $query->where(DB::connection('oracle_his')->raw('service_type_id'), $item);
                            $isFirst = false; // Đặt cờ thành false sau lần đầu tiên
                        } else {
                            $query = $query->orWhere(DB::connection('oracle_his')->raw('service_type_id'), $item);
                        }
                    }
                });
            }
            if ($this->patient_type_ids != null) {
                $data = $data->where(function ($query) {
                    // Khởi tạo biến cờ
                    $isFirst = true;
                    foreach ($this->patient_type_ids as $key => $item) {
                        if ($isFirst) {
                            $query = $query->where(DB::connection('oracle_his')->raw('patient_type_id'), $item);
                            $isFirst = false; // Đặt cờ thành false sau lần đầu tiên
                        } else {
                            $query = $query->orWhere(DB::connection('oracle_his')->raw('patient_type_id'), $item);
                        }
                    }
                });
            }
            if ($this->service_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('service_id'), $this->service_id);
                });
            }
            if ($this->package_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_paty.package_id'), $this->package_id);
                });
            }
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    if (!in_array($key, $this->order_by_join)) {
                        $data->orderBy(DB::connection('oracle_his')->raw('his_service_paty.' . $key . ''), $item);
                    } else {
                        $data->orderBy(DB::connection('oracle_his')->raw($key), $item);
                    }
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            if ($id == null) {
                $data = Cache::remember('service_paty' . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring, $this->time, function () {
                    $data = $this->service_paty
                        ->leftJoin('his_service as service', 'service.id', '=', 'his_service_paty.service_id')
                        ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_service_paty.patient_type_id')
                        ->leftJoin('his_branch as branch', 'branch.id', '=', 'his_service_paty.branch_id')
                        ->leftJoin('his_package as package', 'package.id', '=', 'his_service_paty.package_id')
                        ->select(
                            'his_service_paty.*',
                            'service.service_name as service_name',
                            'service.service_code as service_code',
                            'service.service_type_id as service_type_id',
                            'patient_type.patient_type_name as patient_type_name',
                            'patient_type.patient_type_code as patient_type_code',
                            'branch.branch_name as branch_name',
                            'branch.branch_code as branch_code',
                            'package.package_name as package_name',
                            'package.package_code as package_code',
                        );
                    $count = $data->count();
                    if ($this->order_by != null) {
                        foreach ($this->order_by as $key => $item) {
                            if (!in_array($key, $this->order_by_join)) {
                                $data->orderBy(DB::connection('oracle_his')->raw('lower(his_service_paty.' . $key . ')'), $item);
                            } else {
                                $data->orderBy(DB::connection('oracle_his')->raw('lower(' . $key . ')'), $item);
                            }
                        }
                    }
                    $data = $data
                        ->skip($this->start)
                        ->take($this->limit)
                        ->get();
                    return ['data' => $data, 'count' => $count];
                });
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->service_paty->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $data = get_cache($this->service_paty, $this->service_paty_name, $id, $this->time, $this->start, $this->limit, $this->order_by);
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
                $data11 = get_cache_1_1_1($this->service_paty, "service.service_type", $this->service_paty_name, $id, $this->time);
                $data_param = [
                    'service_paty' => $data,
                    'service' => $data1,
                    'service_type' => $data11,
                    'patient_type' => $data2,
                    'branch' => $data3,
                    'request_room' => $data4,
                    'execute_room' => $data5,
                    'request_deparment' => $data6,
                    'package' => $data7,
                    'service_condition' => $data8,
                    'patient_classify' => $data9,
                    'ration_time' => $data10
                ];
            }
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count'],
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request,
            'service_type_ids' => $this->service_type_ids ?? null,
            'patient_type_ids' => $this->patient_type_ids ?? null,
            'service_id' => $this->service_id ?? null,
            'package_id' => $this->package_id ?? null
        ];
        return return_data_success($param_return, $data_param ?? $data ?? $data['data']);
    }


    // public function service_with_patient_type($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->service_name . '_with_' . $this->patient_type_name;
    //         $param = [
    //             'patient_types:id,patient_type_name,patient_type_code',
    //         ];
    //     } else {
    //         $name = $this->service_name . '_' . $id . '_with_' . $this->patient_type_name;
    //         $param = [
    //             'patient_types',
    //         ];
    //     }
    //     $data = get_cache_full($this->service, $param, $name, $id, $this->time, $this->start, $this->limit);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function patient_type_with_service($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->patient_type_name . '_with_' . $this->service_name;
    //         $param = [
    //             'services:id,service_name,service_code',
    //         ];
    //     } else {
    //         $name = $this->patient_type_name . '_' . $id . '_with_' . $this->service_name;
    //         $param = [
    //             'services',
    //         ];
    //     }
    //     $data = get_cache_full($this->patient_type, $param, $name, $id, $this->time, $this->start, $this->limit);
    //     return response()->json(['data' => $data], 200);
    // }
}
