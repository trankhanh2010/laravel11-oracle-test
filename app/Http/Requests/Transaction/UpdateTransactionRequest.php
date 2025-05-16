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

class UpdateTransactionRequest extends FormRequest
{
    protected $transactionModal;
    protected $treatment;
    protected $transactionType;
    protected $payFormQrId;
    protected $payForm;
    protected $payForm06;
    protected $payForm03;
    protected $transactionTypeHUId;
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
        $this->transactionModal = new Transaction();
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

        $cacheKey = 'pay_form_06_id';
        $this->payForm06 = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '06')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'pay_form_03_id';
        $this->payForm03 = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '03')->get();
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

        return [
            'repay_reason_id' => [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\RepayReason', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            'pay_form_id' => [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\PayForm', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],

            'description' =>        'nullable|string|max:2000',
            'swipe_amount' =>       'required_if:pay_form_id,' . $this->payForm06 . '|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'transfer_amount' =>    'required_if:pay_form_id,' . $this->payForm03 . '|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',

            'buyer_name' =>             'nullable|string|max:200',
            'buyer_tax_code' =>         'nullable|string|max:20',
            'buyer_account_number' =>   'nullable|string|max:500',
            'buyer_organization' =>     'nullable|string|max:500',
            'buyer_address' =>          'nullable|string|max:500',

        ];
    }
    public function messages()
    {
        return [
            'repay_reason_id.integer'       => config('keywords')['transaction_update']['repay_reason_id'].config('keywords')['error']['integer'],
            'repay_reason_id.exists'        => config('keywords')['transaction_update']['repay_reason_id'].config('keywords')['error']['exists'],  

            'pay_form_id.required'      => config('keywords')['transaction_update']['pay_form_id'] . config('keywords')['error']['required'],
            'pay_form_id.integer'       => config('keywords')['transaction_update']['pay_form_id'] . config('keywords')['error']['integer'],
            'pay_form_id.exists'        => config('keywords')['transaction_update']['pay_form_id'] . config('keywords')['error']['exists'],

            'description.string'        => config('keywords')['transaction_update']['description'] . config('keywords')['error']['string'],
            'description.max'           => config('keywords')['transaction_update']['description'] . config('keywords')['error']['string_max'],

            'swipe_amount.required_if'   => config('keywords')['transaction_update']['swipe_amount'] . ' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Quẹt thẻ',
            'swipe_amount.numeric'       => config('keywords')['transaction_update']['swipe_amount'] . config('keywords')['error']['numeric'],
            'swipe_amount.regex'         => config('keywords')['transaction_update']['swipe_amount'] . config('keywords')['error']['regex_19_4'],
            'swipe_amount.min'           => config('keywords')['transaction_update']['swipe_amount'] . config('keywords')['error']['integer_min'],

            'transfer_amount.required_if'   => config('keywords')['transaction_update']['transfer_amount'] . ' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Chuyển khoản',
            'transfer_amount.numeric'       => config('keywords')['transaction_update']['transfer_amount'] . config('keywords')['error']['numeric'],
            'transfer_amount.regex'         => config('keywords')['transaction_update']['transfer_amount'] . config('keywords')['error']['regex_19_4'],
            'transfer_amount.min'           => config('keywords')['transaction_update']['transfer_amount'] . config('keywords')['error']['integer_min'],
            
            'buyer_name.string'        => config('keywords')['transaction_update']['buyer_name'] . config('keywords')['error']['string'],
            'buyer_name.max'           => config('keywords')['transaction_update']['buyer_name'] . config('keywords')['error']['string_max'],

            'buyer_tax_code.string'        => config('keywords')['transaction_update']['buyer_tax_code'] . config('keywords')['error']['string'],
            'buyer_tax_code.max'           => config('keywords')['transaction_update']['buyer_tax_code'] . config('keywords')['error']['string_max'],

            'buyer_account_number.string'        => config('keywords')['transaction_update']['buyer_account_number'] . config('keywords')['error']['string'],
            'buyer_account_number.max'           => config('keywords')['transaction_update']['buyer_account_number'] . config('keywords')['error']['string_max'],

            'buyer_organization.string'        => config('keywords')['transaction_update']['buyer_organization'] . config('keywords')['error']['string'],
            'buyer_organization.max'           => config('keywords')['transaction_update']['buyer_organization'] . config('keywords')['error']['string_max'],

            'buyer_address.string'        => config('keywords')['transaction_update']['buyer_address'] . config('keywords')['error']['string'],
            'buyer_address.max'           => config('keywords')['transaction_update']['buyer_address'] . config('keywords')['error']['string_max'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $id = $this->transaction;
            $dataTransaction = $this->transactionModal->find($id);
            if (!$dataTransaction) {
                $validator->errors()->add('id', 'Giao dịch không tồn tại!');
            }else{
                if ($dataTransaction->is_active == 0) {
                    $validator->errors()->add('id', 'Giao dịch này đang bị khóa!');
                }
                if ($dataTransaction->is_cancel == 1) {
                    $validator->errors()->add('id', 'Giao dịch này đã bị hủy!');
                }

                if($this->swipe_amount){
                    if($this->swipe_amount > $dataTransaction->amount){
                        $validator->errors()->add('swipe_amount', 'Số tiền quẹt thẻ không được lớn hơn số tiền giao dịch!');
                    }
                }
                if($this->transfer_amount){
                    if($this->transfer_amount > $dataTransaction->amount){
                        $validator->errors()->add('transfer_amount', 'Số tiền chuyển khoản không được lớn hơn số tiền giao dịch!');
                    }
                }
                if($this->repay_reason_id){
                    if($dataTransaction->transaction_type_id != $this->transactionTypeHUId){
                        $validator->errors()->add('repay_reason_id', 'Chỉ được cập nhật lý do hoàn ứng khi loại giao dịch là hoàn ứng!');
                    }
                }
                if($dataTransaction->transaction_type_id == $this->transactionTypeHUId && !$this->repay_reason_id){
                    $validator->errors()->add('repay_reason_id', 'Thiếu lý do hoàn ứng!');
                }
                if($dataTransaction->pay_form_id == $this->payFormQrId){
                    if($this->pay_form_id != $this->payFormQrId){
                        $validator->errors()->add('pay_form_id', 'Không được đổi hình thức thanh toán nếu hình thức cũ là thanh toán QR!');
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
