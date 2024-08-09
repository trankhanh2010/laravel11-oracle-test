<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\PtttTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PtttTableController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->pttt_table = new PtttTable();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->pttt_table);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function pttt_table($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->pttt_table
                ->leftJoin('his_execute_room as execute_room', 'execute_room.id', '=', 'his_pttt_table.execute_room_id')
                ->leftJoin('his_room as room', 'room.id', '=', 'execute_room.room_id')
                ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')
                ->leftJoin('his_area as area', 'area.id', '=', 'room.area_id')
                    ->select(
                        'his_pttt_table.*',
                        'execute_room.execute_room_code',
                        'execute_room.execute_room_name',
                        'execute_room.MAX_REQUEST_BY_DAY',
                        'department.department_code',
                        'department.department_name',
                        'area.area_code',
                        'area.area_name',
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('his_pttt_table.pttt_table_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_pttt_table.is_active'), $this->is_active);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_pttt_table.' . $key, $item);
                    }
                }
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->pttt_table_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active, $this->time, function () {
                        $data = $this->pttt_table
                        ->leftJoin('his_execute_room as execute_room', 'execute_room.id', '=', 'his_pttt_table.execute_room_id')
                        ->leftJoin('his_room as room', 'room.id', '=', 'execute_room.room_id')
                        ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')
                        ->leftJoin('his_area as area', 'area.id', '=', 'room.area_id')
                            ->select(
                                'his_pttt_table.*',
                                'execute_room.execute_room_code',
                                'execute_room.execute_room_name',
                                'execute_room.MAX_REQUEST_BY_DAY',
                                'department.department_code',
                                'department.department_name',
                                'area.area_code',
                                'area.area_name',
                            );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_pttt_table.is_active'), $this->is_active);
                            });
                        }

                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_pttt_table.' . $key, $item);
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
                    $data = $this->pttt_table->find($id);
                    if ($data == null) {
                        return return_not_record($id);
                    }
                    $data = Cache::remember($this->pttt_table_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->pttt_table
                        ->leftJoin('his_execute_room as execute_room', 'execute_room.id', '=', 'his_pttt_table.execute_room_id')
                        ->leftJoin('his_room as room', 'room.id', '=', 'execute_room.room_id')
                        ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')
                        ->leftJoin('his_area as area', 'area.id', '=', 'room.area_id')
                            ->select(
                                'his_pttt_table.*',
                                'execute_room.execute_room_code',
                                'execute_room.execute_room_name',
                                'execute_room.MAX_REQUEST_BY_DAY',
                                'department.department_code',
                                'department.department_name',
                                'area.area_code',
                                'area.area_name',
                            )
                            ->where('his_pttt_table.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_pttt_table.is_active'), $this->is_active);
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
    // /// Pttt Table
    // public function pttt_table($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->pttt_table_name;
    //         $param = [
    //             'execute_room:id,execute_room_name,execute_room_code'
    //         ];
    //     } else {
    //         $name = $this->pttt_table_name . '_' . $id;
    //         $param = [
    //             'execute_room'
    //         ];
    //     }
    //     $data = get_cache_full($this->pttt_table, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
    
}
