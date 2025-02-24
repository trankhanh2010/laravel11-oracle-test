<?php

namespace App\Repositories;
use Illuminate\Support\Str;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\TreatmentBedRoomListVView;
use Illuminate\Support\Facades\DB;

class TreatmentBedRoomListVViewRepository
{
    protected $treatmentBedRoomListVView;
    public function __construct(TreatmentBedRoomListVView $treatmentBedRoomListVView)
    {
        $this->treatmentBedRoomListVView = $treatmentBedRoomListVView;
    }

    public function applyJoins()
    {
        return $this->treatmentBedRoomListVView
            ->select(
                'v_his_treatment_bed_room_list.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->whereRaw("
            REGEXP_LIKE(
                NLSSORT(v_his_treatment_bed_room_list.tdl_patient_name, 'NLS_SORT=GENERIC_M_AI'),
                NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                'i'
            )
        ", [$keyword])
                ->orWhere(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.tdl_patient_code'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.treatment_code'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.bed_room_code'), 'like', '%' . $keyword . '%')
                ->orWhereRaw("
            REGEXP_LIKE(
                NLSSORT(v_his_treatment_bed_room_list.bed_room_name, 'NLS_SORT=GENERIC_M_AI'),
                NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                'i'
            )
        ", [$keyword]);
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyDepartmentCodeFilter($query, $code)
    {
        if ($code !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.department_code'), $code);
        }
        return $query;
    }
    public function applyAddLoginnameFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.add_loginname'), $param);
        }
        return $query;
    }
    public function applyBedRoomIdsFilter($query, $ids)
    {
        if ($ids != null) {
            $query->whereIn(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.bed_room_id'), $ids);
        }
        return $query;
    }
    public function applyTreatmentTypeIdsFilter($query, $ids)
    {
        if ($ids != null) {
            $query->whereIn(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.in_treatment_type_id'), $ids);
        }
        return $query;
    }
    public function applyPatientClassifyIdsFilter($query, $ids)
    {
        if ($ids != null) {
            $query->whereIn(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.tdl_patient_classify_id'), $ids);
        }
        return $query;
    }
    public function applyIsInBedFilter($query, $param)
    {
        if ($param !== null) {
            if ($param) {
                $query->whereNotNull(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.bed_id'))
                    ->whereNull(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.remove_time'));
            } else {
                $query->whereNull(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.bed_id'));
            }
        }
        return $query;
    }
    public function applyIsOutFilter($query, $param)
    {
        if ($param !== null) {
            if ($param) {
                $query->whereNotNull(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.out_time'))
                    ->whereNotNull(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.remove_time'));
            } else {
                $query->whereNull(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.out_time'));
            }
        }
        return $query;
    }
    public function applyIsCoTreatDepartmentFilter($query, $param)
    {
        if ($param !== null) {
            if ($param) {
                $query->whereNotNull(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.co_department_ids'));
            } else {
                $query->whereNull(DB::connection('oracle_his')->raw('v_his_treatment_bed_room_list.co_department_ids'));
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
                    $query->orderBy('v_his_treatment_bed_room_list.' . $key, $item);
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
    public function applyGroupByField($data, $groupByField = null)
    {
        if (!$groupByField) {
            return $data;
        }
        // Chuyển camelCase thành snake_case
        $groupByFieldFormat = Str::snake($groupByField);
        return collect($data)->groupBy($groupByFieldFormat)->map(function ($items, $key) use ($groupByField) {
            return [
                $groupByField => $key,
                'total' => $items->count(), 
                'data' => $items->toArray(),
            ];
        })->values();
    }
    
    public function getById($id)
    {
        return $this->treatmentBedRoomListVView->find($id);
    }
}
