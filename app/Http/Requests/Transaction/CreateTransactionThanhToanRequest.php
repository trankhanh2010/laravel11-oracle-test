<?php

namespace App\Http\Requests\Transaction;

use App\Models\HIS\Fund;
use App\Models\HIS\PayForm;
use App\Models\HIS\SereServ;
use App\Models\View\TreatmentFeeDetailVView;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class CreateTransactionThanhToanRequest extends FormRequest
{
    protected $payForm;
    protected $payForm06;
    protected $payForm03;
    protected $treatmentFeeDetailVView;
    protected $fund;
    protected $sereServ;
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
        $this->sereServ = new SereServ();
        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
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

        return [
            'amount' => [
                'required',
                'numeric',
                'regex:/^\d{1,15}(\.\d{1,6})?$/',
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
            // 'repay_reason_id' => [
            //     'nullable',
            //     'integer',
            //     Rule::exists('App\Models\HIS\RepayReason', 'id')
            //         ->where(function ($query) {
            //             $query = $query
            //                 ->where(DB::connection('oracle_his')->raw("is_active"), 1);
            //         }),
            // ],
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
                        $query->where(function ($sub) {
                            $sub->where('is_active', '!=', 0)
                                ->orWhere('is_hein_approval', '!=', 0);
                        });
                    }),
            ],
            'description' =>        'nullable|string|max:2000',
            'swipe_amount' =>       'required_if:pay_form_id,' . $this->payForm06 . '|lte:amount|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'transfer_amount' =>    'required_if:pay_form_id,' . $this->payForm03 . '|lte:amount|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'transaction_time' =>   'required|integer|regex:/^\d{14}$/',

            'exemption' => [
                'nullable',
                'numeric',
                'regex:/^\d{1,15}(\.\d{1,6})?$/',
                'min:1',
            ],
            'exemption_reason' =>             'nullable|string|max:2000',

            'buyer_name' =>             'nullable|string|max:200',
            'buyer_tax_code' =>         'nullable|string|max:20',
            'buyer_account_number' =>   'nullable|string|max:500',
            'buyer_organization' =>     'nullable|string|max:500',
            'buyer_address' =>          'nullable|string|max:500',
            'buyer_phone' =>            'nullable|string|max:20',

            'bill_funds' =>             'nullable|array',

            'buyer_work_place_id' => [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\WorkPlace', 'id')
                    ->where(function ($query) {
                        $query = $query
                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                    }),
            ],

            'sere_servs' =>             'required|array',

            'kc_amount' => [
                'nullable',
                'numeric',
                'regex:/^\d{1,15}(\.\d{1,6})?$/',
                'min:1',
            ],
        ];
    }
    public function messages()
    {
        return [
            'amount.required'      => config('keywords')['transaction_thanh_toan']['amount'] . config('keywords')['error']['required'],
            'amount.numeric'       => config('keywords')['transaction_thanh_toan']['amount'] . config('keywords')['error']['numeric'],
            'amount.regex'         => config('keywords')['transaction_thanh_toan']['amount'] . config('keywords')['error']['regex_21_6'],
            'amount.min'           => config('keywords')['transaction_thanh_toan']['amount'] . config('keywords')['error']['integer_min'],

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
            'treatment_id.exists'        => config('keywords')['transaction_thanh_toan']['treatment_id'] . ' không tồn tại hoặc đang bị khóa viện phí và chưa duyệt BHYT!',

            'description.string'        => config('keywords')['transaction_thanh_toan']['description'] . config('keywords')['error']['string'],
            'description.max'           => config('keywords')['transaction_thanh_toan']['description'] . config('keywords')['error']['string_max'],

            'swipe_amount.required_if'   => config('keywords')['transaction_thanh_toan']['swipe_amount'] . ' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Quẹt thẻ',
            'swipe_amount.numeric'       => config('keywords')['transaction_thanh_toan']['swipe_amount'] . config('keywords')['error']['numeric'],
            'swipe_amount.regex'         => config('keywords')['transaction_thanh_toan']['swipe_amount'] . config('keywords')['error']['regex_19_4'],
            'swipe_amount.min'           => config('keywords')['transaction_thanh_toan']['swipe_amount'] . config('keywords')['error']['integer_min'],
            'swipe_amount.lte'           => config('keywords')['transaction_thanh_toan']['swipe_amount'] . ' phải bé hơn hoặc bằng ' . config('keywords')['transaction_tam_ung']['amount'],

            'transfer_amount.required_if'   => config('keywords')['transaction_thanh_toan']['transfer_amount'] . ' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Chuyển khoản',
            'transfer_amount.numeric'       => config('keywords')['transaction_thanh_toan']['transfer_amount'] . config('keywords')['error']['numeric'],
            'transfer_amount.regex'         => config('keywords')['transaction_thanh_toan']['transfer_amount'] . config('keywords')['error']['regex_19_4'],
            'transfer_amount.min'           => config('keywords')['transaction_thanh_toan']['transfer_amount'] . config('keywords')['error']['integer_min'],
            'transfer_amount.lte'           => config('keywords')['transaction_thanh_toan']['transfer_amount'] . ' phải bé hơn hoặc bằng ' . config('keywords')['transaction_tam_ung']['amount'],

            'transaction_time.required'           => config('keywords')['transaction_thanh_toan']['transaction_time'] . config('keywords')['error']['required'],
            'transaction_time.integer'            => config('keywords')['transaction_thanh_toan']['transaction_time'] . config('keywords')['error']['integer'],
            'transaction_time.regex'              => config('keywords')['transaction_thanh_toan']['transaction_time'] . config('keywords')['error']['regex_ymdhis'],

            'exemption.numeric'       => config('keywords')['transaction_thanh_toan']['exemption'] . config('keywords')['error']['numeric'],
            'exemption.regex'         => config('keywords')['transaction_thanh_toan']['exemption'] . config('keywords')['error']['regex_21_6'],
            'exemption.min'           => config('keywords')['transaction_thanh_toan']['exemption'] . config('keywords')['error']['integer_min'],

            'exemption_reason.string'        => config('keywords')['transaction_thanh_toan']['exemption_reason'] . config('keywords')['error']['string'],
            'exemption_reason.max'           => config('keywords')['transaction_thanh_toan']['exemption_reason'] . config('keywords')['error']['string_max'],

            'buyer_name.string'        => config('keywords')['transaction_thanh_toan']['buyer_name'] . config('keywords')['error']['string'],
            'buyer_name.max'           => config('keywords')['transaction_thanh_toan']['buyer_name'] . config('keywords')['error']['string_max'],

            'buyer_tax_code.string'        => config('keywords')['transaction_thanh_toan']['buyer_tax_code'] . config('keywords')['error']['string'],
            'buyer_tax_code.max'           => config('keywords')['transaction_thanh_toan']['buyer_tax_code'] . config('keywords')['error']['string_max'],

            'buyer_account_number.string'        => config('keywords')['transaction_thanh_toan']['buyer_account_number'] . config('keywords')['error']['string'],
            'buyer_account_number.max'           => config('keywords')['transaction_thanh_toan']['buyer_account_number'] . config('keywords')['error']['string_max'],

            'buyer_organization.string'        => config('keywords')['transaction_thanh_toan']['buyer_organization'] . config('keywords')['error']['string'],
            'buyer_organization.max'           => config('keywords')['transaction_thanh_toan']['buyer_organization'] . config('keywords')['error']['string_max'],

            'buyer_address.string'        => config('keywords')['transaction_thanh_toan']['buyer_address'] . config('keywords')['error']['string'],
            'buyer_address.max'           => config('keywords')['transaction_thanh_toan']['buyer_address'] . config('keywords')['error']['string_max'],

            'buyer_phone.string'        => config('keywords')['transaction_thanh_toan']['buyer_phone'] . config('keywords')['error']['string'],
            'buyer_phone.max'           => config('keywords')['transaction_thanh_toan']['buyer_phone'] . config('keywords')['error']['string_max'],

            'buyer_work_place_id.integer'       => config('keywords')['transaction_thanh_toan']['buyer_work_place_id'] . config('keywords')['error']['integer'],
            'buyer_work_place_id.exists'        => config('keywords')['transaction_thanh_toan']['buyer_work_place_id'] . config('keywords')['error']['exists'],

            'bill_funds.array'        => config('keywords')['transaction_thanh_toan']['bill_funds'] . config('keywords')['error']['array'],

            'sere_servs.required'      => config('keywords')['transaction_thanh_toan']['sere_servs'] . config('keywords')['error']['required'],
            'sere_servs.array'        => config('keywords')['transaction_thanh_toan']['sere_servs'] . config('keywords')['error']['array'],

            'kc_amount.numeric'       => config('keywords')['transaction_thanh_toan']['kc_amount'] . config('keywords')['error']['numeric'],
            'kc_amount.regex'         => config('keywords')['transaction_thanh_toan']['kc_amount'] . config('keywords')['error']['regex_21_6'],
            'kc_amount.min'           => config('keywords')['transaction_thanh_toan']['kc_amount'] . config('keywords')['error']['integer_min'],
        ];
    }


    protected function prepareForValidation()
    {
        if ($this->has('bill_funds') && $this->bill_funds != null) {
            $this->merge([
                'bill_funds_list' => (is_array($this->bill_funds)) ? $this->bill_funds : json_decode($this->bill_funds, true) ?? [],
            ]);
        }

        if ($this->has('sere_servs') && $this->sere_servs != null) {
            $this->merge([
                'sere_servs_list' => (is_array($this->sere_servs)) ? $this->sere_servs : json_decode($this->sere_servs, true) ?? [],
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $totalAmountBillFund = 0;
            if ($this->has('bill_funds_list') && ($this->bill_funds_list[0] ?? 0 != null)) {
                foreach ($this->bill_funds_list as $item) {
                    $totalAmountBillFund = $totalAmountBillFund + $item['amount'];
                    if (!is_numeric($item['amount'])) {
                        $validator->errors()->add('bill_funds', 'Số tiền hỗ trợ với ID quỹ = ' . $item['fund_id'] . config('keywords')['error']['integer']);
                    }
                    if ($item['amount'] < 0) {
                        $validator->errors()->add('bill_funds', 'Số tiền hỗ trợ với ID quỹ = ' . $item['fund_id'] . ' phải lớn hơn 0!');
                    }
                    if (!preg_match('/^\d{1,15}(\.\d{1,4})?$/', $item['amount'])) {
                        $validator->errors()->add('bill_funds', 'Số tiền hỗ trợ với ID quỹ = ' . $item['fund_id'] . config('keywords')['error']['regex_19_4'],);
                    }
                    // Kiểm tra fund_id có tồn tại trong DB không
                    $exists = $this->fund
                        ->where('id', $item['fund_id'])
                        ->where('is_active', 1)
                        ->exists();
                    if (!$exists) {
                        $validator->errors()->add('bill_funds', 'ID quỹ = ' . $item['fund_id'] . ' không tồn tại hoặc đang bị tạm khóa!');
                    }
                }
                if ((($this->exemption ?? 0) + $totalAmountBillFund) > $this->amount) {
                    $validator->errors()->add('amount', 'Tổng tiền chiết khấu, tiền các quỹ tối đa = tiền thanh toán!');
                    $validator->errors()->add('exemption', 'Tổng tiền chiết khấu, tiền các quỹ tối đa = tiền thanh toán!');
                    $validator->errors()->add('bill_funds', 'Tổng tiền chiết khấu, tiền các quỹ tối đa = tiền thanh toán!');
                }
            }

            if ($this->has('sere_servs_list') && ($this->sere_servs_list[0] != null)) {
                foreach ($this->sere_servs_list as $item) {
                    // Kiểm tra sere_serv_id có tồn tại trong DB không
                    $exists = $this->sereServ
                        ->where('his_sere_serv.id', $item['id'])
                        ->where('his_sere_serv.tdl_treatment_id', $this->treatment_id)
                        ->where('his_sere_serv.is_active', 1)
                        ->where('his_sere_serv.is_delete', 0)
                        ->where(function ($query) {
                            $query->where('his_sere_serv.is_no_execute', 0)
                                ->orWhereNull('his_sere_serv.is_no_execute');
                        })
                        ->where(function ($query) {
                            $query->where('his_sere_serv.is_no_pay', 0)
                                ->orWhereNull('his_sere_serv.is_no_pay');
                        })
                        ->whereDoesntHave('sereServBills', function ($q) {
                            $q->where('his_sere_serv_bill.is_delete', 0)
                                ->where(function ($q2) {
                                    $q2->whereNull('his_sere_serv_bill.is_cancel')
                                        ->orWhere('his_sere_serv_bill.is_cancel', 0);
                                });
                        })
                        ->exists();
                    if (!$exists) {
                        $validator->errors()->add('bill_funds', 'ID SereServ = ' . $item['id'] . ' không tồn tại, đang bị tạm khóa, không thực hiện, không thanh toán, đã thanh toán hoặc không thuộc về hồ sơ này!');
                    }
                    if (!preg_match('/^\d{1,15}(\.\d{1,6})?$/', $item['amount'])) {
                        $validator->errors()->add('sere_servs', 'ID SereServ = ' . $item['id'].' số tiền thanh toán dịch vụ' . config('keywords')['error']['regex_21_6'],);
                    }
                    $virTotalPatientPrice = $this->sereServ
                    ->find($item['id']??0)->vir_total_patient_price??0;
                    // Nếu tiền thanh toán dv không khớp với tiền bệnh nhân phải trả
                    if($virTotalPatientPrice != $item['amount']){
                        $validator->errors()->add('sere_servs', 'ID SereServ = ' . $item['id'] . ' tiền thanh toán dịch vụ = '.$item['amount'].' không khớp với tiền bệnh nhân phải trả = '.$virTotalPatientPrice.'!');
                    }

                    $totalAmountBill = array_sum(array_column($this->sere_servs_list, 'amount')) ?? 0;

                    if ($this->amount != $totalAmountBill) {
                        $validator->errors()->add('amount', config('keywords')['transaction_thanh_toan']['amount'] . ' = ' . $this->amount . ' không khớp với tổng số tiền dịch vụ đã chọn mà bệnh nhân cần thanh toán = ' . $totalAmountBill . '!');
                    }
                }
            }

            // Kiểm tra tiền kết chuyển có = hiện dư (tạm ứng + tạm ứng dv - hoàn ứng) không
            $this->treatmentFeeDetailVView = new TreatmentFeeDetailVView();
            $dataTreatmentFee = $this->treatmentFeeDetailVView
                ->select(
                    'xa_v_his_treatment_fee_detail.*'
                )
                ->addSelect(DB::connection('oracle_his')->raw('((total_deposit_amount - total_service_deposit_amount) + total_service_deposit_amount - total_repay_amount) as hien_du')) // hiện dư = (tạm ứng + tạm ứng dv - hoàn ứng)
                ->find($this->treatment_id ?? 0);

            // nếu có gửi kết chuyển thì mới check    
            if($this->kc_amount){
                if ($this->kc_amount != $dataTreatmentFee?->hien_du ?? 0) {
                    $validator->errors()->add('kc_amount', config('keywords')['transaction_thanh_toan']['kc_amount'] . ' = ' . $this->kc_amount . ' không khớp với tiền hiện dư của bệnh nhân là ' . ($dataTreatmentFee->hien_du ?? 0) . ' !');
                }
            }
            // $totalAmountBill = $this->sereServ->whereIn('id', $this->sere_servs)->sum('vir_total_patient_price') ?? 0;
            // if ($totalAmountBill != $this->amount) {
            //     $validator->errors()->add('amount', 'Tiền thanh toán không khớp với tổng tiền thanh toán của các dịch vụ đã chọn!');
            // }
        });
    }

    public function failedValidation(Validator $validator)
    {
        // $messages = implode(' ', $validator->errors()->all());
        // dd($messages);
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Dữ liệu không hợp lệ!',
            'data'      => $validator->errors()
        ], 422));
    }
}
