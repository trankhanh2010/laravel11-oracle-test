<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\Gender;
use Illuminate\Http\Request;

class GenderController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->gender = new Gender();
    }
    public function gender($id = null)
    {
        if ($id == null) {
            $name = $this->gender_name;
            $param = [
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->gender->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->gender_name . '_' . $id;
            $param = [
            ];
        }
        $data = get_cache_full($this->gender, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
}
