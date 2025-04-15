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
                [
                    'id as key',
                    'id',
                    'treatment_id',
                    'in_time',
                    'add_time',
                    'add_loginname',
                    'add_username',
                    'treatment_code',
                    'tdl_patient_code',
                    'out_time',
                    'room_code',
                    'room_name',
                    'tdl_patient_name',
                    'tdl_patient_dob',
                    'tdl_patient_gender_name',
                    'tdl_patient_ethnic_name',
                    'room_type_code',
                    'room_type_name',
                ]
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        if ($keyword != null) {
            return $query->where(function ($query) use ($keyword) {
                $query->whereRaw("
                REGEXP_LIKE(
                    NLSSORT(tdl_patient_name, 'NLS_SORT=GENERIC_M_AI'),
                    NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                    'i'
                )
            ", [$keyword])
                    ->orWhere(('tdl_patient_code'), 'like', '%' . $keyword . '%')
                    ->orWhere(('treatment_code'), 'like', '%' . $keyword . '%')
                    ->orWhere(('room_code'), 'like', '%' . $keyword . '%')
                    ->orWhereRaw("
                REGEXP_LIKE(
                    NLSSORT(room_name, 'NLS_SORT=GENERIC_M_AI'),
                    NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                    'i'
                )
            ", [$keyword]);
            });
        }
        return $query;
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
        if ($code != null) {
            $query->where(('department_code'), $code);
        }
        return $query;
    }
    public function applyTreatmentCodeFilter($query, $code)
    {
        if ($code != null) {
            $query->where(('treatment_code'), $code);
        }
        return $query;
    }
    public function applyPatientCodeFilter($query, $code)
    {
        if ($code != null) {
            $query->where(('tdl_patient_code'), $code);
        }
        return $query;
    }
    public function applyAddLoginnameFilter($query, $param)
    {
        if ($param != null) {
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
    public function applyExecuteRoomCodeFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('execute_room_code'), $param);
        }
        return $query;
    }
    public function applyExecuteRoomIdsFilter($query, $param)
    {
        if ($param != null) {
            $query->whereIn(('execute_room_id'), $param);
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
            if ($param) {
                $query->whereNull(('remove_time'));
            } else {
                $query->whereNotNull(('remove_time'));
            }
        }
        return $query;
    }
    public function applyIsOutFilter($query, $param)
    {
        if ($param !== null) {
            if ($param) {
                $query->whereNotNull(('out_time'))
                    ->whereNotNull(('remove_time'));
            } else {
                $query->whereNull(('out_time'));
            }
        }
        return $query;
    }
    public function applyIsCoTreatDepartmentFilter($query, $param)
    {
        if ($param !== null) {
            if ($param) {
                $query->whereNotNull(('co_treatment_id'));
            } else {
                $query->whereNull(('co_treatment_id'));
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
    public function applyGroupByField($data, $groupByFields = [])
    {
        if (empty($groupByFields)) {
            return $data;
        }

        // Chuyển các field thành snake_case trước khi nhóm
        $fieldMappings = [];
        foreach ($groupByFields as $field) {
            $snakeField = Str::snake($field);
            $fieldMappings[$snakeField] = $field;
        }

        $snakeFields = array_keys($fieldMappings);

        // Đệ quy nhóm dữ liệu theo thứ tự fields đã convert
        $groupData = function ($items, $fields) use (&$groupData, $fieldMappings) {
            if (empty($fields)) {
                return $items->values(); // Hết field nhóm -> Trả về danh sách gốc
            }

            $currentField = array_shift($fields);
            $originalField = $fieldMappings[$currentField];

            return $items->groupBy(function ($item) use ($currentField) {
                return $item[$currentField] ?? null;
            })->map(function ($group, $key) use ($fields, $groupData, $originalField, $currentField) {
                $result = [
                    $originalField => (string)$key, // Trả về tên field gốc
                    'key' => (string)$key,
                    'total' => $group->count(),
                    'children' => $groupData($group, $fields),
                ];
            
                // Nếu group theo roomName thì thêm tdlPatientName (lấy theo phần tử đầu)
                if ($currentField === 'room_name') {
                    $firstItem = $group->first();
                    $result['tdlPatientName'] = $firstItem['room_name'] ?? null;
                }
                return $result;
            })->values();
        };

        return $groupData(collect($data), $snakeFields);
    }

    public function getById($id)
    {
        return $this->treatmentBedRoomListVView->find($id);
    }
}
