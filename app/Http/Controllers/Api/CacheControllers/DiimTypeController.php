<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\DiimType;
use Illuminate\Http\Request;

class DiimTypeController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->diim_type = new DiimType();
    }
    public function diim_type($id = null)
    {
        if ($id == null) {
            $name = $this->diim_type_name;
            $param = [
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->diim_type->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->diim_type_name . '_' . $id;
            $param = [
            ];
        }
        $data = get_cache_full($this->diim_type, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
}
