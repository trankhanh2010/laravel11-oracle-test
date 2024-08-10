<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ServSegr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServSegrController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->serv_segr = new ServSegr();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->serv_segr);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function serv_segr($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->serv_segr
                    ->leftJoin('his_service as service', 'service.id', '=', 'his_serv_segr.service_id')
                    ->leftJoin('his_service_group as service_group', 'service_group.id', '=', 'his_serv_segr.service_group_id')
                    ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                    ->select(
                        'his_serv_segr.*',
                        'service.service_name',
                        'service.service_code',
                        'service_type.service_type_name',
                        'service_type.service_type_code',
                        'service_group.service_group_name',
                        'service_group.service_group_code',
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('service.service_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('service_type.service_type_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('service_group.service_group_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_serv_segr.is_active'), $this->is_active);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_serv_segr.' . $key, $item);
                    }
                }
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->serv_segr_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active, $this->time, function () {
                        $data = $this->serv_segr
                            ->leftJoin('his_service as service', 'service.id', '=', 'his_serv_segr.service_id')
                            ->leftJoin('his_service_group as service_group', 'service_group.id', '=', 'his_serv_segr.service_group_id')
                            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                            ->select(
                                'his_serv_segr.*',
                                'service.service_name',
                                'service.service_code',
                                'service_type.service_type_name',
                                'service_type.service_type_code',
                                'service_group.service_group_name',
                                'service_group.service_group_code',
                            );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_serv_segr.is_active'), $this->is_active);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_serv_segr.' . $key, $item);
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
                    $check_id = $this->check_id($id, $this->serv_segr, $this->serv_segr_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->serv_segr_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->serv_segr
                            ->leftJoin('his_service as service', 'service.id', '=', 'his_serv_segr.service_id')
                            ->leftJoin('his_service_group as service_group', 'service_group.id', '=', 'his_serv_segr.service_group_id')
                            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                            ->select(
                                'his_serv_segr.*',
                                'service.service_name',
                                'service.service_code',
                                'service_type.service_type_name',
                                'service_type.service_type_code',
                                'service_group.service_group_name',
                                'service_group.service_group_code',
                            )
                            ->where('his_serv_segr.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_serv_segr.is_active'), $this->is_active);
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
                'count' => $count ?? ($data['count'] ?? null),
                'is_active' => $this->is_active,
                'keyword' => $this->keyword,
                'order_by' => $this->order_by_request
            ];
            return return_data_success($param_return, $data?? ($data['data'] ?? null));
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
}
