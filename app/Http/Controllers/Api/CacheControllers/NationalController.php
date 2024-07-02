<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\National\CreateNationalRequest;
use App\Http\Requests\National\UpdateNationalRequest;
use Illuminate\Http\Request;
use App\Models\SDA\National;
use App\Events\Cache\DeleteCache;
use Illuminate\Support\Facades\DB;

class NationalController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->national = new National();        
    }
    public function national($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $param = [
            ];
            $data = $this->national
                ->where(DB::connection('oracle_his')->raw('lower(national_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(national_name)'), 'like', '%' . $keyword . '%');
            $count = $data->count();
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            if ($id == null) {
                $name = $this->national_name. '_start_' . $this->start . '_limit_' . $this->limit;
                $param = [];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->national->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $name = $this->national_name . '_' . $id;
                $param = [];
            }
            $data = get_cache_full($this->national, $param, $name, $id, $this->time, $this->start, $this->limit);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count']
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
    public function national_create(CreateNationalRequest $request)
    {
        $data = $this->national::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'national_code' => $request->national_code,
            'national_name' => $request->national_name,
            'mps_national_code' => $request->mps_national_code
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->national_name));
        return return_data_create_success($data);
    }

    public function national_update(UpdateNationalRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->national->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'national_code' => $request->national_code,
            'national_name' => $request->national_name,
            'mps_national_code' => $request->mps_national_code
        ];
        $data->fill($data_update);
        $data->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->national_name));
        return return_data_update_success($data);
    }

    public function national_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->national->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->national_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }

}
