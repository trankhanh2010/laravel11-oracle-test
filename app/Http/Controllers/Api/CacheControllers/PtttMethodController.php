<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\PtttMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
class PtttMethodController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->pttt_method = new PtttMethod();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->pttt_method->getConnection()->getSchemaBuilder()->hasColumn($this->pttt_method->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function pttt_method($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if($keyword != null){
            $data = $this->pttt_method
                    ->leftJoin('his_pttt_group as pttt_group', 'pttt_group.id', '=', 'his_pttt_method.pttt_group_id')
                    ->select(
                        'his_pttt_method.*',
                        'pttt_group.pttt_group_name',
                        'pttt_group.pttt_group_code',
                    );
                    $data = $data->where(function ($query) use ($keyword){
                        $query = $query
                    ->where(DB::connection('oracle_his')->raw('lower(his_pttt_method.pttt_method_code)'), 'like', '%' . $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('lower(his_pttt_method.pttt_method_name)'), 'like', '%' . $keyword . '%');
                    });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_pttt_method.is_active'), $this->is_active);
            });
        } 
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_pttt_method.'.$key, $item);
                    }
                }
                $data = $data    
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
        }else{
            if ($id == null) {
                $his_pttt_method = $this->pttt_method;
                $is_active = $this->is_active;
                $data = Cache::remember($this->pttt_method_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active, $this->time, function () use ($his_pttt_method, $is_active){
                    $data = $his_pttt_method
                    ->leftJoin('his_pttt_group as pttt_group', 'pttt_group.id', '=', 'his_pttt_method.pttt_group_id')
                    ->select(
                        'his_pttt_method.*',
                        'pttt_group.pttt_group_name',
                        'pttt_group.pttt_group_code',
                    );
                    if ($is_active !== null) {
                        $data = $data->where(function ($query) use ($is_active) {
                            $query = $query->where(DB::connection('oracle_his')->raw("his_pttt_method.is_active"), $is_active);
                        });
                    } 
                    $count = $data->count();
                    if ($this->order_by != null) {
                        foreach ($this->order_by as $key => $item) {
                            $data->orderBy('his_pttt_method.'.$key, $item);
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
                $data = $this->pttt_method->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $name = $this->pttt_method_name . '_' . $id. '_is_active_' . $this->is_active;
                $param = [
                    'pttt_group'
                ];
                $data = get_cache_full($this->pttt_method, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active);
            }
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
