<?php

namespace App\Http\Requests\Transaction;

use App\Models\HIS\Fund;
use App\Models\HIS\PayForm;
use App\Models\View\TreatmentFeeDetailVView;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CreateTransactionThanhToanRequest extends FormRequest
{
    protected $payForm;
    protected $payForm06;
    protected $payForm03;
    protected $treatmentFeeDetailVView;
    protected $fund;
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
        $this->fund = new Fund();
        $this->payForm06 = Cache::remember('pay_form_06_id', now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '06')->get();
            return $data->value('id');
        });
        $this->payForm03 = Cache::remember('pay_form_03_id', now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '03')->get();
            return $data->value('id');
        });

        return [
            'amount' => [
                'required',
                'integer',
                'min:0',
            ],
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
            'repay_reason_id' => [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\RepayReason', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],
            // 'cashier_room_id' => [
            //     'required',
            //     'integer',
            //     Rule::exists('App\Models\HIS\CashierRoom', 'id')
            //         ->where(function ($query) {
            //             $query = $query
            //                 ->where(DB::connection('oracle_his')->raw("is_active"), 1);
            //         }),
            // ],
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
            'swipe_amount' =>       'required_if:pay_form_id,' . $this->payForm06 . '|lte:amount',
            'transfer_amount' =>    'required_if:pay_form_id,' . $this->payForm03 . '|lte:amount',
            'transaction_time' =>   'required|integer|regex:/^\d{14}$/',

            'exemption' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'exemption_reason' =>             'nullable|string|max:2000',

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
            'amount.required'      => config('keywords')['transaction_thanh_toan']['amount'] . config('keywords')['error']['required'],
            'amount.integer'       => config('keywords')['transaction_thanh_toan']['amount'] . config('keywords')['error']['integer'],
            'amount.min'           => config('keywords')['transaction_thanh_toan']['amount'] . config('keywords')['error']['integer_min'],
            'amount.max'           => config('keywords')['transaction_thanh_toan']['amount'] . ' tối đa = Tiền đã thu - Tiền bệnh nhân phải thanh toán - Tiền đã nộp (tạm khóa)',

            'account_book_id.required'      => config('keywords')['transaction_thanh_toan']['account_book_id'] . config('keywords')['error']['required'],
            'account_book_id.integer'       => config('keywords')['transaction_thanh_toan']['account_book_id'] . config('keywords')['error']['integer'],
            'account_book_id.exists'        => config('keywords')['transaction_thanh_toan']['account_book_id'] . config('keywords')['error']['exists'],

            'pay_form_id.required'      => config('keywords')['transaction_thanh_toan']['pay_form_id'] . config('keywords')['error']['required'],
            'pay_form_id.integer'       => config('keywords')['transaction_thanh_toan']['pay_form_id'] . config('keywords')['error']['integer'],
            'pay_form_id.exists'        => config('keywords')['transaction_thanh_toan']['pay_form_id'] . config('keywords')['error']['exists'],

            'cashier_room_id.required'      => config('keywords')['transaction_thanh_toan']['cashier_room_id'] . config('keywords')['error']['required'],
            'cashier_room_id.integer'       => config('keywords')['transaction_thanh_toan']['cashier_room_id'] . config('keywords')['error']['integer'],
            'cashier_room_id.exists'        => config('keywords')['transaction_thanh_toan']['cashier_room_id'] . config('keywords')['error']['exists'],

            'treatment_id.required'      => config('keywords')['transaction_thanh_toan']['treatment_id'] . config('keywords')['error']['required'],
            'treatment_id.integer'       => config('keywords')['transaction_thanh_toan']['treatment_id'] . config('keywords')['error']['integer'],
            'treatment_id.exists'        => config('keywords')['transaction_thanh_toan']['treatment_id'] . config('keywords')['error']['exists'],

            'description.string'        => config('keywords')['transaction_thanh_toan']['description'] . config('keywords')['error']['string'],
            'description.max'           => config('keywords')['transaction_thanh_toan']['description'] . config('keywords')['error']['string_max'],

            'swipe_amount.required_if'   => config('keywords')['transaction_thanh_toan']['swipe_amount'] . ' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Quẹt thẻ',
            'swipe_amount.integer'       => config('keywords')['transaction_thanh_toan']['swipe_amount'] . config('keywords')['error']['integer'],
            'swipe_amount.lte'           => config('keywords')['transaction_thanh_toan']['swipe_amount'] . ' phải bé hơn hoặc bằng ' . config('keywords')['transaction_thanh_toan']['amount'],

            'transfer_amount.required_if'   => config('keywords')['transaction_thanh_toan']['transfer_amount'] . ' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Chuyển khoản',
            'transfer_amount.integer'       => config('keywords')['transaction_thanh_toan']['transfer_amount'] . config('keywords')['error']['integer'],
            'transfer_amount.lte'           => config('keywords')['transaction_thanh_toan']['transfer_amount'] . ' phải bé hơn hoặc bằng ' . config('keywords')['transaction_thanh_toan']['amount'],

            'transaction_time.required'           => config('keywords')['transaction_thanh_toan']['transaction_time'] . config('keywords')['error']['required'],
            'transaction_time.integer'            => config('keywords')['transaction_thanh_toan']['transaction_time'] . config('keywords')['error']['integer'],
            'transaction_time.regex'              => config('keywords')['transaction_thanh_toan']['transaction_time'] . config('keywords')['error']['regex_ymdhis'],

            'exemption.integer'       => config('keywords')['transaction_thanh_toan']['exemption'] . config('keywords')['error']['integer'],
            'exemption.min'           => config('keywords')['transaction_thanh_toan']['exemption'] . config('keywords')['error']['integer_min'],

            'exemption_reason.string'        => config('keywords')['transaction_thanh_toan']['exemption_reason'] . config('keywords')['error']['string'],
            'exemption_reason.max'           => config('keywords')['transaction_thanh_toan']['exemption_reason'] . config('keywords')['error']['string_max'],
        ];
    }

    
    protected function prepareForValidation()
    {
        if ($this->has('bill_funds') && $this->bill_funds != null) {
            $this->merge([
                'bill_funds_list' => json_decode($this->bill_funds) ?? [],
            ]);
        }
    }
     
    public function withValidator($validator)
    {
        
        $validator->after(function ($validator) {
            $totalAmountBillFund = 0;
            if ($this->has('bill_funds_list') && ($this->bill_funds_list[0] != null)) {
                foreach ($this->bill_funds_list as $item) {
                    $totalAmountBillFund = $totalAmountBillFund + $item->amount;
                    if (!is_numeric($item->amount)) {
                        $validator->errors()->add('bill_funds', 'Số tiền hỗ trợ với ID quỹ = ' . $item->fund_id . config('keywords')['error']['integer']);
                    }
                    if ($item->amount<0) {
                        $validator->errors()->add('bill_funds', 'Số tiền hỗ trợ với ID quỹ = ' . $item->fund_id . ' phải lớn hơn 0!');
                    }
                    // Kiểm tra fund_id có tồn tại trong DB không
                    $exists = $this->fund->where('id', $item->fund_id)->exists();
                    if (!$exists) {
                        $validator->errors()->add('bill_funds', 'ID quỹ = ' . $item->fund_id . ' không tồn tại hoặc đang bị tạm khóa!');
                    }
                }
            }
            if((($this->exemption??0) + $totalAmountBillFund) > $this->amount){
                $validator->errors()->add('amount', 'Tổng tiền chiết khấu và tiền các quỹ tối đa = tiền thanh toán!');
                $validator->errors()->add('exemption', 'Tổng tiền chiết khấu và tiền các quỹ tối đa = tiền thanh toán!');
                $validator->errors()->add('bill_funds', 'Tổng tiền chiết khấu và tiền các quỹ tối đa = tiền thanh toán!');
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
