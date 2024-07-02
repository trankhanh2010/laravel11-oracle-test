<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ServiceUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceUnitController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->service_unit = new ServiceUnit();
    }
    public function service_unit($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $param = [
                'convert:id,service_unit_name',
            ];
            $data = $this->service_unit
                ->where(DB::connection('oracle_his')->raw('lower(service_unit_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(service_unit_name)'), 'like', '%' . $keyword . '%');
            $count = $data->count();
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            if ($id == null) {
                $name = $this->service_unit_name. '_start_' . $this->start . '_limit_' . $this->limit;
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
            $data = get_cache_full($this->service_unit, $param, $name, $id, $this->time, $this->start, $this->limit);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count']
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
}
