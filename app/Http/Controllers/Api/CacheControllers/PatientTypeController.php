<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\PatientType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PatientTypeController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->patient_type = new PatientType();
    }
    public function patient_type($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $param = [
                'base_patient_type',
                'other_pay_source'
            ];
            $data = $this->patient_type
                ->where(DB::connection('oracle_his')->raw('lower(patient_type_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(patient_type_name)'), 'like', '%' . $keyword . '%');
            $count = $data->count();
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            if ($id == null) {
                $name = $this->patient_type_name. '_start_' . $this->start . '_limit_' . $this->limit;
                $param = [
                    'base_patient_type',
                    'other_pay_source'
                ];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->patient_type->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $name = $this->patient_type_name . '_' . $id;
                $param = [
                    'base_patient_type',
                    'other_pay_source'
                ];
            }
            $data = get_cache_full($this->patient_type, $param, $name, $id, $this->time, $this->start, $this->limit);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count']
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }

    public function patient_type_is_addition()
    {
        $data = Cache::remember($this->patient_type_name.'_is_addition', $this->time, function () {
            return $this->patient_type->where('is_addition', '=', 1)->get();
        });

        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
}
