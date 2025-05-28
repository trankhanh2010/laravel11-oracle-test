<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\TestServiceTypeListVView;
use App\Models\View\TreatmentFeeDetailVView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestServiceTypeListVViewRepository
{
    protected $testServiceTypeListVView;
    public function __construct(TestServiceTypeListVView $testServiceTypeListVView)
    {
        $this->testServiceTypeListVView = $testServiceTypeListVView;
    }

    public function applyJoins()
    {
        return $this->testServiceTypeListVView
            ->select([
                'id as key',
                "id",
                "tdl_patient_id",
                "tdl_treatment_id",
                "is_specimen",
                "is_no_execute",
                "is_no_pay",
                "tdl_is_main_exam",
                "service_type_name",
                "amount",
                "price",
                "vir_total_price",
                "vir_total_hein_price",
                "vir_total_patient_price",
                "discount",
                "other_source_price",
                "vir_total_price_no_expend",
                "patient_type_name",
                "vat_ratio",
                "is_expend",
                "tdl_service_req_code",
                "service_req_id",
                "tdl_service_code",
                "tdl_service_name",
                "service_req_stt_code",
                "service_req_stt_name",
                "request_department_name",
                "request_department_code",
                "intruction_time",
                "service_req_code",
                "request_room_code",
                "request_room_name",
                "da_tam_ung",
                "tam_ung",
                "da_thanh_toan",
            ])
            ->addSelect(DB::connection('oracle_his')->raw('GREATEST(vir_total_patient_price - NVL(discount, 0), 0) as thuc_thu')) // max (tiền bệnh nhân phải trả - chiết khấu, 0)
            ;
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(('test_service_type_list_v_view_code'), 'like', $keyword . '%')
                ->orWhere(('test_service_type_list_v_view_name'), 'like', $keyword . '%');
        });
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
    public function themTienKhiTamUngDV($data, $treatmentId)
    {
        $treatmentFeeDetailVView = new TreatmentFeeDetailVView();
        $dataFee = $treatmentFeeDetailVView->find($treatmentId ?? 0);
        $mucHuongBhyt = getMucHuongBHYT($dataFee['tdl_hein_card_number']??'', $dataFee['total_price']??0, $dataFee['in_time']??0);
        foreach ($data as &$item) {
            $virTotalPatientPrice = $item['vir_total_patient_price'] ?? 0;
            $virTotalHeinPrice = $item['vir_total_hein_price'] ?? 0; 
            if(!$dataFee['tdl_hein_card_number']){
                $virTotalHeinPrice = 0; // Tiền mà khi không có mã BHYT thì là tiền công ty trả, không tính vào đây
            }
            $item['tien_khi_tam_ung_dv'] = (string) round($virTotalPatientPrice + (1 - $mucHuongBhyt) * $virTotalHeinPrice);  // Làm tròn tiền 
        }
    
        return $data;
    }

    public function getById($id)
    {
        return $this->testServiceTypeListVView->find($id);
    }
}