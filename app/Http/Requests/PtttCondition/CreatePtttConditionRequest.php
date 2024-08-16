<?php

namespace App\Http\Requests\PtttCondition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreatePtttConditionRequest extends FormRequest
{
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
        return [
            'pttt_condition_code' =>      'required|string|max:2|unique:App\Models\HIS\PtttCondition,pttt_condition_code',
            'pttt_condition_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'pttt_condition_code.required'    => config('keywords')['pttt_condition']['pttt_condition_code'].config('keywords')['error']['required'],
            'pttt_condition_code.string'      => config('keywords')['pttt_condition']['pttt_condition_code'].config('keywords')['error']['string'],
            'pttt_condition_code.max'         => config('keywords')['pttt_condition']['pttt_condition_code'].config('keywords')['error']['string_max'],
            'pttt_condition_code.unique'      => config('keywords')['pttt_condition']['pttt_condition_code'].config('keywords')['error']['unique'],

            'pttt_condition_name.required'    => config('keywords')['pttt_condition']['pttt_condition_name'].config('keywords')['error']['required'],
            'pttt_condition_name.string'      => config('keywords')['pttt_condition']['pttt_condition_name'].config('keywords')['error']['string'],
            'pttt_condition_name.max'         => config('keywords')['pttt_condition']['pttt_condition_name'].config('keywords')['error']['string_max'],

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
