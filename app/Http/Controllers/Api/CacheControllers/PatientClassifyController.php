<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Http\Requests\PatientClassify\CreatePatientClassifyRequest;
use App\Http\Requests\PatientClassify\UpdatePatientClassifyRequest;
use App\Events\Cache\DeleteCache;
use App\Models\HIS\PatientClassify;
use Illuminate\Support\Facades\DB;

class PatientClassifyController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->patient_classify = new PatientClassify();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->patient_classify);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function patient_classify($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $param = [
                    'patient_type',
                    'other_pay_source',
                ];
                $data = $this->patient_classify;
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('patient_classify_code'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('patient_classify_name'), 'like', $keyword . '%');
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
                if ($this->get_all) {
                    $data = $data
                        ->with($param)
                        ->get();
                } else {
                    $data = $data
                        ->skip($this->start)
                        ->take($this->limit)
                        ->with($param)
                        ->get();
                }
            } else {
                if ($id == null) {
                    $name = $this->patient_classify_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active . '_get_all_' . $this->get_all;
                    $param = [
                        'patient_type',
                        'other_pay_source',
                    ];
                } else {
                    if (!is_numeric($id)) {
                        return return_id_error($id);
                    }
                    $check_id = $this->check_id($id, $this->patient_classify, $this->patient_classify_name);
                    if ($check_id) {
                        return $check_id;
                    }
                    $name = $this->patient_classify_name . '_' . $id . '_is_active_' . $this->is_active;
                    $param = [
                        'patient_type',
                        'other_pay_source',
                    ];
                }
                $data = get_cache_full($this->patient_classify, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by, $this->is_active, $this->get_all);
            }
            $param_return = [
                $this->get_all_name => $this->get_all,
                $this->start_name => ($this->get_all || !is_null($id)) ? null : $this->start,
                $this->limit_name => ($this->get_all || !is_null($id)) ? null : $this->limit,
                $this->count_name => $count ?? ($data['count'] ?? null),
                $this->is_active_name => $this->is_active,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? ($data['data'] ?? null));
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
    public function patient_classify_create(CreatePatientClassifyRequest $request)
    {
        try {
            $data = $this->patient_classify::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'is_active' => 1,
                'is_delete' => 0,
                'patient_classify_code' => $request->patient_classify_code,
                'patient_classify_name' => $request->patient_classify_name,
                'display_color' => $request->display_color,
                'patient_type_id' => $request->patient_type_id,
                'other_pay_source_id' => $request->other_pay_source_id,
                'bhyt_whitelist_ids' => $request->bhyt_whitelist_ids,
                'military_rank_ids' => $request->military_rank_ids,
                'is_police' => $request->is_police

            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->patient_classify_name));
            return return_data_create_success($data);
        } catch (\Exception $e) {
            return return_500_error();
        }
    }

    public function patient_classify_update(UpdatePatientClassifyRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->patient_classify->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data_update = [
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,
                'patient_classify_code' => $request->patient_classify_code,
                'patient_classify_name' => $request->patient_classify_name,
                'display_color' => $request->display_color,
                'patient_type_id' => $request->patient_type_id,
                'other_pay_source_id' => $request->other_pay_source_id,
                'bhyt_whitelist_ids' => $request->bhyt_whitelist_ids,
                'military_rank_ids' => $request->military_rank_ids,
                'is_police' => $request->is_police,
                'is_active' => $request->is_active,

            ];
            $data->fill($data_update);
            $data->save();
            // Gọi event để xóa cache
            event(new DeleteCache($this->patient_classify_name));
            return return_data_update_success($data);
        } catch (\Exception $e) {
            return return_500_error();
        }
    }

    public function patient_classify_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->patient_classify->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->patient_classify_name));
            return return_data_delete_success();
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_data_delete_fail();
        }
    }
}
