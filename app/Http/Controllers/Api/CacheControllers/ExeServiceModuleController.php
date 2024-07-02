<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ExeServiceModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExeServiceModuleController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->exe_service_module = new ExeServiceModule();
    }
    public function exe_service_module($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $param = [
            ];
            $data = $this->exe_service_module
                ->where(DB::connection('oracle_his')->raw('lower(module_link)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(exe_service_module_name)'), 'like', '%' . $keyword . '%');
            $count = $data->count();
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            if ($id == null) {
                $name = $this->exe_service_module_name. '_start_' . $this->start . '_limit_' . $this->limit;
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
            $data = get_cache_full($this->exe_service_module, $param, $name, $id, $this->time, $this->start, $this->limit);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count']
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
}
                   