<?php

namespace App\Http\Requests\TestIndexUnit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateTestIndexUnitRequest extends FormRequest
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
            'test_index_unit_code' =>      'required|string|max:2|unique:App\Models\HIS\TestIndexUnit,test_index_unit_code',
            'test_index_unit_name' =>      'required|string|max:100',
            'test_index_unit_symbol' =>    'nullable|string|max:20',
        ];
    }
    public function messages()
    {
        return [
            'test_index_unit_code.required'    => config('keywords')['test_index_unit']['test_index_unit_code'].config('keywords')['error']['required'],
            'test_index_unit_code.string'      => config('keywords')['test_index_unit']['test_index_unit_code'].config('keywords')['error']['string'],
            'test_index_unit_code.max'         => config('keywords')['test_index_unit']['test_index_unit_code'].config('keywords')['error']['string_max'],
            'test_index_unit_code.unique'      => config('keywords')['test_index_unit']['test_index_unit_code'].config('keywords')['error']['unique'],

            'test_index_unit_name.required'    => config('keywords')['test_index_unit']['test_index_unit_name'].config('keywords')['error']['required'],
            'test_index_unit_name.string'      => config('keywords')['test_index_unit']['test_index_unit_name'].config('keywords')['error']['string'],
            'test_index_unit_name.max'         => config('keywords')['test_index_unit']['test_index_unit_name'].config('keywords')['error']['string_max'],
            'test_index_unit_name.unique'      => config('keywords')['test_index_unit']['test_index_unit_name'].config('keywords')['error']['unique'],

            'test_index_unit_symbol.string'      => config('keywords')['test_index_unit']['test_index_unit_symbol'].config('keywords')['error']['string'],
            'test_index_unit_symbol.max'         => config('keywords')['test_index_unit']['test_index_unit_symbol'].config('keywords')['error']['string_max'],
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
