<?php

namespace App\Http\Requests\Transaction;

use App\Models\HIS\DepositReq;
use App\Models\HIS\PayForm;
use App\Models\HIS\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CreateTransactionTamUngRequest extends FormRequest
{
    protected $payForm;
    protected $payForm06;
    protected $payForm03;
    protected $depositReq;
    protected $transaction;
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
        $this->payForm = new PayForm();
        $this->transaction = new Transaction();
        $this->payForm06 = Cache::remember('pay_form_06_id', now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '06')->get();
            return $data->value('id');
        });
        $this->payForm03 = Cache::remember('pay_form_03_id', now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '03')->get();
            return $data->value('id');
        });
        return [
            'amount' =>                 'required|numeric|regex:/^\d{1,15}(\.\d{1,6})?$/|min:0',
            'account_book_id' => [
                                    'required',
                                    'integer',
                                    Rule::exists('App\Models\HIS\AccountBook', 'id')
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
            // 'cashier_room_id' => [
            //                         'required',
            //                         'integer',
            //                         Rule::exists('App\Models\HIS\CashierRoom', 'id')
            //                         ->where(function ($query) {
            //                             $query = $query
            //                             ->where(DB::connection('oracle_his')->raw("is_active"), 1);
            //                         }),
            //                     ], 
            'treatment_id' => [
                'required',
                'integer',
                Rule::exists('App\Models\View\TreatmentFeeListVView', 'id') 
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1)  // Lọc chưa khóa viện phí
;
                    }),
            ],
            'description' =>        'nullable|string|max:2000',  
            'swipe_amount' =>       'required_if:pay_form_id,'.$this->payForm06.'|lte:amount|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'transfer_amount' =>    'required_if:pay_form_id,'.$this->payForm03.'|lte:amount|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'transaction_time' =>   'required|integer|regex:/^\d{14}$/',


            'buyer_name' =>             'nullable|string|max:200',
            'buyer_tax_code' =>         'nullable|string|max:20',
            'buyer_account_number' =>   'nullable|string|max:500',
            'buyer_organization' =>     'nullable|string|max:500',
            'buyer_address' =>          'nullable|string|max:500',
            'buyer_phone' =>            'nullable|string|max:20',

            'deposit_req_id' => [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\DepositReq', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ], 
        ];
    }
    public function messages()
    {
        return [
            'amount.required'       => config('keywords')['transaction_tam_ung']['amount'].config('keywords')['error']['required'],
            'amount.numeric'        => config('keywords')['transaction_tam_ung']['amount'].config('keywords')['error']['numeric'],
            'amount.regex'          => config('keywords')['transaction_tam_ung']['amount'].config('keywords')['error']['regex_21_6'],
            'amount.min'            => config('keywords')['transaction_tam_ung']['amount'].config('keywords')['error']['integer_min'],

            'account_book_id.required'      => config('keywords')['transaction_tam_ung']['account_book_id'].config('keywords')['error']['required'],
            'account_book_id.integer'       => config('keywords')['transaction_tam_ung']['account_book_id'].config('keywords')['error']['integer'],
            'account_book_id.exists'        => config('keywords')['transaction_tam_ung']['account_book_id'].config('keywords')['error']['exists'],  

            'pay_form_id.required'      => config('keywords')['transaction_tam_ung']['pay_form_id'].config('keywords')['error']['required'],
            'pay_form_id.integer'       => config('keywords')['transaction_tam_ung']['pay_form_id'].config('keywords')['error']['integer'],
            'pay_form_id.exists'        => config('keywords')['transaction_tam_ung']['pay_form_id'].config('keywords')['error']['exists'], 

            'cashier_room_id.required'      => config('keywords')['transaction_tam_ung']['cashier_room_id'].config('keywords')['error']['required'],
            'cashier_room_id.integer'       => config('keywords')['transaction_tam_ung']['cashier_room_id'].config('keywords')['error']['integer'],
            'cashier_room_id.exists'        => config('keywords')['transaction_tam_ung']['cashier_room_id'].config('keywords')['error']['exists'], 

            'treatment_id.required'      => config('keywords')['transaction_tam_ung']['treatment_id'].config('keywords')['error']['required'],
            'treatment_id.integer'       => config('keywords')['transaction_tam_ung']['treatment_id'].config('keywords')['error']['integer'],
            'treatment_id.exists'        => config('keywords')['transaction_tam_ung']['treatment_id'].' không tồn tại hoặc đang bị khóa viện phí!', 

            'description.string'        => config('keywords')['transaction_tam_ung']['description'].config('keywords')['error']['string'],
            'description.max'           => config('keywords')['transaction_tam_ung']['description'].config('keywords')['error']['string_max'],

            'swipe_amount.required_if'   => config('keywords')['transaction_tam_ung']['swipe_amount'].' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Quẹt thẻ',
            'swipe_amount.numeric'       => config('keywords')['transaction_tam_ung']['swipe_amount'].config('keywords')['error']['numeric'],
            'swipe_amount.regex'         => config('keywords')['transaction_tam_ung']['swipe_amount'].config('keywords')['error']['regex_19_4'],
            'swipe_amount.min'           => config('keywords')['transaction_tam_ung']['swipe_amount'].config('keywords')['error']['integer_min'],
            'swipe_amount.lte'           => config('keywords')['transaction_tam_ung']['swipe_amount'].' phải bé hơn hoặc bằng '.config('keywords')['transaction_tam_ung']['amount'],

            'transfer_amount.required_if'   => config('keywords')['transaction_tam_ung']['transfer_amount'].' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Chuyển khoản',
            'transfer_amount.numeric'       => config('keywords')['transaction_tam_ung']['transfer_amount'].config('keywords')['error']['numeric'],
            'transfer_amount.regex'         => config('keywords')['transaction_tam_ung']['transfer_amount'].config('keywords')['error']['regex_19_4'],
            'transfer_amount.min'           => config('keywords')['transaction_tam_ung']['transfer_amount'].config('keywords')['error']['integer_min'],
            'transfer_amount.lte'           => config('keywords')['transaction_tam_ung']['transfer_amount'].' phải bé hơn hoặc bằng '.config('keywords')['transaction_tam_ung']['amount'],

            'transaction_time.required'           => config('keywords')['transaction_tam_ung']['transaction_time'].config('keywords')['error']['required'],
            'transaction_time.integer'            => config('keywords')['transaction_tam_ung']['transaction_time'].config('keywords')['error']['integer'],
            'transaction_time.regex'              => config('keywords')['transaction_tam_ung']['transaction_time'].config('keywords')['error']['regex_ymdhis'],

            
            'buyer_name.string'        => config('keywords')['transaction_tam_ung']['buyer_name'] . config('keywords')['error']['string'],
            'buyer_name.max'           => config('keywords')['transaction_tam_ung']['buyer_name'] . config('keywords')['error']['string_max'],

            'buyer_tax_code.string'        => config('keywords')['transaction_tam_ung']['buyer_tax_code'] . config('keywords')['error']['string'],
            'buyer_tax_code.max'           => config('keywords')['transaction_tam_ung']['buyer_tax_code'] . config('keywords')['error']['string_max'],

            'buyer_account_number.string'        => config('keywords')['transaction_tam_ung']['buyer_account_number'] . config('keywords')['error']['string'],
            'buyer_account_number.max'           => config('keywords')['transaction_tam_ung']['buyer_account_number'] . config('keywords')['error']['string_max'],

            'buyer_organization.string'        => config('keywords')['transaction_tam_ung']['buyer_organization'] . config('keywords')['error']['string'],
            'buyer_organization.max'           => config('keywords')['transaction_tam_ung']['buyer_organization'] . config('keywords')['error']['string_max'],

            'buyer_address.string'        => config('keywords')['transaction_tam_ung']['buyer_address'] . config('keywords')['error']['string'],
            'buyer_address.max'           => config('keywords')['transaction_tam_ung']['buyer_address'] . config('keywords')['error']['string_max'],

            'buyer_phone.string'        => config('keywords')['transaction_tam_ung']['buyer_phone'] . config('keywords')['error']['string'],
            'buyer_phone.max'           => config('keywords')['transaction_tam_ung']['buyer_phone'] . config('keywords')['error']['string_max'],

            'deposit_req_id.integer'       => config('keywords')['transaction_tam_ung']['deposit_req_id'].config('keywords')['error']['integer'],
            'deposit_req_id.exists'        => config('keywords')['transaction_tam_ung']['deposit_req_id'].config('keywords')['error']['exists'], 
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Kiểm tra tiền thanh toán có = tiền yêu cầu tạm ứng k
            if($this->deposit_req_id != null){
                $this->depositReq = new DepositReq();
                $dataDepositReq = $this->depositReq
                ->select(
                    'his_deposit_req.*'
                )
                ->find($this->deposit_req_id??0);
                if(!$dataDepositReq){
                    $validator->errors()->add('deposit_req_id', 'Không tìm thấy yêu cầu tạm ứng!');
                    return;
                }
                if($dataDepositReq->treatment_id != $this->treatment_id){
                    $validator->errors()->add('deposit_req_id', 'Yêu cầu tạm ứng không thuộc về lần điều trị này!');
                }
                if($dataDepositReq->deposit_id != null){
                    $dataTransactionDepositReq = $this->transaction->find($dataDepositReq->deposit_id);
                    if(!$dataTransactionDepositReq->is_cancel){
                        $validator->errors()->add('deposit_req_id', 'Đã tồn tại giao dịch thành công cho yêu cầu tạm ứng này!');
                    }
                }
                if($this->amount != $dataDepositReq->amount){
                    $validator->errors()->add('amount', config('keywords')['transaction_tam_ung']['amount'].' không khớp với tiền của yêu cầu tạm ứng!');
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
