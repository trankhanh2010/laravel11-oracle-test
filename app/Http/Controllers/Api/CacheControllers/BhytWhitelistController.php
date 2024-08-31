<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BhytWhitelist\CreateBhytWhitelistRequest;
use App\Http\Requests\BhytWhitelist\UpdateBhytWhitelistRequest;
use App\Models\HIS\BHYTWhitelist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BhytWhitelistController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->bhyt_whitelist = new BHYTWhitelist();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->bhyt_whitelist);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function bhyt_whitelist($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->bhyt_whitelist;
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('bhyt_whitelist_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_bhyt_whitelist.is_active'), $this->is_active);
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
                        ->get();
                } else {
                    $data = $data
                        ->skip($this->start)
                        ->take($this->limit)
                        ->get();
                }
            } else {
                if ($id == null) {
                    $data = get_cache_full($this->bhyt_whitelist, [], $this->bhyt_whitelist_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active . '_get_all_' . $this->get_all, null, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active, $this->get_all);
                } else {
                    if (!is_numeric($id)) {
                        return return_id_error($id);
                    }
                    $check_id = $this->check_id($id, $this->bhyt_whitelist, $this->bhyt_whitelist_name);
                    if ($check_id) {
                        return $check_id;
                    }
                    $data = get_cache_full($this->bhyt_whitelist, [], $this->bhyt_whitelist_name . '_' . $id . '_is_active_' . $this->is_active . '_get_all_' . $this->get_all, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active, $this->get_all);
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
            return return_data_success($param_return, $data ?? ($data['data'] ?? null));
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }
    public function bhyt_whitelist_create(CreateBhytWhitelistRequest $request)
    {
        try {
            $data = $this->bhyt_whitelist::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'is_active' => 1,
                'is_delete' => 0,
                'bhyt_whitelist_code' => $request->bhyt_whitelist_code,
                'career_id' => $request->career_id,
                'is_not_check_bhyt' => $request->is_not_check_bhyt,

            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->bhyt_whitelist_name));
            return return_data_create_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }
       
    public function bhyt_whitelist_update(UpdateBhytWhitelistRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->bhyt_whitelist->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->update([
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,
                'is_active' => $request->is_active,
                'bhyt_whitelist_code' => $request->bhyt_whitelist_code,
                'career_id' => $request->career_id,
                'is_not_check_bhyt' => $request->is_not_check_bhyt,
            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->bhyt_whitelist_name));
            return return_data_update_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }

    public function bhyt_whitelist_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->bhyt_whitelist->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->bhyt_whitelist_name));
            return return_data_delete_success();
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_data_delete_fail();
        }
    }
}
