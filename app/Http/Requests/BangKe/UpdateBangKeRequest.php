<?php

namespace App\Http\Requests\BangKe;

use App\Models\HIS\PatientType;
use App\Models\HIS\SereServ;
use App\Models\HIS\Service;
use App\Models\HIS\ServicePaty;
use App\Models\HIS\Treatment;
use App\Models\View\BangKeVView;
use App\Repositories\ServicePatyRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UpdateBangKeRequest extends FormRequest
{
    protected $bangKeVView;
    protected $treatment;
    protected $servicePaty;
    protected $sereServ;
    protected $servicePatyRepository;
    protected $service;
    protected $patientType;
    protected $patientTypeBHYTId;
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
        $this->sereServ = new SereServ();
        $this->servicePatyRepository = new ServicePatyRepository($this->servicePaty);
        $this->service = new Service();
        $this->patientType = new PatientType();

        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $cacheKey = 'patient_type_bhyt_id';
        $this->patientTypeBHYTId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data = $this->patientType->where('patient_type_code', '01')->get();
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
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1)
                            ->where(DB::connection('oracle_his')->raw("is_addition"), 1);
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
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $ids = json_decode($this->bang_ke_v_view, true);
            $this->merge([
                'ids' => $ids,
            ]);

            if(is_array($ids)){
                foreach ($ids as $id) {
                    $dataBangKeVView = $this->bangKeVView->find($id);
                    if (!$dataBangKeVView) {
                        $validator->errors()->add('id', 'ID SereServ không tồn tại!');
                    } else {
                        $dataTreatment = $this->treatment->find($dataBangKeVView->tdl_treatment_id);

                        // Lấy data từ DB 
                        if($this->patient_type_id === null){
                            $this->merge([
                                'patient_type_id' => $dataBangKeVView->patient_type_id,
                            ]);
                        }
                        if($this->primary_patient_type_id === null){
                            $this->merge([
                                'primary_patient_type_id' => $dataBangKeVView->primary_patient_type_id,
                            ]);
                        }
                        if($this->is_out_parent_fee === null){
                            $this->merge([
                                'is_out_parent_fee' => $dataBangKeVView->is_out_parent_fee,
                            ]);
                        }
                        if($this->is_expend === null){
                            $this->merge([
                                'is_expend' => $dataBangKeVView->is_expend,
                            ]);
                        }
                        if($this->expend_type_id === null){
                            $this->merge([
                                'expend_type_id' => $dataBangKeVView->expend_type_id,
                            ]);
                        }
                        if($this->is_no_execute === null){
                            $this->merge([
                                'is_no_execute' => $dataBangKeVView->is_no_execute,
                            ]);
                        }
                        if($this->is_not_use_bhyt === null){
                            $this->merge([
                                'is_not_use_bhyt' => $dataBangKeVView->is_not_use_bhyt,
                            ]);
                        }
                        if($this->other_pay_source_id === null){
                            $this->merge([
                                'other_pay_source_id' => $dataBangKeVView->other_pay_source_id,
                            ]);
                        }



                        if (!$dataTreatment) {
                            $validator->errors()->add('id', 'Hồ sơ không tồn tại!');
                        } else {
                            if ($dataTreatment->is_active == 0) {
                                $validator->errors()->add('id', 'Hồ sơ đã bị khóa viện phí!');
                            }
                            if ($dataTreatment->is_hein_approval) {
                                $validator->errors()->add('id', 'Hồ sơ đã duyệt BHYT!');
                            }
    
                            // DTTT
                            // Lấy ra giá của chính sách 
                            if ($this->patient_type_id) {
                                $activePrice = $this->servicePatyRepository->getActivePriceByServieIdPatientTypeId($dataBangKeVView->service_id, $this->patient_type_id, $dataTreatment->in_time)->price ?? null;
                                if ($activePrice === null) {
                                    $validator->errors()->add('patient_type_id', 'Không tìm thấy chính sách giá cho đối tượng thanh toán!');
                                } else {
                                    $this->merge([
                                        'primary_price' => $activePrice,
                                        'price' => $activePrice,
                                        'original_price' => $activePrice,
                                    ]);
                                }
    
                                // Phụ thu
                                // Lấy ra giá của chính sách 
                                if ($this->primary_patient_type_id) {
                                    if ($this->patient_type_id == $this->primary_patient_type_id) {
                                        $validator->errors()->add('primary_patient_type_id', 'Đối tượng thanh toán và đối tượng phụ thu không được trùng nhau!');
                                    }
                                    $activePrimaryPrice = $this->servicePatyRepository->getActivePriceByServieIdPatientTypeId($dataBangKeVView->service_id, $this->primary_patient_type_id, $dataTreatment->in_time)->price ?? null;
                                    if ($activePrimaryPrice === null) {
                                        $validator->errors()->add('primary_patient_type_id', 'Không tìm thấy chính sách giá cho đối tượng phụ thu!');
                                    } else {
                                        if ($activePrimaryPrice <= $activePrice) {
                                            $validator->errors()->add('primary_patient_type_id', 'Giá của đối tượng phụ thu cần lớn hơn giá đối tượng thanh toán!');
                                        }
                                        $this->merge([
                                            'limit_price' => $activePrice, // Nếu có chọn phụ thu thì mới có
                                            'hein_price' => $activePrice, // Nếu có chọn phụ thu thì mới có
                                            'hein_limit_price' => $activePrice, // Nếu có chọn phụ thu thì mới có
                                            'primary_price' => $activePrimaryPrice,
                                            'price' => $activePrimaryPrice,
                                        ]);
                                    }
                                }
                            }
    
                            // CP ngoài gói
                            if ($this->is_out_parent_fee) {
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
                            if ($this->is_expend) {
                                $dataService = $this->service->find($dataBangKeVView->service_id);
                                if ($dataBangKeVView->da_tam_ung) {
                                    $validator->errors()->add('is_expend', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được tạm ứng!');
                                }
                                if ($dataBangKeVView->da_thanh_toan) {
                                    $validator->errors()->add('is_expend', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được thanh toán!');
                                }
                                if (!$dataService->is_allow_expend) {
                                    $validator->errors()->add('is_expend', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' không có quyền thực hiện chức năng này!');
                                }
                            } else {
                                if ($this->expend_type_id && $dataBangKeVView->expend_type_id) {
                                    $validator->errors()->add('expend_type_id', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' cần thực hiện thao tác bỏ hao phí tiền giường trước!');
                                }
                            }
                            // Hao phí tiền giường
                            if ($this->expend_type_id) {
                                if (!$this->is_expend && !$dataBangKeVView->is_expend) {
                                    $validator->errors()->add('expend_type_id', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' không phải là hao phí!');
                                }
                            }
                            // Không thực hiện
                            if ($this->is_no_execute) {
                                if ($dataBangKeVView->da_tam_ung) {
                                    $validator->errors()->add('is_no_execute', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được tạm ứng!');
                                }
                                if ($dataBangKeVView->da_thanh_toan) {
                                    $validator->errors()->add('is_no_execute', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được thanh toán!');
                                }
                            }
                            // Không hưởng BHYT
                            if ($this->is_not_use_bhyt) {
                                if ($dataBangKeVView->da_thanh_toan) {
                                    $validator->errors()->add('is_not_use_bhyt', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được thanh toán!');
                                }
                                if ($this->patient_type_id == $this->patientTypeBHYTId) {
                                    $validator->errors()->add('is_not_use_bhyt', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' không thể thao tác không hưởng BHYT khi đối tượng thanh toán là BHYT!');
                                }
                            }
                            // Nguồn thanh toán khác
                            if ($this->other_pay_source_id) {
                                if ($dataBangKeVView->da_thanh_toan) {
                                    $validator->errors()->add('other_pay_source_id', '(' . $dataBangKeVView->service_req_code . ')' . ' - ' . $dataBangKeVView->tdl_service_name   . ' dịch vụ đã được thanh toán!');
                                }
                            }
                        }
                    }
                }
            }else{
                $validator->errors()->add('id', 'Danh sách dịch vụ không hợp lệ!');  
            }
        });
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
