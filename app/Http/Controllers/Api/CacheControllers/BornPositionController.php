<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\BornPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BornPositionController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->born_position = new BornPosition();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->born_position);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function born_position($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->born_position
                    ->select(
                        'his_born_position.*',
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('his_born_position.born_position_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_born_position.is_active'), $this->is_active);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_born_position.' . $key, $item);
                    }
                }
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->born_position_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active, $this->time, function () {
                        $data = $this->born_position
                        ->select(
                            'his_born_position.*',
                        );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_born_position.is_active'), $this->is_active);
                            });
                        }

                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_born_position.' . $key, $item);
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
                    $data = $this->born_position->find($id);
                    if ($data == null) {
                        return return_not_record($id);
                    }
                    $data = Cache::remember($this->born_position_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->born_position
                        ->select(
                            'his_born_position.*',
                        )
                            ->where('his_born_position.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_born_position.is_active'), $this->is_active);
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
                'keyword' => $this->keyword,
                'order_by' => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? $data['data'] ?? null);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
    // /// Born born_position
    // public function born_born_position($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->born_born_position_name;
    //         $param = [];
    //     } else {
    //         $name = $this->born_born_position_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->born_born_position, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
}
