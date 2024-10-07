<?php

namespace App\Http\Requests\ServiceReqType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateServiceReqTypeRequest extends FormRequest
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
            'service_req_type_code' =>      'required|string|max:2|unique:App\Models\HIS\ServiceReqType,service_req_type_code',
            'service_req_type_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'service_req_type_code.required'    => config('keywords')['service_req_type']['service_req_type_code'].config('keywords')['error']['required'],
            'service_req_type_code.string'      => config('keywords')['service_req_type']['service_req_type_code'].config('keywords')['error']['string'],
            'service_req_type_code.max'         => config('keywords')['service_req_type']['service_req_type_code'].config('keywords')['error']['string_max'],
            'service_req_type_code.unique'      => config('keywords')['service_req_type']['service_req_type_code'].config('keywords')['error']['unique'],

            'service_req_type_name.required'    => config('keywords')['service_req_type']['service_req_type_name'].config('keywords')['error']['required'],
            'service_req_type_name.string'      => config('keywords')['service_req_type']['service_req_type_name'].config('keywords')['error']['string'],
            'service_req_type_name.max'         => config('keywords')['service_req_type']['service_req_type_name'].config('keywords')['error']['string_max'],
            'service_req_type_name.unique'      => config('keywords')['service_req_type']['service_req_type_name'].config('keywords')['error']['unique'],

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
