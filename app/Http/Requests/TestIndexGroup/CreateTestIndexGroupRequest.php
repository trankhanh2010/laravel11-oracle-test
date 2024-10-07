<?php

namespace App\Http\Requests\TestIndexGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateTestIndexGroupRequest extends FormRequest
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
            'test_index_group_code' =>      'required|string|max:6|unique:App\Models\HIS\TestIndexGroup,test_index_group_code',
            'test_index_group_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'test_index_group_code.required'    => config('keywords')['test_index_group']['test_index_group_code'].config('keywords')['error']['required'],
            'test_index_group_code.string'      => config('keywords')['test_index_group']['test_index_group_code'].config('keywords')['error']['string'],
            'test_index_group_code.max'         => config('keywords')['test_index_group']['test_index_group_code'].config('keywords')['error']['string_max'],
            'test_index_group_code.unique'      => config('keywords')['test_index_group']['test_index_group_code'].config('keywords')['error']['unique'],

            'test_index_group_name.required'    => config('keywords')['test_index_group']['test_index_group_name'].config('keywords')['error']['required'],
            'test_index_group_name.string'      => config('keywords')['test_index_group']['test_index_group_name'].config('keywords')['error']['string'],
            'test_index_group_name.max'         => config('keywords')['test_index_group']['test_index_group_name'].config('keywords')['error']['string_max'],
            'test_index_group_name.unique'      => config('keywords')['test_index_group']['test_index_group_name'].config('keywords')['error']['unique'],

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
