<?php

namespace App\Http\Requests\Manufacturer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateManufacturerRequest extends FormRequest
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
            'manufacturer_code' =>      'required|string|max:6|unique:App\Models\HIS\Manufacturer,manufacturer_code',
            'manufacturer_name' =>      'required|string|max:1000',
            'manufacturer_short_name' =>    'nullable|string|max:50',
            'email' =>          'nullable|string|max:100|email',                       
            'phone' =>          'nullable|string|max:20', 
            'address' =>        'nullable|string|max:2000',
        ];
    }
    public function messages()
    {
        return [
            'manufacturer_code.required'    => config('keywords')['manufacturer']['manufacturer_code'].config('keywords')['error']['required'],
            'manufacturer_code.string'      => config('keywords')['manufacturer']['manufacturer_code'].config('keywords')['error']['string'],
            'manufacturer_code.max'         => config('keywords')['manufacturer']['manufacturer_code'].config('keywords')['error']['string_max'],
            'manufacturer_code.unique'      => config('keywords')['manufacturer']['manufacturer_code'].config('keywords')['error']['unique'],

            'manufacturer_name.required'    => config('keywords')['manufacturer']['manufacturer_name'].config('keywords')['error']['required'],
            'manufacturer_name.string'      => config('keywords')['manufacturer']['manufacturer_name'].config('keywords')['error']['string'],
            'manufacturer_name.max'         => config('keywords')['manufacturer']['manufacturer_name'].config('keywords')['error']['string_max'],

            'manufacturer_short_name.string'      => config('keywords')['manufacturer']['manufacturer_short_name'].config('keywords')['error']['string'],
            'manufacturer_short_name.max'         => config('keywords')['manufacturer']['manufacturer_short_name'].config('keywords')['error']['string_max'],

            'email.string'      => config('keywords')['manufacturer']['email'].config('keywords')['error']['string'],
            'email.max'         => config('keywords')['manufacturer']['email'].config('keywords')['error']['string_max'],
            'email.email'         => config('keywords')['manufacturer']['email'].config('keywords')['error']['email'],

            'phone.string'      => config('keywords')['manufacturer']['phone'].config('keywords')['error']['string'],
            'phone.max'         => config('keywords')['manufacturer']['phone'].config('keywords')['error']['string_max'],
            // 'phone.regex'         => config('keywords')['manufacturer']['phone'].config('keywords')['error']['regex_phone'],

            'address.string'      => config('keywords')['manufacturer']['address'].config('keywords')['error']['string'],
            'address.max'         => config('keywords')['manufacturer']['address'].config('keywords')['error']['string_max'],
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
