<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\AccidentCare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AccidentCareController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->accident_care = new AccidentCare();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->accident_care);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function accident_care($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->accident_care
                    ->select(
                        'his_accident_care.*',
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('his_accident_care.accident_care_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_accident_care.is_active'), $this->is_active);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_accident_care.' . $key, $item);
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
                    $data = Cache::remember($this->accident_care_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->accident_care
                        ->select(
                            'his_accident_care.*',
                        );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_accident_care.is_active'), $this->is_active);
                            });
                        }

                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_accident_care.' . $key, $item);
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
                    $check_id = $this->check_id($id, $this->accident_care, $this->accident_care_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->accident_care_name . '_' . $id . '_is_active_' . $this->is_active , $this->time, function () use ($id) {
                        $data = $this->accident_care
                        ->select(
                            'his_accident_care.*',
                        )
                            ->where('his_accident_care.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_accident_care.is_active'), $this->is_active);
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
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? ($data['data'] ?? null) ?? null);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
    // /// Accident Care
    // public function accident_care($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->accident_care_name;
    //         $param = [];
    //     } else {
    //         $name = $this->accident_care_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->accident_care, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
}
