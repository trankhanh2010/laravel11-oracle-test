<?php

namespace App\Http\Requests\ExecuteGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class CreateExecuteGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'execute_group_code' =>              'required|string|max:10|unique:App\Models\HIS\ExecuteGroup,execute_group_code',
            'execute_group_name' =>              'required|string|max:100',
        ];
    }
    public function messages()
    {
        return [
            'execute_group_code.required'    => config('keywords')['execute_group']['execute_group_code'].config('keywords')['error']['required'],
            'execute_group_code.string'      => config('keywords')['execute_group']['execute_group_code'].config('keywords')['error']['string'],
            'execute_group_code.max'         => config('keywords')['execute_group']['execute_group_code'].config('keywords')['error']['string_max'],
            'execute_group_code.unique'      => config('keywords')['execute_group']['execute_group_code'].config('keywords')['error']['unique'],

            'execute_group_name.required'    => config('keywords')['execute_group']['execute_group_name'].config('keywords')['error']['required'],
            'execute_group_name.string'      => config('keywords')['execute_group']['execute_group_name'].config('keywords')['error']['string'],
            'execute_group_name.max'         => config('keywords')['execute_group']['execute_group_name'].config('keywords')['error']['string_max'],
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
