<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\SDA\Province;
use App\Events\Cache\DeleteCache;
use App\Http\Requests\Province\CreateProvinceRequest;
use App\Http\Requests\Province\UpdateProvinceRequest;

class ProvinceController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->province = new Province();
    }
    public function province($id = null)
    {
        if ($id == null) {
            $name = $this->province_name;
            $param = [
                'national:id,national_name,national_code'
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->province->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->province_name . '_' . $id;
            $param = [
                'national',
                'districts'
            ];
        }
        $data = get_cache_full($this->province, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
    public function province_create(CreateProvinceRequest $request)
    {
        $data = $this->province::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'province_code' => $request->province_code,
            'province_name' => $request->province_name,
            'national_id' => $request->national_id,
            'search_code' => $request->search_code,
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->province_name));
        return return_data_create_success($data);
    }
    
    public function province_update(UpdateProvinceRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->province->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'province_code' => $request->province_code,
            'province_name' => $request->province_name,
            'national_id' => $request->national_id,
            'search_code' => $request->search_code,
        ];
        $data->fill($data_update);
        $data->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->province_name));
        return return_data_update_success($data);
    }

    public function province_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->province->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->province_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }
}
