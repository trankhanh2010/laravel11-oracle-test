<?php

namespace App\Http\Controllers\Api\CacheControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\TestType;
use Illuminate\Support\Facades\DB;

class TestTypeController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->test_type = new TestType();

    }
    public function test_type()
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $param = [
            ];
            $data = $this->test_type
                ->where(DB::connection('oracle_his')->raw('lower(test_type_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(test_type_name)'), 'like', '%' . $keyword . '%');
            $count = $data->count();
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            $name = $this->test_type_name. '_start_' . $this->start . '_limit_' . $this->limit;
            $param = [];
            $data = get_cache_full($this->test_type, $param, $name, null, $this->time, $this->start, $this->limit);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count']
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
}
