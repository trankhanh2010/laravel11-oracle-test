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
    ) {
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
                "service_type_code",
                "tdl_service_code",
                "tdl_hein_service_bhyt_code",
                "tdl_service_name",
                "patient_type_name",
                "patient_type_code",
                "patient_type_id",
                "primary_patient_type_name",
                "primary_patient_type_code",
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
                "request_department_num_order",
                "execute_department_code",
                "execute_department_name",
                "execute_department_num_order",
                "request_room_code",
                "request_room_name",
                "execute_room_code",
                "execute_room_name",

                // 'json_patient_type_alter',
                "tdl_treatment_type_id",
                "treatment_type_code",
                "treatment_type_name",
                "hein_service_type_name",
                "hein_service_type_num_order",
                "hein_ratio",
                "hein_price",
                "patient_price_bhyt",
                "vir_hein_price",
                'hein_card_number',
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
                "vir_price_no_expend",
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
    public function addJsonPatientTypeAlter($query){
        $query->addSelect('json_patient_type_alter');
        return $query;
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
            if ($param) {
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
                $totalVirTotalPriceNoExpend = round($group->sum(function ($item) {
                    return ($item['vir_total_price_no_expend']) ?? 0;
                }));
                $totalVirTotalPrice = round($group->sum(function ($item) {
                    return ($item['vir_total_price']) ?? 0;
                }));
                $totalVirTotalHeinPrice =round($group->sum(function ($item) {
                    return ($item['vir_total_hein_price']) ?? 0;
                }));
                $totalVirTotalPatientPrice = round($group->sum(function ($item) {
                    return ($item['vir_total_patient_price']) ?? 0;
                }));
                $totalPriceExpend = round($group->sum(function ($item) {
                    return $item['is_expend'] ? ($item['vir_total_price_no_expend']) : 0;
                }));
                $totalOtherSourcePrice = round($group->sum(function ($item) {
                    return ($item['other_source_price']) ?? 0;
                }));
                $totalPatientPriceBhyt = round($group->sum(function ($item) {
                    return ($item['patient_price_bhyt']) ?? 0;
                }));
                $totalVirHeinPrice = round($group->sum(function ($item) {
                    return ($item['vir_hein_price']) ?? 0;
                }));

                $result = [
                    'key' => (string)$key,
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'total' => $group->count(),
                    'amount' => round($group->sum(function ($item) {
                        return $item['amount'] ?? 0;
                    }),2), // làm tròn 2 chữ số thập phân
                    'virPriceNoExpend' => (int) round($group->first()['vir_price_no_expend']) ?? 0,
                    'price' => (int) round($group->first()['price']) ?? 0,
                    'heinPrice' => (int) round($group->first()['hein_price']) ?? 0,
                    'totalVirTotalPriceNoExpend' => $totalVirTotalPriceNoExpend,
                    'totalVirTotalPrice' => $totalVirTotalPrice,
                    'totalVirTotalHeinPrice' => $totalVirTotalHeinPrice,
                    'totalVirTotalPatientPrice' => $totalVirTotalPatientPrice,
                    'totalPriceExpend' => $totalPriceExpend,
                    'totalOtherSourcePrice' => $totalOtherSourcePrice,
                    'totalPatientPriceBhyt' => $totalPatientPriceBhyt,
                    'totalVirHeinPrice' => $totalVirHeinPrice,

                    // 'children' => $groupData($group, $fields),
                ];
                if ($currentField === 'service_type_name') {
                    $firstItem = $group->first();
                    $result['key'] = $firstItem['service_type_name'] . ' ' . $firstItem['patient_type_name'];
                }
                if($currentField === 'total'){
                    $result['totalVirTotalPriceNoExpendToWords'] = moneyToWords($totalVirTotalPriceNoExpend);
                    $result['totalVirTotalPriceToWords'] = moneyToWords($totalVirTotalPrice);
                    $result['totalVirTotalHeinPriceToWords'] = moneyToWords($totalVirTotalHeinPrice);
                    $result['totalVirTotalPatientPriceToWords'] = moneyToWords($totalVirTotalPatientPrice);
                    $result['totalPriceExpendToWords'] = moneyToWords($totalPriceExpend);
                    $result['totalOtherSourcePriceToWords'] = moneyToWords($totalOtherSourcePrice);
                }
                if($currentField === 'hein_card_number'){
                    $maThe = $group->first()['hein_card_number'] ?? '';
                    $tongChiPhi = $totalVirTotalPrice;
                    $heinCardFromTime = $group->first()['json_patient_type_alter']?->HEIN_CARD_FROM_TIME ?? null;
                    $heinCardToTime = $group->first()['json_patient_type_alter']?->HEIN_CARD_TO_TIME ?? null;
                    $result['maTheBHYT'] = $maThe;
                    $result['mucHuongBHYT'] = getMucHuongBHYT($maThe, $tongChiPhi);
                    $result['heinCardFromTime'] = $heinCardFromTime;
                    $result['heinCardToTime'] = $heinCardToTime;
                }

                if($currentField === 'patient_type_name'){
                    $serviceName = $group->first()['tdl_service_name'] ?? '';
                    $serviceUnitName = $group->first()['service_unit_name'] ?? '';
                    $executeDepartmentName = $group->first()['execute_department_name'] ?? '';
                    $executeRoomName = $group->first()['execute_room_name'] ?? '';
                    $requestDepartmentName = $group->first()['request_department_name'] ?? '';
                    $requestRoomName = $group->first()['request_room_name'] ?? '';
                    $heinRatio = (float) $group->first()['hein_ratio'] ?? 0;

                    $result['tdlServiceName'] = $serviceName;
                    $result['serviceUnitName'] = $serviceUnitName;
                    $result['executeDepartmentName'] = $executeDepartmentName;
                    $result['executeRoomName'] = $executeRoomName;
                    $result['requestDepartmentName'] = $requestDepartmentName;
                    $result['requestRoomName'] = $requestRoomName;
                    $result['heinRatio'] = $heinRatio;

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
    function customizeHeinServiceTypeNameTongHop($data)
    {
        return $data->map(function ($item) {
            if ($item->is_expend == 1 && $item->service_type_code === 'TH') {
                $item->hein_service_type_name = 'Thuốc hao phí trong phẫu thuật';
            }

            if ($item->is_expend == 1 && $item->service_type_code === 'VT') {
                $item->hein_service_type_name = 'Vật tư hao phí trong phẫu thuật';
            }
    
            return $item;
        });
    }
    function customizeBangKeNoiTruVienPhiTheoKhoa6556QDBYT($data)
    {
        return $data->map(function ($item) {
            // Lặp qua để đổi các requestRoom của thuốc và vật tư thành Buồng điều trị
            if (in_array($item->service_type_code, ['TH', 'VT', 'TT', 'PT'])) {
                $item->request_room_name = 'Buồng điều trị';
            }
            // Lặp qua để đổi serviceTypeName từ Vật tư thành Vật tư y tế
            if (in_array($item->service_type_code, ['VT'])) {
                $item->service_type_name = 'Vật tư y tế';
            }
            // Lặp qua để đổi serviceTypeName từ Thuốc thành Thuốc, dịch truyền
            if (in_array($item->service_type_code, ['TH'])) {
                $item->service_type_name = 'Thuốc, dịch truyền';
            }
            // Lặp qua để đổi serviceTypeName từ Phẫu thuật || Thủ thuật thành Thủ thuật, phẫu thuật
            if (in_array($item->service_type_code, ['TT', 'PT'])) {
                $item->service_type_name = 'Thủ thuật, phẫu thuật';
            }
            return $item;
        });
    }

    public function applyStatusFilter($query, $param)
    {
        switch ($param) {
            case 'tatCa':
                break;
            case 'daThanhToanDichVu':
                $query->where('da_thanh_toan', 1);
                break;
            case 'daTamUngDichVu':
                $query->where('da_tam_ung', 1);
                break;
            case 'chuaThanhToan':
                $query->where('da_thanh_toan', 0);
                break;
            case 'chuaTamUng':
                $query->where('da_tam_ung', 0);
                break;
            default:
                return $query;
        }
        return $query;
    }
    public function applyBangKeNgoaiTruHaoPhiFilter($query)
    {
        $query
        ->where('treatment_type_code', '<>', '03')
        ->where('is_expend', 1);
        return $query;
    }
    public function applyBangKeNgoaiTruBHYTHaoPhiFilter($query)
    {
        $query
        ->where('treatment_type_code', '<>', '03')
        ->where('patient_type_code', '01')
        ->where('is_expend', 1);
        return $query;
    }
    public function applyBangKeNgoaiTruVienPhiTPTBFilter($query)
    {
        $query
        ->where(function ($query)  {
            $query->where('treatment_type_code', '<>', '03');
        })
        ->where(function ($query)  {
            $query->where('is_expend', 0)
            ->orWhereNull('is_expend');
        })
        ;
        
        return $query;
    }
    public function applyBangKeNgoaiTruBHYTTheoKhoa6556QDBYTFilter($query)
    {
        $query
        ->where(function ($query)  {
            $query->where('treatment_type_code', '<>', '03');
        })
        ->whereNotNull('hein_card_number')
        ->where(function ($query)  {
            $query->where('is_expend', 0)
            ->orWhereNull('is_expend');
        })
        ;
        
        return $query;
    }
    public function applyBangKeNgoaiTruVienPhiTheoKhoaFilter($query)
    {
        $query
        ->where(function ($query)  {
            $query->where('treatment_type_code',  '<>', '03');
        })
        ->where('patient_type_code', '02')
        ->where(function ($query)  {
            $query->where('is_expend', 0)
            ->orWhereNull('is_expend');
        })
        ;
        
        return $query;
    }
    public function applyBangKeNoiTruHaoPhiFilter($query)
    {
        $query
        ->where('is_expend', 1)
        ->where('treatment_type_code', '03');
        return $query;
    }
    public function applyBangKeNoiTruBHYTTheoKhoa6556QDBYTFilter($query)
    {
        $query
        ->where(function ($query)  {
            $query->where('treatment_type_code', '03');
        })
        ->whereNotNull('hein_card_number')
        ->where(function ($query)  {
            $query->where('is_expend', 0)
            ->orWhereNull('is_expend');
        })
        ;
        
        return $query;
    }
    public function applyBangKeNoiTruVienPhiTheoKhoa6556QDBYTFilter($query)
    {
        $query
        ->where(function ($query)  {
            $query->where('treatment_type_code', '03');
        })
        ->where('patient_type_code', '02')
        ->where(function ($query)  {
            $query->where('is_expend', 0)
            ->orWhereNull('is_expend');
        })
        ;
        
        return $query;
    }
    public function applyBangKeTongHop6556KhoaPhongThanhToanFilter($query)
    {
        return $query;
    }
    public function applyTongHopNgoaiTruVienPhiHaoPhiFilter($query)
    {   
        return $query;
    }
    public function getById($id)
    {
        return $this->bangKeVView->find($id);
    }

    public function updateBangKe($request, $data, $time, $appModifier)
    {
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
    public function updateBangKeIds($request, $ids, $time, $appModifier)
    {
        foreach ($ids as $id) {
            $dataUpdate = [
                'modify_time' => now()->format('YmdHis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $time),
                'app_modifier' => $appModifier,
                'patient_type_id' => $request->patient_type_id[$id],
                'primary_patient_type_id' => $request->primary_patient_type_id[$id],
                'is_out_parent_fee' => $request->is_out_parent_fee[$id] == 0 ? null : $request->is_out_parent_fee[$id], // buộc để null để cột vir không tính sai giá
                'is_expend' => $request->is_expend[$id] == 0 ? null : $request->is_expend[$id], // buộc để null để cột vir không tính sai giá
                'expend_type_id' => $request->expend_type_id[$id],
                'is_no_execute' => $request->is_no_execute[$id] == 0 ? null : $request->is_no_execute[$id], // buộc để null để cột vir không tính sai giá
                'is_not_use_bhyt' => $request->is_not_use_bhyt[$id] == 0 ? null : $request->is_not_use_bhyt[$id], // buộc để null để cột vir không tính sai giá
                'other_pay_source_id' => $request->other_pay_source_id[$id],

                'primary_price' => $request->primary_price[$id],
                'limit_price' => $request->limit_price[$id] ?? null, // phụ thu mới có
                'price' => $request->price[$id],
                'original_price' => $request->original_price[$id],
                'hein_price' => $request->hein_price[$id] ?? null, // phụ thu mới có
                'hein_limit_price' => $request->hein_limit_price[$id] ?? null, // phụ thu mới có
            ];
            if (!$request->other_pay_source_id[$id]) {
                $dataUpdate['other_source_price'] =  0; // khi bỏ chọn Nguồn khác thì set lại = 0
            }
            $this->sereServ->where('id', $id)->update($dataUpdate);
        }
    }
}
