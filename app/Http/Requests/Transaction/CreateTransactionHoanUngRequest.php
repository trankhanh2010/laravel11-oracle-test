<?php

namespace App\Http\Requests\Transaction;

use App\Models\HIS\PayForm;
use App\Models\View\TreatmentFeeDetailVView;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CreateTransactionHoanUngRequest extends FormRequest
{
    protected $payForm;
    protected $payForm06;
    protected $payForm03;
    protected $treatmentFeeDetailVView;
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
        $this->treatmentFeeDetailVView = new TreatmentFeeDetailVView();
        $data = $this->treatmentFeeDetailVView
        ->select(
            'xa_v_his_treatment_fee_detail.*'
        )
        ->addSelect(DB::connection('oracle_his')->raw('(total_deposit_amount - total_repay_amount - total_bill_transfer_amount - total_bill_fund - total_bill_exemption + total_bill_amount + locking_amount) as da_thu'))
        ->find($this->input('treatment_id') ?? 0);
        $max = 0;
        if ($data) {
            $max = max(0, ((int) $data->da_thu - (int) $data->total_patient_price - (int) $data->locking_amount) + (int) $data->total_bill_fund + (int) $data->total_bill_exemption);
        }
        // dd($max);
        // dd( (int) $data->da_thu , (int) $data->total_patient_price , (int) $data->locking_amount);
        return [
            'amount' => [
                'required',
                'numeric',
                'regex:/^\d{1,15}(\.\d{1,6})?$/',
                'min:0',
                'max:'.$max, // Tiền hoàn ứng k lớn hơn tiền đã thu - tiền bệnh nhân phải thanh toán - đã nộp (tạm khóa) 
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


        ];
    }
    public function messages()
    {
        return [
            'amount.required'      => config('keywords')['transaction_hoan_ung']['amount'] . config('keywords')['error']['required'],
            'amount.numeric'       => config('keywords')['transaction_hoan_ung']['amount'].config('keywords')['error']['numeric'],
            'amount.regex'         => config('keywords')['transaction_hoan_ung']['amount'].config('keywords')['error']['regex_21_6'],
            'amount.min'           => config('keywords')['transaction_hoan_ung']['amount'] . config('keywords')['error']['integer_min'],
            'amount.max'           => config('keywords')['transaction_hoan_ung']['amount'] . ' tối đa = Tiền đã thu - Tiền bệnh nhân phải thanh toán - Tiền đã nộp (tạm khóa) + Tiền thu quỹ + Tiền chiết khấu',

            'account_book_id.required'      => config('keywords')['transaction_hoan_ung']['account_book_id'] . config('keywords')['error']['required'],
            'account_book_id.integer'       => config('keywords')['transaction_hoan_ung']['account_book_id'] . config('keywords')['error']['integer'],
            'account_book_id.exists'        => config('keywords')['transaction_hoan_ung']['account_book_id'] . config('keywords')['error']['exists'],

            'pay_form_id.required'      => config('keywords')['transaction_hoan_ung']['pay_form_id'] . config('keywords')['error']['required'],
            'pay_form_id.integer'       => config('keywords')['transaction_hoan_ung']['pay_form_id'] . config('keywords')['error']['integer'],
            'pay_form_id.exists'        => config('keywords')['transaction_hoan_ung']['pay_form_id'] . config('keywords')['error']['exists'],

            'cashier_room_id.required'      => config('keywords')['transaction_hoan_ung']['cashier_room_id'] . config('keywords')['error']['required'],
            'cashier_room_id.integer'       => config('keywords')['transaction_hoan_ung']['cashier_room_id'] . config('keywords')['error']['integer'],
            'cashier_room_id.exists'        => config('keywords')['transaction_hoan_ung']['cashier_room_id'] . config('keywords')['error']['exists'],

            'treatment_id.required'      => config('keywords')['transaction_hoan_ung']['treatment_id'] . config('keywords')['error']['required'],
            'treatment_id.integer'       => config('keywords')['transaction_hoan_ung']['treatment_id'] . config('keywords')['error']['integer'],
            'treatment_id.exists'        => config('keywords')['transaction_hoan_ung']['treatment_id'] . ' không tồn tại hoặc đang bị khóa viện phí!',

            'description.string'        => config('keywords')['transaction_hoan_ung']['description'] . config('keywords')['error']['string'],
            'description.max'           => config('keywords')['transaction_hoan_ung']['description'] . config('keywords')['error']['string_max'],

            'swipe_amount.required_if'   => config('keywords')['transaction_hoan_ung']['swipe_amount'].' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Quẹt thẻ',
            'swipe_amount.numeric'       => config('keywords')['transaction_hoan_ung']['swipe_amount'].config('keywords')['error']['numeric'],
            'swipe_amount.regex'         => config('keywords')['transaction_hoan_ung']['swipe_amount'].config('keywords')['error']['regex_19_4'],
            'swipe_amount.min'           => config('keywords')['transaction_hoan_ung']['swipe_amount'].config('keywords')['error']['integer_min'],
            'swipe_amount.lte'           => config('keywords')['transaction_hoan_ung']['swipe_amount'].' phải bé hơn hoặc bằng '.config('keywords')['transaction_tam_ung']['amount'],

            'transfer_amount.required_if'   => config('keywords')['transaction_hoan_ung']['transfer_amount'].' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Chuyển khoản',
            'transfer_amount.numeric'       => config('keywords')['transaction_hoan_ung']['transfer_amount'].config('keywords')['error']['numeric'],
            'transfer_amount.regex'         => config('keywords')['transaction_hoan_ung']['transfer_amount'].config('keywords')['error']['regex_19_4'],
            'transfer_amount.min'           => config('keywords')['transaction_hoan_ung']['transfer_amount'].config('keywords')['error']['integer_min'],
            'transfer_amount.lte'           => config('keywords')['transaction_hoan_ung']['transfer_amount'].' phải bé hơn hoặc bằng '.config('keywords')['transaction_tam_ung']['amount'],

            'transaction_time.required'           => config('keywords')['transaction_hoan_ung']['transaction_time'] . config('keywords')['error']['required'],
            'transaction_time.integer'            => config('keywords')['transaction_hoan_ung']['transaction_time'] . config('keywords')['error']['integer'],
            'transaction_time.regex'              => config('keywords')['transaction_hoan_ung']['transaction_time'] . config('keywords')['error']['regex_ymdhis'],
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
