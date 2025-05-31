<?php

namespace App\Http\Requests\BangKe;

use App\Models\HIS\HeinApproval;
use App\Models\HIS\HeinServiceType;
use App\Models\HIS\MaterialPaty;
use App\Models\HIS\MedicinePaty;
use App\Models\HIS\PatientType;
use App\Models\HIS\PatientTypeAlter;
use App\Models\HIS\SereServ;
use App\Models\HIS\SereServBill;
use App\Models\HIS\Service;
use App\Models\HIS\ServicePaty;
use App\Models\HIS\Treatment;
use App\Models\View\BangKeVView;
use App\Models\View\TreatmentFeeDetailVView;
use App\Repositories\BangKeVViewRepository;
use App\Repositories\MaterialPatyRepository;
use App\Repositories\MedicinePatyRepository;
use App\Repositories\PatientTypeAlterRepository;
use App\Repositories\ServicePatyRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UpdateBangKeRequest extends FormRequest
{
    protected $bangKeVView;
    protected $treatment;
    protected $servicePaty;
    protected $medicinePaty;
    protected $materialPaty;
    protected $patientTypeAlter;
    protected $heinApproval;
    protected $treatmentFeeDetailVView;
    protected $sereServ;
    protected $heinServiceType;
    protected $servicePatyRepository;
    protected $medicinePatyRepository;
    protected $materialPatyRepository;
    protected $patientTypeAlterRepository;
    protected $bangKeVViewRepository;
    protected $service;
    protected $patientType;
    protected $sereServBill;
    protected $patientTypeBHYTId;
    protected $patientTypeKSKId;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        $this->bangKeVView = new BangKeVView();
        $this->treatment = new Treatment();
        $this->servicePaty = new ServicePaty();
        $this->medicinePaty = new MedicinePaty();
        $this->materialPaty = new MaterialPaty();
        $this->patientTypeAlter = new PatientTypeAlter();
        $this->heinApproval = new HeinApproval();
        $this->treatmentFeeDetailVView = new TreatmentFeeDetailVView();
        $this->sereServ = new SereServ();
        $this->heinServiceType = new HeinServiceType();
        $this->servicePatyRepository = new ServicePatyRepository($this->servicePaty);
        $this->medicinePatyRepository = new MedicinePatyRepository($this->medicinePaty);
        $this->materialPatyRepository = new MaterialPatyRepository($this->materialPaty);
        $this->patientTypeAlterRepository = new PatientTypeAlterRepository($this->patientTypeAlter);
        $this->service = new Service();
        $this->patientType = new PatientType();
        $this->sereServBill = new SereServBill();

        $this->bangKeVViewRepository = new BangKeVViewRepository(
            $this->bangKeVView,
            $this->sereServ,
            $this->heinServiceType,
            $this->treatmentFeeDetailVView,
            $this->patientType,
        );

        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $cacheKey = 'patient_type_bhyt_id';
        $this->patientTypeBHYTId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data = $this->patientType->where('patient_type_code', '01')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'patient_type_ksk_id';
        $this->patientTypeKSKId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data = $this->patientType->where('patient_type_code', '04')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
        return [
            'patient_type_id' => [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\PatientType', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1)
                            ->where(function ($query) {
                                $query->where(DB::connection('oracle_his')->raw('IS_NOT_USE_FOR_PAYMENT'), 0)
                                    ->orWhereNull(DB::connection('oracle_his')->raw('IS_NOT_USE_FOR_PAYMENT'));
                            });;
                    }),
            ],
            'primary_patient_type_id' => [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\PatientType', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'is_out_parent_fee' =>              'nullable|integer|in:0,1',
            'is_expend' =>                      'nullable|integer|in:0,1',
            'is_no_execute' =>                  'nullable|integer|in:0,1',
            'expend_type_id' =>                 'nullable|integer|in:1',
            'is_not_use_bhyt' =>                'nullable|integer|in:0,1',

            'other_pay_source_id' => [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\OtherPaySource', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'equipment_set_id' => [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\EquipmentSet', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'equipment_set_order' =>                      'nullable|integer',
            'parent_id' =>                                'nullable|integer',
            'service_condition_id' => [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\ServiceCondition', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],

        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $ids = json_decode($this->bang_ke_v_view, true);
            $this->merge([
                'ids' => $ids,
            ]);

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $dataBangKeVView = $this->bangKeVView->find($id);
                    if (!$dataBangKeVView) {
                        $validator->errors()->add('id', 'ID SereServ không tồn tại!');
                    } else {
                        $dataTreatment = $this->treatment->find($dataBangKeVView->tdl_treatment_id);
                        $tenThuoc = $dataBangKeVView->service_type_code == 'TH' ? ' (HSD: ' . ($dataBangKeVView->medicine_expired_date ? Carbon::createFromFormat('YmdHis', $dataBangKeVView->medicine_expired_date)->format('d/m/Y') : '') . ', số lô: ' . $dataBangKeVView->medicine_package_number . ')'  : '';
                        $tenVatTu = $dataBangKeVView->service_type_code == 'VT' ? ' (HSD: ' . ($dataBangKeVView->material_expired_date ? Carbon::createFromFormat('YmdHis', $dataBangKeVView->material_expired_date)->format('d/m/Y') : '') . ', số lô: ' . $dataBangKeVView->material_package_number . ')'  : '';
                        if (!$dataTreatment) {
                            $validator->errors()->add('id', 'Hồ sơ không tồn tại!');
                        } else {
                            $this->merge([
                                'treatment_id' => $dataTreatment->id,
                            ]);
                            // Lấy data từ DB 
                            $patient_type_id = $this->has('patient_type_id') ? $this->patient_type_id : $dataBangKeVView->patient_type_id;
                            $primary_patient_type_id = $this->has('primary_patient_type_id') ? $this->primary_patient_type_id : $dataBangKeVView->primary_patient_type_id;
                            $is_out_parent_fee = $this->has('is_out_parent_fee') ? $this->is_out_parent_fee : $dataBangKeVView->is_out_parent_fee;
                            $is_expend = $this->has('is_expend') ? $this->is_expend : $dataBangKeVView->is_expend;
                            $expend_type_id = $this->has('expend_type_id') ? $this->expend_type_id : $dataBangKeVView->expend_type_id;
                            $is_no_execute = $this->has('is_no_execute') ? $this->is_no_execute : $dataBangKeVView->is_no_execute;
                            $is_not_use_bhyt = $this->has('is_not_use_bhyt') ? $this->is_not_use_bhyt : $dataBangKeVView->is_not_use_bhyt;
                            $other_pay_source_id = $this->has('other_pay_source_id') ? $this->other_pay_source_id : $dataBangKeVView->other_pay_source_id;
                            $equipment_set_id = $this->has('equipment_set_id') ? $this->equipment_set_id : $dataBangKeVView->equipment_set_id;
                            $equipment_set_order = $this->has('equipment_set_order') ? $this->equipment_set_order : $dataBangKeVView->equipment_set_order;
                            $parent_id = $this->has('parent_id') ? $this->parent_id : $dataBangKeVView->parent_id;
                            $service_condition_id = $this->has('service_condition_id') ? $this->service_condition_id : $dataBangKeVView->service_condition_id;

                            $coHoaDon = $this->sereServBill
                                ->where('sere_serv_id', $id)
                                ->where(function ($q) {
                                    $q->where('is_cancel', 0)->orWhereNull('is_cancel');
                                })
                                ->exists(); // check xem có bill cho dịch vụ này chưa

                            $heinCardNumber = $dataTreatment->tdl_hein_card_number ?? '';
                            $jsonDataPatientTypeAlter = $this->patientTypeAlterRepository->getJsonByHeinCardNumberAndPatientTypeId($heinCardNumber, $patient_type_id, $dataTreatment->id);

                            // Nếu không có bộ vật tư thì order = null
                            if (!$equipment_set_id) {
                                $equipment_set_order = null;
                            }

                            // DV đi kèm chỉ nhận null để xóa
                            if ($this->has('parent_id') && $this->parent_id) {
                                $validator->errors()->add('parent_id', 'Chỉ có thể xóa!');
                            }
                            // Điều kiện chỉ nhận null để xóa
                            if ($this->has('service_condition_id') && $this->service_condition_id) {
                                $validator->errors()->add('service_condition_id', 'Chỉ có thể xóa!');
                            }

                            // Lưu tạm data update vào mảng để update
                            $patientTypeIds[$id] = $patient_type_id;
                            $primaryPatientTypeIds[$id] = $primary_patient_type_id;
                            $isOutParentFees[$id] = $is_out_parent_fee;
                            $isExpends[$id] = $is_expend;
                            $expendTypeIds[$id] = $expend_type_id;
                            $isNoExecutes[$id] = $is_no_execute;
                            $isNotUseBhyts[$id] = $is_not_use_bhyt;
                            $otherPaySourceIds[$id] = $other_pay_source_id;
                            $equipmentSetIds[$id] = $equipment_set_id;
                            $equipmentSetOrders[$id] = $equipment_set_order;
                            $parentIds[$id] = $parent_id;
                            $serviceConditionIds[$id] = $service_condition_id;


                            if ($dataTreatment->is_active == 0) {
                                $validator->errors()->add('id', 'Hồ sơ đã bị khóa viện phí!');
                            }
                            if ($dataTreatment->is_hein_approval) {
                                $validator->errors()->add('id', 'Hồ sơ đã duyệt BHYT!');
                            }

                            if ($this->has('patient_type_id') || $this->has('primary_patient_type_id')) {
                                if ($coHoaDon) {
                                    $validator->errors()->add('id', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã tồn tại hóa đơn! Không cho phép thực hiện cập nhật giá!');
                                }
                            }
                            // DTTT
                            // Lấy ra giá của chính sách 
                            if ($patient_type_id) {
                                // Check khi chọn đối tượng thanh toán là khám sức khỏe
                                if ($patient_type_id == $this->patientTypeKSKId) {
                                    if ($dataTreatment->tdl_ksk_contract_id == null) {
                                        $validator->errors()->add('patient_type_id',  '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' không tìm thấy thông tin hợp đồng khám sức khỏe vì vậy không thể chọn đối tượng thanh toán là Khám sức khỏe!');
                                    }
                                }
                                // Check khi chọn đối tượng thanh toán là BHYT
                                if ($patient_type_id == $this->patientTypeBHYTId) {
                                    if (!$heinCardNumber) {
                                        $validator->errors()->add('patient_type_id',  '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' không tìm thấy thông tin duyệt BHYT!');
                                    }
                                }
                                if ($dataBangKeVView->da_thanh_toan) {
                                    $validator->errors()->add('patient_type_id', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được thanh toán!');
                                }
                                switch ($dataBangKeVView->service_type_code) {
                                    case 'TH':
                                        $activePrice = $this->medicinePatyRepository->getActivePriceByMedicineIdPatientTypeId($dataBangKeVView->medicine_id, $patient_type_id);
                                        break;
                                    case 'VT':
                                        $activePrice = $this->materialPatyRepository->getActivePriceByMaterialIdPatientTypeId($dataBangKeVView->material_id, $patient_type_id);
                                        break;
                                    default:
                                        $activePrice = $this->servicePatyRepository->getActivePriceByServieIdPatientTypeId($dataBangKeVView->service_id, $patient_type_id, $dataTreatment->in_time)->price ?? null;
                                }
                                if ($activePrice === null) {
                                    $validator->errors()->add('patient_type_id',  '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . $tenThuoc . $tenVatTu . ' không tìm thấy chính sách giá cho đối tượng thanh toán!');
                                } else {
                                    $primaryPrices[$id] = $activePrice;
                                    $prices[$id] = $activePrice;
                                    $originalPrices[$id] = $activePrice;

                                    $this->merge([
                                        'primary_price' => $primaryPrices,
                                        'price' => $prices,
                                        'original_price' => $originalPrices,
                                    ]);

                                    // Nếu DTTT là BHYT thì thêm thông tin
                                    if ($patient_type_id == $this->patientTypeBHYTId) {
                                        $heinCardNumbers[$id] = $heinCardNumber;
                                        $dataFee = $this->treatmentFeeDetailVView->find($dataTreatment->id ?? 0);
                                        $heinRatios[$id] = getTyLeThanhToanDichVuBHYT(
                                            $dataFee['tdl_hein_card_number'] ?? '',
                                            $jsonDataPatientTypeAlter ? json_decode($jsonDataPatientTypeAlter)->LEVEL_CODE : '',
                                            $this->bangKeVViewRepository->getTotalPriceSereServBHYT($dataTreatment->id) ?? 0,
                                            $dataBangKeVView->tdl_hein_service_bhyt_code,
                                            $jsonDataPatientTypeAlter? json_decode($jsonDataPatientTypeAlter)->RIGHT_ROUTE_CODE == 'DT' : false,
                                            $jsonDataPatientTypeAlter ? json_decode($jsonDataPatientTypeAlter)->RIGHT_ROUTE_TYPE_CODE == 'CC' : false,
                                            $dataFee['in_time'],
                                            tyLeRiengCuaDV: null
                                        );
                                        $jsonPatientTypeAlters[$id] = $jsonDataPatientTypeAlter;
                                        $this->merge([
                                            'hein_card_number' => $heinCardNumbers,
                                            'hein_ratio' => $heinRatios,
                                            'json_patient_type_alter' => $jsonPatientTypeAlters,
                                        ]);
                                    }
                                    
                                }
                                // Phụ thu
                                // Lấy ra giá của chính sách 
                                if ($primary_patient_type_id) {
                                    if ($patient_type_id == $primary_patient_type_id && !in_array($dataBangKeVView->service_type_code, ['TH', 'VT'])) {
                                        $validator->errors()->add('primary_patient_type_id',  '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' đối tượng thanh toán và đối tượng phụ thu không được trùng nhau!');
                                    }
                                    if ($dataBangKeVView->da_thanh_toan) {
                                        $validator->errors()->add('primary_patient_type_id', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được thanh toán!');
                                    }
                                    switch ($dataBangKeVView->service_type_code) {
                                        case 'TH':
                                            $activePrimaryPrice = $this->medicinePatyRepository->getActivePriceByMedicineIdPatientTypeId($dataBangKeVView->medicine_id, $primary_patient_type_id);
                                            break;
                                        case 'VT':
                                            $activePrimaryPrice = $this->materialPatyRepository->getActivePriceByMaterialIdPatientTypeId($dataBangKeVView->material_id, $primary_patient_type_id);
                                            break;
                                        default:
                                            $activePrimaryPrice = $this->servicePatyRepository->getActivePriceByServieIdPatientTypeId($dataBangKeVView->service_id, $primary_patient_type_id, $dataTreatment->in_time)->price ?? null;
                                    }
                                    if ($activePrimaryPrice === null) {
                                        $validator->errors()->add('primary_patient_type_id',  '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name  . $tenThuoc . $tenVatTu . ' không tìm thấy chính sách giá cho đối tượng phụ thu!');
                                    } else {
                                        if (!in_array($dataBangKeVView->service_type_code, ['TH', 'VT'])) { // nếu loại dịch vụ không là thuốc hoặc vật tư thì cập nhật lại các trường giá
                                            if ($activePrimaryPrice <= $activePrice) {
                                                $validator->errors()->add('primary_patient_type_id',  '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' giá của đối tượng phụ thu cần lớn hơn giá đối tượng thanh toán!');
                                            }
                                            $limitPrices[$id] = $activePrice;
                                            $prices[$id] = $activePrimaryPrice;
                                            $primaryPrices[$id] = $activePrimaryPrice;
                                            $heinLimitPrices[$id] = ($this->patientTypeBHYTId == $patient_type_id) ? $activePrice : null; // Nếu DTTT là BHYT thì thêm không thì là null
                                            $heinPrices[$id] = ($this->patientTypeBHYTId == $patient_type_id)
                                                ? ($originalPrices[$id] * $heinRatios[$id])
                                                : null; // Nếu DTTT là BHYT thì thêm không thì là null

                                            $this->merge([
                                                'price' => $prices,
                                                'primary_price' => $primaryPrices,
                                                'limit_price' => $limitPrices,
                                                'hein_price' => $heinPrices,
                                                'hein_limit_price' => $heinLimitPrices,
                                            ]);
                                        }
                                    }
                                }
                            }

                            // CP ngoài gói
                            if ($is_out_parent_fee) {
                                $existsDVDinhKem = $this->sereServ->where('parent_id', $id)->exists();
                                if (!$existsDVDinhKem) {
                                    $validator->errors()->add('is_out_parent_fee', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ không phải là dịch vụ đính kèm!');
                                }
                                if ($dataBangKeVView->da_tam_ung) {
                                    $validator->errors()->add('is_out_parent_fee', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được tạm ứng!');
                                }
                                if ($dataBangKeVView->da_thanh_toan) {
                                    $validator->errors()->add('is_out_parent_fee', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được thanh toán!');
                                }
                            }
                            // Hao phí
                            if ($is_expend) {
                                $dataService = $this->service->find($dataBangKeVView->service_id);
                                if ($dataBangKeVView->da_tam_ung) {
                                    $validator->errors()->add('is_expend', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được tạm ứng!');
                                }
                                if ($dataBangKeVView->da_thanh_toan) {
                                    $validator->errors()->add('is_expend', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được thanh toán!');
                                }
                                if ($dataService->is_allow_expend && $this->has('is_expend')) {
                                    $validator->errors()->add('is_expend', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' không có quyền thực hiện chức năng này!');
                                }
                            } else {
                                if ($expend_type_id && $dataBangKeVView->expend_type_id) {
                                    $validator->errors()->add('expend_type_id', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' cần thực hiện thao tác bỏ hao phí tiền giường trước!');
                                }
                            }
                            // Hao phí tiền giường
                            if ($expend_type_id) {
                                if (!$this->is_expend && !$dataBangKeVView->is_expend) {
                                    $validator->errors()->add('expend_type_id', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' không phải là hao phí!');
                                }
                            }
                            // Không thực hiện
                            if ($is_no_execute) {
                                if ($dataBangKeVView->da_tam_ung) {
                                    $validator->errors()->add('is_no_execute', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được tạm ứng!');
                                }
                                if ($dataBangKeVView->da_thanh_toan) {
                                    $validator->errors()->add('is_no_execute', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được thanh toán!');
                                }
                                if ($dataBangKeVView->service_req_stt_code != '01') {
                                    $validator->errors()->add('is_no_execute', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ có trạng thái khác trạng thái chưa xử lý!');
                                }
                            }
                            // Không hưởng BHYT
                            if ($is_not_use_bhyt) {
                                if ($dataBangKeVView->da_thanh_toan) {
                                    $validator->errors()->add('is_not_use_bhyt', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được thanh toán!');
                                }
                                if ($this->patient_type_id == $this->patientTypeBHYTId) {
                                    $validator->errors()->add('is_not_use_bhyt', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' không thể thao tác không hưởng BHYT khi đối tượng thanh toán là BHYT!');
                                }
                            }
                            // Nguồn thanh toán khác
                            if ($other_pay_source_id) {
                                if ($dataBangKeVView->da_thanh_toan) {
                                    $validator->errors()->add('other_pay_source_id', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được thanh toán!');
                                }
                            }
                            // Bộ vật tư
                            if ($equipment_set_id || $equipment_set_order) {
                                if ($dataBangKeVView->service_type_code != 'VT') {
                                    $validator->errors()->add('equipment_set_id', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' có loại dịch vụ không phải là Vật tư!');
                                }
                            }
                        }
                    }
                }

                // Cập nhật lại request sau khi đã lặp qua các id
                $this->merge([
                    'patient_type_id' => $patientTypeIds,
                    'primary_patient_type_id' => $primaryPatientTypeIds,
                    'is_out_parent_fee' => $isOutParentFees,
                    'is_expend' => $isExpends,
                    'expend_type_id' => $expendTypeIds,
                    'is_no_execute' => $isNoExecutes,
                    'is_not_use_bhyt' => $isNotUseBhyts,
                    'other_pay_source_id' => $otherPaySourceIds,
                    'equipment_set_id' => $equipmentSetIds,
                    'equipment_set_order' => $equipmentSetOrders,
                    'parent_id' => $parentIds,
                    'service_condition_id' => $serviceConditionIds,

                ]);
            } else {
                $validator->errors()->add('id', 'Danh sách dịch vụ không hợp lệ!');
            }
        });
    }
    public function messages()
    {
        return [
            'patient_type_id.integer'       => config('keywords')['bang_ke']['patient_type_id'] . config('keywords')['error']['integer'],
            'patient_type_id.exists'        => config('keywords')['bang_ke']['patient_type_id'] . ' không tồn tại hoặc đang bị khóa!',

            'primary_patient_type_id.integer'       => config('keywords')['bang_ke']['primary_patient_type_id'] . config('keywords')['error']['integer'],
            'primary_patient_type_id.exists'        => config('keywords')['bang_ke']['primary_patient_type_id'] . ' không tồn tại hoặc đang bị khóa!',

            'is_out_parent_fee.integer'     => config('keywords')['bang_ke']['is_out_parent_fee'] . config('keywords')['error']['integer'],
            'is_out_parent_fee.in'          => config('keywords')['bang_ke']['is_out_parent_fee'] . config('keywords')['error']['in'],

            'is_expend.integer'     => config('keywords')['bang_ke']['is_expend'] . config('keywords')['error']['integer'],
            'is_expend.in'          => config('keywords')['bang_ke']['is_expend'] . config('keywords')['error']['in'],

            'is_no_execute.integer'     => config('keywords')['bang_ke']['is_no_execute'] . config('keywords')['error']['integer'],
            'is_no_execute.in'          => config('keywords')['bang_ke']['is_no_execute'] . config('keywords')['error']['in'],

            'expend_type_id.integer'     => config('keywords')['bang_ke']['expend_type_id'] . config('keywords')['error']['integer'],
            'expend_type_id.in'          => config('keywords')['bang_ke']['expend_type_id'] . config('keywords')['error']['in'],

            'is_not_use_bhyt.integer'     => config('keywords')['bang_ke']['is_not_use_bhyt'] . config('keywords')['error']['integer'],
            'is_not_use_bhyt.in'          => config('keywords')['bang_ke']['is_not_use_bhyt'] . config('keywords')['error']['in'],

            'equipment_set_id.integer'       => config('keywords')['bang_ke']['equipment_set_id'] . config('keywords')['error']['integer'],
            'equipment_set_id.exists'        => config('keywords')['bang_ke']['equipment_set_id'] . ' không tồn tại hoặc đang bị khóa!',

            'other_pay_source_id.integer'       => config('keywords')['bang_ke']['other_pay_source_id'] . config('keywords')['error']['integer'],
            'other_pay_source_id.exists'        => config('keywords')['bang_ke']['other_pay_source_id'] . ' không tồn tại hoặc đang bị khóa!',

            'parent_id.integer'       => config('keywords')['bang_ke']['parent_id'] . config('keywords')['error']['integer'],
            'parent_id.exists'        => config('keywords')['bang_ke']['parent_id'] . ' không tồn tại hoặc đang bị khóa!',

            'service_condition_id.integer'       => config('keywords')['bang_ke']['service_condition_id'] . config('keywords')['error']['integer'],
            'service_condition_id.exists'        => config('keywords')['bang_ke']['service_condition_id'] . ' không tồn tại hoặc đang bị khóa!',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Dữ liệu không hợp lệ!',
            'data'      => $validator->errors()
        ], 422));
    }
}
