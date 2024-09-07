<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SaleProfitCfg\CreateSaleProfitCfgRequest;
use App\Http\Requests\SaleProfitCfg\UpdateSaleProfitCfgRequest;
use App\Models\HIS\SaleProfitCFG;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleProfitCfgController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->sale_profit_cfg = new SaleProfitCFG();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->sale_profit_cfg);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function sale_profit_cfg($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $param = [];
                $data = $this->sale_profit_cfg;
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('ratio'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('imp_price_from'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('imp_price_to'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_sale_profit_cfg.is_active'), $this->is_active);
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
                    $name = $this->sale_profit_cfg_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active . '_get_all_' . $this->get_all;
                    $param = [];
                } else {
                    if (!is_numeric($id)) {
                        return returnIdError($id);
                    }
                    $check_id = $this->check_id($id, $this->sale_profit_cfg, $this->sale_profit_cfg_name);
                    if ($check_id) {
                        return $check_id;
                    }
                    $name =  $this->sale_profit_cfg_name . '_' . $id . '_is_active_' . $this->is_active;
                    $param = [];
                }
                $model = $this->sale_profit_cfg;
                $data = get_cache_full($model, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active, $this->get_all);
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
            return return_data_success($param_return, $data ?? ($data['data'] ?? null));
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }
    public function sale_profit_cfg_create(CreateSaleProfitCfgRequest $request)
    {
        try {
            $data = $this->sale_profit_cfg::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'is_active' => 1,
                'is_delete' => 0,

                'ratio' => $request->ratio,
                'imp_price_from' => $request->imp_price_from,
                'imp_price_to' => $request->imp_price_to,
                'is_medicine' => $request->is_medicine,
                'is_material' => $request->is_material,
                'is_common_medicine' => $request->is_common_medicine,
                'is_functional_food' => $request->is_functional_food,
                'is_drug_store' => $request->is_drug_store,

            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->sale_profit_cfg_name));
            return return_data_create_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }

    public function sale_profit_cfg_update(UpdateSaleProfitCfgRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->sale_profit_cfg->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->update([
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,
                'ratio' => $request->ratio,
                'imp_price_from' => $request->imp_price_from,
                'imp_price_to' => $request->imp_price_to,
                'is_medicine' => $request->is_medicine,
                'is_material' => $request->is_material,
                'is_common_medicine' => $request->is_common_medicine,
                'is_functional_food' => $request->is_functional_food,
                'is_drug_store' => $request->is_drug_store,
                'is_active' => $request->is_active
            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->sale_profit_cfg_name));
            return return_data_update_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }

    public function sale_profit_cfg_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->sale_profit_cfg->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->sale_profit_cfg_name));
            return return_data_delete_success();
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_data_delete_fail();
        }
    }
}
