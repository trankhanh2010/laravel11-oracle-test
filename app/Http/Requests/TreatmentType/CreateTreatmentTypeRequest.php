<?php

namespace App\Http\Requests\TreatmentType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class CreateTreatmentTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
        return [
            'treatment_type_code' =>                'required|string|max:2|unique:App\Models\HIS\TreatmentType,treatment_type_code',
            'treatment_type_name' =>                'required|string|max:100',
            'hein_treatment_type_code' =>           'required|string|max:2|in:KH,DT',
            'end_code_prefix' =>                    'nullable|string|max:5',
            'required_service_id' =>                'nullable|integer|exists:App\Models\HIS\Service,id',
            'is_allow_reception' =>                 'required|integer|in:0,1',
            'is_not_allow_unpause' =>               'nullable|integer|in:0,1',
            'allow_hospitalize_when_pres' =>        'nullable|integer|in:0,1',
            'is_not_allow_share_bed' =>             'nullable|integer|in:0,1',
            'is_required_service_bed' =>            'nullable|integer|in:0,1',
            'is_dis_service_repay' =>               'nullable|integer|in:0,1',
            'dis_service_deposit_option' =>         'nullable|integer|in:1,2',
            'dis_deposit_option' =>                 'nullable|integer|in:1,2',
            'unsign_doc_finish_option' =>           'nullable|integer|in:1,2',
            'trans_time_out_time_option' =>         'nullable|integer|in:1,2',
            'fee_debt_option' =>                    'nullable|integer|in:1,2',
        ];
    }
    public function messages()
    {
        return [
            'treatment_type_code.required'  => config('keywords')['treatment_type']['treatment_type_code'].' không được bỏ trống!',
            'treatment_type_code.string'    => config('keywords')['treatment_type']['treatment_type_code'].' phải là chuỗi string!',
            'treatment_type_code.max'       => config('keywords')['treatment_type']['treatment_type_code'].' tối đa 2 kí tự!',            
            'treatment_type_code.unique'    => config('keywords')['treatment_type']['treatment_type_code'].' = '.$this->treatment_type_code.' đã tồn tại!',

            'treatment_type_name.required'  => config('keywords')['treatment_type']['treatment_type_name'].' không được bỏ trống!',
            'treatment_type_name.string'    => config('keywords')['treatment_type']['treatment_type_name'].' phải là chuỗi string!',
            'treatment_type_name.max'       => config('keywords')['treatment_type']['treatment_type_name'].' tối đa 100 kí tự!',   
            
            'hein_treatment_type_code.required'   => config('keywords')['treatment_type']['hein_treatment_type_code'].' không được bỏ trống!',            
            'hein_treatment_type_code.string'     => config('keywords')['treatment_type']['hein_treatment_type_code'].' phải là chuỗi string!',
            'hein_treatment_type_code.max'        => config('keywords')['treatment_type']['hein_treatment_type_code'].' tối đa 2 kí tự!',            
            'hein_treatment_type_code.in'         => config('keywords')['treatment_type']['hein_treatment_type_code'].' phải là KH hoặc DT!',            

            'end_code_prefix.string'  => config('keywords')['treatment_type']['end_code_prefix'].' phải là chuỗi string!',
            'end_code_prefix.max'     => config('keywords')['treatment_type']['end_code_prefix'].' tối đa 5 kí tự!',      

            'required_service_id.integer'     => config('keywords')['treatment_type']['required_service_id'].' phải là số nguyên!',
            'required_service_id.exists'      => config('keywords')['treatment_type']['required_service_id'].' không tồn tại!', 

            'is_allow_reception.required'       => config('keywords')['treatment_type']['is_allow_reception'].' không được bỏ trống!',            
            'is_allow_reception.integer'        => config('keywords')['treatment_type']['is_allow_reception'].' phải là số nguyên!',
            'is_allow_reception.in'             => config('keywords')['treatment_type']['is_allow_reception'].' phải là 0 hoặc 1!', 

            'is_not_allow_unpause.integer'        => config('keywords')['treatment_type']['is_not_allow_unpause'].' phải là số nguyên!',
            'is_not_allow_unpause.in'             => config('keywords')['treatment_type']['is_not_allow_unpause'].' phải là 0 hoặc 1!', 
            
            'allow_hospitalize_when_pres.integer'        => config('keywords')['treatment_type']['allow_hospitalize_when_pres'].' phải là số nguyên!',
            'allow_hospitalize_when_pres.in'             => config('keywords')['treatment_type']['allow_hospitalize_when_pres'].' phải là 0 hoặc 1!', 

            'is_not_allow_share_bed.integer'        => config('keywords')['treatment_type']['is_not_allow_share_bed'].' phải là số nguyên!',
            'is_not_allow_share_bed.in'             => config('keywords')['treatment_type']['is_not_allow_share_bed'].' phải là 0 hoặc 1!', 

            'is_required_service_bed.integer'        => config('keywords')['treatment_type']['is_required_service_bed'].' phải là số nguyên!',
            'is_required_service_bed.in'             => config('keywords')['treatment_type']['is_required_service_bed'].' phải là 0 hoặc 1!', 

            'is_dis_service_repay.integer'        => config('keywords')['treatment_type']['is_dis_service_repay'].' phải là số nguyên!',
            'is_dis_service_repay.in'             => config('keywords')['treatment_type']['is_dis_service_repay'].' phải là 0 hoặc 1!', 
 
            'dis_service_deposit_option.integer'        => config('keywords')['treatment_type']['dis_service_deposit_option'].' phải là số nguyên!',
            'dis_service_deposit_option.in'             => config('keywords')['treatment_type']['dis_service_deposit_option'].' phải là 1 hoặc 2!', 

            'dis_deposit_option.integer'        => config('keywords')['treatment_type']['dis_deposit_option'].' phải là số nguyên!',
            'dis_deposit_option.in'             => config('keywords')['treatment_type']['dis_deposit_option'].' phải là 1 hoặc 2!', 

            'unsign_doc_finish_option.integer'        => config('keywords')['treatment_type']['unsign_doc_finish_option'].' phải là số nguyên!',
            'unsign_doc_finish_option.in'             => config('keywords')['treatment_type']['unsign_doc_finish_option'].' phải là 1 hoặc 2!', 

            'trans_time_out_time_option.integer'        => config('keywords')['treatment_type']['trans_time_out_time_option'].' phải là số nguyên!',
            'trans_time_out_time_option.in'             => config('keywords')['treatment_type']['trans_time_out_time_option'].' phải là 1 hoặc 2!', 

            'fee_debt_option.integer'        => config('keywords')['treatment_type']['fee_debt_option'].' phải là số nguyên!',
            'fee_debt_option.in'             => config('keywords')['treatment_type']['fee_debt_option'].' phải là 1 hoặc 2!', 
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
