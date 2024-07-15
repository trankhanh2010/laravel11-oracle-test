<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ServiceCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServiceConditionController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->service_condition = new ServiceCondition();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->service_condition->getConnection()->getSchemaBuilder()->hasColumn($this->service_condition->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function service_condition($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $param = [
            ];
            $data = $this->service_condition;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('lower(service_condition_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(service_condition_name)'), 'like', '%' . $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('is_active'), $this->is_active);
            });
        }
        if ($this->service_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_service_condition.service_id'), $this->service_id);
            });
        }  
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy($key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            if ($id == null) {
                $data = Cache::remember($this->service_condition_name.'_service_'.$this->service_id. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active, $this->time, function (){
                    $data = $this->service_condition
                    ->select('*');
                    if ($this->is_active !== null) {
                        $data = $data->where(function ($query) {
                            $query = $query->where(DB::connection('oracle_his')->raw('his_service_condition.is_active'), $this->is_active);
                        });
                    }
                    if ($this->service_id != null) {
                        $data = $data->where(function ($query) {
                            $query = $query->where(DB::connection('oracle_his')->raw('his_service_condition.service_id'), $this->service_id);
                        });
                    }   
                    $count = $data->count();
                    if ($this->order_by != null) {
                        foreach ($this->order_by as $key => $item) {
                            $data->orderBy('his_service_condition.'.$key, $item);
                        }
                    }
                    $data = $data    
                        ->skip($this->start)
                        ->take($this->limit)
                        ->get();
                    return ['data' => $data, 'count' => $count];
                });
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->service_condition->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $data = Cache::remember($this->service_condition_name.'_'.$id. '_is_active_' . $this->is_active, $this->time, function () use ($id){
                    $data = DB::connection('oracle_his')->table('his_service_condition as service_condition')
                    ->select('*')
                    ->where('service_condition.id', $id);
                    if ($this->is_active !== null) {
                        $data = $data->where(function ($query) {
                            $query = $query->where(DB::connection('oracle_his')->raw("service_condition.is_active"), $this->is_active);
                        });
                    } 
                    $data = $data->first();
                    return $data;
                });
            }  
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? (is_array($data) ? $data['count'] : null  ),
            'service_id' => $this->service_id,
            'is_active' => $this->is_active,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
}
