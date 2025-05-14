<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\DocumentListVView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentListVViewRepository
{
    protected $documentListVView;
    public function __construct(DocumentListVView $documentListVView)
    {
        $this->documentListVView = $documentListVView;
    }

    public function applyJoins()
    {
        return $this->documentListVView
            ->select([
                "id as key",
                "id",
                "create_time",
                "modify_time",
                "creator",
                "modifier",
                "app_creator",
                "app_modifier",
                "document_code",
                "document_name",
                "treatment_code",
                "treatment_id",
                "document_type_id",
                "his_code",
                "his_order",
                "is_multi_sign",
                "is_capture", 
                "create_date",
                "document_name_unsign",
                "document_time",
                "document_date",
                "last_version_url",
                "document_type_code",
                "document_type_name",
                'document_type_num_order',
                'document_group_code',
                'document_group_name',
                'document_group_num_order',
            ]
            );
    }
    public function applyWithParam($query)
    {
        return $query->with([
            'signs' => function ($q) {
                $q->select([
                    "id",
                    "document_id",
                    "num_order",
                    "loginname",
                    "username",
                    "title",
                    "department_code",
                    "department_name",
                    "sign_time",
                    "sign_date",
                    "reject_time",
                    "reject_date",
                    "reject_reason",
                    "description",
                    "is_sign_board",
                    "sign_image",
                    "password",
                    "secret_key",
                    "cancel_time",
                    "cancel_reason",
                ]);
            },
        ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(('document_list_code'), 'like', '%'. $keyword . '%')
            ->orWhere(('lower(document_list_name)'), 'like', '%'. strtolower($keyword) . '%');
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
    public function applyDocumentTypeIdFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('document_type_id'), $param);
        }
        return $query;
    }
    public function applyTreatmentCodeFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('treatment_code'), $param);
        }
        return $query;
    }
    public function applyDocumentIdsFilter($query, $param)
    {
        if ($param !== null) {
            $query->whereIn(('id'), $param);
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
        return $this->documentListVView->find($id);
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
            
                // Nếu group theo documentTypeName thì thêm documentName (lấy theo phần tử đầu)
                if ($currentField === 'document_type_name') {
                    $firstItem = $group->first();
                    $result['documentName'] = $firstItem['document_type_name'] ?? null;
                }
                return $result;
            })->values();
        };

        return $groupData(collect($data), $snakeFields);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->documentListVView::create([
    //         'create_time' => now()->format('YmdHis'),
    //         'modify_time' => now()->format('YmdHis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'document_list_v_view_code' => $request->document_list_v_view_code,
    //         'document_list_v_view_name' => $request->document_list_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('YmdHis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'document_list_v_view_code' => $request->document_list_v_view_code,
    //         'document_list_v_view_name' => $request->document_list_v_view_name,
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
                ProcessElasticIndexingJob::dispatch('document_list_v_view', 'v_emr_document_list', $startId, $endId, $batchSize);
            }
        }
    }
}