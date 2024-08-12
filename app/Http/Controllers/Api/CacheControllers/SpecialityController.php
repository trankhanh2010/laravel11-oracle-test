<?php

namespace App\Http\Controllers\Api\CacheControllers;

use Illuminate\Http\Request;
use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\Speciality;
use App\Http\Requests\Speciality\CreateSpecialityRequest;
use App\Http\Requests\Speciality\UpdateSpecialityRequest;
use Illuminate\Support\Facades\DB;

class SpecialityController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->speciality = new Speciality();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->speciality);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function speciality($id = null)
    {
        $keyword = $this->keyword;
        if ($keyword != null) {
            $param = [
            ];
            $data = $this->speciality;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('speciality_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('speciality_name'), 'like', $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('is_active'), $this->is_active);
            });
        } 
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy($key, $item);
                }
            }
            if($this->get_all){
                $data = $data
                ->with($param)
                ->get();
            }else{
                $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
            }
        } else {
            if ($id == null) {
                $name = $this->speciality_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active. '_get_all_' . $this->get_all;
                $param = [];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $check_id = $this->check_id($id, $this->speciality, $this->speciality_name);
                if($check_id){
                    return $check_id; 
                }
                $name = $this->speciality_name . '_' . $id. '_is_active_' . $this->is_active;
                $param = [];
            }
            $data = get_cache_full($this->speciality, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active, $this->get_all);
        }
        $param_return = [
            $this->get_all_name => $this->get_all,
            $this->start_name => ($this->get_all || !is_null($id)) ? null : $this->start,
            $this->limit_name => ($this->get_all || !is_null($id)) ? null : $this->limit,
            $this->count_name => $count ?? ($data['count'] ?? null),
            $this->is_active_name => $this->is_active,
            $this->keyword_name=> $this->keyword,
            $this->order_by_name => $this->order_by_request
        ];
        return return_data_success($param_return, $data?? ($data['data'] ?? null));
    }

    public function speciality_create(CreateSpecialityRequest $request)
    {
        $data = $this->speciality::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'speciality_code' => $request->speciality_code,
            'speciality_name' => $request->speciality_name,
            'bhyt_limit' => $request->bhyt_limit
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->speciality_name));
        return return_data_create_success($data);
    }

    public function speciality_update(UpdateSpecialityRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->speciality->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'speciality_code' => $request->speciality_code,
            'speciality_name' => $request->speciality_name,
            'bhyt_limit' => $request->bhyt_limit,
            'is_active' => $request->is_active,

        ];
        $data->fill($data_update);
        $data->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->speciality_name));
        return return_data_update_success($data);
    }

    public function speciality_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->speciality->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->speciality_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }
}
