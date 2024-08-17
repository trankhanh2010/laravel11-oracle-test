<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateSupplierRequest extends FormRequest
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
            'supplier_code' =>      'required|string|max:10|unique:App\Models\HIS\Supplier,supplier_code',
            'supplier_name' =>      'required|string|max:1000',
            'supplier_short_name' =>      'nullable|string|max:100',
            'email' =>              'nullable|string|max:100|email',
            'phone' =>              'nullable|string|max:20',
            'tax_code' =>           'nullable|string|max:20',

            'representative' =>         'nullable|string|max:200',
            'position' =>               'nullable|string|max:100',
            'auth_letter_num' =>        'nullable|string|max:50',
            'auth_letter_issue_date' => 'nullable|numeric|regex:/^\d{14}$/',
            'contract_num' =>           'nullable|string|max:50',
            'contract_date' =>          'nullable|numeric|regex:/^\d{14}$/',
        ];
    }
    public function messages()
    {
        return [
            'supplier_code.required'    => config('keywords')['supplier']['supplier_code'].config('keywords')['error']['required'],
            'supplier_code.string'      => config('keywords')['supplier']['supplier_code'].config('keywords')['error']['string'],
            'supplier_code.max'         => config('keywords')['supplier']['supplier_code'].config('keywords')['error']['string_max'],
            'supplier_code.unique'      => config('keywords')['supplier']['supplier_code'].config('keywords')['error']['unique'],

            'supplier_name.required'    => config('keywords')['supplier']['supplier_name'].config('keywords')['error']['required'],
            'supplier_name.string'      => config('keywords')['supplier']['supplier_name'].config('keywords')['error']['string'],
            'supplier_name.max'         => config('keywords')['supplier']['supplier_name'].config('keywords')['error']['string_max'],

            'supplier_short_name.string'      => config('keywords')['supplier']['supplier_short_name'].config('keywords')['error']['string'],
            'supplier_short_name.max'         => config('keywords')['supplier']['supplier_short_name'].config('keywords')['error']['string_max'],

            'email.string'      => config('keywords')['supplier']['email'].config('keywords')['error']['string'],
            'email.max'         => config('keywords')['supplier']['email'].config('keywords')['error']['string_max'],
            'email.email'         => config('keywords')['supplier']['email'].config('keywords')['error']['email'],

            'phone.string'      => config('keywords')['supplier']['phone'].config('keywords')['error']['string'],
            'phone.max'         => config('keywords')['supplier']['phone'].config('keywords')['error']['string_max'],

            'tax_code.string'      => config('keywords')['supplier']['tax_code'].config('keywords')['error']['string'],
            'tax_code.max'         => config('keywords')['supplier']['tax_code'].config('keywords')['error']['string_max'],


            'representative.string'      => config('keywords')['supplier']['representative'].config('keywords')['error']['string'],
            'representative.max'         => config('keywords')['supplier']['representative'].config('keywords')['error']['string_max'],

            'position.string'      => config('keywords')['supplier']['position'].config('keywords')['error']['string'],
            'position.max'         => config('keywords')['supplier']['position'].config('keywords')['error']['string_max'],

            'auth_letter_num.string'      => config('keywords')['supplier']['auth_letter_num'].config('keywords')['error']['string'],
            'auth_letter_num.max'         => config('keywords')['supplier']['auth_letter_num'].config('keywords')['error']['string_max'],

            'auth_letter_issue_date.numeric'      => config('keywords')['supplier']['auth_letter_issue_date'].config('keywords')['error']['numeric'],
            'auth_letter_issue_date.regex'              => config('keywords')['supplier']['auth_letter_issue_date'].config('keywords')['error']['regex_ymdhis'],

            'contract_num.string'      => config('keywords')['supplier']['contract_num'].config('keywords')['error']['string'],
            'contract_num.max'         => config('keywords')['supplier']['contract_num'].config('keywords')['error']['string_max'],

            'contract_date.numeric'      => config('keywords')['supplier']['contract_date'].config('keywords')['error']['numeric'],
            'contract_date.regex'              => config('keywords')['supplier']['contract_date'].config('keywords')['error']['regex_ymdhis'],
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
