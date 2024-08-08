<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\PatientTypeAllow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PatientTypeAllowController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->patient_type_allow = new PatientTypeAllow();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->patient_type_allow);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function patient_type_allow($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->patient_type_allow
                ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_patient_type_allow.patient_type_id')
                ->leftJoin('his_patient_type as patient_type_allow', 'patient_type_allow.id', '=', 'his_patient_type_allow.patient_type_allow_id')

                    ->select(
                        'his_patient_type_allow.*',
                        'patient_type_allow.patient_type_code as patient_type_allow_code',
                        'patient_type_allow.patient_type_name as patient_type_allow_name',
                        'patient_type.patient_type_code',
                        'patient_type.patient_type_name',
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('patient_type_allow.patient_type_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('patient_type.patient_type_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_allow.is_active'), $this->is_active);
                    });
                }
                if ($this->patient_type_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_allow.patient_type_id'), $this->patient_type_id);
                    });
                }
                if ($this->patient_type_allow_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_allow.patient_type_allow_id'), $this->patient_type_allow_id);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_patient_type_allow.' . $key, $item);
                    }
                }
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->patient_type_allow_name .'_patient_type_id_'.$this->patient_type_id. '_patient_type_allow_id_'.$this->patient_type_allow_id. '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active, $this->time, function () {
                        $data = $this->patient_type_allow
                        ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_patient_type_allow.patient_type_id')
                        ->leftJoin('his_patient_type as patient_type_allow', 'patient_type_allow.id', '=', 'his_patient_type_allow.patient_type_allow_id')
        
                            ->select(
                                'his_patient_type_allow.*',
                                'patient_type_allow.patient_type_code as patient_type_allow_code',
                                'patient_type_allow.patient_type_name as patient_type_allow_name',
                                'patient_type.patient_type_code',
                                'patient_type.patient_type_name',
                            );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_allow.is_active'), $this->is_active);
                            });
                        }
                        if ($this->patient_type_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_allow.patient_type_id'), $this->patient_type_id);
                            });
                        }
                        if ($this->patient_type_allow_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_allow.patient_type_allow_id'), $this->patient_type_allow_id);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_patient_type_allow.' . $key, $item);
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
                    $data = $this->patient_type_allow->find($id);
                    if ($data == null) {
                        return return_not_record($id);
                    }
                    $data = Cache::remember($this->patient_type_allow_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->patient_type_allow
                        ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_patient_type_allow.patient_type_id')
                        ->leftJoin('his_patient_type as patient_type_allow', 'patient_type_allow.id', '=', 'his_patient_type_allow.patient_type_allow_id')
        
                            ->select(
                                'his_patient_type_allow.*',
                                'patient_type_allow.patient_type_code as patient_type_allow_code',
                                'patient_type_allow.patient_type_name as patient_type_allow_name',
                                'patient_type.patient_type_code',
                                'patient_type.patient_type_name',
                            )
                            ->where('his_patient_type_allow.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_patient_type_allow.is_active'), $this->is_active);
                            });
                        }
                        $data = $data->first();
                        return $data;
                    });
                }
            }
            $param_return = [
                'start' => $this->start,
                'limit' => $this->limit,
                'count' => $count ?? (is_array($data) ? $data['count'] : null),
                'is_active' => $this->is_active,
                'patient_type_id' => $this->patient_type_id,
                'patient_type_allow_id' => $this->patient_type_allow_id,
                'keyword' => $this->keyword,
                'order_by' => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? $data['data'] ?? null);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
    // /// Patient Type Allow
    // public function patient_type_allow($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->patient_type_allow_name;
    //         $param = [
    //             'patient_type',
    //             'patient_type_allow'
    //         ];
    //     } else {
    //         $name = $this->patient_type_allow_name . '_' . $id;
    //         $param = [
    //             'patient_type',
    //             'patient_type_allow'
    //         ];
    //     }
    //     $data = get_cache_full($this->patient_type_allow, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
}
