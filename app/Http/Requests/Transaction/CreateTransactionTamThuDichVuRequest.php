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

class CreateTransactionTamThuDichVuRequest extends FormRequest
{
    protected $payForm;
    protected $payForm06;
    protected $payForm03;
    protected $treatmentFeeDetailVView;
    protected $fund;
    protected $sereServ;
    protected $mucHuongBhyt;
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
        $this->treatmentFeeDetailVView = new TreatmentFeeDetailVView();
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
                            ->where(function ($q) {
                                $q->orWhereIn('last_treatment_log_type_code', ['01','04']) 
                                  ->orWhereNull('fee_lock_time');
                            });
                    }),
            ],
            'description' =>        'nullable|string|max:2000',
            'swipe_amount' =>       'required_if:pay_form_id,' . $this->payForm06 . '|lte:amount|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'transfer_amount' =>    'required_if:pay_form_id,' . $this->payForm03 . '|lte:amount|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0',
            'transaction_time' =>   'required|integer|regex:/^\d{14}$/',



            'buyer_name' =>             'nullable|string|max:200',
            'buyer_tax_code' =>         'nullable|string|max:20',
            'buyer_account_number' =>   'nullable|string|max:500',
            'buyer_organization' =>     'nullable|string|max:500',
            'buyer_address' =>          'nullable|string|max:500',
            'buyer_phone' =>            'nullable|string|max:20',

            'sere_servs' =>             'required|array',
        ];
    }
    public function messages()
    {
        return [
            'amount.required'      => config('keywords')['transaction_tam_thu_dich_vu']['amount'] . config('keywords')['error']['required'],
            'amount.numeric'       => config('keywords')['transaction_tam_thu_dich_vu']['amount'] . config('keywords')['error']['numeric'],
            'amount.regex'         => config('keywords')['transaction_tam_thu_dich_vu']['amount'] . config('keywords')['error']['regex_21_6'],
            'amount.min'           => config('keywords')['transaction_tam_thu_dich_vu']['amount'] . config('keywords')['error']['integer_min'],

            'account_book_id.required'      => config('keywords')['transaction_tam_thu_dich_vu']['account_book_id'] . config('keywords')['error']['required'],
            'account_book_id.integer'       => config('keywords')['transaction_tam_thu_dich_vu']['account_book_id'] . config('keywords')['error']['integer'],
            'account_book_id.exists'        => config('keywords')['transaction_tam_thu_dich_vu']['account_book_id'] . config('keywords')['error']['exists'],

            'pay_form_id.required'      => config('keywords')['transaction_tam_thu_dich_vu']['pay_form_id'] . config('keywords')['error']['required'],
            'pay_form_id.integer'       => config('keywords')['transaction_tam_thu_dich_vu']['pay_form_id'] . config('keywords')['error']['integer'],
            'pay_form_id.exists'        => config('keywords')['transaction_tam_thu_dich_vu']['pay_form_id'] . config('keywords')['error']['exists'],

            'cashier_room_id.required'      => config('keywords')['transaction_tam_thu_dich_vu']['cashier_room_id'] . config('keywords')['error']['required'],
            'cashier_room_id.integer'       => config('keywords')['transaction_tam_thu_dich_vu']['cashier_room_id'] . config('keywords')['error']['integer'],
            'cashier_room_id.exists'        => config('keywords')['transaction_tam_thu_dich_vu']['cashier_room_id'] . config('keywords')['error']['exists'],

            'treatment_id.required'      => config('keywords')['transaction_tam_thu_dich_vu']['treatment_id'] . config('keywords')['error']['required'],
            'treatment_id.integer'       => config('keywords')['transaction_tam_thu_dich_vu']['treatment_id'] . config('keywords')['error']['integer'],
            'treatment_id.exists'        => config('keywords')['transaction_tam_thu_dich_vu']['treatment_id'] . config('keywords')['error']['exists'],

            'description.string'        => config('keywords')['transaction_tam_thu_dich_vu']['description'] . config('keywords')['error']['string'],
            'description.max'           => config('keywords')['transaction_tam_thu_dich_vu']['description'] . config('keywords')['error']['string_max'],

            'swipe_amount.required_if'   => config('keywords')['transaction_tam_thu_dich_vu']['swipe_amount'] . ' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Quẹt thẻ',
            'swipe_amount.numeric'       => config('keywords')['transaction_tam_thu_dich_vu']['swipe_amount'] . config('keywords')['error']['numeric'],
            'swipe_amount.regex'         => config('keywords')['transaction_tam_thu_dich_vu']['swipe_amount'] . config('keywords')['error']['regex_19_4'],
            'swipe_amount.min'           => config('keywords')['transaction_tam_thu_dich_vu']['swipe_amount'] . config('keywords')['error']['integer_min'],
            'swipe_amount.lte'           => config('keywords')['transaction_tam_thu_dich_vu']['swipe_amount'] . ' phải bé hơn hoặc bằng ' . config('keywords')['transaction_tam_ung']['amount'],

            'transfer_amount.required_if'   => config('keywords')['transaction_tam_thu_dich_vu']['transfer_amount'] . ' không được bỏ trống nếu hình thức thanh toán là Tiền mặt/Chuyển khoản',
            'transfer_amount.numeric'       => config('keywords')['transaction_tam_thu_dich_vu']['transfer_amount'] . config('keywords')['error']['numeric'],
            'transfer_amount.regex'         => config('keywords')['transaction_tam_thu_dich_vu']['transfer_amount'] . config('keywords')['error']['regex_19_4'],
            'transfer_amount.min'           => config('keywords')['transaction_tam_thu_dich_vu']['transfer_amount'] . config('keywords')['error']['integer_min'],
            'transfer_amount.lte'           => config('keywords')['transaction_tam_thu_dich_vu']['transfer_amount'] . ' phải bé hơn hoặc bằng ' . config('keywords')['transaction_tam_ung']['amount'],

            'transaction_time.required'           => config('keywords')['transaction_tam_thu_dich_vu']['transaction_time'] . config('keywords')['error']['required'],
            'transaction_time.integer'            => config('keywords')['transaction_tam_thu_dich_vu']['transaction_time'] . config('keywords')['error']['integer'],
            'transaction_time.regex'              => config('keywords')['transaction_tam_thu_dich_vu']['transaction_time'] . config('keywords')['error']['regex_ymdhis'],

            'buyer_name.string'        => config('keywords')['transaction_tam_thu_dich_vu']['buyer_name'] . config('keywords')['error']['string'],
            'buyer_name.max'           => config('keywords')['transaction_tam_thu_dich_vu']['buyer_name'] . config('keywords')['error']['string_max'],

            'buyer_tax_code.string'        => config('keywords')['transaction_tam_thu_dich_vu']['buyer_tax_code'] . config('keywords')['error']['string'],
            'buyer_tax_code.max'           => config('keywords')['transaction_tam_thu_dich_vu']['buyer_tax_code'] . config('keywords')['error']['string_max'],

            'buyer_account_number.string'        => config('keywords')['transaction_tam_thu_dich_vu']['buyer_account_number'] . config('keywords')['error']['string'],
            'buyer_account_number.max'           => config('keywords')['transaction_tam_thu_dich_vu']['buyer_account_number'] . config('keywords')['error']['string_max'],

            'buyer_organization.string'        => config('keywords')['transaction_tam_thu_dich_vu']['buyer_organization'] . config('keywords')['error']['string'],
            'buyer_organization.max'           => config('keywords')['transaction_tam_thu_dich_vu']['buyer_organization'] . config('keywords')['error']['string_max'],

            'buyer_address.string'        => config('keywords')['transaction_tam_thu_dich_vu']['buyer_address'] . config('keywords')['error']['string'],
            'buyer_address.max'           => config('keywords')['transaction_tam_thu_dich_vu']['buyer_address'] . config('keywords')['error']['string_max'],

            'buyer_phone.string'        => config('keywords')['transaction_tam_thu_dich_vu']['buyer_phone'] . config('keywords')['error']['string'],
            'buyer_phone.max'           => config('keywords')['transaction_tam_thu_dich_vu']['buyer_phone'] . config('keywords')['error']['string_max'],

            'sere_servs.required'      => config('keywords')['transaction_tam_thu_dich_vu']['sere_servs'] . config('keywords')['error']['required'],
            'sere_servs.array'        => config('keywords')['transaction_tam_thu_dich_vu']['sere_servs'] . config('keywords')['error']['array'],

        ];
    }


    protected function prepareForValidation()
    {
        if ($this->has('sere_servs') && $this->sere_servs != null) {
            $this->merge([
                'sere_servs_list' => (is_array($this->sere_servs)) ? $this->sere_servs : json_decode($this->sere_servs, true) ?? [],
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if($this->treatment_id){   
                $dataFee = $this->treatmentFeeDetailVView->find($this->treatment_id ?? 0);
                $this->mucHuongBhyt = getMucHuongBHYT($dataFee?->value('tdl_hein_card_number'??''))??0;
            }
            if ($this->has('sere_servs_list') && ($this->sere_servs_list[0] ?? 0 != null)) {
                foreach ($this->sere_servs_list as $item) {
                    // Kiểm tra sere_serv_id có tồn tại trong DB không, có tạm thu dv chưa
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
                        ->whereDoesntHave('sereServDeposits', function ($q) {
                            $q->where('his_sere_serv_deposit.is_delete', 0)
                                ->where(function ($q2) {
                                    $q2->whereNull('his_sere_serv_deposit.is_cancel')
                                        ->orWhere('his_sere_serv_deposit.is_cancel', 0);
                                });
                        })
                        ->exists();
                    if (!$exists) {
                        $validator->errors()->add('sere_serv_ids', 'ID SereServ = ' . $item['id'] . ' không tồn tại, không thực hiện, đã tạm thu dịch vụ hoặc không thuộc về hồ sơ này!');
                    }
                    if (!preg_match('/^\d{1,15}(\.\d{1,6})?$/', $item['amount'])) {
                        $validator->errors()->add('sere_servs', 'ID SereServ = ' . $item['id'].' số tiền tạm thu dịch vụ' . config('keywords')['error']['regex_21_6'],);
                    }
                    $dataSereServ = $this->sereServ
                    ->find($item['id']??0);
                    $virTotalPatientPrice = $dataSereServ->vir_total_patient_price ?? 0;
                    $virTotalHeinPrice =$dataSereServ->vir_total_hein_price ?? 0;

                    $tienKhiTamUngDv = round($virTotalPatientPrice + (1 - $this->mucHuongBhyt) * $virTotalHeinPrice);  // Làm tròn tiền
                    // Nếu tiền thanh toán dv không khớp với tiền bệnh nhân phải trả
                    if($tienKhiTamUngDv != $item['amount']){
                        $validator->errors()->add('sere_servs', 'ID SereServ = ' . $item['id'] . ' tiền tạm ứng dịch vụ = '.$item['amount'].' không khớp với (tiền bệnh nhân phải trả + tiền mức hưởng BHYT) = '.$tienKhiTamUngDv.'!');
                    }
                }

                $totalAmountDeposit = array_sum(array_column($this->sere_servs_list, 'amount')) ?? 0;
                if ($totalAmountDeposit != $this->amount) {
                    $validator->errors()->add('amount', 'Tiền tạm thu = '.$this->amount.' không khớp với tổng tiền tạm thu của các dịch vụ đã chọn = '.$totalAmountDeposit.'!');
                }
            }
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
