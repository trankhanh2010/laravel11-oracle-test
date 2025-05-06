<?php

namespace App\Http\Requests\Transaction;

use App\Models\HIS\PayForm;
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
        $this->payForm06 = Cache::remember('pay_form_06_id', now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '06')->get();
            return $data->value('id');
        });
        $this->payForm03 = Cache::remember('pay_form_03_id', now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '03')->get();
            return $data->value('id');
        });
        return [
            'amount' =>                         'required|integer|min:0',  
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
                                    Rule::exists('App\Models\HIS\Treatment', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
            'description' =>        'nullable|string|max:2000',  
            'swipe_amount' =>       'required_if:pay_form_id,'.$this->payForm06.'|lte:amount',
            'transfer_amount' =>    'required_if:pay_form_id,'.$this->payForm03.'|lte:amount',
            'transaction_time' =>   'required|integer|regex:/^\d{14}$/',


            'buyer_name' =>             'nullable|string|max:200',
            'buyer_tax_code' =>         'nullable|string|max:20',
            'buyer_account_number' =>   'nullable|string|max:500',
            'buyer_organization' =>     'nullable|string|max:500',
            'buyer_address' =>          'nullable|string|max:500',
            'buyer_phone' =>            'nullable|string|max:20',

        ];
    }
    public function messages()
    {
        return [
            'amount.required'      => config('keywords')['transaction_tam_ung']['amount'].config('keywords')['error']['required'],
            'amount.integer'       => config('keywords')['transaction_tam_ung']['amount'].config('keywords')['error']['integer'],
            'amount.min'           => config('keywords')['transaction_tam_ung']['amount'].config('keywords')['error']['integer_min'],

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
            'treatment_id.exists'        => config('keywords')['transaction_tam_ung']['treatment_id'].config('keywords')['error']['exists'], 

            'description.string'        => config('keywords')['transaction_tam_ung']['description'].config('keywords')['error']['string'],
            'description.max'           => config('keywords')['transaction_tam_ung']['description'].config('keywords')['error']['string_max'],

            'swipe_amount.required_if'   => config('keywords')['transaction_tam_ung']['swipe_amount'].' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Quẹt thẻ',
            'swipe_amount.integer'       => config('keywords')['transaction_tam_ung']['swipe_amount'].config('keywords')['error']['integer'],
            'swipe_amount.lte'           => config('keywords')['transaction_tam_ung']['swipe_amount'].' phải bé hơn hoặc bằng '.config('keywords')['transaction_tam_ung']['amount'],

            'transfer_amount.required_if'   => config('keywords')['transaction_tam_ung']['transfer_amount'].' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Chuyển khoản',
            'transfer_amount.integer'       => config('keywords')['transaction_tam_ung']['transfer_amount'].config('keywords')['error']['integer'],
            'transfer_amount.lte'           => config('keywords')['transaction_tam_ung']['transfer_amount'].' phải bé hơn hoặc bằng '.config('keywords')['transaction_tam_ung']['amount'],

            'transaction_time.required'           => config('keywords')['transaction_tam_ung']['transaction_time'].config('keywords')['error']['required'],
            'transaction_time.integer'            => config('keywords')['transaction_tam_ung']['transaction_time'].config('keywords')['error']['integer'],
            'transaction_time.regex'              => config('keywords')['transaction_tam_ung']['transaction_time'].config('keywords')['error']['regex_ymdhis'],
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
