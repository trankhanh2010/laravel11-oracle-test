<?php

namespace App\Http\Requests\HospitalizeReason;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateHospitalizeReasonRequest extends FormRequest
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
            'hospitalize_reason_code' =>      'required|string|max:10|unique:App\Models\HIS\HospitalizeReason,hospitalize_reason_code',
            'hospitalize_reason_name' =>      'required|string|max:1000',
            
        ];
    }
    public function messages()
    {
        return [
            'hospitalize_reason_code.required'    => config('keywords')['hospitalize_reason']['hospitalize_reason_code'].config('keywords')['error']['required'],
            'hospitalize_reason_code.string'      => config('keywords')['hospitalize_reason']['hospitalize_reason_code'].config('keywords')['error']['string'],
            'hospitalize_reason_code.max'         => config('keywords')['hospitalize_reason']['hospitalize_reason_code'].config('keywords')['error']['string_max'],
            'hospitalize_reason_code.unique'      => config('keywords')['hospitalize_reason']['hospitalize_reason_code'].config('keywords')['error']['unique'],

            'hospitalize_reason_name.required'    => config('keywords')['hospitalize_reason']['hospitalize_reason_name'].config('keywords')['error']['required'],
            'hospitalize_reason_name.string'      => config('keywords')['hospitalize_reason']['hospitalize_reason_name'].config('keywords')['error']['string'],
            'hospitalize_reason_name.max'         => config('keywords')['hospitalize_reason']['hospitalize_reason_name'].config('keywords')['error']['string_max'],

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
