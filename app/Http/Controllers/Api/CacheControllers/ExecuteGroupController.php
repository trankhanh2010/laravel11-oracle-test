<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HIS\ExecuteGroup;
use App\Events\Cache\DeleteCache;
use App\Http\Requests\ExecuteGroup\CreateExecuteGroupRequest;
use App\Http\Requests\ExecuteGroup\UpdateExecuteGroupRequest;

class ExecuteGroupController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->execute_group = new ExecuteGroup();
    }
    public function execute_group($id = null)
    {
        if ($id == null) {
            $name = $this->execute_group_name;
            $param = [
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->execute_group->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->execute_group_name . '_' . $id;
            $param = [
            ];
        }
        $data = get_cache_full($this->execute_group, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
    public function execute_group_create(CreateExecuteGroupRequest $request)
    {
        $data = $this->execute_group::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'execute_group_code' => $request->execute_group_code,
            'execute_group_name' => $request->execute_group_name,
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->execute_group_name));
        return return_data_create_success($data);
    }

    public function execute_group_update(UpdateExecuteGroupRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->execute_group->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'execute_group_code' => $request->execute_group_code,
            'execute_group_name' => $request->execute_group_name,
        ];
        $data->fill($data_update);
        $data->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->execute_group_name));
        return return_data_update_success($data);
    }

    public function execute_group_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->execute_group->find($id);
        if ($data == null) {
            return return_not_record($id);
        }

        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->execute_group_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }


}
}