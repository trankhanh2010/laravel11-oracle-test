<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreatePackageRequest extends FormRequest
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
            'package_code' =>      'required|string|max:4|unique:App\Models\HIS\Package,package_code',
            'package_name' =>      'required|string|max:100',
            'is_not_fixed_service' => 'nullable|integer|in:0,1',
        ];
    }
    public function messages()
    {
        return [
            'package_code.required'    => config('keywords')['package']['package_code'].config('keywords')['error']['required'],
            'package_code.string'      => config('keywords')['package']['package_code'].config('keywords')['error']['string'],
            'package_code.max'         => config('keywords')['package']['package_code'].config('keywords')['error']['string_max'],
            'package_code.unique'      => config('keywords')['package']['package_code'].config('keywords')['error']['unique'],

            'package_name.required'    => config('keywords')['package']['package_name'].config('keywords')['error']['required'],
            'package_name.string'      => config('keywords')['package']['package_name'].config('keywords')['error']['string'],
            'package_name.max'         => config('keywords')['package']['package_name'].config('keywords')['error']['string_max'],
            'package_name.unique'      => config('keywords')['package']['package_name'].config('keywords')['error']['unique'],

            'is_not_fixed_service.integer'      => config('keywords')['package']['is_not_fixed_service'].config('keywords')['error']['integer'], 
            'is_not_fixed_service.in'           => config('keywords')['package']['is_not_fixed_service'].config('keywords')['error']['in'], 

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
