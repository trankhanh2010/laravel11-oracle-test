<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\MedicineTypeAcIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MedicineTypeAcinController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->medicine_type_acin = new MedicineTypeAcIn();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->medicine_type_acin);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function medicine_type_acin($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->medicine_type_acin
                ->leftJoin('his_medicine_type as medicine_type', 'medicine_type.id', '=', 'his_medicine_type_acin.medicine_type_id')
                ->leftJoin('his_active_ingredient as active_ingredient', 'active_ingredient.id', '=', 'his_medicine_type_acin.active_ingredient_id')
                    ->select(
                        'his_medicine_type_acin.*',
                        'medicine_type.medicine_type_code',
                        'medicine_type.medicine_type_name',
                        'active_ingredient.active_ingredient_code',
                        'active_ingredient.active_ingredient_name'
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('active_ingredient.active_ingredient_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_medicine_type_acin.medicine_type_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_medicine_type_acin.is_active'), $this->is_active);
                    });
                }
                if ($this->medicine_type_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_medicine_type_acin.medicine_type_id'), $this->medicine_type_id);
                    });
                }
                if ($this->active_ingredient_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_medicine_type_acin.active_ingredient_id'), $this->active_ingredient_id);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_medicine_type_acin.' . $key, $item);
                    }
                }
                if($this->get_all){
                    $data = $data
                    ->get();
                }else{
                    $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
                }
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->medicine_type_acin_name .'_medicine_type_id_'.$this->medicine_type_id. '_active_ingredient_id_'.$this->active_ingredient_id. '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->medicine_type_acin
                        ->leftJoin('his_medicine_type as medicine_type', 'medicine_type.id', '=', 'his_medicine_type_acin.medicine_type_id')
                        ->leftJoin('his_active_ingredient as active_ingredient', 'active_ingredient.id', '=', 'his_medicine_type_acin.active_ingredient_id')
                            ->select(
                                'his_medicine_type_acin.*',
                                'medicine_type.medicine_type_code',
                                'medicine_type.medicine_type_name',
                                'active_ingredient.active_ingredient_code',
                                'active_ingredient.active_ingredient_name'
                            );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medicine_type_acin.is_active'), $this->is_active);
                            });
                        }
                        if ($this->medicine_type_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medicine_type_acin.medicine_type_id'), $this->medicine_type_id);
                            });
                        }
                        if ($this->active_ingredient_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medicine_type_acin.active_ingredient_id'), $this->active_ingredient_id);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_medicine_type_acin.' . $key, $item);
                            }
                        }
                        if($this->get_all){
                            $data = $data
                            ->get();
                        }else{
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
                    $check_id = $this->check_id($id, $this->medicine_type_acin, $this->medicine_type_acin_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->medicine_type_acin_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->medicine_type_acin
                        ->leftJoin('his_medicine_type as medicine_type', 'medicine_type.id', '=', 'his_medicine_type_acin.medicine_type_id')
                        ->leftJoin('his_active_ingredient as active_ingredient', 'active_ingredient.id', '=', 'his_medicine_type_acin.active_ingredient_id')
                            ->select(
                                'his_medicine_type_acin.*',
                                'medicine_type.medicine_type_code',
                                'medicine_type.medicine_type_name',
                                'active_ingredient.active_ingredient_code',
                                'active_ingredient.active_ingredient_name'
                            )
                            ->where('his_medicine_type_acin.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medicine_type_acin.is_active'), $this->is_active);
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
                $this->is_active_name => $this->is_active,
                $this->medicine_type_id_name => $this->medicine_type_id,
                $this->active_ingredient_id_name => $this->active_ingredient_id,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? ($data['data'] ?? null) ?? null);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
   // /// Medicine Type Active Ingredient
    // public function medicine_type_acin($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medicine_type_acin_name;
    //         $param = [
    //             'medicine_type:id,medicine_type_name,medicine_type_code',
    //             'active_ingredient:id,active_ingredient_name,active_ingredient_code'
    //         ];
    //     } else {
    //         $name = $this->medicine_type_acin_name . '_' . $id;
    //         $param = [
    //             'medicine_type',
    //             'active_ingredient'
    //         ];
    //     }
    //     $data = get_cache_full($this->medicine_type_acin, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function medicine_type_with_active_ingredient($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medicine_type_name . '_with_' . $this->active_ingredient_name;
    //         $param = [
    //             'active_ingredients:id,active_ingredient_name,active_ingredient_code'
    //         ];
    //     } else {
    //         $name = $this->medicine_type_name . '_' . $id . '_with_' . $this->active_ingredient_name;
    //         $param = [
    //             'active_ingredients'
    //         ];
    //     }
    //     $data = get_cache_full($this->medicine_type, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function active_ingredient_with_medicine_type($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->active_ingredient_name . '_with_' . $this->medicine_type_name;
    //         $param = [
    //             'medicine_types:id,medicine_type_name,medicine_type_code'
    //         ];
    //     } else {
    //         $name = $this->active_ingredient_name . '_' . $id . '_with_' . $this->medicine_type_name;
    //         $param = [
    //             'medicine_types'
    //         ];
    //     }
    //     $data = get_cache_full($this->active_ingredient, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
}