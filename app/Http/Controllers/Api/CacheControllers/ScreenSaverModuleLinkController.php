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
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->module->getConnection()->getSchemaBuilder()->hasColumn($this->module->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }

    public function screen_saver_module_link()
    {
        $keyword = $this->keyword;
        if ($keyword != null) {
            $data = $this->module::select('acs_module.*')
                ->join('ACS_MODULE_GROUP', 'ACS_MODULE.module_group_id', '=', 'ACS_MODULE_GROUP.id')
                ->where('ACS_MODULE_GROUP.module_group_code', 'MHC');
                $data = $data->where(function ($query) use ($keyword){
                    $query = $query
                ->where(DB::connection('oracle_his')->raw('module_link'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('module_name'), 'like', $keyword . '%');
                });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('acs_module.is_active'), $this->is_active);
            });
        } 
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('acs_module.'.$key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = Cache::remember('screen_saver_module_link' . '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active, $this->time, function () {
                $data = $this->module::select('acs_module.*')
                    ->join('ACS_MODULE_GROUP', 'ACS_MODULE.module_group_id', '=', 'ACS_MODULE_GROUP.id')
                    ->where('ACS_MODULE_GROUP.module_group_code', 'MHC');
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('acs_module.is_active'), $this->is_active);
                    });
                } 
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('acs_module.'.$key, $item);
                    }
                }
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
            'count' => $count ?? $data['count'],
            'is_active' => $this->is_active,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
}
