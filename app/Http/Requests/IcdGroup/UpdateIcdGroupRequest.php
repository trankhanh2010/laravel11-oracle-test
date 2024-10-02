<?php

namespace App\Http\Requests\IcdGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateIcdGroupRequest extends FormRequest
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
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->icd_group)){
            throw new HttpResponseException(returnIdError($this->icd_group));
        }
        return [
            'icd_group_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:10',
                                                    Rule::unique('App\Models\HIS\IcdGroup')->ignore($this->icd_group),
                                                ],
            'icd_group_name' =>        'required|string|max:500',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'icd_group_code.required'    => config('keywords')['icd_group']['icd_group_code'].config('keywords')['error']['required'],
            'icd_group_code.string'      => config('keywords')['icd_group']['icd_group_code'].config('keywords')['error']['string'],
            'icd_group_code.max'         => config('keywords')['icd_group']['icd_group_code'].config('keywords')['error']['string_max'],
            'icd_group_code.unique'      => config('keywords')['icd_group']['icd_group_code'].config('keywords')['error']['unique'],

            'icd_group_name.string'      => config('keywords')['icd_group']['icd_group_name'].config('keywords')['error']['string'],
            'icd_group_name.max'         => config('keywords')['icd_group']['icd_group_name'].config('keywords')['error']['string_max'],
            'icd_group_name.unique'      => config('keywords')['icd_group']['icd_group_name'].config('keywords')['error']['unique'],

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
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
