<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\Machine;
use Illuminate\Http\Request;
use App\Events\Cache\DeleteCache;
use Illuminate\Support\Facades\DB;
class MachineController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->machine = new Machine();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->machine);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function machine($id = null)
    {
        $param = [
            'department:id,department_name',
        ];
        $keyword = $this->keyword;
        if ($keyword != null) {
            $data = $this->machine;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('machine_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('machine_name'), 'like', $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_machine.is_active'), $this->is_active);
            });
        }  
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy($key, $item);
                }
            }
            $data = $data->with($param)
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            if ($id == null) {
                $data = get_cache_full($this->machine, $param, $this->machine_name . '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active, null, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active);
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $check_id = $this->check_id($id, $this->machine, $this->machine_name);
                if($check_id){
                    return $check_id; 
                }
                $data = get_cache_full($this->machine, $param, $this->machine_name.'_'.$id. '_is_active_' . $this->is_active, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active);
            }
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? ($data['count'] ?? null),
            'is_active' => $this->is_active,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data?? ($data['data'] ?? null));
    }
}
