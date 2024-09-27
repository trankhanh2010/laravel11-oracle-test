<?php

namespace App\Repositories;

use App\Models\HIS\TreatmentType;
use Illuminate\Support\Facades\DB;

class TreatmentTypeRepository
{
    protected $treatmentType;
    public function __construct(TreatmentType $treatmentType)
    {
        $this->treatmentType = $treatmentType;
    }

    public function applyJoins()
    {
        return $this->treatmentType
            ->select(
                'his_treatment_type.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_treatment_type.treatment_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_treatment_type.treatment_type_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_treatment_type.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_treatment_type.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->treatmentType->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->treatmentType::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

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
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

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
            'is_active' => $request->is_active,
        ]);
        return $data;
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($id = null)
    {
        $data = $this->applyJoins();
        if ($id != null) {
            $data = $data->where('his_treatment_type.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
            }
        } else {
            $data = $data->get();
            $data = $data->map(function ($item) {
                return $item->getAttributes();
            })->toArray();
        }
        return $data;
    }
}
