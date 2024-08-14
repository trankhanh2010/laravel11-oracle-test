<?php

namespace App\Http\Requests\FileType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateFileTypeRequest extends FormRequest
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
            'file_type_code' =>      'required|string|max:10|unique:App\Models\HIS\FileType,file_type_code',
            'file_type_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'file_type_code.required'    => config('keywords')['file_type']['file_type_code'].config('keywords')['error']['required'],
            'file_type_code.string'      => config('keywords')['file_type']['file_type_code'].config('keywords')['error']['string'],
            'file_type_code.max'         => config('keywords')['file_type']['file_type_code'].config('keywords')['error']['string_max'],
            'file_type_code.unique'      => config('keywords')['file_type']['file_type_code'].config('keywords')['error']['unique'],

            'file_type_name.required'    => config('keywords')['file_type']['file_type_name'].config('keywords')['error']['required'],
            'file_type_name.string'      => config('keywords')['file_type']['file_type_name'].config('keywords')['error']['string'],
            'file_type_name.max'         => config('keywords')['file_type']['file_type_name'].config('keywords')['error']['string_max'],

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
