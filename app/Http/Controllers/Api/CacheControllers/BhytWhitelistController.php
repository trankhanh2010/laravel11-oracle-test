<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
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
            // foreach ($this->order_by as $key => $item) {
            //     if (!in_array($key, $this->order_by_join)) {
            //         if (!$this->bhyt_whitelist->getConnection()->getSchemaBuilder()->hasColumn($this->bhyt_whitelist->getTable(), $key)) {
            //             unset($this->order_by_request[camelCaseFromUnderscore($key)]);
            //             unset($this->order_by[$key]);
            //         }
            //     }
            // }
            $this->order_by_join = [];
            $columns = Cache::remember('columns_' . $this->bhyt_whitelist_name, $this->columns_time, function () {
                return  Schema::connection('oracle_his')->getColumnListing($this->bhyt_whitelist->getTable()) ?? [];

            });
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if ((!in_array($key, $columns))) {
                        $this->errors[snakeToCamel($key)] = $this->mess_order_by_name;
                        unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                        unset($this->order_by[$key]);
                    }
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }

    }
    public function bhyt_whitelist($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
        try {
        $keyword = $this->keyword;
        if ($keyword != null) {
            $data = $this->bhyt_whitelist;
            $data = $data->where(function ($query) use ($keyword){
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
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            if ($id == null) {
                $data = get_cache_full($this->bhyt_whitelist, [], $this->bhyt_whitelist_name . '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active, null, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active);
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->bhyt_whitelist->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $data = get_cache_full($this->bhyt_whitelist, [], $this->bhyt_whitelist_name.'_'.$id. '_is_active_' . $this->is_active, $id, $this->time, $this->start, $this->limit, $this->order_by,$this->is_active);
            }
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count'],
            'is_active' => $this->is_active,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    } catch (\Exception $e) {
        // Xử lý lỗi và trả về phản hồi lỗi
        return return_500_error();
    }
    }
}
