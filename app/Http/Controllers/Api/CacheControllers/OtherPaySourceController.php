<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\HIS\OtherPaySource;
use App\Http\Requests\OtherPaySource\CreateOtherPaySourceRequest;
use App\Http\Requests\OtherPaySource\UpdateOtherPaySourceRequest;
use App\Events\Cache\DeleteCache;
use Illuminate\Support\Facades\DB;

class OtherPaySourceController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->other_pay_source = new OtherPaySource();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->other_pay_source->getConnection()->getSchemaBuilder()->hasColumn($this->other_pay_source->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function other_pay_source($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword !== null) {
            $param = [
            ];
            $data = $this->other_pay_source;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('lower(other_pay_source_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(other_pay_source_name)'), 'like', '%' . $keyword . '%');
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
                $name = $this->other_pay_source_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring;
                $param = [];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->other_pay_source->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $name = $this->other_pay_source_name . '_' . $id;
                $param = [];
            }
            $data = get_cache_full($this->other_pay_source, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by);
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

    public function other_pay_source_create(CreateOtherPaySourceRequest $request)
    {
        $data = $this->other_pay_source::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'other_pay_source_code' => $request->other_pay_source_code,
            'other_pay_source_name' => $request->other_pay_source_name,
            'hein_pay_source_type_id' => $request->hein_pay_source_type_id,
            'is_not_for_treatment' => $request->is_not_for_treatment,
            'is_not_paid_diff' => $request->is_not_paid_diff,
            'is_paid_all' => $request->is_paid_all,
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->other_pay_source_name));
        return return_data_create_success($data);
    }

    public function other_pay_source_update(UpdateOtherPaySourceRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->other_pay_source->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'other_pay_source_code' => $request->other_pay_source_code,
            'other_pay_source_name' => $request->other_pay_source_name,
            'hein_pay_source_type_id' => $request->hein_pay_source_type_id,
            'is_not_for_treatment' => $request->is_not_for_treatment,
            'is_not_paid_diff' => $request->is_not_paid_diff,
            'is_paid_all' => $request->is_paid_all,
            'is_active' => $request->is_active,

        ];
        $data->fill($data_update);
        $data->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->other_pay_source_name));
        return return_data_update_success($data);
    }

    public function other_pay_source_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->other_pay_source->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->other_pay_source_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }
}
