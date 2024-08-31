<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MediStockMety\CreateMediStockMetyRequest;
use App\Models\HIS\MedicineType;
use App\Models\HIS\MediStock;
use App\Models\HIS\MediStockMety;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MediStockMetyController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->medi_stock_mety = new MediStockMety();
        $this->medi_stock = new MediStock();
        $this->medicine_type = new MedicineType();
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->medi_stock_mety);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function medi_stock_mety_list($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->medi_stock_mety
                ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_medi_stock_mety.medi_stock_id')
                ->leftJoin('his_medicine_type as medicine_type', 'medicine_type.id', '=', 'his_medi_stock_mety.medicine_type_id')
                ->leftJoin('his_service_unit as service_unit', 'service_unit.id', '=', 'medicine_type.tdl_service_unit_id')
                ->leftJoin('his_medi_stock as exp_medi_stock', 'exp_medi_stock.id', '=', 'his_medi_stock_mety.exp_medi_stock_id')

                    ->select(
                        'his_medi_stock_mety.*',
                        'medi_stock.medi_stock_code as medi_stock_code',
                        'medi_stock.medi_stock_name',
                        'service_unit.service_unit_code',
                        'service_unit.service_unit_name',
                        'medicine_type.medicine_type_code',
                        'medicine_type.medicine_type_name',
                        'medicine_type.CONCENTRA',
                        'medicine_type.REGISTER_NUMBER',
                        'medicine_type.ACTIVE_INGR_BHYT_CODE',
                        'medicine_type.ACTIVE_INGR_BHYT_NAME',
                        'medicine_type.DISTRIBUTED_AMOUNT',
                        'exp_medi_stock.medi_stock_code as exp_medi_stock_code',
                        'exp_medi_stock.medi_stock_name as exp_medi_stock_name',
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('medicine_type.medicine_type_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('medi_stock.medi_stock_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_mety.is_active'), $this->is_active);
                    });
                }
                if ($this->medi_stock_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_mety.medi_stock_id'), $this->medi_stock_id);
                    });
                }
                if ($this->medicine_type_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_mety.medicine_type_id'), $this->medicine_type_id);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_medi_stock_mety.' . $key, $item);
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
                    $data = Cache::remember($this->medi_stock_mety_name .'_medi_stock_id_'.$this->medi_stock_id. '_medicine_type_id_'.$this->medicine_type_id. '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->medi_stock_mety
                        ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_medi_stock_mety.medi_stock_id')
                        ->leftJoin('his_medicine_type as medicine_type', 'medicine_type.id', '=', 'his_medi_stock_mety.medicine_type_id')
                        ->leftJoin('his_service_unit as service_unit', 'service_unit.id', '=', 'medicine_type.tdl_service_unit_id')
                        ->leftJoin('his_medi_stock as exp_medi_stock', 'exp_medi_stock.id', '=', 'his_medi_stock_mety.exp_medi_stock_id')

                            ->select(
                                'his_medi_stock_mety.*',
                                'medi_stock.medi_stock_code',
                                'medi_stock.medi_stock_name',
                                'service_unit.service_unit_code',
                                'service_unit.service_unit_name',
                                'medicine_type.medicine_type_code',
                                'medicine_type.medicine_type_name',
                                'medicine_type.CONCENTRA',
                                'medicine_type.REGISTER_NUMBER',
                                'medicine_type.ACTIVE_INGR_BHYT_CODE',
                                'medicine_type.ACTIVE_INGR_BHYT_NAME',
                                'medicine_type.DISTRIBUTED_AMOUNT',
                                'exp_medi_stock.medi_stock_code as exp_medi_stock_code',
                                'exp_medi_stock.medi_stock_name as exp_medi_stock_name',
                            );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_mety.is_active'), $this->is_active);
                            });
                        }
                        if ($this->medi_stock_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_mety.medi_stock_id'), $this->medi_stock_id);
                            });
                        }
                        if ($this->medicine_type_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_mety.medicine_type_id'), $this->medicine_type_id);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_medi_stock_mety.' . $key, $item);
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
                    $check_id = $this->check_id($id, $this->medi_stock_mety, $this->medi_stock_mety_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->medi_stock_mety_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->medi_stock_mety
                        ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_medi_stock_mety.medi_stock_id')
                        ->leftJoin('his_medicine_type as medicine_type', 'medicine_type.id', '=', 'his_medi_stock_mety.medicine_type_id')
                        ->leftJoin('his_service_unit as service_unit', 'service_unit.id', '=', 'medicine_type.tdl_service_unit_id')
                        ->leftJoin('his_medi_stock as exp_medi_stock', 'exp_medi_stock.id', '=', 'his_medi_stock_mety.exp_medi_stock_id')

                            ->select(
                                'his_medi_stock_mety.*',
                                'medi_stock.medi_stock_code',
                                'medi_stock.medi_stock_name',
                                'service_unit.service_unit_code',
                                'service_unit.service_unit_name',
                                'medicine_type.medicine_type_code',
                                'medicine_type.medicine_type_name',
                                'medicine_type.CONCENTRA',
                                'medicine_type.REGISTER_NUMBER',
                                'medicine_type.ACTIVE_INGR_BHYT_CODE',
                                'medicine_type.ACTIVE_INGR_BHYT_NAME',
                                'medicine_type.DISTRIBUTED_AMOUNT',
                                'exp_medi_stock.medi_stock_code as exp_medi_stock_code',
                                'exp_medi_stock.medi_stock_name as exp_medi_stock_name',
                            )
                            ->where('his_medi_stock_mety.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medi_stock_mety.is_active'), $this->is_active);
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
                $this->medi_stock_id_name => $this->medi_stock_id,
                $this->medicine_type_id_name => $this->medicine_type_id,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? ($data['data'] ?? null) ?? null);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }
       // /// Medi Stock Mety List

    // public function medi_stock_mety_list($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medi_stock_mety_list_name;
    //         $param = [
    //             'medi_stock:id,medi_stock_name,medi_stock_code',
    //             'medicine_type:id,medicine_type_name,medicine_type_code',
    //             'exp_medi_stock:id,medi_stock_name,medi_stock_code'
    //         ];
    //     } else {
    //         $name = $this->medi_stock_mety_list_name . '_' . $id;
    //         $param = [
    //             'medi_stock',
    //             'medicine_type',
    //             'exp_medi_stock'
    //         ];
    //     }
    //     $data = get_cache_full($this->medi_stock_mety_list, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function medi_stock_with_medicine_type($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medi_stock_name . '_with_' . $this->medicine_type_name;
    //         $param = [
    //             'medicine_types:id,medicine_type_name,medicine_type_code,tdl_service_unit_id',
    //             'medicine_types.service_unit:id,service_unit_name,service_unit_code'
    //         ];
    //     } else {
    //         $name = $this->medi_stock_name . '_' . $id . '_with_' . $this->medicine_type_name;
    //         $param = [
    //             'medicine_types',
    //             'medicine_types.service_unit'

    //         ];
    //     }
    //     $data = get_cache_full($this->medi_stock, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function medicine_type_with_medi_stock($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medicine_type_name . '_with_' . $this->medi_stock_name;
    //         $param = [
    //             'medi_stocks:id,medi_stock_name,medi_stock_code',
    //             'service_unit:id,service_unit_name,service_unit_code'
    //         ];
    //     } else {
    //         $name = $this->medicine_type_name . '_' . $id . '_with_' . $this->medi_stock_name;
    //         $param = [
    //             'medi_stocks',
    //             'service_unit'
    //         ];
    //     }
    //     $data = get_cache_full($this->medicine_type, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
    public function medi_stock_mety_list_create(CreateMediStockMetyRequest $request)
    {
        if($request->medi_stock_id != null){
            $id = $request->medi_stock_id;
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->medi_stock->find($id);
            if ($data == null) {
                return return_not_record($id);
            }   
            // Start transaction
            DB::connection('oracle_his')->beginTransaction();
            try {
                if($request->medicine_type_ids !== null){
                    $medicine_type_ids_arr = explode(',', $request->medicine_type_ids);
                    foreach($medicine_type_ids_arr as $key => $item){
                        $medicine_type_ids_arr_data[$item] =  [
                            'create_time' => now()->format('Ymdhis'),
                            'modify_time' => now()->format('Ymdhis'),
                            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                            'app_creator' => $this->app_creator,
                            'app_modifier' => $this->app_modifier,
                            'is_prevent_max' => $request->is_prevent_max,
                            'is_prevent_exp' => $request->is_prevent_exp,
                            'is_goods_restrict' => $request->is_goods_restrict,
                        ];
                    }
                    foreach($medicine_type_ids_arr as $key => $item){
                        $data->medicine_types()->sync($medicine_type_ids_arr_data);
                    }
                }else{
                    MediStockMety::where('medi_stock_id', $data->id)->delete();
                }
                DB::connection('oracle_his')->commit();
                // Gọi event để xóa cache
                event(new DeleteCache($this->medi_stock_mety_name));
                return return_data_create_success([$data]);
            } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
                // Rollback transaction nếu có lỗi
                DB::connection('oracle_his')->rollBack();
                return return_data_fail_transaction();
            }
        }else{
            $id = $request->medicine_type_id;
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->medicine_type->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            // Start transaction
            DB::connection('oracle_his')->beginTransaction();
            try {
                if($request->medi_stock_ids !== null){
                    $medi_stock_ids_arr = explode(',', $request->medi_stock_ids);
                    foreach($medi_stock_ids_arr as $key => $item){
                        $medi_stock_ids_arr_data[$item] =  [
                            'create_time' => now()->format('Ymdhis'),
                            'modify_time' => now()->format('Ymdhis'),
                            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                            'app_creator' => $this->app_creator,
                            'app_modifier' => $this->app_modifier,
                            'is_prevent_max' => $request->is_prevent_max,
                            'is_prevent_exp' => $request->is_prevent_exp,
                            'is_goods_restrict' => $request->is_goods_restrict,
                        ];
                    }
                    foreach($medi_stock_ids_arr as $key => $item){
                        $data->medi_stocks()->sync($medi_stock_ids_arr_data);
                    }
                }else{
                    MediStockMety::where('medicine_type_id', $data->id)->delete();
                }
                DB::connection('oracle_his')->commit();
                // Gọi event để xóa cache
                event(new DeleteCache($this->medi_stock_mety_name));
                return return_data_create_success([$data]);
            } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
                // Rollback transaction nếu có lỗi
                DB::connection('oracle_his')->rollBack();
                return return_data_fail_transaction();
            }
        }
    }
}
