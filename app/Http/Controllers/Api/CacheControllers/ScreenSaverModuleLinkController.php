<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\ACS\Module;
use Illuminate\Http\Request;

class ScreenSaverModuleLinkController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->module = new Module();
    }

    public function screen_saver_module_link()
    {
        $name = 'screen_saver_module_link';
        $data = $this->module::select('acs_module.*')
            ->join('ACS_MODULE_GROUP', 'ACS_MODULE.module_group_id', '=', 'ACS_MODULE_GROUP.id')
            ->where('ACS_MODULE_GROUP.module_group_code', 'MHC')
            ->get();
        update_cache($name, $data, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
}
