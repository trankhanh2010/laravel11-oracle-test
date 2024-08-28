<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ServiceFollow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServiceFollowController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->service_follow = new ServiceFollow();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->service_follow);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function service_follow($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if (($keyword != null) || ($this->service_ids != null) || ($this->machine_ids != null)) {
                $data = $this->service_follow
                    ->leftJoin('his_service as service', 'service.id', '=', 'his_service_follow.service_id')
                    ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                    ->leftJoin('his_service as service_follow', 'service_follow.id', '=', 'his_service_follow.follow_id')
                    ->leftJoin('his_service_type as service_follow_type', 'service_type.id', '=', 'service_follow.service_type_id')

                    ->select(
                        'his_service_follow.*',
                        'service.service_name',
                        'service.service_code',
                        'service_type.service_type_name',
                        'service_type.service_type_code',
                        'service_follow.service_name as service_follow_name',
                        'service_follow.service_code as service_follow_code',
                        'service_follow_type.service_type_name as service_follow_type_name',
                        'service_follow_type.service_type_code as service_follow_type_code',
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('service.service_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('service_follow.service_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_service_follow.is_active'), $this->is_active);
                    });
                }
                if ($this->service_ids != null) {
                    $data = $data->where(function ($query) {
                        $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_follow.service_id'), $this->service_ids);
                    });
                }
                if ($this->service_follow_ids != null) {
                    $data = $data->where(function ($query) {
                        $query = $query->whereIn(DB::connection('oracle_his')->raw('his_service_follow.service_follow_id'), $this->service_follow_ids);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_service_follow.' . $key, $item);
                    }
                }
                if ($this->get_all) {
                    $data = $data
                        ->get();
                } else {
                    $data = $data
                        ->skip($this->start)
                        ->take($this->limit)
                        ->get();
                }
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->service_follow_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active . '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->service_follow
                            ->leftJoin('his_service as service', 'service.id', '=', 'his_service_follow.service_id')
                            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                            ->leftJoin('his_service as service_follow', 'service_follow.id', '=', 'his_service_follow.follow_id')
                            ->leftJoin('his_service_type as service_follow_type', 'service_type.id', '=', 'service_follow.service_type_id')

                            ->select(
                                'his_service_follow.*',
                                'service.service_name',
                                'service.service_code',
                                'service_type.service_type_name',
                                'service_type.service_type_code',
                                'service_follow.service_name as service_follow_name',
                                'service_follow.service_code as service_follow_code',
                                'service_follow_type.service_type_name as service_follow_type_name',
                                'service_follow_type.service_type_code as service_follow_type_code',
                            );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_service_follow.is_active'), $this->is_active);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_service_follow.' . $key, $item);
                            }
                        }
                        if ($this->get_all) {
                            $data = $data
                                ->get();
                        } else {
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
                    $check_id = $this->check_id($id, $this->service_follow, $this->service_follow_name);
                    if ($check_id) {
                        return $check_id;
                    }
                    $data = Cache::remember($this->service_follow_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->service_follow
                            ->leftJoin('his_service as service', 'service.id', '=', 'his_service_follow.service_id')
                            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
                            ->leftJoin('his_service as service_follow', 'service_follow.id', '=', 'his_service_follow.follow_id')
                            ->leftJoin('his_service_type as service_follow_type', 'service_type.id', '=', 'service_follow.service_type_id')

                            ->select(
                                'his_service_follow.*',
                                'service.service_name',
                                'service.service_code',
                                'service_type.service_type_name',
                                'service_type.service_type_code',
                                'service_follow.service_name as service_follow_name',
                                'service_follow.service_code as service_follow_code',
                                'service_follow_type.service_type_name as service_follow_type_name',
                                'service_follow_type.service_type_code as service_follow_type_code',
                            )
                            ->where('his_service_follow.id', $id);;
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_service_follow.is_active'), $this->is_active);
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
                $this->service_ids_name => $this->service_ids ?? null,
                $this->service_follow_ids_name => $this->service_follow_ids ?? null,
                $this->is_active_name => $this->is_active,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data['data'] ?? $data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
    // public function service_with_follow($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->service_name . '_with_follow' . $this->service_name;
    //         $param = [
    //             'follows:id,service_name,service_code',
    //         ];
    //     } else {
    //         $name = $this->service_name . '_' . $id . '_with_' . $this->machine_name;
    //         $param = [
    //             'follows',
    //         ];
    //     }
    //     $data = get_cache_full($this->service, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }

    // public function follow_with_service($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->service_name . '_follow_with_' . $this->service_name;
    //         $param = [
    //             'services:id,service_name,service_code',
    //         ];
    //     } else {
    //         $name = $this->service_name . '_' . $id . '_with_' . $this->service_name;
    //         $param = [
    //             'services',
    //         ];
    //     }
    //     $data = get_cache_full($this->service, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
}
