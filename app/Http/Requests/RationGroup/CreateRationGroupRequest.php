<?php

namespace App\Http\Requests\RationGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateRationGroupRequest extends FormRequest
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
            'ration_group_code' =>      'required|string|max:2|unique:App\Models\HIS\RationGroup,ration_group_code',
            'ration_group_name' =>      'required|string|max:100',
        ];
    }
    public function messages()
    {
        return [
            'ration_group_code.required'    => config('keywords')['ration_group']['ration_group_code'].config('keywords')['error']['required'],
            'ration_group_code.string'      => config('keywords')['ration_group']['ration_group_code'].config('keywords')['error']['string'],
            'ration_group_code.max'         => config('keywords')['ration_group']['ration_group_code'].config('keywords')['error']['string_max'],
            'ration_group_code.unique'      => config('keywords')['ration_group']['ration_group_code'].config('keywords')['error']['unique'],

            'ration_group_name.required'    => config('keywords')['ration_group']['ration_group_name'].config('keywords')['error']['required'],
            'ration_group_name.string'      => config('keywords')['ration_group']['ration_group_name'].config('keywords')['error']['string'],
            'ration_group_name.max'         => config('keywords')['ration_group']['ration_group_name'].config('keywords')['error']['string_max'],

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