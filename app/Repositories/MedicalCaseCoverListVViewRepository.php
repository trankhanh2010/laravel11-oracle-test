<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\MedicalCaseCoverListVView;
use Illuminate\Support\Facades\DB;

class MedicalCaseCoverListVViewRepository
{
    protected $medicalCaseCoverListVView;
    public function __construct(MedicalCaseCoverListVView $medicalCaseCoverListVView)
    {
        $this->medicalCaseCoverListVView = $medicalCaseCoverListVView;
    }

    public function applyJoins()
    {
        return $this->medicalCaseCoverListVView
            ->select(
                'v_his_medical_case_cover_list.*'
            );
    }
    public function applyWithParam($query)
    {
        return $query->with([
            'department_trans:id,department_id,previous_id,department_in_time,request_time,treatment_id',
            'department_trans.department:id,department_name,department_code',
            'service_req_KH',
            'dhsts',
        ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.tdl_patient_name'), 'like', '%'. $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyDepartmentCodeFilter($query, $code)
    {
        if ($code !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.department_code'), $code);
        }
        return $query;
    }
    public function applyAddLoginnameFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.add_loginname'), $param);
        }
        return $query;
    }
    public function applyBedRoomIdsFilter($query, $ids)
    {
        if ($ids != null) {
            $query->whereIn(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.bed_room_id'), $ids);
        }
        return $query;
    }
    public function applyTreatmentTypeIdsFilter($query, $ids)
    {
        if ($ids != null) {
            $query->whereIn(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.in_treatment_type_id'), $ids);
        }
        return $query;
    }
    public function applyPatientClassifyIdsFilter($query, $ids)
    {
        if ($ids != null) {
            $query->whereIn(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.tdl_patient_classify_id'), $ids);
        }
        return $query;
    }
    public function applyIsInBedFilter($query, $param)
    {
        if ($param !== null) {
            if($param){
                $query->whereNotNull(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.bed_id'))
                ->whereNull(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.remove_time'));
            }else{
                $query->whereNull(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.bed_id'));
            }
        }
        return $query;
    }
    public function applyIsOutFilter($query, $param)
    {
        if ($param !== null) {
            if($param){
                $query->whereNotNull(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.out_time'))
                ->whereNotNull(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.remove_time'));
            }else{
                $query->whereNull(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.out_time'));
            }
        }
        return $query;
    }
    public function applyIsCoTreatDepartmentFilter($query, $param)
    {
        if ($param !== null) {
            if($param){
                $query->whereNotNull(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.co_department_ids'));
            }else{
                $query->whereNull(DB::connection('oracle_his')->raw('v_his_medical_case_cover_list.co_department_ids'));
            }
        }
        return $query;
    }
    public function applyAddTimeFromFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                $query->where('add_time', '>=', $param);
            });
        }
        return $query;
    }
    public function applyAddTimeToFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                $query->where('add_time', '<=', $param);
            });
        }
        return $query;
    }
     public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('v_his_medical_case_cover_list.' . $key, $item);
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
        return $this->medicalCaseCoverListVView->find($id);
    }

}