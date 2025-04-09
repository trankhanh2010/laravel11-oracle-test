<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ReportTypeCat;
use App\Models\View\SereServTeinChartsVView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SereServTeinChartsVViewRepository
{
    protected $sereServTeinChartsVView;
    protected $reportTypeCat;
    public function __construct(SereServTeinChartsVView $sereServTeinChartsVView, ReportTypeCat $reportTypeCat)
    {
        $this->sereServTeinChartsVView = $sereServTeinChartsVView;
        $this->reportTypeCat = $reportTypeCat;
    }

    public function applyJoins()
    {
        return $this->sereServTeinChartsVView
            ->select([
                'service_req_is_no_execute',
                'is_no_execute',
                'intruction_date',
                'intruction_time',
                "service_name",
                "service_code",
                'value',
                'test_index_unit_name',
                'test_index_name',
                'num_order',
                'note',
                'description',
            ]);
    }
    public function applyWithParam($query)
    {
        return $query;
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(('service_code'), 'like', '%' . $keyword . '%')
                ->orWhere(('lower(service_name)'), 'like', '%' . strtolower($keyword) . '%');
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
    public function applyServiceTypeCodesFilter($query, $param)
    {
        if ($param !== null) {
            $query->whereIn(('service_type_code'), $param);
        }
        return $query;
    }
    public function applyServiceCodesFilter($query, $param)
    {
        if ($param !== null) {
            $query->whereIn(('service_code'), $param);
        }
        return $query;
    }
    public function applyIntructionTimeFilter($query, $from, $to)
    {
        if (isset($to) && isset($from)) {
            $query->whereBetween('intruction_time', [$from, $to]);
        }
        return $query;
    }
    public function applyTabFilter($query, $param)
    {
        if ($param !== null) {
            if ($param == 'CongKham') {
                $query->where(('service_req_type_code'), 'KH');
            }
            if ($param == 'CLS') {
                $query->whereNot(('service_req_type_code'), 'KH');
            }
            if ($param == 'CDHA') {
                $query->whereIn(('service_type_code'), ['HA','NS','SA','CN']);
            }
            if ($param == 'XN') {
                $query->whereIn(('service_type_code'), ['XN']);
            }
        }
        return $query;
    }
    public function applyReportTypeCodeFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(function ($q) use ($param) {
                $q->where('report_type_code', $param)
                  ->orWhereNull('report_type_code');
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
            })->map(function ($group, $key) use ($fields, $groupData, $originalField) {
                return [
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'data' => $groupData($group, $fields),
                ];
            })->values();
        };
    
        return $groupData(collect($data), $snakeFields);
    }


    public function generateMonthList($from, $to)
    {
        $fromMonth = substr($from, 0, 6); // Lấy năm và tháng từ from
        $toMonth = substr($to, 0, 6); // Lấy năm và tháng từ to

        $currentMonth = $fromMonth;
        $monthList = [];

        while ($currentMonth <= $toMonth) {
            $monthList[] = $currentMonth . '00000000'; // Thêm ngày 00 để phù hợp với định dạng virIntructionMonth
            $currentMonth = date('Ym', strtotime($currentMonth . '01 +1 month')); // Tăng tháng
        }
        return $monthList;
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
        return $this->sereServTeinChartsVView->find($id);
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
                ProcessElasticIndexingJob::dispatch('sere_serv_tein_charts_v_view', 'v_his_sere_serv_tein_charts', $startId, $endId, $batchSize);
            }
        }
    }
}
