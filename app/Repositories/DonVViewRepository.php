<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\DonVView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DonVViewRepository
{
    protected $donVView;
    public function __construct(DonVView $donVView)
    {
        $this->donVView = $donVView;
    }

    public function applyJoins()
    {
        return $this->donVView
            ->select(
                'xa_v_his_don.*'
            );
    }
    public function applyJoinsDonCuKeDonThuocPhongKham()
    {
        return $this->donVView
            ->select([
                'xa_v_his_don.id',
                'xa_v_his_don.m_type',
                'xa_v_his_don.key',
                'xa_v_his_don.is_active',
                'xa_v_his_don.is_delete',
                'xa_v_his_don.exp_mest_id',
                'xa_v_his_don.exp_mest_code',
                'xa_v_his_don.m_type_code',
                'xa_v_his_don.m_type_name',
                'xa_v_his_don.amount',
                'xa_v_his_don.service_unit_code',
                'xa_v_his_don.service_unit_name',
                'xa_v_his_don.is_expend',
                'xa_v_his_don.expend_type_id',
                'xa_v_his_don.is_out_parent_fee',
                'xa_v_his_don.exceed_limit_in_pres_reason',
                'xa_v_his_don.exceed_limit_in_day_reason',
                'xa_v_his_don.odd_pres_reason',
                'xa_v_his_don.over_result_test_reason',
                'xa_v_his_don.over_kidney_reason',
                'xa_v_his_don.exceed_limit_in_treat_reason',
                'xa_v_his_don.tutorial',
                'xa_v_his_don.exp_mest_type_code',
                'xa_v_his_don.exp_mest_type_name',
                'xa_v_his_don.service_req_id',
                'xa_v_his_don.tdl_service_req_code',
                'xa_v_his_don.exp_mest_medi_stock_code',
                'xa_v_his_don.exp_mest_medi_stock_name',
                'xa_v_his_don.tdl_intruction_time',
                'xa_v_his_don.tdl_intruction_date',
                'xa_v_his_don.tdl_patient_id',
                'xa_v_his_don.req_room_code',
                'xa_v_his_don.req_room_name',
                'xa_v_his_don.req_loginname',
                'xa_v_his_don.req_username',
                'xa_v_his_don.icd_code',
                'xa_v_his_don.icd_name',
                'xa_v_his_don.icd_sub_code',
                'xa_v_his_don.icd_text',
                'xa_v_his_don.num_order',
            ]);
    }
    public function applyJoinsThuocDaKeTrongNgay()
    {
        return $this->donVView
            ->select([
                'xa_v_his_don.m_type_id',
                'xa_v_his_don.m_type_code',
                'xa_v_his_don.m_type_name',
                'xa_v_his_don.active_ingr_bhyt_code',
                'xa_v_his_don.active_ingr_bhyt_name',
                'xa_v_his_don.amount',
            ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_don.don_code'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(xa_v_his_don.don_name)'), 'like', '%' . strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_don.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_don.is_delete'), $isDelete);
        }
        return $query;
    }

    public function applyPatientIdFilter($query, $param)
    {
        if ($param != null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_don.tdl_patient_id'), $param);
        }
        return $query;
    }
    public function applyIntructionTimeFromFilter($query, $param)
    {
        if ($param != null) {
            return $query->where(function ($query) use ($param) {
                $query->where('tdl_intruction_time', '>=', $param);
            });
        }
        return $query;
    }
    public function applyIntructionTimeToFilter($query, $param)
    {
        if ($param != null) {
            return $query->where(function ($query) use ($param) {
                $query->where('tdl_intruction_time', '<=', $param);
            });
        }
        return $query;
    }
    public function applyIntructionDateFilter($query, $param)
    {
        if ($param != null) {
            return $query->where(function ($query) use ($param) {
                $query->where('tdl_intruction_date', $param);
            });
        }
        return $query;
    }
    public function applyTabFilter($query, $param)
    {
        switch ($param) {
            case 'donCuKeDonThuocPhongKham':
                return $query->where(function ($q) {
                    $q->whereNotIn('xa_v_his_don.exp_mest_type_code', ['02', '03', '04', '05', '07', '08', '10', '12', '13'])
                        ->orWhereNull('xa_v_his_don.exp_mest_type_code');
                });
            case 'thuocDaKeTrongNgay':
                return $query->where('xa_v_his_don.m_type', 'TH');
            default:
                return $query;
        }
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('xa_v_his_don.' . $key, $item);
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
        return $this->donVView->find($id);
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
                $result =  [
                    'key' => (string)$key,
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'total' => $group->count(),
                ];

                if ($currentField === 'tdl_intruction_time') {
                    $firstItem = $group->first();
                    $result['expMestCode'] = $firstItem['exp_mest_code'];
                    $result['reqRoomCode'] = $firstItem['req_room_code'];
                    $result['reqRoomName'] = $firstItem['req_room_name'];
                    $result['reqLoginname'] = $firstItem['req_loginname'];
                    $result['reqUsername'] = $firstItem['req_username'];
                }

                if ($currentField === 'tdl_service_req_code') {
                    $firstItem = $group->first();
                    $result['serviceReqId'] = $firstItem['service_req_id'];
                    $result['expMestMediStockCode'] = $firstItem['exp_mest_medi_stock_code'];
                    $result['expMestMediStockName'] = $firstItem['exp_mest_medi_stock_name'];
                }

                $result['children'] = $groupData($group, $fields);
                return $result;
            })->values();
        };

        return $groupData(collect($data), $snakeFields);
    }
    public function applyGroupByFieldThuocDaKeTrongNgay($data, $groupByFields = [])
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
                $result =  [
                    'key' => (string)$key,
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'total' => $group->count(),
                    'amount' => $group->sum('amount'),
                ];

                if ($currentField === 'm_type_name') {
                    $firstItem = $group->first();
                    $result['mTypeId'] = $firstItem['m_type_id'];
                    $result['mTypeCode'] = $firstItem['m_type_code'];
                    $result['activeIngrBhytCode'] = $firstItem['active_ingr_bhyt_code'];
                    $result['activeIngrBhytName'] = $firstItem['active_ingr_bhyt_name'];
                }

                // $result['children'] = $groupData($group, $fields);
                return $result;
            })->values();
        };

        return $groupData(collect($data), $snakeFields);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->donVView::create([
    //         'create_time' => now()->format('YmdHis'),
    //         'modify_time' => now()->format('YmdHis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'don_v_view_code' => $request->don_v_view_code,
    //         'don_v_view_name' => $request->don_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('YmdHis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'don_v_view_code' => $request->don_v_view_code,
    //         'don_v_view_name' => $request->don_v_view_name,
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
            $data = $this->applyJoins()->where('xa_v_his_don.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('xa_v_his_don.id');
            $maxId = $this->applyJoins()->max('xa_v_his_don.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('don_v_view', 'xa_v_his_don', $startId, $endId, $batchSize);
            }
        }
    }
}
