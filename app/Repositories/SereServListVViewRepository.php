<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\SereServListVView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SereServListVViewRepository
{
    protected $sereServListVView;
    public function __construct(SereServListVView $sereServListVView)
    {
        $this->sereServListVView = $sereServListVView;
    }

    public function applyJoins()
    {
        return $this->sereServListVView
            ->select(
                'v_his_sere_serv_list.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_list.sere_serv_list_code'), 'like', '%'. $keyword . '%')
            ->orWhere(DB::connection('oracle_his')->raw('lower(v_his_sere_serv_list.sere_serv_list_name)'), 'like', '%'. strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_list.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_list.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyTreatmentIdFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_list.treatment_id'), $param);
        }
        return $query;
    }
    public function applyTrackingIdFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_list.tracking_id'), $param);
        }
        return $query;
    }
    public function applyNotInTrackingFilter($query, $param)
    {
        if ($param == true) {
            $query->whereNull(DB::connection('oracle_his')->raw('v_his_sere_serv_list.tracking_id'));
        }
        return $query;
    }
    public function applyServiceReqIdFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_sere_serv_list.service_req_id'), $param);
        }
        return $query;
    }

    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('v_his_sere_serv_list.' . $key, $item);
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
                    'total' => $group->count(),
                    'data' => $groupData($group, $fields),
                ];
            })->values();
        };
    
        return $groupData(collect($data), $snakeFields);
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
        return $this->sereServListVView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->sereServListVView::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'sere_serv_list_v_view_code' => $request->sere_serv_list_v_view_code,
    //         'sere_serv_list_v_view_name' => $request->sere_serv_list_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'sere_serv_list_v_view_code' => $request->sere_serv_list_v_view_code,
    //         'sere_serv_list_v_view_name' => $request->sere_serv_list_v_view_name,
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
            $data = $this->applyJoins()->where('v_his_sere_serv_list.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('v_his_sere_serv_list.id');
            $maxId = $this->applyJoins()->max('v_his_sere_serv_list.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('sere_serv_list_v_view', 'v_his_sere_serv_list', $startId, $endId, $batchSize);
            }
        }
    }
}