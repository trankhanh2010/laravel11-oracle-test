<?php

namespace App\Http\Requests\RationTime;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateRationTimeRequest extends FormRequest
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
            'ration_time_code' =>      'required|string|max:2|unique:App\Models\HIS\RationTime,ration_time_code',
            'ration_time_name' =>      'required|string|max:100',
        ];
    }
    public function messages()
    {
        return [
            'ration_time_code.required'    => config('keywords')['ration_time']['ration_time_code'].config('keywords')['error']['required'],
            'ration_time_code.string'      => config('keywords')['ration_time']['ration_time_code'].config('keywords')['error']['string'],
            'ration_time_code.max'         => config('keywords')['ration_time']['ration_time_code'].config('keywords')['error']['string_max'],
            'ration_time_code.unique'      => config('keywords')['ration_time']['ration_time_code'].config('keywords')['error']['unique'],

            'ration_time_name.required'    => config('keywords')['ration_time']['ration_time_name'].config('keywords')['error']['required'],
            'ration_time_name.string'      => config('keywords')['ration_time']['ration_time_name'].config('keywords')['error']['string'],
            'ration_time_name.max'         => config('keywords')['ration_time']['ration_time_name'].config('keywords')['error']['string_max'],

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
