<?php

namespace App\Http\Requests\LicenseClass;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateLicenseClassRequest extends FormRequest
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
            'license_class_code' =>      'required|string|max:5|unique:App\Models\HIS\LicenseClass,license_class_code',
            'license_class_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'license_class_code.required'    => config('keywords')['license_class']['license_class_code'].config('keywords')['error']['required'],
            'license_class_code.string'      => config('keywords')['license_class']['license_class_code'].config('keywords')['error']['string'],
            'license_class_code.max'         => config('keywords')['license_class']['license_class_code'].config('keywords')['error']['string_max'],
            'license_class_code.unique'      => config('keywords')['license_class']['license_class_code'].config('keywords')['error']['unique'],

            'license_class_name.required'    => config('keywords')['license_class']['license_class_name'].config('keywords')['error']['required'],
            'license_class_name.string'      => config('keywords')['license_class']['license_class_name'].config('keywords')['error']['string'],
            'license_class_name.max'         => config('keywords')['license_class']['license_class_name'].config('keywords')['error']['string_max'],

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
