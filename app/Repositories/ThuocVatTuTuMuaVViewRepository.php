<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\MediStock;
use App\Models\View\ThuocVatTuTuMuaVView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ThuocVatTuTuMuaVViewRepository
{
    protected $thuocVatTuTuMuaVView;
    protected $mediStock;
    public function __construct(
        ThuocVatTuTuMuaVView $thuocVatTuTuMuaVView,
        MediStock $mediStock,
        )
    {
        $this->thuocVatTuTuMuaVView = $thuocVatTuTuMuaVView;
        $this->mediStock = $mediStock;
    }

    public function applyJoins()
    {
        return $this->thuocVatTuTuMuaVView
            ->select(
                'xa_v_his_thuoc_vat_tu_tu_mua.*'
            );
    }
    public function applyJoinsKeDonThuocPhongKham()
    {
        return $this->thuocVatTuTuMuaVView->select([
            'xa_v_his_thuoc_vat_tu_tu_mua.id as key',
            'xa_v_his_thuoc_vat_tu_tu_mua.id as value',
            'xa_v_his_thuoc_vat_tu_tu_mua.service_type_name as title',
            'xa_v_his_thuoc_vat_tu_tu_mua.id',
            'xa_v_his_thuoc_vat_tu_tu_mua.m_type_id',
            'xa_v_his_thuoc_vat_tu_tu_mua.m_type_code',
            'xa_v_his_thuoc_vat_tu_tu_mua.m_type_name',
            'xa_v_his_thuoc_vat_tu_tu_mua.service_id',
            'xa_v_his_thuoc_vat_tu_tu_mua.m_parent_code',
            'xa_v_his_thuoc_vat_tu_tu_mua.m_parent_name',
            'xa_v_his_thuoc_vat_tu_tu_mua.CONCENTRA',
            'xa_v_his_thuoc_vat_tu_tu_mua.ACTIVE_INGR_BHYT_CODE',
            'xa_v_his_thuoc_vat_tu_tu_mua.ACTIVE_INGR_BHYT_NAME',
            'xa_v_his_thuoc_vat_tu_tu_mua.service_unit_code',
            'xa_v_his_thuoc_vat_tu_tu_mua.service_unit_name',
            'xa_v_his_thuoc_vat_tu_tu_mua.bean_amount',
            'xa_v_his_thuoc_vat_tu_tu_mua.tdl_package_number',
            'xa_v_his_thuoc_vat_tu_tu_mua.tdl_medicine_register_number',
            'xa_v_his_thuoc_vat_tu_tu_mua.tdl_medicine_expired_date',
            'xa_v_his_thuoc_vat_tu_tu_mua.national_name',
            'xa_v_his_thuoc_vat_tu_tu_mua.last_exp_price',
            'xa_v_his_thuoc_vat_tu_tu_mua.last_exp_vat_ratio',
            'xa_v_his_thuoc_vat_tu_tu_mua.last_imp_vat_ratio',
            'xa_v_his_thuoc_vat_tu_tu_mua.medi_stock_id',
            'xa_v_his_thuoc_vat_tu_tu_mua.medi_stock_code',
            'xa_v_his_thuoc_vat_tu_tu_mua.medi_stock_name',
            'xa_v_his_thuoc_vat_tu_tu_mua.is_drug_store',
            'xa_v_his_thuoc_vat_tu_tu_mua.manufacturer_code',
            'xa_v_his_thuoc_vat_tu_tu_mua.manufacturer_name',
            'xa_v_his_thuoc_vat_tu_tu_mua.service_type_code',
            'xa_v_his_thuoc_vat_tu_tu_mua.service_type_name',
            'xa_v_his_thuoc_vat_tu_tu_mua.description', // ghi chú lúc rê chuột vào
            'xa_v_his_thuoc_vat_tu_tu_mua.medicine_use_form_id',
            'xa_v_his_thuoc_vat_tu_tu_mua.medicine_use_form_name',
            'xa_v_his_thuoc_vat_tu_tu_mua.medicine_use_form_code',
            'xa_v_his_thuoc_vat_tu_tu_mua.ALERT_MAX_IN_PRESCRIPTION',
            'xa_v_his_thuoc_vat_tu_tu_mua.ALERT_MAX_IN_DAY',
            'xa_v_his_thuoc_vat_tu_tu_mua.ALERT_MAX_IN_TREATMENT',
            'xa_v_his_thuoc_vat_tu_tu_mua.IS_BLOCK_MAX_IN_PRESCRIPTION',
            'xa_v_his_thuoc_vat_tu_tu_mua.IS_BLOCK_MAX_IN_DAY',
            'xa_v_his_thuoc_vat_tu_tu_mua.IS_BLOCK_MAX_IN_TREATMENT',

        ])
            ->where('xa_v_his_thuoc_vat_tu_tu_mua.is_leaf', 1);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_thuoc_vat_tu_tu_mua.thuoc_vat_tu_tu_mua_code'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(xa_v_his_thuoc_vat_tu_tu_mua.thuoc_vat_tu_tu_mua_name)'), 'like', '%' . strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_thuoc_vat_tu_tu_mua.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_thuoc_vat_tu_tu_mua.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('xa_v_his_thuoc_vat_tu_tu_mua.' . $key, $item);
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
                    'beanAmount' => $group->sum('bean_amount'),
                ];

                if ($currentField === 'm_parent_name') {
                }
                if ($currentField === 'm_type_name') {
                }

                // Nếu group theo mediStockName 
                if ($currentField === 'medi_stock_name') {
                    $firstItem = $group->first();
                    $result['key'] = $firstItem['m_type_id'].'-'.$firstItem['medi_stock_id'].'-'.$firstItem['service_id'];
                    $result['id'] = $firstItem['id'];
                    $result['serviceId'] = $firstItem['service_id'];
                    $result['mTypeId'] = $firstItem['m_type_id'];
                    $result['mTypeName'] = $firstItem['m_type_name'];
                    $result['serviceUnitCode'] = $firstItem['service_unit_code'];
                    $result['serviceUnitName'] = $firstItem['service_unit_name'];
                    $result['concentra'] = $firstItem['concentra'];
                    $result['activeIngrBhytCode'] = $firstItem['active_ingr_bhyt_code'];
                    $result['activeIngrBhytName'] = $firstItem['active_ingr_bhyt_name'];
                    $result['beanAmount'] = $group->sum('bean_amount');
                    $result['mediStockId'] = $firstItem['medi_stock_id'];
                    $result['mediStockCode'] = $firstItem['medi_stock_code'];
                    $result['mediStockName'] = $firstItem['medi_stock_name'];
                    $result['lastExpPrice'] = $firstItem['last_exp_price'];
                    $result['lastExpVatRatio'] = $firstItem['last_exp_vat_ratio'];
                    $result['lastImpVatRatio'] = $firstItem['last_imp_vat_ratio'];
                    $result['tdlPackageNumber'] = $firstItem['tdl_package_number'];
                    $result['tdlMedicineRegisterNumber'] = $firstItem['tdl_medicine_register_number'];
                    $result['tdlMedicineExpiredDate'] = $firstItem['tdl_medicine_expired_date'];
                    $result['nationalName'] = $firstItem['national_name'];
                    $result['manufacturerCode'] = $firstItem['manufacturer_code'];
                    $result['manufacturerName'] = $firstItem['manufacturer_name'];
                    $result['mTypeCode'] = $firstItem['m_type_code'];
                    $result['mParentCode'] = $firstItem['m_parent_code'];
                    $result['mParentName'] = $firstItem['m_parent_name'];
                    $result['serviceTypeCode'] = $firstItem['service_type_code'];
                    $result['serviceTypeName'] = $firstItem['service_type_name'];
                    $result['description'] = $firstItem['description'];
                    $result['medicineUseFormId'] = $firstItem['medicine_use_form_id'];
                    $result['medicineUseFormName'] = $firstItem['medicine_use_form_name'];
                    $result['medicineUseFormCode'] = $firstItem['medicine_use_form_code'];
                    $result['alertMaxInPrescription'] = $firstItem['alert_max_in_prescription'];
                    $result['alertMaxInDay'] = $firstItem['alert_max_in_day'];
                    $result['alertMaxInTreatment'] = $firstItem['alert_max_in_treatment'];
                    $result['isBlockMaxInPrescription'] = $firstItem['is_block_max_in_prescription'];
                    $result['isBlockMaxInDay'] = $firstItem['is_block_max_in_day'];
                    $result['isBlockMaxInTreatment'] = $firstItem['is_block_max_in_treatment'];

                }

                if ($currentField === 'medi_stock_name') {
                } else {
                    $result['children'] = $groupData($group, $fields);
                }
                return $result;
            })->values();
        };

        return $groupData(collect($data), $snakeFields);
    }
public function flattenGroupLevel($items)
{
    return collect($items)->map(function ($item) {
        $newChildren = [];

        foreach ($item['children'] ?? [] as $childLevel2) {
            $childChildren = $childLevel2['children'] ?? [];
            if ($childChildren instanceof \Illuminate\Support\Collection) {
                $newChildren = array_merge($newChildren, $childChildren->all());
            } elseif (is_array($childChildren)) {
                $newChildren = array_merge($newChildren, $childChildren);
            }
        }

        $item['children'] = $newChildren;
        return $item;
    })->all();
}


    public function getById($id)
    {
        return $this->thuocVatTuTuMuaVView->find($id);
    }

    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('xa_v_his_thuoc_vat_tu_tu_mua.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('xa_v_his_thuoc_vat_tu_tu_mua.id');
            $maxId = $this->applyJoins()->max('xa_v_his_thuoc_vat_tu_tu_mua.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('thuoc_vat_tu_tu_mua_v_view', 'xa_v_his_thuoc_vat_tu_tu_mua', $startId, $endId, $batchSize);
            }
        }
    }
}
