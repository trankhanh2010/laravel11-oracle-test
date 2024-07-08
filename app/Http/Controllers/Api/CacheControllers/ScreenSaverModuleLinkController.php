<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\ACS\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ScreenSaverModuleLinkController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->module = new Module();
    }

    public function screen_saver_module_link()
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword !== null) {
            $data = $this->module::select('acs_module.*')
                ->join('ACS_MODULE_GROUP', 'ACS_MODULE.module_group_id', '=', 'ACS_MODULE_GROUP.id')
                ->where('ACS_MODULE_GROUP.module_group_code', 'MHC');
                $data = $data->where(function ($query) use ($keyword){
                    $query = $query
                ->where(DB::connection('oracle_his')->raw('lower(module_link)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(module_name)'), 'like', '%' . $keyword . '%');
                });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('acs_module.is_active'), $this->is_active);
            });
        } 
            $count = $data->count();
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = Cache::remember('screen_saver_module_link' . '_start_' . $this->start . '_limit_' . $this->limit, $this->time, function () {
                $data = $this->module::select('acs_module.*')
                    ->join('ACS_MODULE_GROUP', 'ACS_MODULE.module_group_id', '=', 'ACS_MODULE_GROUP.id')
                    ->where('ACS_MODULE_GROUP.module_group_code', 'MHC');
                $count = $data->count();
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
                return ['data' => $data, 'count' => $count];
            });
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count']
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
}
