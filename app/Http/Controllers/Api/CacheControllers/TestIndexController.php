<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\TestIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TestIndexController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->test_index = new TestIndex();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->test_index);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function test_index($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->test_index
                ->leftJoin('his_service as service', 'service.id', '=', 'his_test_index.test_service_type_id')
                ->leftJoin('his_test_index_unit as test_index_unit', 'test_index_unit.id', '=', 'his_test_index.test_index_unit_id')
                ->leftJoin('his_test_index_group as test_index_group', 'test_index_group.id', '=', 'his_test_index.test_index_group_id')
                ->leftJoin('his_material_type as material_type', 'material_type.id', '=', 'his_test_index.material_type_id')

                    ->select(
                        'his_test_index.*',
                        'service.service_code',
                        'service.service_name',
                        'test_index_unit.test_index_unit_code',
                        'test_index_unit.test_index_unit_name',
                        'test_index_group.test_index_group_code',
                        'test_index_group.test_index_group_name',
                        'material_type.material_type_code',
                        'material_type.material_type_name',
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('his_test_index.test_index_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_test_index.is_active'), $this->is_active);
                    });
                }
                if ($this->test_service_type_id !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_test_index.test_service_type_id'), $this->test_service_type_id);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_test_index.' . $key, $item);
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
                    $data = Cache::remember($this->test_index_name .'_test_service_type_id_'.$this->test_service_type_id. '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->test_index
                        ->leftJoin('his_service as service', 'service.id', '=', 'his_test_index.test_service_type_id')
                        ->leftJoin('his_test_index_unit as test_index_unit', 'test_index_unit.id', '=', 'his_test_index.test_index_unit_id')
                        ->leftJoin('his_test_index_group as test_index_group', 'test_index_group.id', '=', 'his_test_index.test_index_group_id')
                        ->leftJoin('his_material_type as material_type', 'material_type.id', '=', 'his_test_index.material_type_id')
        
                            ->select(
                                'his_test_index.*',
                                'service.service_code',
                                'service.service_name',
                                'test_index_unit.test_index_unit_code',
                                'test_index_unit.test_index_unit_name',
                                'test_index_group.test_index_group_code',
                                'test_index_group.test_index_group_name',
                                'material_type.material_type_code',
                                'material_type.material_type_name',
                            );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_test_index.is_active'), $this->is_active);
                            });
                        }
                        if ($this->test_service_type_id !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_test_index.test_service_type_id'), $this->test_service_type_id);
                            });
                        }

                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_test_index.' . $key, $item);
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
                    $check_id = $this->check_id($id, $this->test_index, $this->test_index_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->test_index_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->test_index
                        ->leftJoin('his_service as service', 'service.id', '=', 'his_test_index.test_service_type_id')
                        ->leftJoin('his_test_index_unit as test_index_unit', 'test_index_unit.id', '=', 'his_test_index.test_index_unit_id')
                        ->leftJoin('his_test_index_group as test_index_group', 'test_index_group.id', '=', 'his_test_index.test_index_group_id')
                        ->leftJoin('his_material_type as material_type', 'material_type.id', '=', 'his_test_index.material_type_id')
        
                            ->select(
                                'his_test_index.*',
                                'service.service_code',
                                'service.service_name',
                                'test_index_unit.test_index_unit_code',
                                'test_index_unit.test_index_unit_name',
                                'test_index_group.test_index_group_code',
                                'test_index_group.test_index_group_name',
                                'material_type.material_type_code',
                                'material_type.material_type_name',
                            )
                            ->where('his_test_index.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_test_index.is_active'), $this->is_active);
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
                $this->test_service_type_id_name => $this->test_service_type_id,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? ($data['data'] ?? null) ?? null);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }
    // /// Test Index
    // public function test_index($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->test_index_name;
    //         $param = [
    //             'test_service_type:id,service_name,service_code',
    //             'test_index_unit:id,test_index_unit_name,test_index_unit_code',
    //             'test_index_group:id,test_index_group_name,test_index_group_code',
    //             'material_type:id,material_type_name,material_type_code'
    //         ];
    //     } else {
    //         $name = $this->test_index_name . '_' . $id;
    //         $param = [
    //             'test_service_type',
    //             'test_index_unit',
    //             'test_index_group',
    //             'material_type'
    //         ];
    //     }
    //     $data = get_cache_full($this->test_index, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
}
