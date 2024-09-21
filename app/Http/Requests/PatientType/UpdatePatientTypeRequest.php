<?php

namespace App\Http\Requests\PatientType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class UpdatePatientTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->patient_type)){
            throw new HttpResponseException(returnIdError($this->patient_type));
        }
        return [
            'patient_type_code'  =>             [
                                                    'required',
                                                    'string',
                                                    'max:6',
                                                    Rule::unique('App\Models\HIS\PatientType')->ignore($this->patient_type),
                                                ],    
            'patient_type_name'  =>             'required|string|max:100',       
            'description' =>                    'nullable|string|max:500',   
            'priority'   =>                     'nullable|integer',

            'base_patient_type_id' =>  [
                                                'nullable',
                                                'integer',
                                                Rule::exists('App\Models\HIS\PatientType', 'id')
                                                ->where(function ($query) {
                                                    $query = $query
                                                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                }),
                                                'not_in:'.$this->patient_type
                                            ], 
            'other_pay_source_ids'  =>          'nullable|string|max:100',   
            'treatment_type_ids'  =>            'nullable|string|max:500',   
            'is_copayment' =>                   'nullable|integer|in:0,1',

            'is_not_use_for_patient' =>         'nullable|integer|in:0,1',
            'is_not_for_kiosk' =>               'nullable|integer|in:0,1',
            'is_addition_required' =>           'nullable|integer|in:0,1',
            'is_addition' =>                    'nullable|integer|in:0,1',

            'is_not_service_bill' =>            'nullable|integer|in:0,1',
            'is_check_fee_when_assign' =>       'nullable|integer|in:0,1',
            'is_check_finish_cls_when_pres'=>   'nullable|integer|in:0,1',
            'is_check_fee_when_pres' =>         'nullable|integer|in:0,1',

            'is_not_edit_assign_service' =>     'nullable|integer|in:0,1',
            'is_showing_out_stock_by_def' =>    'nullable|integer|in:0,1',
            'is_not_check_fee_when_exp_pres' => 'nullable|integer|in:0,1',
            'is_for_sale_exp' =>                'nullable|integer|in:0,1',

            'must_be_guaranteed' =>             'nullable|integer|in:0,1',
            'is_ration' =>                      'nullable|integer|in:0,1',

            'is_active' =>                      'required|integer|in:0,1'
        ];
    }
    public function messages()
    {
        return [
            'patient_type_code.required'    => config('keywords')['patient_type']['patient_type_code'].config('keywords')['error']['required'],
            'patient_type_code.string'      => config('keywords')['patient_type']['patient_type_code'].config('keywords')['error']['string'],
            'patient_type_code.max'         => config('keywords')['patient_type']['patient_type_code'].config('keywords')['error']['string_max'],
            'patient_type_code.unique'      => config('keywords')['patient_type']['patient_type_code'].config('keywords')['error']['unique'],

            'patient_type_name.required'    => config('keywords')['patient_type']['patient_type_name'].config('keywords')['error']['required'],
            'patient_type_name.string'      => config('keywords')['patient_type']['patient_type_name'].config('keywords')['error']['string'],
            'patient_type_name.max'         => config('keywords')['patient_type']['patient_type_name'].config('keywords')['error']['string_max'],

            'description.string'      => config('keywords')['patient_type']['description'].config('keywords')['error']['string'],
            'description.max'         => config('keywords')['patient_type']['description'].config('keywords')['error']['string_max'],

            'priority.integer'      => config('keywords')['patient_type']['priority'].config('keywords')['error']['integer'],


            'base_patient_type_id.required'    => config('keywords')['patient_type']['base_patient_type_id'].config('keywords')['error']['required'],
            'base_patient_type_id.integer'     => config('keywords')['patient_type']['base_patient_type_id'].config('keywords')['error']['integer'],
            'base_patient_type_id.exists'      => config('keywords')['patient_type']['base_patient_type_id'].config('keywords')['error']['exists'],
            'base_patient_type_id.not_in'      => config('keywords')['error']['parent_not_in_id'], 

            'other_pay_source_ids.string'      => config('keywords')['patient_type']['other_pay_source_ids'].config('keywords')['error']['string'],
            'other_pay_source_ids.max'         => config('keywords')['patient_type']['other_pay_source_ids'].config('keywords')['error']['string_max'],

            'treatment_type_ids.string'      => config('keywords')['patient_type']['treatment_type_ids'].config('keywords')['error']['string'],
            'treatment_type_ids.max'         => config('keywords')['patient_type']['treatment_type_ids'].config('keywords')['error']['string_max'],

            'is_copayment.integer'      => config('keywords')['patient_type']['is_copayment'].config('keywords')['error']['integer'],
            'is_copayment.in'           => config('keywords')['patient_type']['is_copayment'].config('keywords')['error']['in'],

            
            'is_not_use_for_patient.integer'      => config('keywords')['patient_type']['is_not_use_for_patient'].config('keywords')['error']['integer'],
            'is_not_use_for_patient.in'           => config('keywords')['patient_type']['is_not_use_for_patient'].config('keywords')['error']['in'],

            'is_not_for_kiosk.integer'      => config('keywords')['patient_type']['is_not_for_kiosk'].config('keywords')['error']['integer'],
            'is_not_for_kiosk.in'           => config('keywords')['patient_type']['is_not_for_kiosk'].config('keywords')['error']['in'],

            'is_addition_required.integer'      => config('keywords')['patient_type']['is_addition_required'].config('keywords')['error']['integer'],
            'is_addition_required.in'           => config('keywords')['patient_type']['is_addition_required'].config('keywords')['error']['in'],

            'is_addition.integer'      => config('keywords')['patient_type']['is_addition'].config('keywords')['error']['integer'],
            'is_addition.in'           => config('keywords')['patient_type']['is_addition'].config('keywords')['error']['in'],


            'is_not_service_bill.integer'      => config('keywords')['patient_type']['is_not_service_bill'].config('keywords')['error']['integer'],
            'is_not_service_bill.in'           => config('keywords')['patient_type']['is_not_service_bill'].config('keywords')['error']['in'],

            'is_check_fee_when_assign.integer'      => config('keywords')['patient_type']['is_check_fee_when_assign'].config('keywords')['error']['integer'],
            'is_check_fee_when_assign.in'           => config('keywords')['patient_type']['is_check_fee_when_assign'].config('keywords')['error']['in'],

            'is_check_finish_cls_when_pres.integer'      => config('keywords')['patient_type']['is_check_finish_cls_when_pres'].config('keywords')['error']['integer'],
            'is_check_finish_cls_when_pres.in'           => config('keywords')['patient_type']['is_check_finish_cls_when_pres'].config('keywords')['error']['in'],

            'is_check_fee_when_pres.integer'      => config('keywords')['patient_type']['is_check_fee_when_pres'].config('keywords')['error']['integer'],
            'is_check_fee_when_pres.in'           => config('keywords')['patient_type']['is_check_fee_when_pres'].config('keywords')['error']['in'],


            'is_not_edit_assign_service.integer'      => config('keywords')['patient_type']['is_not_edit_assign_service'].config('keywords')['error']['integer'],
            'is_not_edit_assign_service.in'           => config('keywords')['patient_type']['is_not_edit_assign_service'].config('keywords')['error']['in'],

            'is_showing_out_stock_by_def.integer'      => config('keywords')['patient_type']['is_showing_out_stock_by_def'].config('keywords')['error']['integer'],
            'is_showing_out_stock_by_def.in'           => config('keywords')['patient_type']['is_showing_out_stock_by_def'].config('keywords')['error']['in'],

            'is_not_check_fee_when_exp_pres.integer'      => config('keywords')['patient_type']['is_not_check_fee_when_exp_pres'].config('keywords')['error']['integer'],
            'is_not_check_fee_when_exp_pres.in'           => config('keywords')['patient_type']['is_not_check_fee_when_exp_pres'].config('keywords')['error']['in'],

            'is_for_sale_exp.integer'      => config('keywords')['patient_type']['is_for_sale_exp'].config('keywords')['error']['integer'],
            'is_for_sale_exp.in'           => config('keywords')['patient_type']['is_for_sale_exp'].config('keywords')['error']['in'],

            'is_ration.integer'      => config('keywords')['patient_type']['is_ration'].config('keywords')['error']['integer'],
            'is_ration.in'           => config('keywords')['patient_type']['is_ration'].config('keywords')['error']['in'],

            'must_be_guaranteed.integer'      => config('keywords')['patient_type']['must_be_guaranteed'].config('keywords')['error']['integer'],
            'must_be_guaranteed.in'           => config('keywords')['patient_type']['must_be_guaranteed'].config('keywords')['error']['in'],

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('other_pay_source_ids')) {
            $this->merge([
                'other_pay_source_ids_list' => explode(',', $this->other_pay_source_ids),
            ]);
        }
        if ($this->has('treatment_type_ids')) {
            $this->merge([
                'treatment_type_ids_list' => explode(',', $this->treatment_type_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('other_pay_source_ids_list') && ($this->other_pay_source_ids_list[0] != null)) {
                foreach ($this->other_pay_source_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\OtherPaySource::find($id)) {
                        $validator->errors()->add('other_pay_source_ids', 'Nguồn chi trả khác với id = ' . $id . ' trong danh sách không tồn tại!');
                    }
                }
            }
            if ($this->has('treatment_type_ids_list') && ($this->treatment_type_ids_list[0] != null)) {
                foreach ($this->treatment_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\PatientType::find($id)) {
                        $validator->errors()->add('treatment_type_ids', 'Đối tượng cha với id = ' . $id . ' trong danh sách không tồn tại!');
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
