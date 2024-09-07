<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Icd\CreateIcdRequest;
use App\Http\Requests\Icd\UpdateIcdRequest;
use App\Models\HIS\Icd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ICDController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->icd = new Icd();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->icd);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function icd($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->icd
                    ->select(
                        'his_icd.*',
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('his_icd.icd_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_icd.is_active'), $this->is_active);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_icd.' . $key, $item);
                    }
                }
                if($this->get_all){
                    $data = $data
                    ->get();
                }else{
                    $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
                }
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->icd_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->icd
                        ->select(
                            'his_icd.*',
                        );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_icd.is_active'), $this->is_active);
                            });
                        }

                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_icd.' . $key, $item);
                            }
                        }
                        if($this->get_all){
                            $data = $data
                            ->get();
                        }else{
                            $data = $data
                            ->skip($this->start)
                            ->take($this->limit)
                            ->get();
                        }
                        return ['data' => $data, 'count' => $count];
                    });
                } else {
                    if (!is_numeric($id)) {
                        return returnIdError($id);
                    }
                    $check_id = $this->check_id($id, $this->icd, $this->icd_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->icd_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->icd
                        ->select(
                            'his_icd.*',
                        )
                            ->where('his_icd.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_icd.is_active'), $this->is_active);
                            });
                        }
                        $data = $data->first();
                        return $data;
                    });
                }
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
            return return_data_success($param_return, $data ?? ($data['data'] ?? null) ?? null);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }

    public function icd_create(CreateIcdRequest $request)
    {
        try {
            $data = $this->icd::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'is_active' => 1,
                'is_delete' => 0,

                'icd_code' => $request->icd_code,
                'icd_name' => $request->icd_name,
                'icd_name_en' => $request->icd_name_en,
                'icd_name_common' => $request->icd_name_common,
                'icd_group_id' => $request->icd_group_id,
                'attach_icd_codes' => $request->attach_icd_codes,

                'age_from' => $request->age_from,
                'age_to' => $request->age_to,
                'age_type_id' => $request->age_type_id,
                'gender_id' => $request->gender_id,
                'is_sword' => $request->is_sword,
                'is_subcode' => $request->is_subcode,

                'is_latent_tuberculosis' => $request->is_latent_tuberculosis,
                'is_cause' => $request->is_cause,
                'is_hein_nds' => $request->is_hein_nds,
                'is_require_cause' => $request->is_require_cause,
                'is_traditional' => $request->is_traditional,
                'unable_for_treatment' => $request->unable_for_treatment,

                'do_not_use_hein' => $request->do_not_use_hein,
                'is_covid' => $request->is_covid,

            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->icd_name));
            return return_data_create_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }
     
    public function icd_update(UpdateIcdRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->icd->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->update([
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,

                'icd_code' => $request->icd_code,
                'icd_name' => $request->icd_name,
                'icd_name_en' => $request->icd_name_en,
                'icd_name_common' => $request->icd_name_common,
                'icd_group_id' => $request->icd_group_id,
                'attach_icd_codes' => $request->attach_icd_codes,

                'age_from' => $request->age_from,
                'age_to' => $request->age_to,
                'age_type_id' => $request->age_type_id,
                'gender_id' => $request->gender_id,
                'is_sword' => $request->is_sword,
                'is_subcode' => $request->is_subcode,

                'is_latent_tuberculosis' => $request->is_latent_tuberculosis,
                'is_cause' => $request->is_cause,
                'is_hein_nds' => $request->is_hein_nds,
                'is_require_cause' => $request->is_require_cause,
                'is_traditional' => $request->is_traditional,
                'unable_for_treatment' => $request->unable_for_treatment,

                'do_not_use_hein' => $request->do_not_use_hein,
                'is_covid' => $request->is_covid,
                'is_active' => $request->is_active
            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->icd_name));
            return return_data_update_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }

    public function icd_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->icd->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->icd_name));
            return return_data_delete_success();
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_data_delete_fail();
        }
    }
}
