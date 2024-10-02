<?php

namespace App\Http\Requests\FuexType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateFuexTypeRequest extends FormRequest
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
            'fuex_type_code' =>      'required|string|max:10|unique:App\Models\HIS\FuexType,fuex_type_code',
            'fuex_type_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'fuex_type_code.required'    => config('keywords')['fuex_type']['fuex_type_code'].config('keywords')['error']['required'],
            'fuex_type_code.string'      => config('keywords')['fuex_type']['fuex_type_code'].config('keywords')['error']['string'],
            'fuex_type_code.max'         => config('keywords')['fuex_type']['fuex_type_code'].config('keywords')['error']['string_max'],
            'fuex_type_code.unique'      => config('keywords')['fuex_type']['fuex_type_code'].config('keywords')['error']['unique'],

            'fuex_type_name.required'    => config('keywords')['fuex_type']['fuex_type_name'].config('keywords')['error']['required'],
            'fuex_type_name.string'      => config('keywords')['fuex_type']['fuex_type_name'].config('keywords')['error']['string'],
            'fuex_type_name.max'         => config('keywords')['fuex_type']['fuex_type_name'].config('keywords')['error']['string_max'],
            'fuex_type_name.unique'      => config('keywords')['fuex_type']['fuex_type_name'].config('keywords')['error']['unique'],

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
