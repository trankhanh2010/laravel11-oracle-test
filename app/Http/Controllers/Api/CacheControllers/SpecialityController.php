<?php

namespace App\Http\Controllers\Api\CacheControllers;

use Illuminate\Http\Request;
use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\Speciality;
use App\Http\Requests\Speciality\CreateSpecialityRequest;
use App\Http\Requests\Speciality\UpdateSpecialityRequest;
class SpecialityController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->speciality = new Speciality();
    }
    public function speciality($id = null)
    {
        if ($id == null) {
            $name = $this->speciality_name;
            $param = [];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->speciality->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->speciality_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->speciality, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
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
            'bhyt_limit' => $request->bhyt_limit
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
