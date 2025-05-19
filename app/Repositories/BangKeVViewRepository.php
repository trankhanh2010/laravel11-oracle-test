<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\SereServ;
use App\Models\View\BangKeVView;
use App\Models\View\TreatmentFeeDetailVView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BangKeVViewRepository
{
    protected $bangKeVView;
    protected $sereServ;
    public function __construct(
        BangKeVView $bangKeVView,
        SereServ $sereServ,
        )
    {
        $this->bangKeVView = $bangKeVView;
        $this->sereServ = $sereServ;
    }

    public function applyJoins()
    {
        return $this->bangKeVView
            ->select([
                'id as key',
                "id",
                "service_req_code",
                "intruction_time",
                "service_type_name",
                "tdl_service_code",
                "tdl_hein_service_bhyt_code",
                "tdl_service_name",
                "patient_type_name",
                "patient_type_id",
                "primary_patient_type_name",
                "primary_patient_type_id",
                "service_unit_name",
                "amount",
                "price",
                "package_price",
                "vir_total_price",
                "is_user_package_price",
                "is_expend",
                "expend_type_id",
                "is_no_execute",
                "is_not_use_bhyt",
                "parent_service_req_code",
                "parent_code",
                "parent_name",
                "equipment_set_code",
                "equipment_set_name",
                "service_condition_code",
                "service_condition_name",
                "other_pay_source_code",
                "other_pay_source_name",
                "other_source_price",
                "amount_temp",
                "vir_total_patient_price_temp",
                "stent_order",
                "share_count",
                "package_code",
                "package_name",
                "description",
                "request_department_code",
                "request_department_name",
                "request_room_code",
                "request_room_name",

                // 'json_patient_type_alter',
                'service_id',
                "other_pay_source_id",
                "tdl_patient_id",
                "tdl_treatment_id",
                "is_specimen",
                "is_no_pay",
                "tdl_is_main_exam",
                "vir_total_hein_price",
                "vir_total_patient_price",
                "discount",
                "vir_total_price_no_expend",
                "vat_ratio",
                "service_req_id",
                "service_req_stt_code",
                "service_req_stt_name",
                "intruction_date",
                "service_req_code",
                "da_tam_ung",
                "da_thanh_toan",
            ])
            ;
    }
    public function applyKeywordFilter($query, $keyword)
    {
        if ($keyword != null) {
            return $query->where(function ($query) use ($keyword) {
                $query->whereRaw("
                        REGEXP_LIKE(
                            NLSSORT(tdl_service_name, 'NLS_SORT=GENERIC_M_AI'),
                            NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                            'i'
                        )
                    ", [$keyword])
                    ->orWhereRaw("
                        REGEXP_LIKE(
                            NLSSORT(service_type_name, 'NLS_SORT=GENERIC_M_AI'),
                            NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                            'i'
                        )
                    ", [$keyword])
                    ->orWhere(('tdl_service_code'), 'like', '%' . $keyword . '%')
                    ->orWhere(('service_req_code'), 'like', '%' . $keyword . '%')
                    ->orWhere(('tdl_hein_service_bhyt_code'), 'like', '%' . $keyword . '%');
            });
        }
        return $query;
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(('is_active'), $isActive);
        }
        return $query;
    }
    public function applyChuaThanhToanFilter($query)
    {
        $query->where(('da_thanh_toan'), 0);
        return $query;
    }
    public function applyCoPhiFilter($query)
    {
        $query->where(('vir_total_patient_price'), '>', 0);
        return $query;
    }
    public function applyTreatmentIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(('tdl_treatment_id'), $id);
        }
        return $query;
    }
    public function applyIntructionTimeFromFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                $query->where('intruction_time', '>=', $param);
            });
        }
        return $query;
    }
    public function applyIntructionTimeToFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                $query->where('intruction_time', '<=', $param);
            });
        }
        return $query;
    }
    public function applyAmountGreaterThan0Filter($query, $param)
    {
        if ($param !== null) {
            if($param){
                return $query->where(function ($query) use ($param) {
                    $query->where('amount', '>', 0);
                });
            }
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
            })->map(function ($group, $key) use ($fields, $groupData, $originalField, $currentField) {
                $result = [
                    'key' => (string)$key,
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'total' => $group->count(),
                    'amount' => $group->sum(function ($item) {
                        return (int) $item['amount'] ?? 0;
                    }),
                    'virTotalPrice' => $group->sum(function ($item) {
                        return (int) $item['vir_total_price'] ?? 0;
                    }),
                    'virTotalHeinPrice' => $group->sum(function ($item) {
                        return (int) $item['vir_total_hein_price'] ?? 0;
                    }),
                    'virTotalPatientPrice' => $group->sum(function ($item) {
                        return (int) $item['vir_total_patient_price'] ?? 0;
                    }),
                    // 'children' => $groupData($group, $fields),
                ];
                if ($currentField === 'service_type_name') {
                    $firstItem = $group->first();
                    $result['key'] = $firstItem['service_type_name'].' '.$firstItem['patient_type_name'];
                }

                // Đem children xuống dưới để nằm dưới các trường được thêm
                $result['children'] = $groupData($group, $fields);
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
        return $this->bangKeVView->find($id);
    }

    public function updateBangKe($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'patient_type_id' => $request->patient_type_id,
            'primary_patient_type_id' => $request->primary_patient_type_id,
            'is_out_parent_fee' => $request->is_out_parent_fee,
            'is_expend' => $request->is_expend,
            'expend_type_id' => $request->expend_type_id,
            'is_no_execute' => $request->is_no_execute,
            'is_not_use_bhyt' => $request->is_not_use_bhyt,
            'other_pay_source_id' => $request->other_pay_source_id,

            'primary_price' => $request->primary_price,
            'limit_price' => $request->limit_price,
            'price' => $request->price,
            'original_price' => $request->original_price,
            'hein_price' => $request->hein_price,
            'hein_limit_price' => $request->hein_limit_price,

        ]);
        return $data;
    }
    public function updateBangKeIds($request, $ids, $time, $appModifier){
        $this->sereServ->whereIn('id', $ids)->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'patient_type_id' => $request->patient_type_id,
            'primary_patient_type_id' => $request->primary_patient_type_id,
            'is_out_parent_fee' => $request->is_out_parent_fee,
            'is_expend' => $request->is_expend,
            'expend_type_id' => $request->expend_type_id,
            'is_no_execute' => $request->is_no_execute,
            'is_not_use_bhyt' => $request->is_not_use_bhyt,
            'other_pay_source_id' => $request->other_pay_source_id,

            'primary_price' => $request->primary_price,
            'limit_price' => $request->limit_price,
            'price' => $request->price,
            'original_price' => $request->original_price,
            'hein_price' => $request->hein_price,
            'hein_limit_price' => $request->hein_limit_price,

        ]);
    }
}