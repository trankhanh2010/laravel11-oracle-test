<?php

namespace App\Repositories;

use App\Models\HIS\Debate;
use Illuminate\Support\Facades\DB;

class DebateRepository
{
    protected $debate;
    public function __construct(Debate $debate)
    {
        $this->debate = $debate;
    }

    public function applyJoins()
    {
        return $this->debate
            ->select(
                'his_debate.*'
            );
    }
    public function debate($query)
    {
        return $query
            ->with([
                'debate_ekip_users:id,debate_id,loginname,username,execute_role_id,department_id',
                'debate_invite_users:id,debate_id,loginname,username,execute_role_id',
                'debate_users:id,debate_id,loginname,username,execute_role_id',
            ]);
    }
    public function paramDebate()
    {
        return [
            'his_debate.id',
            'his_debate.create_time',
            'his_debate.modify_time',
            'his_debate.creator',
            'his_debate.modifier',
            'his_debate.app_creator',
            'his_debate.app_modifier',
            'his_debate.is_active',
            'his_debate.is_delete',
            'his_debate.group_code',
            'his_debate.treatment_id',
            'his_debate.icd_id__delete',
            'his_debate.icd_code',
            'his_debate.icd_name',
            'his_debate.icd_sub_code',
            'his_debate.icd_text',
            'his_debate.department_id',
            'his_debate.debate_time',
            'his_debate.request_loginname',
            'his_debate.request_username',
            'his_debate.treatment_tracking',
            'his_debate.treatment_from_time',
            'his_debate.treatment_to_time',
            'his_debate.treatment_method',
            'his_debate.location',
            'his_debate.request_content',
            'his_debate.pathological_history',
            'his_debate.hospitalization_state',
            'his_debate.before_diagnostic',
            'his_debate.diagnostic',
            'his_debate.care_method',
            'his_debate.conclusion',
            'his_debate.discussion',
            'his_debate.medicine_tutorial',
            'his_debate.medicine_use_form_name',
            'his_debate.medicine_type_name',
            'his_debate.medicine_concentra',
            'his_debate.medicine_use_time',
            'his_debate.debate_type_id',
            'his_debate.content_type',
            'his_debate.subclinical_processes',
            'his_debate.internal_medicine_state',
            'his_debate.surgery_service_id',
            'his_debate.emotionless_method_id',
            'his_debate.surgery_time',
            'his_debate.prognosis',
            'his_debate.pttt_method_id',
            'his_debate.pttt_method_name',
            'his_debate.medicine_type_ids',
            'his_debate.active_ingredient_ids',
            'his_debate.tracking_id',
            'his_debate.service_id',
            'his_debate.tmp_id',
            'his_debate.debate_reason_id',
        ];
    }
    public function debateView($query)
    {
        return $query
            ->leftJoin('his_treatment as treatment', 'treatment.id', '=', 'his_debate.treatment_id')
            ->leftJoin('his_department as department', 'department.id', '=', 'his_debate.department_id')
            ->leftJoin('his_debate_type as debate_type', 'debate_type.id', '=', 'his_debate.debate_type_id')
            ->leftJoin('his_emotionless_method as emotionless_method', 'emotionless_method.id', '=', 'his_debate.emotionless_method_id')
            ->leftJoin('his_debate_reason as debate_reason', 'debate_reason.id', '=', 'his_debate.debate_reason_id');
    }
    public function paramDebateView()
    {
        return   [
            "his_debate.id",
            "his_debate.create_time",
            "his_debate.modify_time",
            "his_debate.creator",
            "his_debate.modifier",
            "his_debate.app_creator",
            "his_debate.app_modifier",
            "his_debate.is_active",
            "his_debate.is_delete",
            "his_debate.treatment_id",
            "his_debate.icd_code",
            "his_debate.icd_name",
            "his_debate.icd_sub_code",
            "his_debate.icd_text",
            "his_debate.department_id",
            "his_debate.debate_time",
            "his_debate.request_loginname",
            "his_debate.request_username",
            "his_debate.treatment_tracking",
            "his_debate.treatment_from_time",
            "his_debate.treatment_to_time",
            "his_debate.location",
            "his_debate.conclusion",
            "his_debate.debate_type_id",
            "his_debate.content_type",
            "his_debate.subclinical_processes",
            "his_debate.emotionless_method_id",
            "his_debate.surgery_time",
            "his_debate.pttt_method_id",

            "treatment.patient_id",
            "treatment.treatment_code",
            "treatment.tdl_patient_first_name",
            "treatment.tdl_patient_last_name",
            "treatment.tdl_patient_name",
            "treatment.tdl_patient_dob",
            "treatment.tdl_patient_address",
            "treatment.tdl_patient_gender_name",

            "department.department_code",
            "department.department_name",

            "debate_type.debate_type_code",
            "debate_type.debate_type_name",

            "emotionless_method.emotionless_method_code",
            "emotionless_method.emotionless_method_name",

            "debate_reason.debate_reason_code",
            "debate_reason.debate_reason_name",
        ];
    }
    public function selectDebate($query)
    {
        return $query->select($this->paramDebate());
    }
    public function selectDebateView($query)
    {
        return $query->select($this->paramDebateView());
    }
    public function selectAll($query){
        return $query->select(array_unique(array_merge($this->paramDebate(), $this->paramDebateView())));
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_debate.icd_code'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_debate.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_debate.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyTreatmentIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_debate.treatment_id'), $id);
        }
        return $query;
    }
    public function applyTreatmentCodeFilter($query, $code)
    {
        if ($code !== null) {
            $query->where(DB::connection('oracle_his')->raw('treatment.treatment_code'), $code);
        }
        return $query;
    }
    public function applyDepartmentIdsFilter($query, $ids)
    {
        if ($ids !== null) {
            $query->whereIn(DB::connection('oracle_his')->raw('his_debate.department_id'), $ids);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_debate.' . $key, $item);
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
        return $this->debate->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->debate::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'debate_code' => $request->debate_code,
    //         'debate_name' => $request->debate_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'debate_code' => $request->debate_code,
    //         'debate_name' => $request->debate_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    public function getDataFromDbToElastic($id = null)
    {
        $data = $this->debate($this->debate);
        $data = $this->debateView($data);
        $data = $this->selectAll($data);
        if ($id != null) {
            $data = $data->where('his_debate_reason.id', '=', $id)->first();
            if ($data) {
                $data->toArray();
            }
        } else {
            $data = $data->get();
            $data->toArray();
        }
        return $data;
    }
}
