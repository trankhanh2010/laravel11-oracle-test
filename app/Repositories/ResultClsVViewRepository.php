<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\ResultClsVView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResultClsVViewRepository
{
    protected $resultClsVView;
    public function __construct(ResultClsVView $resultClsVView)
    {
        $this->resultClsVView = $resultClsVView;
    }

    public function applyJoins()
    {
        return $this->resultClsVView
            ->select([
                DB::connection('oracle_his')->raw("ROWNUM as key"),
                "id",
                "is_delete",
                "test_index_name",
                "test_index_code",
                "value",
                "note",
                "description",
                "service_name",
                "service_code",
                "service_req_is_no_execute",
                "is_no_execute",
                "patient_code",
                "treatment_code",
                "unit_code",
                "unit_name",
                "intruction_time",
                "intruction_date",
                "service_type_code",
                "service_type_name",
            ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        if ($keyword != null) {
            return $query->where(function ($query) use ($keyword) {
                $query->whereRaw("
                    REGEXP_LIKE(
                        NLSSORT(test_index_name, 'NLS_SORT=GENERIC_M_AI'),
                        NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                        'i'
                    )
                ", [$keyword])
                ->orWhereRaw("
                    REGEXP_LIKE(
                        NLSSORT(test_index_code, 'NLS_SORT=GENERIC_M_AI'),
                        NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                        'i'
                    )
                ", [$keyword])
                ->orWhereRaw("
                    REGEXP_LIKE(
                        NLSSORT(value, 'NLS_SORT=GENERIC_M_AI'),
                        NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                        'i'
                    )
                ", [$keyword])
                ->orWhereRaw("
                    REGEXP_LIKE(
                        NLSSORT(note, 'NLS_SORT=GENERIC_M_AI'),
                        NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                        'i'
                    )
                ", [$keyword])
                ->orWhereRaw("
                    REGEXP_LIKE(
                        NLSSORT(description, 'NLS_SORT=GENERIC_M_AI'),
                        NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                        'i'
                    )
                ", [$keyword])
                ->orWhereRaw("
                    REGEXP_LIKE(
                        NLSSORT(service_name, 'NLS_SORT=GENERIC_M_AI'),
                        NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                        'i'
                    )
                ", [$keyword])
                ->orWhereRaw("
                    REGEXP_LIKE(
                        NLSSORT(service_code, 'NLS_SORT=GENERIC_M_AI'),
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
    public function applyIntructionTimeFromFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('intruction_date'), '>=', $param);
        }
        return $query;
    }
    public function applyIntructionTimeToFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('intruction_date'), '<=', $param);
        }
        return $query;
    }
    public function applyIsNoExecuteFilter($query)
    {
        $query->where(function ($q) {
            $q->where('IS_NO_EXECUTE', 0)
              ->orWhereNull('IS_NO_EXECUTE');
        });
        return $query;
    }
    public function applyServiceReqIsNoExecuteFilter($query)
    {
        $query->where(function ($q) {
            $q->where('SERVICE_REQ_IS_NO_EXECUTE', 0)
              ->orWhereNull('SERVICE_REQ_IS_NO_EXECUTE');
        });
        return $query;
    }
    public function applyPatientCodeFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('patient_code'), $param);
        }
        return $query;
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
                    'key' => (string)$key,
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'total' => $group->count(),
                    // 'children' => $groupData($group, $fields),
                ];
                // Nếu group theo intructionDate thì thêm intructionTime (lấy theo phần tử đầu)
                if ($currentField === 'intruction_date') {
                    $firstItem = $group->first();
                    $result['intructionTime'] = $firstItem['intruction_date'] ?? null;
                }
                if ($currentField === 'service_name') {
                    $firstItem = $group->first();
                    $result['key'] = $firstItem['intruction_date'].$firstItem['service_name'];
                }

                // Đem children xuống dưới để nằm dưới các trường được thêm
                $result['children'] = $groupData($group, $fields);
                return $result;
            })->values();
        };
    
        return $groupData(collect($data), $snakeFields);
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
        return $this->resultClsVView->find($id);
    }

    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('id');
            $maxId = $this->applyJoins()->max('id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('result_cls_v_view', 'v_his_result_cls', $startId, $endId, $batchSize);
            }
        }
    }
}