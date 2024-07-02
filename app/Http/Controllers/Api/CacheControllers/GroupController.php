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

    }

    public function group()
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $param = [
            ];
            $data = $this->group
                ->where(DB::connection('oracle_his')->raw('lower(group_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(group_name)'), 'like', '%' . $keyword . '%');
            $count = $data->count();
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            $name = $this->group_name. '_start_' . $this->start . '_limit_' . $this->limit;
            $param = [];
            $data = get_cache_full($this->group, $param, $name, null, $this->time, $this->start, $this->limit);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count']
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
}
