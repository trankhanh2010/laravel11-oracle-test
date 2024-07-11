<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\RationTime;
use Illuminate\Http\Request;
use App\Events\Cache\DeleteCache;
use Illuminate\Support\Facades\DB;
class RationTimeController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->ration_time = new RationTime();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->ration_time->getConnection()->getSchemaBuilder()->hasColumn($this->ration_time->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function ration_time($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $data = $this->ration_time;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('lower(ration_time_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(ration_time_name)'), 'like', '%' . $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_ration_time.is_active'), $this->is_active);
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
                ->get();
        } else {
            if ($id == null) {
                $data = get_cache($this->ration_time, $this->ration_time_name . '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring, null, $this->time, $this->start, $this->limit, $this->order_by);
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->ration_time->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $data = get_cache($this->ration_time, $this->ration_time_name, $id, $this->time, $this->start, $this->limit, $this->order_by);
            }
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count'],
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }

}
