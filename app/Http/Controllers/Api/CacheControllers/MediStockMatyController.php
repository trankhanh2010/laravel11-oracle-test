<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\MediStockMaty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MediStockMatyController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->medi_stock_maty = new MediStockMaty();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->medi_stock_maty);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function medi_stock_maty_list($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->medi_stock_maty
                ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_medi_stock_maty.medi_stock_id')
                ->leftJoin('his_material_type as material_type', 'material_type.id', '=', 'his_medi_stock_maty.material_type_id')
                ->leftJoin('his_service_unit as service_unit', 'service_unit.id', '=', 'material_type.tdl_service_unit_id')
                ->leftJoin('his_medi_stock as exp_medi_stock', 'exp_medi_stock.id', '=', 'his_medi_stock_maty.exp_medi_stock_id')

                    ->select(
                        'his_medi_stock_maty.*',
                        'medi_stock.medi_stock_code',
                        'medi_stock.medi_stock_name',
                        'service_unit.service_unit_code',
                        'service_unit.service_unit_name',
                        'material_type.material_type_code',
                        'material_type.material_type_name',
                        'exp_medi_stock.medi_stock_code as exp_medi_stock_code',
                        'exp_medi_stock.medi_stock_name as exp_medi_stock_name',
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('material_type.material_type_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_medi_stock_maty.medi_stock_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_maty.is_active'), $this->is_active);
                    });
                }
                if ($this->medi_stock_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_maty.medi_stock_id'), $this->medi_stock_id);
                    });
                }
                if ($this->material_type_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_maty.material_type_id'), $this->material_type_id);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_medi_stock_maty.' . $key, $item);
                    }
                }
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->medi_stock_maty_name .'_medi_stock_id_'.$this->medi_stock_id. '_material_type_id_'.$this->material_type_id. '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active, $this->time, function () {
                        $data = $this->medi_stock_maty
                        ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_medi_stock_maty.medi_stock_id')
                        ->leftJoin('his_material_type as material_type', 'material_type.id', '=', 'his_medi_stock_maty.material_type_id')
                        ->leftJoin('his_service_unit as service_unit', 'service_unit.id', '=', 'material_type.tdl_service_unit_id')
                        ->leftJoin('his_medi_stock as exp_medi_stock', 'exp_medi_stock.id', '=', 'his_medi_stock_maty.exp_medi_stock_id')
        
                            ->select(
                                'his_medi_stock_maty.*',
                                'medi_stock.medi_stock_code',
                                'medi_stock.medi_stock_name',
                                'service_unit.service_unit_code',
                                'service_unit.service_unit_name',
                                'material_type.material_type_code',
                                'material_type.material_type_name',
                                'exp_medi_stock.medi_stock_code as exp_medi_stock_code',
                                'exp_medi_stock.medi_stock_name as exp_medi_stock_name',
                            );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_maty.is_active'), $this->is_active);
                            });
                        }
                        if ($this->medi_stock_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_maty.medi_stock_id'), $this->medi_stock_id);
                            });
                        }
                        if ($this->material_type_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_maty.material_type_id'), $this->material_type_id);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_medi_stock_maty.' . $key, $item);
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
                    $data = $this->medi_stock_maty->find($id);
                    if ($data == null) {
                        return return_not_record($id);
                    }
                    $data = Cache::remember($this->medi_stock_maty_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->medi_stock_maty
                        ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_medi_stock_maty.medi_stock_id')
                        ->leftJoin('his_material_type as material_type', 'material_type.id', '=', 'his_medi_stock_maty.material_type_id')
                        ->leftJoin('his_service_unit as service_unit', 'service_unit.id', '=', 'material_type.tdl_service_unit_id')
                        ->leftJoin('his_medi_stock as exp_medi_stock', 'exp_medi_stock.id', '=', 'his_medi_stock_maty.exp_medi_stock_id')
        
                            ->select(
                                'his_medi_stock_maty.*',
                                'medi_stock.medi_stock_code',
                                'medi_stock.medi_stock_name',
                                'service_unit.service_unit_code',
                                'service_unit.service_unit_name',
                                'material_type.material_type_code',
                                'material_type.material_type_name',
                                'exp_medi_stock.medi_stock_code as exp_medi_stock_code',
                                'exp_medi_stock.medi_stock_name as exp_medi_stock_name',
                            )
                            ->where('his_medi_stock_maty.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_maty.is_active'), $this->is_active);
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
                'material_type_id' => $this->material_type_id,
                'keyword' => $this->keyword,
                'order_by' => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? $data['data'] ?? null);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
   // /// Medi Stock Maty List
    // public function medi_stock_maty_list($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medi_stock_maty_list_name;
    //         $param = [
    //             'medi_stock:id,medi_stock_name,medi_stock_code',
    //             'material_type:id,material_type_name,material_type_code',
    //             'exp_medi_stock:id,medi_stock_name,medi_stock_code'
    //         ];
    //     } else {
    //         $name = $this->medi_stock_maty_list_name . '_' . $id;
    //         $param = [
    //             'medi_stock',
    //             'material_type',
    //             'exp_medi_stock'
    //         ];
    //     }
    //     $data = get_cache_full($this->medi_stock_maty_list, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function medi_stock_with_material_type($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medi_stock_name . '_with_' . $this->material_type_name;
    //         $param = [
    //             'material_types:id,material_type_name,material_type_code,tdl_service_unit_id',
    //             'material_types.service_unit:id,service_unit_name,service_unit_code'
    //         ];
    //     } else {
    //         $name = $this->medi_stock_name . '_' . $id . '_with_' . $this->material_type_name;
    //         $param = [
    //             'material_types',
    //             'material_types.service_unit'
    //         ];
    //     }
    //     $data = get_cache_full($this->medi_stock, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function material_type_with_medi_stock($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->material_type_name . '_with_' . $this->medi_stock_name;
    //         $param = [
    //             'medi_stocks:id,medi_stock_name,medi_stock_code',
    //             'service_unit:id,service_unit_name,service_unit_code',
    //         ];
    //     } else {
    //         $name = $this->material_type_name . '_' . $id . '_with_' . $this->medi_stock_name;
    //         $param = [
    //             'medi_stocks',
    //             'service_unit',
    //         ];
    //     }
    //     $data = get_cache_full($this->material_type, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
}
