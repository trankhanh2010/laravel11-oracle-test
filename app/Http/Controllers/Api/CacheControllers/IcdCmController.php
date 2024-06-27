<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Controllers\Controller;
use App\Models\HIS\IcdCm;
use Illuminate\Http\Request;
use App\Events\Cache\DeleteCache;
use App\Http\Requests\IcdCm\CreateIcdCmRequest;
use App\Http\Requests\IcdCm\UpdateIcdCmRequest;

class IcdCmController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->icd_cm = new IcdCm();
    }

    public function icd_cm($id = null)
    {
        if ($id == null) {
            $name = $this->icd_cm_name;
            $param = [
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->icd_cm->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->icd_cm_name . '_' . $id;
            $param = [
            ];
        }
        $data = get_cache_full($this->icd_cm, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
    public function icd_cm_create(CreateIcdCmRequest $request)
    {
        $data = $this->icd_cm::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'icd_cm_code' => $request->icd_cm_code,
            'icd_cm_name' => $request->icd_cm_name,
            'icd_cm_chapter_code' => $request->icd_cm_chapter_code,
            'icd_cm_chapter_name' => $request->icd_cm_chapter_name,
            'icd_cm_group_code' => $request->icd_cm_group_code,
            'icd_cm_group_name' => $request->icd_cm_group_name,
            'icd_cm_sub_group_code' => $request->icd_cm_sub_group_code,
            'icd_cm_sub_group_name' => $request->icd_cm_sub_group_name,
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->icd_cm_name));
        return return_data_create_success($data);
    }

    public function icd_cm_update(UpdateIcdCmRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->icd_cm->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'icd_cm_code' => $request->icd_cm_code,
            'icd_cm_name' => $request->icd_cm_name,
            'icd_cm_chapter_code' => $request->icd_cm_chapter_code,
            'icd_cm_chapter_name' => $request->icd_cm_chapter_name,
            'icd_cm_group_code' => $request->icd_cm_group_code,
            'icd_cm_group_name' => $request->icd_cm_group_name,
            'icd_cm_sub_group_code' => $request->icd_cm_sub_group_code,
            'icd_cm_sub_group_name' => $request->icd_cm_sub_group_name,
        ];
        $data->fill($data_update);
        $data->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->icd_cm_name));
        return return_data_update_success($data);
    }

    public function icd_cm_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->icd_cm->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->icd_cm_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }
}
