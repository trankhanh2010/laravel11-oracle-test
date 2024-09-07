<?php

namespace App\Http\Requests\OtherPaySource;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
class UpdateOtherPaySourceRequest extends FormRequest
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
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->other_pay_source)){
            throw new HttpResponseException(returnIdError($this->other_pay_source));
        }
        return [
            'other_pay_source_code' =>      [
                                                'required',
                                                'string',
                                                'max:20',
                                                Rule::unique('App\Models\HIS\OtherPaySource')->ignore($this->other_pay_source),
                                            ],
            'other_pay_source_name' =>      'required|string|max:200',
            'hein_pay_source_type_id' =>    'nullable|integer|in:1,2,3',
            'is_not_for_treatment' =>       'nullable|integer|in:0,1',
            'is_not_paid_diff' =>           'nullable|integer|in:0,1',
            'is_paid_all' =>                'nullable|integer|in:0,1',
            'is_active' =>                  'required|integer|in:0,1'


        ];
    }
    public function messages()
    {
        return [
            'other_pay_source_code.required'    => config('keywords')['other_pay_source']['other_pay_source_code'].config('keywords')['error']['required'],
            'other_pay_source_code.string'      => config('keywords')['other_pay_source']['other_pay_source_code'].config('keywords')['error']['string'],
            'other_pay_source_code.max'         => config('keywords')['other_pay_source']['other_pay_source_code'].config('keywords')['error']['string_max'],
            'other_pay_source_code.unique'      => config('keywords')['other_pay_source']['other_pay_source_code'].config('keywords')['error']['unique'],

            'other_pay_source_name.required'    => config('keywords')['other_pay_source']['other_pay_source_name'].config('keywords')['error']['required'],
            'other_pay_source_name.string'      => config('keywords')['other_pay_source']['other_pay_source_name'].config('keywords')['error']['string'],
            'other_pay_source_name.max'         => config('keywords')['other_pay_source']['other_pay_source_name'].config('keywords')['error']['string_max'],

            'hein_pay_source_type_id.integer'       => config('keywords')['other_pay_source']['hein_pay_source_type_id'].config('keywords')['error']['integer'],
            'hein_pay_source_type_id.in'            => config('keywords')['other_pay_source']['hein_pay_source_type_id'].config('keywords')['error']['in'],

            'is_not_for_treatment.integer'       => config('keywords')['other_pay_source']['is_not_for_treatment'].config('keywords')['error']['integer'],
            'is_not_for_treatment.in'            => config('keywords')['other_pay_source']['is_not_for_treatment'].config('keywords')['error']['in'],

            'is_not_paid_diff.integer'       => config('keywords')['other_pay_source']['is_not_paid_diff'].config('keywords')['error']['integer'],
            'is_not_paid_diff.in'            => config('keywords')['other_pay_source']['is_not_paid_diff'].config('keywords')['error']['in'],

            'is_paid_all.integer'       => config('keywords')['other_pay_source']['is_paid_all'].config('keywords')['error']['integer'],
            'is_paid_all.in'            => config('keywords')['other_pay_source']['is_paid_all'].config('keywords')['error']['in'],

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
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
