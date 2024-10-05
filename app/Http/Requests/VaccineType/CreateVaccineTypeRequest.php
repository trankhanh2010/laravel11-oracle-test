<?php

namespace App\Http\Requests\VaccineType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateVaccineTypeRequest extends FormRequest
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
            'vaccine_type_code' =>      'required|string|max:20|unique:App\Models\HIS\VaccineType,vaccine_type_code',
            'vaccine_type_name' =>      'required|string|max:500',
            'is_use_for_ksk' =>         'nullable|integer|in:0,1',
        ];
    }
    public function messages()
    {
        return [
            'vaccine_type_code.required'    => config('keywords')['vaccine_type']['vaccine_type_code'].config('keywords')['error']['required'],
            'vaccine_type_code.string'      => config('keywords')['vaccine_type']['vaccine_type_code'].config('keywords')['error']['string'],
            'vaccine_type_code.max'         => config('keywords')['vaccine_type']['vaccine_type_code'].config('keywords')['error']['string_max'],
            'vaccine_type_code.unique'      => config('keywords')['vaccine_type']['vaccine_type_code'].config('keywords')['error']['unique'],

            'vaccine_type_name.required'    => config('keywords')['vaccine_type']['vaccine_type_name'].config('keywords')['error']['required'],
            'vaccine_type_name.string'      => config('keywords')['vaccine_type']['vaccine_type_name'].config('keywords')['error']['string'],
            'vaccine_type_name.max'         => config('keywords')['vaccine_type']['vaccine_type_name'].config('keywords')['error']['string_max'],
            'vaccine_type_name.unique'      => config('keywords')['vaccine_type']['vaccine_type_name'].config('keywords')['error']['unique'],

            'is_use_for_ksk.integer'     => config('keywords')['vaccine_type']['is_use_for_ksk'].config('keywords')['error']['integer'], 
            'is_use_for_ksk.in'          => config('keywords')['vaccine_type']['is_use_for_ksk'].config('keywords')['error']['in'], 
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
