<?php

namespace App\Http\Requests\Transaction;

use App\Models\HIS\PayForm;
use App\Models\HIS\Transaction;
use App\Models\HIS\TransactionType;
use App\Models\HIS\Treatment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UpdateCancelTransactionRequest extends FormRequest
{
    protected $transaction;
    protected $treatment;
    protected $payFormQrId;
    protected $payForm;
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
            'cancel_reason' => 'nullable|string|max:2000',
            'cancel_reason_id' => [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\CancelReason', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ], 
            'cancel_time' =>   'required|integer|regex:/^\d{14}$/',

        ];
    }
    public function messages()
    {
        return [
            'cancel_reason_id.integer'       => config('keywords')['transaction_cancel']['cancel_reason_id'].config('keywords')['error']['integer'],
            'cancel_reason_id.exists'        => config('keywords')['transaction_cancel']['cancel_reason_id'].config('keywords')['error']['exists'],  

            'cancel_reason.string'        => config('keywords')['transaction_cancel']['cancel_reason'].config('keywords')['error']['string'],
            'cancel_reason.max'           => config('keywords')['transaction_cancel']['cancel_reason'].config('keywords')['error']['string_max'],

            'cancel_time.required'           => config('keywords')['transaction_cancel']['cancel_time'] . config('keywords')['error']['required'],
            'cancel_time.integer'            => config('keywords')['transaction_cancel']['cancel_time'] . config('keywords')['error']['integer'],
            'cancel_time.regex'              => config('keywords')['transaction_cancel']['cancel_time'] . config('keywords')['error']['regex_ymdhis'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $id = $this->id;
            $dataTransaction = $this->transaction->find($id);
            if (!$dataTransaction) {
                $validator->errors()->add('id', 'ID giao dịch không tồn tại!');
            }else{
                if ($dataTransaction->is_active == 0) {
                    $validator->errors()->add('id', 'Giao dịch này đang bị khóa!');
                }
                if ($dataTransaction->is_cancel == 1) {
                    $validator->errors()->add('id', 'Giao dịch này đã bị hủy!');
                }
                if ($dataTransaction->treatment_id) {
                    $dataTreatment = $this->treatment->find($dataTransaction->treatment_id);
                    if (!$dataTreatment) {
                        $validator->errors()->add('id', 'Hồ sơ không tồn tại!');
                    }else{
                        if ($dataTreatment->is_active == 0) {
                            $validator->errors()->add('id', 'Hồ sơ đã bị khóa viện phí!');
                        }
                    }
                }
                if($dataTransaction->pay_form_id == $this->payFormQrId){
                    $validator->errors()->add('id', 'Không thể hủy giao dịch với hình thức thanh toán là Thanh toán QR!');
                }
                if ($this->cancel_time && $this->cancel_time < $dataTransaction->transaction_time) {
                    $validator->errors()->add('cancel_time', 'Thời gian hủy không được nhỏ hơn thời gian giao dịch!');
                }
                // nếu là tạm thu => Check xem sau giao dịch này (id > hơn id transaction này) có giao dịch hoàn ứng hay giao dịch thanh toán nào mà có kc không, nếu có => không cho hủy
                if ($dataTransaction->transaction_type_id == $this->transactionTypeTUId) {
                    $exists = $this->transaction
                        ->where('his_transaction.treatment_id', $dataTransaction->treatment_id)
                        ->where('his_transaction.id', '>', $dataTransaction->id)
                        ->where(function ($q) {
                            $q->where(function ($q1) {
                                // Giao dịch hoàn ứng
                                $q1->where('his_transaction.transaction_type_id', $this->transactionTypeHUId);
                            })
                            ->orWhere(function ($q2) {
                                // Giao dịch thanh toán và có kc_amount > 0
                                $q2->where('his_transaction.transaction_type_id', $this->transactionTypeTTId)
                                    ->where('his_transaction.kc_amount', '>', 0);
                            });
                        })
                        ->where(function ($q) {
                            // Kiểm tra is_cancel null hoặc 0
                            $q->whereNull('his_transaction.is_cancel')
                              ->orWhere('his_transaction.is_cancel', 0);
                        })
                        ->exists();
                
                    if ($exists) {
                        $validator->errors()->add('sere_servs', 'Xử lý thất bại! Số tiền đã được hoàn ứng hoặc kết chuyển một phần!');
                    }
                }
                
            }

            if(($this->cancel_reason == null) && ($this->cancel_reason_id == null)){
                $validator->errors()->add('cancel_reason_id', 'Thiếu lý do hủy giao dịch!');
                $validator->errors()->add('cancel_reason', 'Thiếu lý do hủy giao dịch!');
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
