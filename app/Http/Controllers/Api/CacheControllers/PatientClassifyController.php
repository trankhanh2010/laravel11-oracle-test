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
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->patient_classify = new PatientClassify();
    }
    public function patient_classify($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $param = [
                'patient_type',
                'other_pay_source',
            ];
            $data = $this->patient_classify
                ->where(DB::connection('oracle_his')->raw('lower(patient_classify_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(patient_classify_name)'), 'like', '%' . $keyword . '%');
            $count = $data->count();
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            if ($id == null) {
                $name = $this->patient_classify_name. '_start_' . $this->start . '_limit_' . $this->limit;
                $param = [
                    'patient_type',
                    'other_pay_source',
                ];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->patient_classify->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $name = $this->patient_classify_name . '_' . $id;
                $param = [
                    'patient_type',
                    'other_pay_source',
                ];
            }
            $data = get_cache_full($this->patient_classify, $param, $name, $id, $this->time, $this->start, $this->limit);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count']
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
    public function patient_classify_create(CreatePatientClassifyRequest $request)
    {
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
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
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
        ];
        $data->fill($data_update);
        $data->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->patient_classify_name));
        return return_data_update_success($data);
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
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }
}
