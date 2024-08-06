<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServicePaty\CreateServicePatyRequest;
use App\Http\Requests\ServicePaty\UpdateServicePatyRequest;
use App\Models\HIS\Department;
use App\Models\HIS\Room;
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
        $this->room = new Room();
        $this->department = new Department();
        $this->order_by_join = ['service_name', 'service_code', 'patient_type_name', 'patient_type_code', 'branch_name', 'branch_code', 'package_name', 'package_code'];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->service_paty);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function service_paty($id = null)
    {
        $keyword = $this->keyword;
        if ($keyword != null) {
            $data = $this->service_paty
                ->leftJoin('his_service as service', 'service.id', '=', 'his_service_paty.service_id')
                ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_service_paty.patient_type_id')
                ->leftJoin('his_branch as branch', 'branch.id', '=', 'his_service_paty.branch_id')
                ->leftJoin('his_package as package', 'package.id', '=', 'his_service_paty.package_id')
                ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
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
                    'service_type.service_type_name as service_type_name'

                );
            if ($keyword !== null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query->where(DB::connection('oracle_his')->raw('service.service_name'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('service.service_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('patient_type.patient_type_name'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('patient_type.patient_type_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('package.package_name'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('package.package_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('service_type.service_type_name'), 'like', $keyword . '%');
                        
                });
            }
            if ($this->service_type_ids !== null) {
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
            if ($this->patient_type_ids !== null) {
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
            if ($this->service_id !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('service_id'), $this->service_id);
                });
            }
            if ($this->package_id !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_paty.package_id'), $this->package_id);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_paty.is_active'), $this->is_active);
                });
            } 
            $now = now()->format('Ymdhis');
            if ($this->effective) {
                $data = $data->where(function ($query) use ($now) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_service_paty.to_time'), '>=', $now)
                    ->orWhere(DB::connection('oracle_his')->raw('his_service_paty.to_time'), null);
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
                $data = Cache::remember('service_paty' .'_effective_'.$this->effective.'_package_id_'.$this->package_id.'_service_id_'.$this->service_id.'_patient_type_ids_'.$this->patient_type_ids_string.'_service_type_ids_'.$this->service_type_ids_string. '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring. '_is_active_' . $this->is_active, $this->time, function () {
                    $data = $this->service_paty
                        ->leftJoin('his_service as service', 'service.id', '=', 'his_service_paty.service_id')
                        ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_service_paty.patient_type_id')
                        ->leftJoin('his_branch as branch', 'branch.id', '=', 'his_service_paty.branch_id')
                        ->leftJoin('his_package as package', 'package.id', '=', 'his_service_paty.package_id')
                        ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
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
                            'service_type.service_type_name as service_type_name'
                        );
                        if ($this->service_type_ids !== null) {
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
                        if ($this->patient_type_ids !== null) {
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
                        if ($this->service_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('service_id'), $this->service_id);
                            });
                        }
                        if ($this->package_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_service_paty.package_id'), $this->package_id);
                            });
                        }
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_service_paty.is_active'), $this->is_active);
                            });
                        } 
                        $now = now()->format('Ymdhis');
                        if ($this->effective) {
                            $data = $data->where(function ($query) use ($now) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_service_paty.to_time'), '>=', $now)
                                ->orWhere(DB::connection('oracle_his')->raw('his_service_paty.to_time'), null);
                            });
                        } 
                    $count = $data->count();
                    if ($this->order_by != null) {
                        foreach ($this->order_by as $key => $item) {
                            if (!in_array($key, $this->order_by_join)) {
                                $data->orderBy(DB::connection('oracle_his')->raw('his_service_paty.' . $key . ''), $item);
                            } else {
                                $data->orderBy(DB::connection('oracle_his')->raw('' . $key . ''), $item);
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
                $data = get_cache_full($this->service_paty, [], $this->service_paty_name.'_id_'. $id. '_is_active_'. $this->is_active, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active);
                if($data != null){
                    $data_a = $data;
                    $data1 = get_cache_1_1($this->service_paty, "service", $this->service_paty_name, $id, $this->time);
                    $data2 = get_cache_1_1($this->service_paty, "patient_type", $this->service_paty_name, $id, $this->time);
                    $data3 = get_cache_1_1($this->service_paty, "branch", $this->service_paty_name, $id, $this->time);
                    // $data4 = get_cache_1_n_with_ids($this->service_paty, "request_room", $this->service_paty_name, $id, $this->time);
                    // $data5 = get_cache_1_n_with_ids($this->service_paty, "execute_room", $this->service_paty_name, $id, $this->time);
                    $data6 = get_cache_1_n_with_ids($this->service_paty, "request_deparment", $this->service_paty_name, $id, $this->time);
                    $data7 = get_cache_1_1($this->service_paty, "package", $this->service_paty_name, $id, $this->time);
                    $data8 = get_cache_1_1($this->service_paty, "service_condition", $this->service_paty_name, $id, $this->time);
                    $data9 = get_cache_1_1($this->service_paty, "patient_classify", $this->service_paty_name, $id, $this->time);
                    $data10 = get_cache_1_1($this->service_paty, "ration_time", $this->service_paty_name, $id, $this->time);
                    $data11 = get_cache_1_1_1($this->service_paty, "service.service_type", $this->service_paty_name, $id, $this->time);
                    $data12 = Cache::remember('request_room_' .$this->service_paty_name. $id, $this->time, function () use ($id, $data_a) {
                        $data = $this->room
                        ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                        ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                        ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                        ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                        ->leftJoin('his_refectory as refectory', 'his_room.id', '=', 'refectory.room_id')
                        ->leftJoin('his_sample_room as sample_room', 'his_room.id', '=', 'sample_room.room_id')
                        ->leftJoin('his_medi_stock as medi_stock', 'his_room.id', '=', 'medi_stock.room_id')
                        ->leftJoin('his_data_store as data_store', 'his_room.id', '=', 'data_store.room_id')
                        ->leftJoin('his_station as station', 'his_room.id', '=', 'station.room_id')
        
                        ->select(
                            'his_room.id',
                            'his_room.department_id',
                            'his_room.room_type_id',
                            DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                            NVL(cashier.cashier_room_name, 
                            NVL(execute.execute_room_name, 
                            NVL(reception.reception_room_name,
                            NVL(refectory.refectory_name,
                            NVL(sample_room.sample_room_name,
                            NVL(medi_stock.medi_stock_name,
                            NVL(data_store.data_store_name,
                            station.station_name)))))))) AS "room_name"'),
                            DB::connection('oracle_his')->raw('NVL(bed.bed_room_code, 
                            NVL(cashier.cashier_room_code, 
                            NVL(execute.execute_room_code, 
                            NVL(reception.reception_room_code,
                            NVL(refectory.refectory_code,
                            NVL(sample_room.sample_room_code,
                            NVL(medi_stock.medi_stock_code,
                            NVL(data_store.data_store_code,
                            station.station_code)))))))) AS "room_code"')
                        )
                        ->whereNotNull(DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                        NVL(cashier.cashier_room_name, 
                        NVL(execute.execute_room_name, 
                        NVL(reception.reception_room_name,
                        NVL(refectory.refectory_name,
                        NVL(sample_room.sample_room_name,
                        NVL(medi_stock.medi_stock_name,
                        NVL(data_store.data_store_name,
                        station.station_name))))))))'));
                        if ($id !== null) {
                            $data = $data->where(function ($query) use ($data_a) {
                                $query = $query->whereIn(DB::connection('oracle_his')->raw("his_room.id"), explode(",",$data_a->request_room_ids));
                            });
                        } 
                            $data = $data    
                            ->get();
                        return $data;
                    });
                    $data13 = Cache::remember('execute_room_' .$this->service_paty_name. $id, $this->time, function () use ($id, $data_a) {
                        $data = $this->room
                        ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                        ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                        ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                        ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                        ->leftJoin('his_refectory as refectory', 'his_room.id', '=', 'refectory.room_id')
                        ->leftJoin('his_sample_room as sample_room', 'his_room.id', '=', 'sample_room.room_id')
                        ->leftJoin('his_medi_stock as medi_stock', 'his_room.id', '=', 'medi_stock.room_id')
                        ->leftJoin('his_data_store as data_store', 'his_room.id', '=', 'data_store.room_id')
                        ->leftJoin('his_station as station', 'his_room.id', '=', 'station.room_id')
        
                        ->select(
                            'his_room.id',
                            'his_room.department_id',
                            'his_room.room_type_id',
                            DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                            NVL(cashier.cashier_room_name, 
                            NVL(execute.execute_room_name, 
                            NVL(reception.reception_room_name,
                            NVL(refectory.refectory_name,
                            NVL(sample_room.sample_room_name,
                            NVL(medi_stock.medi_stock_name,
                            NVL(data_store.data_store_name,
                            station.station_name)))))))) AS "room_name"'),
                            DB::connection('oracle_his')->raw('NVL(bed.bed_room_code, 
                            NVL(cashier.cashier_room_code, 
                            NVL(execute.execute_room_code, 
                            NVL(reception.reception_room_code,
                            NVL(refectory.refectory_code,
                            NVL(sample_room.sample_room_code,
                            NVL(medi_stock.medi_stock_code,
                            NVL(data_store.data_store_code,
                            station.station_code)))))))) AS "room_code"')
                        )
                        ->whereNotNull(DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                        NVL(cashier.cashier_room_name, 
                        NVL(execute.execute_room_name, 
                        NVL(reception.reception_room_name,
                        NVL(refectory.refectory_name,
                        NVL(sample_room.sample_room_name,
                        NVL(medi_stock.medi_stock_name,
                        NVL(data_store.data_store_name,
                        station.station_name))))))))'));
                        if ($id !== null) {
                            $data = $data->where(function ($query) use ($data_a) {
                                $query = $query->whereIn(DB::connection('oracle_his')->raw("his_room.id"), explode(",",$data_a->execute_room_ids));
                            });
                        } 
                            $data = $data    
                            ->get();
                        return $data;
                    });
                }

                $data_param = [
                    'service_paty' => $data,
                    'service' => $data1 ?? null,
                    'service_type' => $data11 ?? null,
                    'patient_type' => $data2 ?? null,
                    'branch' => $data3 ?? null,
                    'request_room' => $data12 ?? null,
                    'execute_room' => $data13 ?? null,
                    'request_deparment' => $data6 ?? null,
                    'package' => $data7 ?? null,
                    'service_condition' => $data8 ?? null,
                    'patient_classify' => $data9 ?? null,
                    'ration_time' => $data10 ?? null
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

    public function service_paty_create(CreateServicePatyRequest $request)
    {
        foreach($request->branch_ids as $branch => $branch_id){
            dd($branch_id);
        }
        $data = $this->service_paty::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'is_active' => 1,
            'is_delete' => 0,

        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->service_paty_name));
        return return_data_create_success($data);
    }

    public function service_paty_update(UpdateServicePatyRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->service_paty->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'area_code' => $request->area_code,
            'area_name' => $request->area_name,
            'department_id' => $request->department_id,
            'is_active' => $request->is_active
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->service_paty_name));
        return return_data_update_success($data);
    }

    public function service_paty_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->service_paty->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->service_paty_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
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
