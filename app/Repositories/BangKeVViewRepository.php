<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\HeinServiceType;
use App\Models\HIS\SereServ;
use App\Models\View\BangKeVView;
use App\Models\View\TreatmentFeeDetailVView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class BangKeVViewRepository
{
    protected $bangKeVView;
    protected $sereServ;
    protected $heinServiceType;
    protected $heinServiceTypeVatTuTrongDanhMucNumOder;
    protected $heinServiceTypeThuocTrongDanhMucNumOder;
    protected $heinServiceTypeVatTuNgoaiDanhMucId;
    protected $heinServiceTypeVatTuTrongDanhMucId;
    protected $heinServiceTypeThuocTrongDanhMucId;
    protected $heinServiceTypeThuocNgoaiDanhMucId;

    public function __construct(
        BangKeVView $bangKeVView,
        SereServ $sereServ,
        HeinServiceType $heinServiceType,
    ) {
        $this->bangKeVView = $bangKeVView;
        $this->sereServ = $sereServ;
        $this->heinServiceType = $heinServiceType;

        $cacheKey = 'hein_service_type_vat_tu_trong_danh_muc_num_oder';
        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $this->heinServiceTypeVatTuTrongDanhMucNumOder = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->heinServiceType->where('hein_service_type_code', 'VT_TDM')->get();
            return $data->value('vir_parent_num_order');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'hein_service_type_thuoc_trong_danh_muc_num_oder';
        $this->heinServiceTypeThuocTrongDanhMucNumOder = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->heinServiceType->where('hein_service_type_code', 'TH_TDM')->get();
            return $data->value('vir_parent_num_order');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'hein_service_type_vat_tu_ngoai_danh_muc_id';
        $this->heinServiceTypeVatTuNgoaiDanhMucId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->heinServiceType->where('hein_service_type_code', 'VT_NDM')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'hein_service_type_vat_tu_trong_danh_muc_id';
        $this->heinServiceTypeVatTuTrongDanhMucId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->heinServiceType->where('hein_service_type_code', 'VT_TDM')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'hein_service_type_thuoc_ngoai_danh_muc_id';
        $this->heinServiceTypeThuocNgoaiDanhMucId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->heinServiceType->where('hein_service_type_code', 'TH_NDM')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'hein_service_type_thuoc_trong_danh_muc_id';
        $this->heinServiceTypeThuocTrongDanhMucId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->heinServiceType->where('hein_service_type_code', 'TH_TDM')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
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
                "execute_room_is_exam",
                "is_out_parent_fee",
                
                // 'json_patient_type_alter',
                "tdl_treatment_type_id",
                "treatment_type_code",
                "treatment_type_name",
                "hein_service_type_name",
                "hein_service_type_code",
                "hein_service_type_num_order",
                "hein_ratio",
                "hein_price",
                "hein_limit_price",
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
                "vir_total_patient_price_no_dc",
                "vir_patient_price",
                "vir_patient_price_bhyt",
                "vir_total_patient_price_bhyt",
                "discount",
                "original_price",
                "vir_price_no_expend",
                "vir_total_price_no_expend",
                "vat_ratio",
                "service_req_id",
                "service_req_stt_code",
                "service_req_stt_name",
                "service_req_type_code",
                "service_req_type_name",
                "intruction_date",
                "service_req_code",
                "da_tam_ung",
                "da_thanh_toan",
            ])
        ;
    }
    public function addJsonPatientTypeAlter($query)
    {
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
                $result = [
                    'key' => (string)$key,
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'total' => $group->count(),
                    'amount' => round($group->sum(function ($item) {
                        return $item['amount'] ?? 0;
                    }), 2), // làm tròn 2 chữ số thập phân

                    // 'children' => $groupData($group, $fields),
                ];

                // Đem children xuống dưới để nằm dưới các trường được thêm
                $result['children'] = $groupData($group, $fields);
                return $result;
            })->values();
        };

        return $groupData(collect($data), $snakeFields);
    }
    public function applyGroupByFieldBieuMau($data, $groupByFields = [], $tab = '')
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
        $groupData = function ($items, $fields) use (&$groupData, $fieldMappings, $tab) {
            if (empty($fields)) {
                return $items->values(); // Hết field nhóm -> Trả về danh sách gốc
            }
            $laMauHaoPhi = in_array($tab, ['bangKeNgoaiTruHaoPhi', 'bangKeNoiTruHaoPhi']);
            $laMauBHYTHaoPhi = in_array($tab, ['bangKeNgoaiTruBHYTHaoPhi']);
            $laMauTPTB = in_array($tab, ['bangKeNgoaiTruVienPhiTPTB', 'bangKeNoiTruVienPhiTPTB']);
            $laMauTongHop = in_array($tab, ['tongHopNgoaiTruVienPhiHaoPhi']);

            $currentField = array_shift($fields);
            $originalField = $fieldMappings[$currentField];

            return $items->groupBy(function ($item) use ($currentField) {
                return $item[$currentField] ?? null;
            })->map(function ($group, $key) use ($fields, $groupData, $originalField, $currentField, $laMauHaoPhi, $laMauBHYTHaoPhi, $laMauTPTB, $laMauTongHop) {
                // Nếu đang nhóm theo 'hein_service_type_name' thì sắp xếp $group theo 'tdl_service_name'
                if ($currentField === 'hein_service_type_name') {
                    $group = $group->sortBy('tdl_service_name')->values();
                }

                
                $totalThanhTienBV = round($group->sum(function ($item) {
                    return ($item['vir_total_price']) ?? 0;
                }));


                $totalQuyBHYT = round($group->sum(function ($item) {
                    return ($item['vir_total_hein_price']) ?? 0;
                }));
                $totalKhac = round($group->sum(function ($item) {
                    return ($item['other_source_price']) ?? 0;
                }));
                $totalThanhTienBH = round($group->sum(function ($item) {
                    return ($item['vir_hein_price']) ?? 0;
                }));
                $totalGiaNguoiBenhCungChiTra = round($group->sum(function ($item) {
                    return (($item['price']) ?? 0) > (($item['hein_limit_price']) ?? 0)
                        ? ($item['patient_price_bhyt'] ?? 0)
                        : ($item['vir_total_patient_price'] ?? 0); // Nếu là kỹ thuật cao thì dùng $patientPriceBhyt không thì dùng virTotalPatientPrice;
                }));

                if ($laMauHaoPhi) {
                    $totalThanhTienBV = round($group->sum(function ($item) {
                        return ($item['vir_total_price_no_expend']) ?? 0;
                    }));
                }
                if ($laMauBHYTHaoPhi) {
                    $totalThanhTienBV = 0;
                }
                if($laMauTongHop){
                    $totalThanhTienBV = round($group->sum(function ($item) {
                        // Nếu là vật tư hoặc thuốc hao phí
                        if(in_array($item->service_type_code , ['VT','TH']) && $item->is_expend == 1){
                            return ($item['vir_total_price_no_expend']) ?? 0;
                        }
                        return ($item['vir_total_price']) ?? 0;
                    }));

                    $totalKhac = round($group->sum(function ($item) {
                        // Nếu là vật tư hoặc thuốc hao phí
                        if(in_array($item->service_type_code , ['VT','TH']) && $item->is_expend == 1){
                             return ($item['other_source_price']) ?? 0;
                        }
                       return ($item['vir_total_patient_price_no_dc']) ?? 0;
                    }));

                }

                if ($laMauTPTB) {
                    $totalThanhTienBV = round($group->sum(function ($item) {
                        if ($item->patient_type_id && $item->primary_patient_type_id) {
                            if($item->hein_service_type_code != 'TH_TDM'){
                                return ($item['vir_total_patient_price_no_dc'] - $item['vir_total_patient_price_bhyt']) ?? 0;
                            }
                        }
                        return ($item['vir_total_price']) ?? 0;
                    }));
                    $totalQuyBHYT = round($group->sum(function ($item) {
                        if ($item->patient_type_id && $item->primary_patient_type_id) {
                            if($item->hein_service_type_code != 'TH_TDM'){
                                return 0;
                            }
                        }
                        return ($item['vir_total_hein_price']) ?? 0;
                    }));
                    $totalGiaNguoiBenhCungChiTra = round($group->sum(function ($item) {
                        if ($item->patient_type_id && $item->primary_patient_type_id) {
                            return 0;
                        }
                        return (($item['price']) ?? 0) > (($item['hein_limit_price']) ?? 0)
                            ? ($item['patient_price_bhyt'] ?? 0)
                            : ($item['vir_total_patient_price'] ?? 0); // Nếu là kỹ thuật cao thì dùng $patientPriceBhyt không thì dùng virTotalPatientPrice;
                    }));
                    $totalKhac = round($group->sum(function ($item) {
                        if ($item->patient_type_id && $item->primary_patient_type_id) {
                            return 0;
                        }
                        return ($item['other_source_price']) ?? 0;
                    }));
                }
                $totalGiaNguoiBenhTuTra =  $totalThanhTienBV - $totalQuyBHYT - $totalGiaNguoiBenhCungChiTra - $totalKhac; // = Thành tiền BV - Quỹ BHYT - Người bệnh cùng chi trả - Khác


                $result = [
                    'key' => (string)$key,
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'total' => $group->count(),
                    'amount' => round($group->sum(function ($item) {
                        return $item['amount'] ?? 0;
                    }), 2), // làm tròn 2 chữ số thập phân
                ];

                $result['totalThanhTienBV'] = $totalThanhTienBV;
                if ($currentField != 'total') {
                    $result['totalThanhTienBH'] = $totalThanhTienBH;
                }
                $result['totalQuyBHYT'] = $totalQuyBHYT;
                $result['totalGiaNguoiBenhCungChiTra'] = $totalGiaNguoiBenhCungChiTra;
                $result['totalGiaNguoiBenhTuTra'] = $totalGiaNguoiBenhTuTra;
                $result['totalKhac'] = $totalKhac;

                if ($currentField === 'hein_service_type_name') {
                    $firstItem = $group->first();
                    $result['heinServiceTypeNumOrder'] = $firstItem['hein_service_type_num_order'] ? (int) $firstItem['hein_service_type_num_order'] : '';
                }
                if (in_array($currentField, ['total', 'is_expend', 'hein_card_number'])) {
                    if ($laMauHaoPhi) {
                        $totalThanhTienBV = 0;
                        $totalGiaNguoiBenhTuTra = 0;
                        $result['totalThanhTienBV'] = $totalThanhTienBV;
                        $result['totalGiaNguoiBenhTuTra'] = $totalGiaNguoiBenhTuTra;
                    }
                    $result['totalThanhTienBVToWords'] = moneyToWords($totalThanhTienBV);
                    $result['totalQuyBHYTToWords'] = moneyToWords($totalQuyBHYT);
                    $result['totalGiaNguoiBenhCungChiTraToWords'] = moneyToWords($totalGiaNguoiBenhCungChiTra);
                    $result['totalGiaNguoiBenhTuTraToWords'] = moneyToWords($totalGiaNguoiBenhTuTra);
                    $result['totalKhacToWords'] = moneyToWords($totalKhac);
                }
                if ($currentField === 'hein_card_number') {
                    $maThe = $group->first()['hein_card_number'] ?? '';
                    $tongChiPhi = $totalThanhTienBV;
                    $heinCardFromTime = $group->first()['json_patient_type_alter']?->HEIN_CARD_FROM_TIME ?? null;
                    $heinCardToTime = $group->first()['json_patient_type_alter']?->HEIN_CARD_TO_TIME ?? null;
                    $result['maTheBHYT'] = $maThe;
                    $result['mucHuongBHYT'] = getMucHuongBHYT($maThe, $tongChiPhi);
                    $result['heinCardFromTime'] = $heinCardFromTime;
                    $result['heinCardToTime'] = $heinCardToTime;
                }

                if ($currentField === 'patient_type_name') {
                    $serviceName = $group->first()['tdl_service_name'] ?? '';
                    $serviceUnitName = $group->first()['service_unit_name'] ?? '';
                    $executeDepartmentName = $group->first()['execute_department_name'] ?? '';
                    $executeRoomName = $group->first()['execute_room_name'] ?? '';
                    $requestDepartmentName = $group->first()['request_department_name'] ?? '';
                    $requestRoomName = $group->first()['request_room_name'] ?? '';
                    $tiLeThanhToanBHYT = (float) $group->first()['hein_ratio'] ?? 0;
                    $donGiaBH = ((float)($group->first()['price'])) > ((float)($group->first()['hein_limit_price']))
                        ? ((float) ($group->first()['hein_price']))
                        : ((float) ($group->first()['original_price'])); // Nếu giá price > HEIN_LIMIT_PRICE thì dùng hein_price không thì dùng original_price // Đơn giá BH
                    $quyBHYT = (int) round($group->first()['vir_total_hein_price']) ?? 0; // Thành tiền BH
                    $khac = (float) $group->first()['other_source_price'] ?? 0;
                    $thanhTienBVHaoPhi = (float) $group->first()['vir_total_price_no_expend'] ?? 0;

                    $result['tdlServiceName'] = $serviceName;
                    $result['serviceUnitName'] = $serviceUnitName;
                    $result['executeDepartmentName'] = $executeDepartmentName;
                    $result['executeRoomName'] = $executeRoomName;
                    $result['requestDepartmentName'] = $requestDepartmentName;
                    $result['requestRoomName'] = $requestRoomName;

                    $result['donGiaBV'] = (float) round($group->first()['price']) ?? 0;
                    $result['donGiaBH'] = $donGiaBH;
                    $result['tiLeThanhToanTheoDV'] = 1;
                    $result['tiLeThanhToanBHYT'] = $tiLeThanhToanBHYT;
                    $result['quyBHYT'] = $quyBHYT;
                    $result['khac'] = $khac;

                    if ($laMauHaoPhi) {
                        $result['donGiaBV'] = (float) round($group->first()['vir_price_no_expend']) ?? 0;
                        $result['thanhTienBV'] = $thanhTienBVHaoPhi;
                        $result['totalGiaNguoiBenhTuTra'] = 0;
                    }
                    if ($laMauBHYTHaoPhi) {
                        $result['donGiaBV'] = 0;
                    }
                    if ($laMauTPTB) {
                        if ($group->first()['patient_type_id'] && $group->first()['primary_patient_type_id']) {
                            $result['donGiaBV'] = (float) round($group->first()['vir_patient_price'] - $group->first()['vir_patient_price_bhyt']) ?? 0;
                        }
                    }
                }

                // Đem children xuống dưới để nằm dưới các trường được thêm
                if ($currentField === 'patient_type_name') {
                    $result['children'] = [];
                } else {
                    $result['children'] = $groupData($group, $fields);
                }
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
    function customizeBangKeNgoaiTruVienPhiTPTB($data)
    {
        return $this->customizeBangKeVienPhiTPTB($data);
    }
    function customizeBangKeNgoaiTruBHYTTheoKhoa6556QDBYT($data)
    {
        return $data->map(function ($item) {
            // Lặp qua để đổi serviceTypeName từ Thuốc thành Thuốc, dịch truyền
            if (in_array($item->service_type_code, ['TH'])) {
                $item->hein_service_type_name = 'Thuốc, dịch truyền';
                $item->hein_service_type_num_order = $this->heinServiceTypeThuocTrongDanhMucNumOder;
            }
            // Lặp qua để đổi serviceTypeName từ Phẫu thuật || Thủ thuật || Nội soi thành Thủ thuật, phẫu thuật
            if (in_array($item->service_type_code, ['TT', 'PT', 'NS'])) {
                $item->hein_service_type_name = 'Thủ thuật, phẫu thuật';
            }
            return $item;
        });
    }
    function customizeBangKeNgoaiTruVienPhiTheoKhoa6556QDBYT($data)
    {
        return $data->map(function ($item) {
            if ($item->request_department_name == $item->execute_department_name) {
                $item->request_room_name = $item->execute_room_name;
            }
            // Lặp qua để đổi serviceTypeName từ Thuốc thành Thuốc, dịch truyền
            if (in_array($item->service_type_code, ['TH'])) {
                $item->hein_service_type_name = 'Thuốc, dịch truyền';
                $item->hein_service_type_num_order = $this->heinServiceTypeThuocTrongDanhMucNumOder;
            }
            // Lặp qua để đổi serviceTypeName từ Phẫu thuật || Thủ thuật || Nội soi thành Thủ thuật, phẫu thuật
            if (in_array($item->service_type_code, ['TT', 'PT', 'NS'])) {
                $item->hein_service_type_name = 'Thủ thuật, phẫu thuật';
            }
            return $item;
        });
    }
    function customizeTongHopNgoaiTruVienPhiHaoPhi($data)
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
    function customizeBangKeNoiTruVienPhiTPTB($data)
    {
        return $this->customizeBangKeVienPhiTPTB($data);
    }

    function customizeBangKeVienPhiTPTB($data)
    {
        $data->map(function ($item) {
            // Nếu là vật tư ngoài danh mục thì chuyển sang khác
            if (in_array($item->hein_service_type_code, ['VT_NDM'])) {
                $item->hein_service_type_name = '';
            }
            // Nếu là thuốc ngoài danh mục và là đơn trực thì chuyển sang khác
            if (in_array($item->hein_service_type_code, ['TH_NDM','TH_TDM']) && $item->service_req_type_code == 'DT') {
                $item->hein_service_type_name = '';
            }
            return $item;
        });
        $checks = [];
        return $data->flatMap(function ($item) use (&$checks) {
            $results = [$item];
            // Nếu có cả patient_type_id và primary_patient_type_id
            if ($item->patient_type_id && $item->primary_patient_type_id && !in_array($item->hein_service_type_code, ['TH_NDM','TH_TDM'])) {
                // Clone bản ghi và cập nhật patient_type_id
                $cloned = clone $item;
                $cloned->patient_type_name = $item->primary_patient_type_name;
                $results[] = $cloned;
                
                // Nếu loại dịch vụ là XN,PT,TT thì clone thêm 1 cái có (tổng amount clone = tổng amount - 1)
                if(in_array($item->service_type_code, ['XN', 'PT', 'TT', 'GI']) && $item->amount >= 1){
                    if($checks[$item->tdl_service_code]??false){
                        $cloned = clone $item;
                        $cloned->patient_type_name = 'addPatientTypeTPTB';

                        $results[] = $cloned;
                    }else{
                        $checks[$item->tdl_service_code] = true;
                    }
                }
                // Trả về bản gốc + bản đã sửa
                return $results;
            }
            // Trường hợp còn lại: chỉ trả về bản gốc
            return [$item];
        });
    }
    function customizeBangKeNoiTruBHYTTheoKhoa6556QDBYT($data)
    {
        return $data->map(function ($item) {
            if ($item->request_department_name == $item->execute_department_name) {
                $item->request_room_name = $item->execute_room_name;
            }
            // Lặp qua để đổi các requestRoom của thuốc và vật tư thành Buồng điều trị
            if (in_array($item->service_type_code, ['TH', 'VT', 'TT', 'PT']) || !$item->hein_service_type_name) {
                $item->request_room_name = 'Buồng điều trị';
            }
            // Lặp qua để đổi serviceTypeName từ Vật tư thành Vật tư y tế
            if (in_array($item->service_type_code, ['VT'])) {
                $item->hein_service_type_name = 'Vật tư y tế';
                $item->hein_service_type_num_order = $this->heinServiceTypeVatTuTrongDanhMucNumOder;
            }
            // Lặp qua để đổi serviceTypeName từ Thuốc thành Thuốc, dịch truyền
            if (in_array($item->service_type_code, ['TH'])) {
                $item->hein_service_type_name = 'Thuốc, dịch truyền';
                $item->hein_service_type_num_order = $this->heinServiceTypeThuocTrongDanhMucNumOder;
            }
            // Lặp qua để đổi serviceTypeName từ Phẫu thuật || Thủ thuật || Nội soi thành Thủ thuật, phẫu thuật
            if (in_array($item->service_type_code, ['TT', 'PT', 'NS'])) {
                $item->hein_service_type_name = 'Thủ thuật, phẫu thuật';
            }
            return $item;
        });
    }
    function customizeBangKeNoiTruVienPhiTheoKhoa6556QDBYT($data)
    {
        return $data->map(function ($item) {
            if ($item->request_department_name == $item->execute_department_name) {
                $item->request_room_name = $item->execute_room_name;
            }
            // Lặp qua để đổi các requestRoom của thuốc và vật tư thành Buồng điều trị
            if (in_array($item->service_type_code, ['TH', 'VT', 'TT', 'PT']) || !$item->hein_service_type_name) {
                $item->request_room_name = 'Buồng điều trị';
            }
            // Lặp qua để đổi serviceTypeName từ Vật tư thành Vật tư y tế
            if (in_array($item->service_type_code, ['VT'])) {
                $item->hein_service_type_name = 'Vật tư y tế';
                $item->hein_service_type_num_order = $this->heinServiceTypeVatTuTrongDanhMucNumOder;
            }
            // Lặp qua để đổi serviceTypeName từ Thuốc thành Thuốc, dịch truyền
            if (in_array($item->service_type_code, ['TH'])) {
                $item->hein_service_type_name = 'Thuốc, dịch truyền';
                $item->hein_service_type_num_order = $this->heinServiceTypeThuocTrongDanhMucNumOder;
            }
            // Lặp qua để đổi serviceTypeName từ Phẫu thuật || Thủ thuật || Nội soi thành Thủ thuật, phẫu thuật
            if (in_array($item->service_type_code, ['TT', 'PT', 'NS'])) {
                $item->hein_service_type_name = 'Thủ thuật, phẫu thuật';
            }
            return $item;
        });
    }
    function customizeBangKeTongHop6556KhoaPhongThanhToan($data)
    {
        return $data->map(function ($item) {
            if ($item->request_department_name == $item->execute_department_name) {
                $item->request_room_name = $item->execute_room_name;
            }
            // Lặp qua để đổi các requestRoom của thuốc và vật tư thành Buồng điều trị
            if (in_array($item->service_type_code, ['TH', 'VT', 'TT', 'PT']) || !$item->hein_service_type_name) {
                $item->request_room_name = 'Buồng điều trị';
            }
            // Lặp qua để đổi serviceTypeName từ Vật tư thành Vật tư y tế
            if (in_array($item->service_type_code, ['VT'])) {
                $item->hein_service_type_name = 'Vật tư y tế';
                $item->hein_service_type_num_order = $this->heinServiceTypeVatTuTrongDanhMucNumOder;
            }
            // Lặp qua để đổi serviceTypeName từ Thuốc thành Thuốc, dịch truyền
            if (in_array($item->service_type_code, ['TH'])) {
                $item->hein_service_type_name = 'Thuốc, dịch truyền';
                $item->hein_service_type_num_order = $this->heinServiceTypeThuocTrongDanhMucNumOder;
            }
            // Lặp qua để đổi serviceTypeName từ Phẫu thuật || Thủ thuật || Nội soi thành Thủ thuật, phẫu thuật
            if (in_array($item->service_type_code, ['TT', 'PT', 'NS'])) {
                $item->hein_service_type_name = 'Thủ thuật, phẫu thuật';
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
            ->where(function ($query) {
                $query->where('treatment_type_code', '<>', '03');
            })
            ->where(function ($query) {
                $query->where('is_expend', 0)
                    ->orWhereNull('is_expend');
            })
        ;

        return $query;
    }
    public function applyBangKeNgoaiTruBHYTTheoKhoa6556QDBYTFilter($query)
    {
        $query
            ->where(function ($query) {
                $query->where('treatment_type_code', '<>', '03');
            })
            ->whereNotNull('hein_card_number')
            ->where(function ($query) {
                $query->where('is_expend', 0)
                    ->orWhereNull('is_expend');
            })
        ;

        return $query;
    }
    public function applyBangKeNgoaiTruVienPhiTheoKhoaFilter($query)
    {
        $query
            ->where(function ($query) {
                $query->where('treatment_type_code',  '<>', '03');
            })
            ->where('patient_type_code', '02')
            ->where(function ($query) {
                $query->where('is_expend', 0)
                    ->orWhereNull('is_expend');
            })
        ;

        return $query;
    }
    public function applyBangKeNgoaiTruVienPhi100ptChuaThanhToanFilter($query)
    {
        $query
            ->where(function ($query) {
                $query->where('treatment_type_code',  '<>', '03');
            })
            ->where('patient_type_code', '02')
            ->where(function ($query) {
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
    public function applyBangKeNoiTruVienPhiTPTBFilter($query)
    {
        $query
            ->where(function ($query) {
                $query->where('treatment_type_code', '03');
            })
            ->where(function ($query) {
                $query->where('is_expend', 0)
                    ->orWhereNull('is_expend');
            })
        ;

        return $query;
    }
    public function applyBangKeNoiTruBHYTTheoKhoa6556QDBYTFilter($query)
    {
        $query
            ->where(function ($query) {
                $query->where('treatment_type_code', '03');
            })
            ->whereNotNull('hein_card_number')
            ->where(function ($query) {
                $query->where('is_expend', 0)
                    ->orWhereNull('is_expend');
            })
        ;

        return $query;
    }
    public function applyBangKeNoiTruVienPhiTheoKhoa6556QDBYTFilter($query)
    {
        $query
            ->where(function ($query) {
                $query->where('treatment_type_code', '03');
            })
            ->where('patient_type_code', '02')
            ->where(function ($query) {
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
