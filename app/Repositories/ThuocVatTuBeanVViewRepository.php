<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\ThuocVatTuBeanVView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ThuocVatTuBeanVViewRepository
{
    protected $thuocVatTuBeanVView;
    public function __construct(ThuocVatTuBeanVView $thuocVatTuBeanVView)
    {
        $this->thuocVatTuBeanVView = $thuocVatTuBeanVView;
    }

    public function applyJoins()
    {
        return $this->thuocVatTuBeanVView
            ->select(
                'xa_v_his_thuoc_vat_tu_bean.*'
            );
    }
    public function applyJoinsKeDonThuocPhongKham()
    {
        return $this->thuocVatTuBeanVView->select([
            'xa_v_his_thuoc_vat_tu_bean.id as key',
            'xa_v_his_thuoc_vat_tu_bean.id',
            'xa_v_his_thuoc_vat_tu_bean.m_type_code',
            'xa_v_his_thuoc_vat_tu_bean.m_type_name',
            'xa_v_his_thuoc_vat_tu_bean.service_id',
            'xa_v_his_thuoc_vat_tu_bean.m_parent_code',
            'xa_v_his_thuoc_vat_tu_bean.m_parent_name',
            'xa_v_his_thuoc_vat_tu_bean.CONCENTRA',
            'xa_v_his_thuoc_vat_tu_bean.ACTIVE_INGR_BHYT_CODE',
            'xa_v_his_thuoc_vat_tu_bean.ACTIVE_INGR_BHYT_NAME',
            'xa_v_his_thuoc_vat_tu_bean.service_unit_code',
            'xa_v_his_thuoc_vat_tu_bean.service_unit_name',
            'xa_v_his_thuoc_vat_tu_bean.bean_amount',
            'xa_v_his_thuoc_vat_tu_bean.tdl_package_number',
            'xa_v_his_thuoc_vat_tu_bean.tdl_medicine_register_number',
            'xa_v_his_thuoc_vat_tu_bean.tdl_medicine_expired_date',
            'xa_v_his_thuoc_vat_tu_bean.national_name',
            'xa_v_his_thuoc_vat_tu_bean.last_exp_price',
            'xa_v_his_thuoc_vat_tu_bean.last_exp_vat_ratio',
            'xa_v_his_thuoc_vat_tu_bean.medi_stock_code',
            'xa_v_his_thuoc_vat_tu_bean.medi_stock_name',
            'xa_v_his_thuoc_vat_tu_bean.is_drug_store',
            'xa_v_his_thuoc_vat_tu_bean.manufacturer_code',
            'xa_v_his_thuoc_vat_tu_bean.manufacturer_name',
            'xa_v_his_thuoc_vat_tu_bean.service_type_code',
            'xa_v_his_thuoc_vat_tu_bean.service_type_name',
        ])
        ->where('xa_v_his_thuoc_vat_tu_bean.is_leaf', 1);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_thuoc_vat_tu_bean.thuoc_vat_tu_bean_code'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(xa_v_his_thuoc_vat_tu_bean.thuoc_vat_tu_bean_name)'), 'like', '%' . strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_thuoc_vat_tu_bean.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('xa_v_his_thuoc_vat_tu_bean.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyMediStockIdsFilter($query, $param)
    {
        if ($param != null) {
            $query->whereIn('xa_v_his_thuoc_vat_tu_bean.medi_stock_id', $param);
        }
        return $query;
    }
    public function applyTypeKeDonThuocPhongKhamFilter($query, $param)
    {
        switch ($param) {
            case 'thuocVatTuTrongKho':
                return $query;
            case 'thuocVatTuMuaNgoai':
                return $query->where('xa_v_his_thuoc_vat_tu_bean.IS_OUT_HOSPITAL', 1);
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
                    $query->orderBy('xa_v_his_thuoc_vat_tu_bean.' . $key, $item);
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

                // Nếu group theo mediStockName 
                if ($currentField === 'medi_stock_name') {
                    $firstItem = $group->first();
                    $result['key'] = $firstItem['key'];
                    $result['id'] = $firstItem['id'];
                    $result['serviceId'] = $firstItem['serviceId'];
                    $result['mTypeName'] = $firstItem['m_type_name'];
                    $result['serviceUnitCode'] = $firstItem['service_unit_code'];
                    $result['serviceUnitName'] = $firstItem['service_unit_code'];
                    $result['concentra'] = $firstItem['concentra'];
                    $result['activeIngrBhytCode'] = $firstItem['active_ingr_bhyt_code'];
                    $result['activeIngrBhytName'] = $firstItem['active_ingr_bhyt_name'];
                    $result['beanAmount'] = $group->sum('bean_amount');
                    $result['mediStockCode'] = $firstItem['medi_stock_code'];
                    $result['mediStockName'] = $firstItem['medi_stock_name'];
                    $result['lastExpPrice'] = $firstItem['last_exp_price'];
                    $result['lastExpVatRatio'] = $firstItem['last_exp_vat_ratio'];
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
    public function getById($id)
    {
        return $this->thuocVatTuBeanVView->find($id);
    }

    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('xa_v_his_thuoc_vat_tu_bean.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('xa_v_his_thuoc_vat_tu_bean.id');
            $maxId = $this->applyJoins()->max('xa_v_his_thuoc_vat_tu_bean.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('thuoc_vat_tu_bean_v_view', 'xa_v_his_thuoc_vat_tu_bean', $startId, $endId, $batchSize);
            }
        }
    }
}
