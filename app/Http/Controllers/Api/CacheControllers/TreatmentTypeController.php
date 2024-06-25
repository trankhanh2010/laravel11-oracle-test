<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\HIS\TreatmentType;
use App\Http\Requests\TreatmentType\CreateTreatmentTypeRequest;
use App\Http\Requests\TreatmentType\UpdateTreatmentTypeRequest;
use App\Events\Cache\DeleteCache;

class TreatmentTypeController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->treatment_type = new TreatmentType();
    }
    public function treatment_type($id = null)
    {
        if ($id == null) {
            $name = $this->treatment_type_name;
            $param = [
                'required_service'
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->treatment_type->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->treatment_type_name . '_' . $id;
            $param = [
                'required_service'
            ];
        }
        $data = get_cache_full($this->treatment_type, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }

    public function treatment_type_create(CreateTreatmentTypeRequest $request)
    {
        $data = $this->treatment_type::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'treatment_type_code' => $request->treatment_type_code,
            'treatment_type_name' => $request->treatment_type_name,
            'hein_treatment_type_code' => $request->hein_treatment_type_code,
            'end_code_prefix' => $request->end_code_prefix,
            'required_service_id' => $request->required_service_id,
            'is_allow_reception' => $request->is_allow_reception,
            'is_not_allow_unpause' => $request->is_not_allow_unpause,
            'allow_hospitalize_when_pres' => $request->allow_hospitalize_when_pres,
            'is_not_allow_share_bed' => $request->is_not_allow_share_bed,
            'is_required_service_bed' => $request->is_required_service_bed,
            'is_dis_service_repay' => $request->is_dis_service_repay,
            'dis_service_deposit_option' => $request->dis_service_deposit_option,
            'dis_deposit_option' => $request->dis_deposit_option,
            'unsign_doc_finish_option' => $request->unsign_doc_finish_option,
            'trans_time_out_time_option' => $request->trans_time_out_time_option,
            'fee_debt_option' => $request->fee_debt_option,
            'id' => null,
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->treatment_type_name));
        return return_data_create_success($data);
    }

    public function treatment_type_update(UpdateTreatmentTypeRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->treatment_type->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'hein_treatment_type_code' => $request->hein_treatment_type_code,
            'end_code_prefix' => $request->end_code_prefix,
            'required_service_id' => $request->required_service_id,
            'is_allow_reception' => $request->is_allow_reception,
            'is_not_allow_unpause' => $request->is_not_allow_unpause,
            'allow_hospitalize_when_pres' => $request->allow_hospitalize_when_pres,
            'is_not_allow_share_bed' => $request->is_not_allow_share_bed,
            'is_required_service_bed' => $request->is_required_service_bed,
            'is_dis_service_repay' => $request->is_dis_service_repay,
            'dis_service_deposit_option' => $request->dis_service_deposit_option,
            'dis_deposit_option' => $request->dis_deposit_option,
            'unsign_doc_finish_option' => $request->unsign_doc_finish_option,
            'trans_time_out_time_option' => $request->trans_time_out_time_option,
            'fee_debt_option' => $request->fee_debt_option,
        ];
        $data->fill($data_update);
        $data->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->treatment_type_name));
        return return_data_update_success($data);
    }

    public function treatment_type_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->treatment_type->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->treatment_type_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }
}
