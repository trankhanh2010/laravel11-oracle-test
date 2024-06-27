<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ServiceUnit;
use Illuminate\Http\Request;

class ServiceUnitController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->service_unit = new ServiceUnit();
    }
    public function service_unit($id = null)
    {
        if ($id == null) {
            $name = $this->service_unit_name;
            $param = [
                'convert:id,service_unit_name',
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->service_unit->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->service_unit_name . '_' . $id;
            $param = [
                'convert',
            ];
        }
        $data = get_cache_full($this->service_unit, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
}
