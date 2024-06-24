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
            'treatment_type_code.required'  => config('keywords')['treatment_type']['treatment_type_code'].config('keywords')['error']['required'],
            'treatment_type_code.string'    => config('keywords')['treatment_type']['treatment_type_code'].config('keywords')['error']['string'],
            'treatment_type_code.max'       => config('keywords')['treatment_type']['treatment_type_code'].config('keywords')['error']['string_max'],            
            'treatment_type_code.unique'    => config('keywords')['treatment_type']['treatment_type_code'].config('keywords')['error']['unique'],

            'treatment_type_name.required'  => config('keywords')['treatment_type']['treatment_type_name'].config('keywords')['error']['required'],
            'treatment_type_name.string'    => config('keywords')['treatment_type']['treatment_type_name'].config('keywords')['error']['string'],
            'treatment_type_name.max'       => config('keywords')['treatment_type']['treatment_type_name'].config('keywords')['error']['string_max'],   
            
            'hein_treatment_type_code.required'   => config('keywords')['treatment_type']['hein_treatment_type_code'].config('keywords')['error']['required'],            
            'hein_treatment_type_code.string'     => config('keywords')['treatment_type']['hein_treatment_type_code'].config('keywords')['error']['string'],
            'hein_treatment_type_code.max'        => config('keywords')['treatment_type']['hein_treatment_type_code'].config('keywords')['error']['string_max'],            
            'hein_treatment_type_code.in'         => config('keywords')['treatment_type']['hein_treatment_type_code'].config('keywords')['error']['in'],            

            'end_code_prefix.string'  => config('keywords')['treatment_type']['end_code_prefix'].config('keywords')['error']['string'],
            'end_code_prefix.max'     => config('keywords')['treatment_type']['end_code_prefix'].config('keywords')['error']['string_max'],      

            'required_service_id.integer'     => config('keywords')['treatment_type']['required_service_id'].config('keywords')['error']['integer'],
            'required_service_id.exists'      => config('keywords')['treatment_type']['required_service_id'].config('keywords')['error']['exists'], 

            'is_allow_reception.required'       => config('keywords')['treatment_type']['is_allow_reception'].config('keywords')['error']['required'],            
            'is_allow_reception.integer'        => config('keywords')['treatment_type']['is_allow_reception'].config('keywords')['error']['integer'],
            'is_allow_reception.in'             => config('keywords')['treatment_type']['is_allow_reception'].config('keywords')['error']['in'], 

            'is_not_allow_unpause.integer'        => config('keywords')['treatment_type']['is_not_allow_unpause'].config('keywords')['error']['integer'],
            'is_not_allow_unpause.in'             => config('keywords')['treatment_type']['is_not_allow_unpause'].config('keywords')['error']['in'], 
            
            'allow_hospitalize_when_pres.integer'        => config('keywords')['treatment_type']['allow_hospitalize_when_pres'].config('keywords')['error']['integer'],
            'allow_hospitalize_when_pres.in'             => config('keywords')['treatment_type']['allow_hospitalize_when_pres'].config('keywords')['error']['in'], 

            'is_not_allow_share_bed.integer'        => config('keywords')['treatment_type']['is_not_allow_share_bed'].config('keywords')['error']['integer'],
            'is_not_allow_share_bed.in'             => config('keywords')['treatment_type']['is_not_allow_share_bed'].config('keywords')['error']['in'], 

            'is_required_service_bed.integer'        => config('keywords')['treatment_type']['is_required_service_bed'].config('keywords')['error']['integer'],
            'is_required_service_bed.in'             => config('keywords')['treatment_type']['is_required_service_bed'].config('keywords')['error']['in'], 

            'is_dis_service_repay.integer'        => config('keywords')['treatment_type']['is_dis_service_repay'].config('keywords')['error']['integer'],
            'is_dis_service_repay.in'             => config('keywords')['treatment_type']['is_dis_service_repay'].config('keywords')['error']['in'], 
 
            'dis_service_deposit_option.integer'        => config('keywords')['treatment_type']['dis_service_deposit_option'].config('keywords')['error']['integer'],
            'dis_service_deposit_option.in'             => config('keywords')['treatment_type']['dis_service_deposit_option'].config('keywords')['error']['in'], 

            'dis_deposit_option.integer'        => config('keywords')['treatment_type']['dis_deposit_option'].config('keywords')['error']['integer'],
            'dis_deposit_option.in'             => config('keywords')['treatment_type']['dis_deposit_option'].config('keywords')['error']['in'], 

            'unsign_doc_finish_option.integer'        => config('keywords')['treatment_type']['unsign_doc_finish_option'].config('keywords')['error']['integer'],
            'unsign_doc_finish_option.in'             => config('keywords')['treatment_type']['unsign_doc_finish_option'].config('keywords')['error']['in'], 

            'trans_time_out_time_option.integer'        => config('keywords')['treatment_type']['trans_time_out_time_option'].config('keywords')['error']['integer'],
            'trans_time_out_time_option.in'             => config('keywords')['treatment_type']['trans_time_out_time_option'].config('keywords')['error']['in'], 

            'fee_debt_option.integer'        => config('keywords')['treatment_type']['fee_debt_option'].config('keywords')['error']['integer'],
            'fee_debt_option.in'             => config('keywords')['treatment_type']['fee_debt_option'].config('keywords')['error']['in'], 
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
