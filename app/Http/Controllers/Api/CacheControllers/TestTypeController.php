<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\TestType;

class TestTypeController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->test_type = new TestType();

    }
    public function test_type()
    {
        $name = $this->test_type_name;
        $param = [];
        $data = get_cache_full($this->test_type, $param, $name, null, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
}
