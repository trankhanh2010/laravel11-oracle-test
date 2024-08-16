<?php

namespace App\Http\Requests\PreparationsBlood;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreatePreparationsBloodRequest extends FormRequest
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
            'preparations_blood_code' =>      'required|string|max:5|unique:App\Models\HIS\PreparationsBlood,preparations_blood_code',
            'preparations_blood_name' =>      'required|string|max:1000',
            
        ];
    }
    public function messages()
    {
        return [
            'preparations_blood_code.required'    => config('keywords')['preparations_blood']['preparations_blood_code'].config('keywords')['error']['required'],
            'preparations_blood_code.string'      => config('keywords')['preparations_blood']['preparations_blood_code'].config('keywords')['error']['string'],
            'preparations_blood_code.max'         => config('keywords')['preparations_blood']['preparations_blood_code'].config('keywords')['error']['string_max'],
            'preparations_blood_code.unique'      => config('keywords')['preparations_blood']['preparations_blood_code'].config('keywords')['error']['unique'],

            'preparations_blood_name.required'    => config('keywords')['preparations_blood']['preparations_blood_name'].config('keywords')['error']['required'],
            'preparations_blood_name.string'      => config('keywords')['preparations_blood']['preparations_blood_name'].config('keywords')['error']['string'],
            'preparations_blood_name.max'         => config('keywords')['preparations_blood']['preparations_blood_name'].config('keywords')['error']['string_max'],

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
