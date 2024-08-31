<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServiceCondition\CreateServiceConditionRequest;
use App\Http\Requests\ServiceCondition\UpdateServiceConditionRequest;
use App\Models\HIS\ServiceCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServiceConditionController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->service_condition = new ServiceCondition();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->service_condition);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function service_condition($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $param = [];
                $data = $this->service_condition;
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('service_condition_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('service_condition_name'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('is_active'), $this->is_active);
                    });
                }
                if ($this->service_id != null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_service_condition.service_id'), $this->service_id);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy($key, $item);
                    }
                }
                if ($this->get_all) {
                    $data = $data
                        ->with($param)
                        ->get();
                } else {
                    $data = $data
                        ->skip($this->start)
                        ->take($this->limit)
                        ->with($param)
                        ->get();
                }
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->service_condition_name . '_service_' . $this->service_id . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active . '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->service_condition
                            ->select('*');
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_service_condition.is_active'), $this->is_active);
                            });
                        }
                        if ($this->service_id != null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_service_condition.service_id'), $this->service_id);
                            });
                        }
                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_service_condition.' . $key, $item);
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
                    $check_id = $this->check_id($id, $this->service_condition, $this->service_condition_name);
                    if ($check_id) {
                        return $check_id;
                    }
                    $data = Cache::remember($this->service_condition_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = DB::connection('oracle_his')->table('his_service_condition as service_condition')
                            ->select('*')
                            ->where('service_condition.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw("service_condition.is_active"), $this->is_active);
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
                $this->count_name => $count ?? (is_array($data) ? $data['count'] : null),
                $this->service_id_name => $this->service_id,
                $this->is_active_name => $this->is_active,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? ($data['data'] ?? null));
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }

    public function service_condition_create(CreateServiceConditionRequest $request)
    {
        try {
            $data = $this->service_condition::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'is_active' => 1,
                'is_delete' => 0,

                'service_condition_code' => $request->service_condition_code,
                'service_condition_name' => $request->service_condition_name,
                'hein_ratio' => $request->hein_ratio,
                'hein_price' => $request->hein_price,
                'service_id' => $request->service_id,

            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->service_condition_name));
            return return_data_create_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }

    public function service_condition_update(UpdateServiceConditionRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->service_condition->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->update([
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,

                'service_condition_code' => $request->service_condition_code,
                'service_condition_name' => $request->service_condition_name,
                'hein_ratio' => $request->hein_ratio,
                'hein_price' => $request->hein_price,
                'service_id' => $request->service_id,
                'is_active' => $request->is_active
            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->service_condition_name));
            return return_data_update_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }

    public function service_condition_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->service_condition->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->service_condition_name));
            return return_data_delete_success();
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_data_delete_fail();
        }
    }
}
