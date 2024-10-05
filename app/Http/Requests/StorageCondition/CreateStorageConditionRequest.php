<?php

namespace App\Http\Requests\StorageCondition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateStorageConditionRequest extends FormRequest
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
            'storage_condition_code' =>      'required|string|max:10|unique:App\Models\HIS\StorageCondition,storage_condition_code',
            'storage_condition_name' =>      'required|string|max:200',
            'from_temperature' =>            'nullable|numeric|regex:/^\d{1,17}(\.\d{1,2})?$/',    
            'to_temperature' =>              'nullable|numeric|regex:/^\d{1,17}(\.\d{1,2})?$/',   
        ];
    }
    public function messages()
    {
        return [
            'storage_condition_code.required'    => config('keywords')['storage_condition']['storage_condition_code'].config('keywords')['error']['required'],
            'storage_condition_code.string'      => config('keywords')['storage_condition']['storage_condition_code'].config('keywords')['error']['string'],
            'storage_condition_code.max'         => config('keywords')['storage_condition']['storage_condition_code'].config('keywords')['error']['string_max'],
            'storage_condition_code.unique'      => config('keywords')['storage_condition']['storage_condition_code'].config('keywords')['error']['unique'],

            'storage_condition_name.required'    => config('keywords')['storage_condition']['storage_condition_name'].config('keywords')['error']['required'],
            'storage_condition_name.string'      => config('keywords')['storage_condition']['storage_condition_name'].config('keywords')['error']['string'],
            'storage_condition_name.max'         => config('keywords')['storage_condition']['storage_condition_name'].config('keywords')['error']['string_max'],

            'from_temperature.numeric'     => config('keywords')['storage_condition']['from_temperature'].config('keywords')['error']['numeric'],
            'from_temperature.regex'       => config('keywords')['storage_condition']['from_temperature'].config('keywords')['error']['regex_19_2'],
            
            'to_temperature.numeric'     => config('keywords')['storage_condition']['to_temperature'].config('keywords')['error']['numeric'],
            'to_temperature.regex'       => config('keywords')['storage_condition']['to_temperature'].config('keywords')['error']['regex_19_2'],
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
