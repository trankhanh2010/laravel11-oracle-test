<?php

namespace App\Http\Requests\TestIndexUnit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateTestIndexUnitRequest extends FormRequest
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
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->test_index_unit)){
            throw new HttpResponseException(returnIdError($this->test_index_unit));
        }
        return [
            'test_index_unit_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:2',
                                                    Rule::unique('App\Models\HIS\TestIndexUnit')->ignore($this->test_index_unit),
                                                ],
            'test_index_unit_name' =>        'required|string|max:100',
            'test_index_unit_symbol' =>    'nullable|string|max:20',
            'is_active' =>                      'required|integer|in:0,1'

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
