<?php

namespace App\Http\Requests\GroupType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateGroupTypeRequest extends FormRequest
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
            'group_type_code' =>      'required|string|max:3|unique:App\Models\SDA\GroupType,group_type_code',
            'group_type_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'group_type_code.required'    => config('keywords')['group_type']['group_type_code'].config('keywords')['error']['required'],
            'group_type_code.string'      => config('keywords')['group_type']['group_type_code'].config('keywords')['error']['string'],
            'group_type_code.max'         => config('keywords')['group_type']['group_type_code'].config('keywords')['error']['string_max'],
            'group_type_code.unique'      => config('keywords')['group_type']['group_type_code'].config('keywords')['error']['unique'],

            'group_type_name.required'    => config('keywords')['group_type']['group_type_name'].config('keywords')['error']['required'],
            'group_type_name.string'      => config('keywords')['group_type']['group_type_name'].config('keywords')['error']['string'],
            'group_type_name.max'         => config('keywords')['group_type']['group_type_name'].config('keywords')['error']['string_max'],
            'group_type_name.unique'      => config('keywords')['group_type']['group_type_name'].config('keywords')['error']['unique'],

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
