<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BodyPart\CreateBodyPartRequest;
use App\Http\Requests\BodyPart\UpdateBodyPartRequest;
use App\Models\HIS\BodyPart;
use Illuminate\Http\Request;
use App\Events\Cache\DeleteCache;

class BodyPartController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->body_part = new BodyPart();
    }
    public function body_part($id = null)
    {
        if ($id == null) {
            $name = $this->body_part_name;
            $param = [
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->body_part->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->body_part_name . '_' . $id;
            $param = [
            ];
        }
        $data = get_cache_full($this->body_part, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }

    public function body_part_create(CreateBodyPartRequest $request)
    {
        $data = $this->body_part::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'body_part_code' => $request->body_part_code,
            'body_part_name' => $request->body_part_name,
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->body_part_name));
        return return_data_create_success($data);
    }

    public function body_part_update(UpdateBodyPartRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->body_part->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'body_part_code' => $request->body_part_code,
            'body_part_name' => $request->body_part_name,
        ];
        $data->fill($data_update);
        $data->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->body_part_name));
        return return_data_update_success($data);
    }

    public function body_part_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->body_part->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->body_part_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }
}
