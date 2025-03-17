<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ReportTypeCat;
use App\Models\View\SereServClsListVView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SereServClsListVViewRepository
{
    protected $sereServClsListVView;
    protected $reportTypeCat;
    public function __construct(SereServClsListVView $sereServClsListVView, ReportTypeCat $reportTypeCat)
    {
        $this->sereServClsListVView = $sereServClsListVView;
        $this->reportTypeCat = $reportTypeCat;
    }

    public function applyJoins()
    {
        return $this->sereServClsListVView
            ->select();
    }
    public function applyWithParam($query, $tab, $serviceCodes, $groupBy)
    {
        if($tab != null){
            if($serviceCodes != null){
                if($tab == 'CDHA' && $groupBy == [ "intructionDate", "intructionTime", "serviceTypeName"]){
                    return $query->with([
                        'sere_serv_exts' => function ($query) {
                            $query->select('sere_serv_id', 'conclude')->where('is_delete', 0)->where('is_active', 1);
                        },
                    ]);
                }
                if($tab == 'XN' && $groupBy == null){
                    return $query->with([
                        'test_results' => function ($query) {
                            $query->select('sere_serv_id','intruction_time', 'value', 'test_index_name', 'test_index_unit_name')->where('is_delete', 0)->where('is_active', 1);
                        },
                    ]);
                }
            }
        }
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
    public function applyGroupByField($data, $groupByFields = [], $from, $to, $reportTypeCode,  $tab = null, $serviceCodes = [])
    {
        $monthList = [];
        if (isset($from) && isset($to)) {
            $monthList = $this->generateMonthList($from, $to);
        }

        // Lấy danh sách tất cả category từ DB
        $categoryList = [];
        if (isset($reportTypeCode)) {
            $categoryList = Cache::remember('category_list_' . $reportTypeCode, 14400, function () use ($reportTypeCode) {
                return $this->reportTypeCat->where('report_type_code', $reportTypeCode)->pluck('category_name', 'num_order')->toArray();
            });
        }
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

        // Hàm đệ quy nhóm dữ liệu
        $groupData = function ($items, $fields) use (&$groupData, $fieldMappings, $monthList, $categoryList, $tab, $serviceCodes) {
            if (empty($fields)) {
                return $items->values(); // Hết field nhóm -> Trả về danh sách gốc
            }

            $currentField = array_shift($fields);
            $originalField = $fieldMappings[$currentField];

            $grouped = $items->groupBy(function ($item) use ($currentField) {
                return $item[$currentField] ?? null;
            })->map(function ($group, $key) use ($fields, $groupData, $originalField, $tab, $serviceCodes) {
                $result = [
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'serviceCode' => $originalField === 'serviceName' ? ($group->first()['service_code'] ?? null) : null, // serviceCode
                    'totalAmount' => $group->sum('amount'),
                    'total' => $group->count(),
                    'data' => $groupData($group, $fields),
                ];
            
                // Xóa 'serviceCode' nếu không nhóm theo 'serviceName'
                if ($originalField !== 'serviceName') {
                    unset($result['serviceCode']);
                }
                // Nếu tab là 'CDHA' và serviceCode rỗng, thì xóa phần data khi nhóm theo serviceName
                // Nếu tab là 'XN' và serviceCode rỗng, thì xóa phần data khi nhóm theo serviceName
                if ($originalField === 'serviceName' && (
                    ($tab === 'CDHA' && empty($serviceCodes))
                    || ($tab === 'XN' && empty($serviceCodes))
                )) {
                    unset($result['data']);
                }
                return $result;
            });

            // Nếu nhóm theo virIntructionMonth, đảm bảo đủ các tháng
            if ($originalField === 'virIntructionMonth') {
                foreach ($monthList as $month) {
                    if (!$grouped->has($month)) {
                        $grouped[$month] = [
                            $originalField => $month,
                            'totalAmount' => 0,
                            'total' => 0,
                            'data' => collect([]),
                        ];
                    }
                }
                // **Sắp xếp theo thứ tự tăng dần**
                $grouped = collect($grouped)->sortBy(function ($item) {
                    return $item['virIntructionMonth'];
                });
            }

            // Nếu nhóm theo intructionDate
            if ($originalField === 'intructionDate') {
                // **Sắp xếp theo thứ tự giảm dần**
                $grouped = collect($grouped)->sortByDesc(function ($item) {
                    return $item['intructionDate'];
                });
            }
            // Nếu nhóm theo intructionTime
            if ($originalField === 'intructionTime') {
                // **Sắp xếp theo thứ tự giảm dần**
                $grouped = collect($grouped)->sortByDesc(function ($item) {
                    return $item['intructionTime'];
                });
            }

            // Nếu nhóm theo categoryName, đảm bảo đủ tất cả các category
            if ($originalField === 'categoryName') {
                foreach ($categoryList as $numOrder => $category) {
                    if (!$grouped->has($category)) {
                        $grouped[$category] = [
                            $originalField => $category,
                            'totalAmount' => 0,
                            'total' => 0,
                            'numOrder' => $numOrder,
                            'data' => collect([]),
                        ];
                    }                        
                    $grouped[$category] = $grouped[$category] + ['numOrder' => $numOrder];
                }
                if(isset($grouped[""])){
                    $grouped[""] = $grouped[""] + ['numOrder' => 0];
                }
                // **Sắp xếp theo thứ tự tăng dần theo numOrder category**
                $grouped = collect($grouped)->sortBy(fn($item) => $item['numOrder']);
            }
            return $grouped->values();
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
        return $this->sereServClsListVView->find($id);
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
                ProcessElasticIndexingJob::dispatch('sere_serv_cls_list_v_view', 'v_his_sere_serv_cls_list', $startId, $endId, $batchSize);
            }
        }
    }
}
