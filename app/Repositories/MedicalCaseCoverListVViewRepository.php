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
            ->select();
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
            $query->where(('tdl_patient_name'), 'like', '%'. $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(('is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(('is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyDepartmentCodeFilter($query, $code)
    {
        if ($code !== null) {
            $query->where(('department_code'), $code);
        }
        return $query;
    }
    public function applyAddLoginnameFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('add_loginname'), $param);
        }
        return $query;
    }
    public function applyBedRoomIdsFilter($query, $ids)
    {
        if ($ids != null) {
            $query->whereIn(('bed_room_id'), $ids);
        }
        return $query;
    }
    public function applyTreatmentTypeIdsFilter($query, $ids)
    {
        if ($ids != null) {
            $query->whereIn(('in_treatment_type_id'), $ids);
        }
        return $query;
    }
    public function applyPatientClassifyIdsFilter($query, $ids)
    {
        if ($ids != null) {
            $query->whereIn(('tdl_patient_classify_id'), $ids);
        }
        return $query;
    }
    public function applyIsInBedFilter($query, $param)
    {
        if ($param !== null) {
            if($param){
                $query->whereNotNull(('bed_id'))
                ->whereNull(('remove_time'));
            }else{
                $query->whereNull(('bed_id'));
            }
        }
        return $query;
    }
    public function applyIsOutFilter($query, $param)
    {
        if ($param !== null) {
            if($param){
                $query->whereNotNull(('out_time'))
                ->whereNotNull(('remove_time'));
            }else{
                $query->whereNull(('out_time'));
            }
        }
        return $query;
    }
    public function applyIsCoTreatDepartmentFilter($query, $param)
    {
        if ($param !== null) {
            if($param){
                $query->whereNotNull(('co_department_ids'));
            }else{
                $query->whereNull(('co_department_ids'));
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
                    $query->orderBy('' . $key, $item);
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