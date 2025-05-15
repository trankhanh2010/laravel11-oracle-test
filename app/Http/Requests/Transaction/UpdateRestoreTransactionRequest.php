<?php

namespace App\Http\Requests\Transaction;

use App\Models\HIS\PayForm;
use App\Models\HIS\SereServDeposit;
use App\Models\HIS\ServicePaty;
use App\Models\HIS\Transaction;
use App\Models\HIS\TransactionType;
use App\Models\HIS\Treatment;
use App\Repositories\SereServDepositRepository;
use App\Repositories\ServicePatyRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UpdateRestoreTransactionRequest extends FormRequest
{
    protected $transaction;
    protected $treatment;
    protected $payFormQrId;
    protected $payForm;
    protected $sereServDeposit;
    protected $servicePaty;
    protected $servicePatyRepository;
    protected $transactionType;
    protected $transactionTypeHUId;
    protected $transactionTypeTUId;
    protected $transactionTypeTTId;
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
        $this->transaction = new Transaction();
        $this->treatment = new Treatment();
        $this->payForm = new PayForm();
        $this->transactionType = new TransactionType();
        $this->sereServDeposit = new SereServDeposit();
        $this->servicePaty = new ServicePaty();
        $this->servicePatyRepository = new ServicePatyRepository($this->servicePaty);

        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $cacheKey = 'pay_form_qr_vietin_bank_id';
        $this->payFormQrId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '08')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'transaction_type_HU_id';
        $this->transactionTypeHUId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->transactionType->where('transaction_type_code', 'HU')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'transaction_type_TU_id';
        $this->transactionTypeTUId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->transactionType->where('transaction_type_code', 'TU')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
        
        $cacheKey = 'transaction_type_TT_id';
        $this->transactionTypeTTId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->transactionType->where('transaction_type_code', 'TT')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
        return [

        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $id = $this->transaction_restore;
            $dataTransaction = $this->transaction->find($id);
            if (!$dataTransaction) {
                $validator->errors()->add('id', 'ID giao dịch không tồn tại!');
            }else{
                if ($dataTransaction->is_active == 0) {
                    $validator->errors()->add('id', 'Giao dịch này đang bị khóa!');
                }
                if ($dataTransaction->is_cancel != 1) {
                    $validator->errors()->add('id', 'Giao dịch này chưa bị hủy!');
                }
                if ($dataTransaction->treatment_id) {
                    $dataTreatment = $this->treatment->find($dataTransaction->treatment_id);
                    if (!$dataTreatment) {
                        $validator->errors()->add('id', 'Hồ sơ không tồn tại!');
                    }else{
                        if ($dataTreatment->is_active == 0) {
                            $validator->errors()->add('id', 'Hồ sơ đã bị khóa viện phí!');
                        }
                        if($dataTreatment->is_hein_approval){
                            $validator->errors()->add('id', 'Hồ sơ đã duyệt BHYT!');
                        }
                    }
                }
                if($dataTransaction->pay_form_id == $this->payFormQrId){
                    $validator->errors()->add('id', 'Không thể khôi phục giao dịch với hình thức thanh toán là Thanh toán QR!');
                }

                // nếu là tạm thu dịch vụ => check lại xem cái dịch vụ của deposit có cái nào bị thay đổi giá không, nếu bị thay đổi => không cho khôi phục giao dịch
                if (($dataTransaction->transaction_type_id == $this->transactionTypeTUId) && ($dataTransaction->tdl_sere_serv_deposit_count > 0)) {
                    $listSereServDeposit = $this->sereServDeposit->where('deposit_id',$dataTransaction->id)->get();
                    foreach ($listSereServDeposit as $key => $item) {
                        // Lấy ra giá của chính sách 
                        $activePrice = $this->servicePatyRepository->getActivePriceByServieIdPatientTypeId($item->tdl_service_id, $item->tdl_patient_type_id)->price ?? null;
                        if($activePrice && $activePrice != $item->vir_price){
                            $validator->errors()->add('id', 'Dịch vụ '.$item->tdl_service_name.' đã thay đổi giá!');
                        }
                    }
                }
                
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
