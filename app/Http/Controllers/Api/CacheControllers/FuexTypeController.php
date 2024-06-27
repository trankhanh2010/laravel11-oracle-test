<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\FuexType;
use Illuminate\Http\Request;

class FuexTypeController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->fuex_type = new FuexType();
    }
    public function fuex_type($id = null)
    {
        if ($id == null) {
            $name = $this->fuex_type_name;
            $param = [
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->fuex_type->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->fuex_type_name . '_' . $id;
            $param = [
            ];
        }
        $data = get_cache_full($this->fuex_type, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
}
