<?php

namespace App\Http\Requests\OtherPaySource;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class CreateOtherPaySourceRequest extends FormRequest
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
            'other_pay_source_code' =>      'required|string|max:20|unique:App\Models\HIS\OtherPaySource,other_pay_source_code',
            'other_pay_source_name' =>      'required|string|max:200',
            'hein_pay_source_type_id' =>    'nullable|integer|in:1,2,3',
            'is_not_for_treatment' =>       'nullable|integer|in:0,1',
            'is_not_paid_diff' =>           'nullable|integer|in:0,1',
            'is_paid_all' =>                'nullable|integer|in:0,1',

        ];
    }
    public function messages()
    {
        return [
            'other_pay_source_code.required'    => config('keywords')['other_pay_source']['other_pay_source_code'].' không được bỏ trống!',
            'other_pay_source_code.string'      => config('keywords')['other_pay_source']['other_pay_source_code'].' phải là chuỗi string!',
            'other_pay_source_code.max'         => config('keywords')['other_pay_source']['other_pay_source_code'].' tối đa 10 kí tự!',
            'other_pay_source_code.unique'      => config('keywords')['other_pay_source']['other_pay_source_code'].' = '. $this->other_pay_source_code . ' đã tồn tại!',

            'other_pay_source_name.required'    => config('keywords')['other_pay_source']['other_pay_source_name'].' không được bỏ trống!',
            'other_pay_source_name.string'      => config('keywords')['other_pay_source']['other_pay_source_name'].' phải là chuỗi string!',
            'other_pay_source_name.max'         => config('keywords')['other_pay_source']['other_pay_source_name'].' tối đa 200 kí tự!',

            'hein_pay_source_type_id.integer'       => config('keywords')['other_pay_source']['hein_pay_source_type_id'].' phải là số nguyên!',
            'hein_pay_source_type_id.in'            => config('keywords')['other_pay_source']['hein_pay_source_type_id'].' phải là 1,2 hoặc 3!',

            'is_not_for_treatment.integer'       => config('keywords')['other_pay_source']['is_not_for_treatment'].' phải là số nguyên!',
            'is_not_for_treatment.in'            => config('keywords')['other_pay_source']['is_not_for_treatment'].' phải là 0 hoặc 1!',

            'is_not_paid_diff.integer'       => config('keywords')['other_pay_source']['is_not_paid_diff'].' phải là số nguyên!',
            'is_not_paid_diff.in'            => config('keywords')['other_pay_source']['is_not_paid_diff'].' phải là 0 hoặc 1!',

            'is_paid_all.integer'       => config('keywords')['other_pay_source']['is_paid_all'].' phải là số nguyên!',
            'is_paid_all.in'            => config('keywords')['other_pay_source']['is_paid_all'].' phải là 0 hoặc 1!',

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
