<?php

namespace App\Http\Requests\IcdGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateIcdGroupRequest extends FormRequest
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
            'icd_group_code' =>      'required|string|max:10|unique:App\Models\HIS\IcdGroup,icd_group_code',
            'icd_group_name' =>      'required|string|max:500',
            
        ];
    }
    public function messages()
    {
        return [
            'icd_group_code.required'    => config('keywords')['icd_group']['icd_group_code'].config('keywords')['error']['required'],
            'icd_group_code.string'      => config('keywords')['icd_group']['icd_group_code'].config('keywords')['error']['string'],
            'icd_group_code.max'         => config('keywords')['icd_group']['icd_group_code'].config('keywords')['error']['string_max'],
            'icd_group_code.unique'      => config('keywords')['icd_group']['icd_group_code'].config('keywords')['error']['unique'],

            'icd_group_name.required'    => config('keywords')['icd_group']['icd_group_name'].config('keywords')['error']['required'],
            'icd_group_name.string'      => config('keywords')['icd_group']['icd_group_name'].config('keywords')['error']['string'],
            'icd_group_name.max'         => config('keywords')['icd_group']['icd_group_name'].config('keywords')['error']['string_max'],
            'icd_group_name.unique'      => config('keywords')['icd_group']['icd_group_name'].config('keywords')['error']['unique'],

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
