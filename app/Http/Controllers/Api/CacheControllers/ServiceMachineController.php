<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\Machine;
use App\Models\HIS\Service;
use App\Models\HIS\ServiceMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServiceMachineController extends BaseApiCacheController
{

    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->service_machine = new ServiceMachine();
        $this->service = new Service();
        $this->machine = new Machine();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->service_machine);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function service_machine($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if (($keyword != null)) {
                $data = $this->service_machine
                    ->leftJoin('his_service as service', 'service.id', '=', 'his_service_machine.service_id')
                    ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                    ->leftJoin('his_machine as machine', 'machine.id', '=', 'his_service_machine.machine_id')

                    ->select(
                        'his_service_machine.*',
                        'service.service_name',
                        'service.service_code',
                        'service_type.service_type_name',
                        'service_type.service_type_code',
                        'machine.machine_name',
                        'machine.machine_code',
                        'machine.machine_group_code'
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('service.service_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('machine.machine_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_service_machine.is_active'), $this->is_active);
                    });
                }
                if ($this->service_ids != null) {
                    $data = $data->where(function ($query) {
                        $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_machine.service_id'), $this->service_ids);
                    });
                }
                if ($this->machine_ids != null) {
                    $data = $data->where(function ($query) {
                        $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_machine.machine_id'), $this->machine_ids);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_service_machine.' . $key, $item);
                    }
                }
                if ($this->get_all) {
                    $data = $data
                        ->get();
                } else {
                    $data = $data
                        ->skip($this->start)
                        ->take($this->limit)
                        ->get();
                }
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->service_machine_name . '_start_' . $this->start . '_machine_ids_' . $this->machine_ids_string . '_service_ids_' . $this->service_ids_string . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active . '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->service_machine
                            ->leftJoin('his_service as service', 'service.id', '=', 'his_service_machine.service_id')
                            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                            ->leftJoin('his_machine as machine', 'machine.id', '=', 'his_service_machine.machine_id')

                            ->select(
                                'his_service_machine.*',
                                'service.service_name',
                                'service.service_code',
                                'service_type.service_type_name',
                                'service_type.service_type_code',
                                'machine.machine_name',
                                'machine.machine_code',
                                'machine.machine_group_code'
                            );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_service_machine.is_active'), $this->is_active);
                            });
                        }
                        if ($this->service_ids != null) {
                            $data = $data->where(function ($query) {
                                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_machine.service_id'), $this->service_ids);
                            });
                        }
                        if ($this->machine_ids != null) {
                            $data = $data->where(function ($query) {
                                $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_machine.machine_id'), $this->machine_ids);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_service_machine.' . $key, $item);
                            }
                        }
                        if ($this->get_all) {
                            $data = $data
                                ->get();
                        } else {
                            $data = $data
                                ->skip($this->start)
                                ->take($this->limit)
                                ->get();
                        }
                        return ['data' => $data, 'count' => $count];
                    });
                } else {
                    if (!is_numeric($id)) {
                        return return_id_error($id);
                    }
                    $check_id = $this->check_id($id, $this->service_machine, $this->service_machine_name);
                    if ($check_id) {
                        return $check_id;
                    }
                    $data = Cache::remember($this->service_machine_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->service_machine
                            ->leftJoin('his_service as service', 'service.id', '=', 'his_service_machine.service_id')
                            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                            ->leftJoin('his_machine as machine', 'machine.id', '=', 'his_service_machine.machine_id')

                            ->select(
                                'his_service_machine.*',
                                'service.service_name',
                                'service.service_code',
                                'service_type.service_type_name',
                                'service_type.service_type_code',
                                'machine.machine_name',
                                'machine.machine_code',
                                'machine.machine_group_code'
                            )
                            ->where('his_service_machine.id', $id);;
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_service_machine.is_active'), $this->is_active);
                            });
                        }
                        $data = $data->first();
                        return $data;
                    });
                }
            }
            $param_return = [
                $this->get_all_name => $this->get_all,
                $this->start_name => ($this->get_all || !is_null($id)) ? null : $this->start,
                $this->limit_name => ($this->get_all || !is_null($id)) ? null : $this->limit,
                $this->count_name => $count ?? ($data['count'] ?? null),
                $this->service_ids_name => $this->service_ids ?? null,
                $this->machine_ids_name => $this->machine_ids ?? null,
                $this->is_active_name => $this->is_active,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data['data'] ?? $data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }

    // public function service_with_machine($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->service_name . '_with_' . $this->machine_name;
    //         $param = [
    //             'machines:id,machine_name,machine_code',
    //         ];
    //     } else {
    //         $name = $this->service_name . '_' . $id . '_with_' . $this->machine_name;
    //         $param = [
    //             'machines:id,machine_name,machine_code',
    //         ];
    //     }
    //     $data = get_cache_full($this->service, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active);
    //     $param_return = [
    //     ];
    //     return return_data_success($param_return, $data['data'] ?? $data);
    // }

    // public function machine_with_service($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->machine_name . '_with_' . $this->service_name;
    //         $param = [
    //             'services:id,service_name,service_code',
    //         ];
    //     } else {
    //         $name = $this->machine_name . '_' . $id . '_with_' . $this->service_name;
    //         $param = [
    //             'services',
    //         ];
    //     }
    //     $data = get_cache_full($this->machine, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active);
    //     return response()->json(['data' => $data], 200);
    // }
}
