<?php

namespace App\Http\Requests\ExeServiceModule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateExeServiceModuleRequest extends FormRequest
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
            'module_link' =>      'required|string|max:200',
            'exe_service_module_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'module_link.required'    => config('keywords')['exe_service_module']['module_link'].config('keywords')['error']['required'],
            'module_link.string'      => config('keywords')['exe_service_module']['module_link'].config('keywords')['error']['string'],
            'module_link.max'         => config('keywords')['exe_service_module']['module_link'].config('keywords')['error']['string_max'],

            'exe_service_module_name.required'    => config('keywords')['exe_service_module']['exe_service_module_name'].config('keywords')['error']['required'],
            'exe_service_module_name.string'      => config('keywords')['exe_service_module']['exe_service_module_name'].config('keywords')['error']['string'],
            'exe_service_module_name.max'         => config('keywords')['exe_service_module']['exe_service_module_name'].config('keywords')['error']['string_max'],
            'exe_service_module_name.unique'      => config('keywords')['exe_service_module']['exe_service_module_name'].config('keywords')['error']['unique'],

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
