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
            ->select([
                "xa_v_his_sere_serv_list.id as key",     
                "xa_v_his_sere_serv_list.id",     
                "xa_v_his_sere_serv_list.is_no_execute",
                "xa_v_his_sere_serv_list.is_delete",
                "xa_v_his_sere_serv_list.amount",
                "xa_v_his_sere_serv_list.service_req_id",
                "xa_v_his_sere_serv_list.service_code",
                "xa_v_his_sere_serv_list.service_name",
                "xa_v_his_sere_serv_list.service_unit_name",
                "xa_v_his_sere_serv_list.service_req_code",
                "xa_v_his_sere_serv_list.treatment_id",
                "xa_v_his_sere_serv_list.tracking_id",
                "xa_v_his_sere_serv_list.intruction_time",
                "xa_v_his_sere_serv_list.intruction_date",
                "xa_v_his_sere_serv_list.vir_intruction_month",
                "xa_v_his_sere_serv_list.patient_type_name",
                "xa_v_his_sere_serv_list.request_department_name",
                "xa_v_his_sere_serv_list.department_name",
                "xa_v_his_sere_serv_list.tutorial",
                "xa_v_his_sere_serv_list.tracking_creator",
                "xa_v_his_sere_serv_list.service_type_code",
                "xa_v_his_sere_serv_list.service_type_name",
                "xa_v_his_sere_serv_list.service_req_stt_code",
                "xa_v_his_sere_serv_list.service_req_stt_name",
                "xa_v_his_sere_serv_list.patient_code",
            ]);
    }
    public function applyJoinsDichVuYeuCau()
    {
        return $this->sereServListVView
            ->leftJoin('his_machine', 'xa_v_his_sere_serv_list.machine_id', '=','his_machine.id')
            ->select([
                "xa_v_his_sere_serv_list.id as key",     
                "xa_v_his_sere_serv_list.id",     
                "xa_v_his_sere_serv_list.is_no_execute",
                "xa_v_his_sere_serv_list.is_delete",
                "xa_v_his_sere_serv_list.amount",
                "xa_v_his_sere_serv_list.service_code",
                "xa_v_his_sere_serv_list.service_name",
                "xa_v_his_sere_serv_list.service_unit_name",
                "xa_v_his_sere_serv_list.service_req_code",
                "xa_v_his_sere_serv_list.treatment_id",
                "xa_v_his_sere_serv_list.tracking_id",
                "xa_v_his_sere_serv_list.service_type_code",
                "xa_v_his_sere_serv_list.service_type_name",
                "xa_v_his_sere_serv_list.tdl_instruction_note",
                "xa_v_his_sere_serv_list.vir_total_price",
                "xa_v_his_sere_serv_list.block",
                "his_machine.machine_code",
                "his_machine.machine_name",
            ])
            ;
    }
    
    public function applyJoinsDichVuChiDinh()
    {
        return $this->sereServListVView
            ->select([
                "id as key",
                "id",     
                "is_no_execute",
                "is_delete",
                "amount",
                "service_req_id",
                "service_code",
                "service_name",
                "service_req_code",
                "service_type_code",
                "service_type_name",
                "service_req_stt_code",
                "service_req_stt_name",
                "service_unit_code",
                "service_unit_name",
                "execute_room_code",
                "execute_room_name",
                "execute_department_code",
                "execute_department_name",
            ]);
    }
    public function applyJoinsSuaChiDinh()
    {
        return $this->sereServListVView
            ->select([
                "xa_v_his_sere_serv_list.id as key",     
                "xa_v_his_sere_serv_list.id",     
                "xa_v_his_sere_serv_list.is_no_execute",
                "xa_v_his_sere_serv_list.is_delete",
                "xa_v_his_sere_serv_list.amount",
                "xa_v_his_sere_serv_list.service_req_id",
                "xa_v_his_sere_serv_list.service_code",
                "xa_v_his_sere_serv_list.service_name",
                "xa_v_his_sere_serv_list.service_unit_name",
                "xa_v_his_sere_serv_list.service_req_code",
                "xa_v_his_sere_serv_list.treatment_id",
                "xa_v_his_sere_serv_list.tracking_id",
                "xa_v_his_sere_serv_list.intruction_time",
                "xa_v_his_sere_serv_list.intruction_date",
                "xa_v_his_sere_serv_list.vir_intruction_month",
                "xa_v_his_sere_serv_list.patient_type_name",
                "xa_v_his_sere_serv_list.request_department_name",
                "xa_v_his_sere_serv_list.department_name",
                "xa_v_his_sere_serv_list.tutorial",
                "xa_v_his_sere_serv_list.tracking_creator",
                "xa_v_his_sere_serv_list.service_type_code",
                "xa_v_his_sere_serv_list.service_type_name",
                "xa_v_his_sere_serv_list.service_req_stt_code",
                "xa_v_his_sere_serv_list.service_req_stt_name",
                "xa_v_his_sere_serv_list.patient_code",

                "xa_v_his_sere_serv_list.is_expend",
                "xa_v_his_sere_serv_list.is_out_parent_fee",
                "xa_v_his_sere_serv_list.is_not_use_bhyt",
                "xa_v_his_sere_serv_list.patient_type_id",
                "xa_v_his_sere_serv_list.primary_patient_type_id",
                "xa_v_his_sere_serv_list.assign_num_order",
            ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(('xa_v_his_sere_serv_list.sere_serv_list_code'), 'like', '%'. $keyword . '%')
            ->orWhere(('xa_v_his_sere_serv_list.lower(sere_serv_list_name)'), 'like', '%'. strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(('xa_v_his_sere_serv_list.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(('xa_v_his_sere_serv_list.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyIsNoExecuteFilter($query)
    {
        $query->where(function ($q) {
            $q->where('xa_v_his_sere_serv_list.IS_NO_EXECUTE', 0)
              ->orWhereNull('xa_v_his_sere_serv_list.IS_NO_EXECUTE');
        });
        return $query;
    }
    public function applyServiceReqIsNoExecuteFilter($query)
    {
        $query->where(function ($q) {
            $q->where('xa_v_his_sere_serv_list.SERVICE_REQ_IS_NO_EXECUTE', 0)
              ->orWhereNull('xa_v_his_sere_serv_list.SERVICE_REQ_IS_NO_EXECUTE');
        });
        return $query;
    }
    public function applyTreatmentIdFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('xa_v_his_sere_serv_list.treatment_id'), $param);
        }
        return $query;
    }
    public function applyNotInDichVuNoiTruFilter($query)
    {
        $query->where(function ($q) {
            $q->whereNotIn('xa_v_his_sere_serv_list.exam_end_type', [2])
              ->whereNotNull('xa_v_his_sere_serv_list.exam_end_type');
        });
        return $query;
    }
    public function applyPatientCodeFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('xa_v_his_sere_serv_list.patient_code'), $param);
        }
        return $query;
    }
    public function applyServiceTypeCodesFilter($query, $param)
    {
        if ($param !== null) {
            $query->whereIn(('xa_v_his_sere_serv_list.service_type_code'), $param);
        }
        return $query;
    }
    public function applyTrackingIdFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('xa_v_his_sere_serv_list.tracking_id'), $param);
        }
        return $query;
    }
    public function applyNotInTrackingFilter($query, $param)
    {
        if ($param == true) {
            $query->whereNull(('xa_v_his_sere_serv_list.tracking_id'));
        }
        return $query;
    }
    public function applyServiceReqIdFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('xa_v_his_sere_serv_list.service_req_id'), $param);
        }
        return $query;
    }
    public function applyServiceIdsFilter($query, $param)
    {
        if ($param != null) {
            $query->leftJoin('v_his_room request_room', 'request_room.id', '=', 'xa_v_his_sere_serv_list.request_room_id')
            ->addSelect([
                'request_room.room_code as request_room_code',
                'request_room.room_name as request_room_name',
                ])
            ->whereIn(('xa_v_his_sere_serv_list.service_id'), $param);
        }
        return $query;
    }
    public function applyNotInServiceIdsFilter($query, $param)
    {
        if ($param != null) {
            $query->whereNotIn(('xa_v_his_sere_serv_list.service_id'), $param);
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
                    'total' => $group->count(),
                    'data' => $groupData($group, $fields),
                ];
            })->values();
        };
    
        return $groupData(collect($data), $snakeFields);
    }
    public function applyGroupByFieldYeuCauClsPttt($data, $groupByFields = [])
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
                    'total' => $group->filter(function ($item) {
                        return $item;
                    })->pluck('service_req_code')->unique()->count(),
                    'totalServiceReqCode03' => $group->filter(function ($item) {
                        return $item['service_req_stt_code'] == '03';
                    })->pluck('service_req_code')->unique()->count(),
                ];
            
                if ($currentField === 'service_req_code') {
                    $firstItem = $group->first();
                    $result['key'] = $firstItem['service_req_code'].$firstItem['execute_room_name'].$firstItem['execute_department_name'].$firstItem['id'] ?? null;
                    $result['executeRoomName'] = $firstItem['execute_room_name'] ?? null;
                    $result['executeDepartmentName'] = $firstItem['execute_department_name'] ?? null;
                    $result['serviceReqId'] = $firstItem['service_req_id'] ?? null;
                    $result['serviceReqSttCode'] = $firstItem['service_req_stt_code'] ?? null;
                    $result['serviceReqSttName'] = $firstItem['service_req_stt_name'] ?? null;
                }

                $result['children']  = $groupData($group, $fields);
                return $result;
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
    //         'create_time' => now()->format('YmdHis'),
    //         'modify_time' => now()->format('YmdHis'),
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
    //         'modify_time' => now()->format('YmdHis'),
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
                ProcessElasticIndexingJob::dispatch('sere_serv_list_v_view', 'v_his_sere_serv_list', $startId, $endId, $batchSize);
            }
        }
    }
}