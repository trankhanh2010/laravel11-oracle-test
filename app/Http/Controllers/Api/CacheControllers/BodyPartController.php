<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BodyPart\CreateBodyPartRequest;
use App\Http\Requests\BodyPart\UpdateBodyPartRequest;
use App\Models\HIS\BodyPart;
use Illuminate\Http\Request;
use App\Events\Cache\DeleteCache;
use Illuminate\Support\Facades\DB;

class BodyPartController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->body_part = new BodyPart();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->body_part->getConnection()->getSchemaBuilder()->hasColumn($this->body_part->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function body_part($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $data = $this->body_part
                ->where(DB::connection('oracle_his')->raw('lower(body_part_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(body_part_name)'), 'like', '%' . $keyword . '%');
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
                $name = $this->body_part_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring;
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
            $data = get_cache_full($this->body_part, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by);
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
