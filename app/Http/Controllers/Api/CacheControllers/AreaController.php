<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Http\Requests\Area\CreateAreaRequest;
use App\Http\Requests\Area\UpdateAreaRequest;
use App\Models\HIS\Area;
use App\Events\Cache\DeleteCache;
use Illuminate\Support\Facades\DB;

class AreaController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->area = new Area();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->area->getConnection()->getSchemaBuilder()->hasColumn($this->area->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function area($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword !== null) {
            $data = $this->area;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('lower(area_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(area_name)'), 'like', '%' . $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_area.is_active'), $this->is_active);
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
                $data = get_cache($this->area, $this->area_name . '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring, null, $this->time, $this->start, $this->limit, $this->order_by);
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->area->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $data = get_cache($this->area, $this->area_name, $id, $this->time, $this->start, $this->limit, $this->order_by);
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

    public function area_create(CreateAreaRequest $request)
    {
        $data = $this->area::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'is_active' => 1,
            'is_delete' => 0,
            'area_code' => $request->area_code,
            'area_name' => $request->area_name,
            'department_id' => $request->department_id
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->area_name));
        return return_data_create_success($data);
    }

    public function area_update(UpdateAreaRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->area->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'area_code' => $request->area_code,
            'area_name' => $request->area_name,
            'department_id' => $request->department_id,
            'is_active' => $request->is_active
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->area_name));
        return return_data_update_success($data);
    }

    public function area_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->area->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->area_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }
}
