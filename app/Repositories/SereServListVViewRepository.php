<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\DonVView;
use App\Models\View\SereServListVView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SereServListVViewRepository
{
    protected $sereServListVView;
    protected $donVView;
    public function __construct(
        SereServListVView $sereServListVView,
        DonVView $donVView,
    ) {
        $this->sereServListVView = $sereServListVView;
        $this->donVView = $donVView;
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
            ->leftJoin('his_machine', 'xa_v_his_sere_serv_list.machine_id', '=', 'his_machine.id')
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
                "xa_v_his_sere_serv_list.id as key",
                "xa_v_his_sere_serv_list.id",
                "xa_v_his_sere_serv_list.is_no_execute",
                "xa_v_his_sere_serv_list.is_delete",
                "xa_v_his_sere_serv_list.amount",
                "xa_v_his_sere_serv_list.service_req_id",
                "xa_v_his_sere_serv_list.service_code",
                "xa_v_his_sere_serv_list.service_name",
                "xa_v_his_sere_serv_list.service_req_code",
                "xa_v_his_sere_serv_list.service_type_code",
                "xa_v_his_sere_serv_list.service_type_name",
                "xa_v_his_sere_serv_list.service_req_stt_code",
                "xa_v_his_sere_serv_list.service_req_stt_name",
                "xa_v_his_sere_serv_list.service_unit_code",
                "xa_v_his_sere_serv_list.service_unit_name",
                "xa_v_his_sere_serv_list.execute_room_code",
                "xa_v_his_sere_serv_list.execute_room_name",
                "xa_v_his_sere_serv_list.execute_department_code",
                "xa_v_his_sere_serv_list.execute_department_name",
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
                "xa_v_his_sere_serv_list.note",
                "xa_v_his_sere_serv_list.amount",
                "xa_v_his_sere_serv_list.price",
                "xa_v_his_sere_serv_list.service_req_id",
                "xa_v_his_sere_serv_list.service_id",
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
                "xa_v_his_sere_serv_list.patient_type_code",
                "xa_v_his_sere_serv_list.patient_type_name",
                "xa_v_his_sere_serv_list.primary_patient_type_id",
                "xa_v_his_sere_serv_list.assign_num_order",
                "xa_v_his_sere_serv_list.execute_room_id",
                "xa_v_his_sere_serv_list.execute_room_code",
                "xa_v_his_sere_serv_list.execute_room_name",
            ]);
    }
    public function applyWithParamSuaChiDinh($query)
    {
        return $query->with([
            'sere_serv_exts:sere_serv_id,instruction_note',
            'list_select_patient_types',
        ]);
    }
    public function applyJoinsChonThongTinXuLy()
    {
        // UnionAll rồi mới addSelect sau
        return $this->sereServListVView->select([
            'xa_v_his_sere_serv_list.service_name',
            'xa_v_his_sere_serv_list.service_code',
        ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(('xa_v_his_sere_serv_list.sere_serv_list_code'), 'like', '%' . $keyword . '%')
                ->orWhere(('xa_v_his_sere_serv_list.lower(sere_serv_list_name)'), 'like', '%' . strtolower($keyword) . '%');
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
        $query->join('his_treatment_type', function ($join) {
            $join->on('his_treatment_type.id', '=', 'xa_v_his_sere_serv_list.req_treatment_type_id')
                ->whereNotIn('his_treatment_type.treatment_type_code', ["03"]); // bỏ các dịch vụ nội trú tdl_service_type_id của service_req là nội trú
        });
        $query->where(function ($q) {
            $q->where('xa_v_his_sere_serv_list.is_main_exam', 0)
                ->orWhereNull('xa_v_his_sere_serv_list.is_main_exam');
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
    public function applyParentServiceReqIdFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('xa_v_his_sere_serv_list.parent_service_req_id'), $param);
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
    public function applyChonThongTinXuLyFilter($query)
    {
        $query->whereIn(('xa_v_his_sere_serv_list.service_type_code'), ['KH', 'XN', 'HA', 'TT', 'CN', 'GI', 'NS', 'SA', 'PT', 'CL', 'PH', 'GB',]);
        return $query;
    }
    public function applyUnionAllDichVuDonChonThongTinXuLy($query, $treatmentId)
    {
        $queryDichVu = clone $query;
        // $queryDon = clone $query;

        $queryDichVu = $this->getQueryDichVuLucChonThongTinXuLy($queryDichVu);
        $queryDon = $this->getQueryDonLucChonThongTinXuLy($treatmentId);
        $queryResult =  $queryDichVu->unionall($queryDon); // Hợp đơn với dịch vụ ở trên

        return $queryResult;
    }
    public function getQueryDonLucChonThongTinXuLy($treatmentId)
    {
        try {
            return $this->donVView
                ->select([
                    'xa_v_his_don.service_name',
                    'xa_v_his_don.service_code',
                    'xa_v_his_don.CONCENTRA',
                    DB::connection('oracle_his')->raw('SUM(xa_v_his_don.amount) as amount'),
                    'xa_v_his_don.service_unit_code',
                    'xa_v_his_don.service_unit_name',
                    'xa_v_his_don.service_type_code',
                    'xa_v_his_don.service_type_name',
                    'xa_v_his_don.tutorial',
                ])
                ->where('xa_v_his_don.is_delete', 0)
                ->whereIn('xa_v_his_don.service_type_code', ['TH', 'VT'])
                ->where('xa_v_his_don.TREATMENT_ID', $treatmentId)
                ->groupBy( // Nhóm lại theo serviceName amount là tổng amount
                    'xa_v_his_don.service_name',
                    'xa_v_his_don.service_code',
                    'xa_v_his_don.CONCENTRA',
                    'xa_v_his_don.service_unit_code',
                    'xa_v_his_don.service_unit_name',
                    'xa_v_his_don.service_type_code',
                    'xa_v_his_don.service_type_name',
                    'xa_v_his_don.tutorial'
                );
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['don_v_view'], $e);
        }
    }

    public function getQueryDichVuLucChonThongTinXuLy($query)
    {
        $query = $this->applyChonThongTinXuLyFilter($query);
        $query->addSelect([
            DB::connection('oracle_his')->raw("NULL as concentra"),
            'xa_v_his_sere_serv_list.amount',
            'xa_v_his_sere_serv_list.service_unit_code',
            'xa_v_his_sere_serv_list.service_unit_name',
            'xa_v_his_sere_serv_list.service_type_code',
            'xa_v_his_sere_serv_list.service_type_name',
            DB::connection('oracle_his')->raw("NULL as tutorial"),
        ]);
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
                    $result['key'] = $firstItem['service_req_code'] . $firstItem['execute_room_name'] . $firstItem['execute_department_name'] . $firstItem['id'] ?? null;
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
            $data = $query->get();
            $data->each(function ($service) {
                $service->list_select_patient_types->each->makeHidden('pivot');
                // $service->list_select_service_room->each->makeHidden('pivot');
            });
            return $data;
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function fetchDataNotWith($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            $data = $query->get();
            return $data;
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
