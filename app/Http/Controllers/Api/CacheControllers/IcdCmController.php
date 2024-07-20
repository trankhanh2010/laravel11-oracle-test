<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Controllers\Controller;
use App\Models\HIS\IcdCm;
use Illuminate\Http\Request;
use App\Events\Cache\DeleteCache;
use App\Http\Requests\IcdCm\CreateIcdCmRequest;
use App\Http\Requests\IcdCm\UpdateIcdCmRequest;
use Illuminate\Support\Facades\DB;

class IcdCmController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->icd_cm = new IcdCm();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->icd_cm->getConnection()->getSchemaBuilder()->hasColumn($this->icd_cm->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }

    public function icd_cm($id = null)
    {
        $keyword = create_slug(mb_strtolower($this->keyword, 'UTF-8'));
        if ($keyword != null) {
            $param = [
            ];
            $data = $this->icd_cm;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('FUN_CONVERT_TO_UNSIGN(lower(icd_cm_code))'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('FUN_CONVERT_TO_UNSIGN(lower(icd_cm_name))'), 'like', '%' . $keyword . '%');
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
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            if ($id == null) {
                $name = $this->icd_cm_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active;
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
                $name = $this->icd_cm_name . '_' . $id. '_is_active_' . $this->is_active;
                $param = [
                ];
            }
            $data = get_cache_full($this->icd_cm, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count'],
            'is_active' => $this->is_active,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data ?? $data['data']);
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
            'is_active' => $request->is_active,

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
