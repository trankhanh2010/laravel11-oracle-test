<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\TrackingListVView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrackingListVViewRepository
{
    protected $trackingListVView;
    public function __construct(TrackingListVView $trackingListVView)
    {
        $this->trackingListVView = $trackingListVView;
    }

    public function applyJoins()
    {
        return $this->trackingListVView
            ->select("*");
    }
    public function applyJoinsDanhSachToDieuTriCu()
    {
        return $this->trackingListVView
            ->select([
                "id",
                "creator",            
                "treatment_id",
                "tracking_time",
                "icd_code",
                "icd_name",
                "medical_instruction",
                "department_id",
                "care_instruction",
                "room_id",
                "EMR_DOCUMENT_STT_ID",
                "content",
                "department_code",
                "department_name",
                "intruction_date",
                "tracking_creator",
            ]);
    }
    public function applyJoinsDanhSachTheoKhoaDieuTri()
    {
        return $this->trackingListVView
            ->select([
                DB::connection('oracle_his')->raw("'tracking' || id as key"),
                "id",
                "creator",            
                "tracking_time",
                "icd_code",
                "icd_name",
                "content",
                "department_name",
                "intruction_date",
                "tracking_creator",
            ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(('tracking_list_code'), 'like', '%'. $keyword . '%')
            ->orWhere(('lower(tracking_list_name)'), 'like', '%'. strtolower($keyword) . '%');
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
    public function applyTreatmentIdFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('treatment_id'), $param);
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
                if ($currentField === 'tracking_time' && !empty($item[$currentField])) {
                    $value = $item[$currentField];
                
                    // Nếu là chuỗi và có đủ 8 ký tự trở lên (YYYYMMDDhhmmss)
                    if (is_string($value) && strlen($value) >= 8) {
                        return substr($value, 0, 8) . '000000'; // Chỉ giữ lại YYYYMMDD và thay hhmmss thành 000000
                    }
                }
                return $item[$currentField] ?? null;
            })->map(function ($group, $key) use ($fields, $groupData, $originalField) {
                return [
                    'key' => (string)$key,
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'total' => $group->count(),
                    'children' => $groupData($group, $fields),
                ];
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
        // dd($query->toSql());
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
        return $this->trackingListVView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->trackingListVView::create([
    //         'create_time' => now()->format('YmdHis'),
    //         'modify_time' => now()->format('YmdHis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'tracking_list_v_view_code' => $request->tracking_list_v_view_code,
    //         'tracking_list_v_view_name' => $request->tracking_list_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('YmdHis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'tracking_list_v_view_code' => $request->tracking_list_v_view_code,
    //         'tracking_list_v_view_name' => $request->tracking_list_v_view_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
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
                ProcessElasticIndexingJob::dispatch('tracking_list_v_view', 'v_his_tracking_list', $startId, $endId, $batchSize);
            }
        }
    }
}