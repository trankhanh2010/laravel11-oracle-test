<?php

namespace App\Http\Requests\AtcGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateAtcGroupRequest extends FormRequest
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
            'atc_group_code' =>      'required|string|max:5|unique:App\Models\HIS\AtcGroup,atc_group_code',
            'atc_group_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'atc_group_code.required'    => config('keywords')['atc_group']['atc_group_code'].config('keywords')['error']['required'],
            'atc_group_code.string'      => config('keywords')['atc_group']['atc_group_code'].config('keywords')['error']['string'],
            'atc_group_code.max'         => config('keywords')['atc_group']['atc_group_code'].config('keywords')['error']['string_max'],
            'atc_group_code.unique'      => config('keywords')['atc_group']['atc_group_code'].config('keywords')['error']['unique'],

            'atc_group_name.string'      => config('keywords')['atc_group']['atc_group_name'].config('keywords')['error']['string'],
            'atc_group_name.max'         => config('keywords')['atc_group']['atc_group_name'].config('keywords')['error']['string_max'],
            'atc_group_name.unique'      => config('keywords')['atc_group']['atc_group_name'].config('keywords')['error']['unique'],

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
