<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Controllers\Controller;
use App\Models\HIS\MestPatientType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MestPatientTypeController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->mest_patient_type = new MestPatientType();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->mest_patient_type);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function mest_patient_type($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->mest_patient_type
                ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_mest_patient_type.medi_stock_id')
                ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_mest_patient_type.patient_type_id')
                    ->select(
                        'his_mest_patient_type.*',
                        'medi_stock.medi_stock_code',
                        'medi_stock.medi_stock_name',
                        'patient_type.patient_type_code',
                        'patient_type.patient_type_name'
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('patient_type.patient_type_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_mest_patient_type.medi_stock_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_mest_patient_type.is_active'), $this->is_active);
                    });
                }
                if ($this->medi_stock_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_mest_patient_type.medi_stock_id'), $this->medi_stock_id);
                    });
                }
                if ($this->patient_type_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_mest_patient_type.patient_type_id'), $this->patient_type_id);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_mest_patient_type.' . $key, $item);
                    }
                }
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->mest_patient_type_name .'_medi_stock_id_'.$this->medi_stock_id. '_patient_type_id_'.$this->patient_type_id. '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active, $this->time, function () {
                        $data = $this->mest_patient_type
                        ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_mest_patient_type.medi_stock_id')
                        ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_mest_patient_type.patient_type_id')
                            ->select(
                                'his_mest_patient_type.*',
                                'medi_stock.medi_stock_code',
                                'medi_stock.medi_stock_name',
                                'patient_type.patient_type_code',
                                'patient_type.patient_type_name'
                            );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_mest_patient_type.is_active'), $this->is_active);
                            });
                        }
                        if ($this->medi_stock_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_mest_patient_type.medi_stock_id'), $this->medi_stock_id);
                            });
                        }
                        if ($this->patient_type_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_mest_patient_type.patient_type_id'), $this->patient_type_id);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_mest_patient_type.' . $key, $item);
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
                    $data = $this->mest_patient_type->find($id);
                    if ($data == null) {
                        return return_not_record($id);
                    }
                    $data = Cache::remember($this->mest_patient_type_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->mest_patient_type
                        ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_mest_patient_type.medi_stock_id')
                        ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_mest_patient_type.patient_type_id')
                            ->select(
                                'his_mest_patient_type.*',
                                'medi_stock.medi_stock_code',
                                'medi_stock.medi_stock_name',
                                'patient_type.patient_type_code',
                                'patient_type.patient_type_name'
                            )
                            ->where('his_mest_patient_type.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_mest_patient_type.is_active'), $this->is_active);
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
                'medi_stock_id' => $this->medi_stock_id,
                'patient_type_id' => $this->patient_type_id,
                'keyword' => $this->keyword,
                'order_by' => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? $data['data'] ?? null);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }

    // /// Mest Patient Type

    // public function mest_patient_type($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->mest_patient_type_name;
    //         $param = [
    //             'medi_stock:id,medi_stock_name,medi_stock_code',
    //             'patient_type:id,patient_type_name,patient_type_code'
    //         ];
    //     } else {
    //         $name = $this->mest_patient_type_name . '_' . $id;
    //         $param = [
    //             'medi_stock',
    //             'patient_type'
    //         ];
    //     }
    //     $data = get_cache_full($this->mest_patient_type, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function medi_stock_with_patient_type($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medi_stock_name . '_with_' . $this->patient_type_name;
    //         $param = [
    //             'patient_types:id,patient_type_name,patient_type_code'
    //         ];
    //     } else {
    //         $name = $this->medi_stock_name . '_' . $id . '_with_' . $this->patient_type_name;
    //         $param = [
    //             'patient_types'
    //         ];
    //     }
    //     $data = get_cache_full($this->medi_stock, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function patient_type_with_medi_stock($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->patient_type_name . '_with_' . $this->medi_stock_name;
    //         $param = [
    //             'medi_stocks:id,medi_stock_name,medi_stock_code'
    //         ];
    //     } else {
    //         $name = $this->patient_type_name . '_' . $id . '_with_' . $this->medi_stock_name;
    //         $param = [
    //             'medi_stocks'
    //         ];
    //     }
    //     $data = get_cache_full($this->patient_type, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
}
