<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ExeServiceModule;
use Illuminate\Http\Request;

class ExeServiceModuleController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->exe_service_module = new ExeServiceModule();
    }
    public function exe_service_module($id = null)
    {
        if ($id == null) {
            $name = $this->exe_service_module_name;
            $param = [
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->exe_service_module->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->exe_service_module_name . '_' . $id;
            $param = [
            ];
        }
        $data = get_cache_full($this->exe_service_module, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
}
                   