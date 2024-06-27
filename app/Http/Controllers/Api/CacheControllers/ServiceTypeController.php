<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ServiceType;
use Illuminate\Http\Request;

class ServiceTypeController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->service_type = new ServiceType();
    }
    public function service_type($id = null)
    {
        if ($id == null) {
            $name = $this->service_type_name;
            $param = [
                'exe_service_module:id,exe_service_module_name,module_link',
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->service_type->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->service_type_name . '_' . $id;
            $param = [
                'exe_service_module',
            ];
        }
        $data = get_cache_full($this->service_type, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
}
