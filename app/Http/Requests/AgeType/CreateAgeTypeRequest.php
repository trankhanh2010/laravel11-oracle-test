<?php

namespace App\Http\Requests\AgeType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateAgeTypeRequest extends FormRequest
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
            'age_type_code' =>      'required|string|max:10|unique:App\Models\HIS\AgeType,age_type_code',
            'age_type_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'age_type_code.required'    => config('keywords')['age_type']['age_type_code'].config('keywords')['error']['required'],
            'age_type_code.string'      => config('keywords')['age_type']['age_type_code'].config('keywords')['error']['string'],
            'age_type_code.max'         => config('keywords')['age_type']['age_type_code'].config('keywords')['error']['string_max'],
            'age_type_code.unique'      => config('keywords')['age_type']['age_type_code'].config('keywords')['error']['unique'],

            'age_type_name.required'    => config('keywords')['age_type']['age_type_name'].config('keywords')['error']['required'],
            'age_type_name.string'      => config('keywords')['age_type']['age_type_name'].config('keywords')['error']['string'],
            'age_type_name.max'         => config('keywords')['age_type']['age_type_name'].config('keywords')['error']['string_max'],
            'age_type_name.unique'      => config('keywords')['age_type']['age_type_name'].config('keywords')['error']['unique'],

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
