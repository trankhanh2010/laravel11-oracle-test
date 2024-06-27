<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\TestSampleType;
use Illuminate\Http\Request;

class TestSampleTypeController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->test_sample_type = new TestSampleType();
    }
    public function test_sample_type($id = null)
    {
        if ($id == null) {
            $name = $this->test_sample_type_name;
            $param = [
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->test_sample_type->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->test_sample_type_name . '_' . $id;
            $param = [
            ];
        }
        $data = get_cache_full($this->test_sample_type, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
}
