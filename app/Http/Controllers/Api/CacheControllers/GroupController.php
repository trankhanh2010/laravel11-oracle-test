<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\SDA\Group;
use Illuminate\Support\Facades\DB;

class GroupController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->group = new Group();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->group->getConnection()->getSchemaBuilder()->hasColumn($this->group->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }

    public function group()
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword !== null) {
            $param = [
            ];
            $data = $this->group;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('lower(group_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(group_name)'), 'like', '%' . $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('is_active'), $this->is_active);
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
                ->with($param)
                ->get();
        } else {
            $name = $this->group_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring;
            $param = [];
            $data = get_cache_full($this->group, $param, $name, null, $this->time, $this->start, $this->limit, $this->order_by);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count'],
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
}
