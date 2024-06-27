<?php

namespace App\Http\Requests\IcdCm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateIcdCmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->id)){
            throw new HttpResponseException(return_id_error($this->id));
        }
        return [
            'icd_cm_code' =>                  [
                'required',
                'string',
                'max:10',
                Rule::unique('App\Models\HIS\IcdCm')->ignore($this->id),
            ],
            'icd_cm_name' =>                  'required|string|max:1000',
            'icd_cm_chapter_code' =>          'nullable|string|max:10',
            'icd_cm_chapter_name' =>          'nullable|string|max:1000',
            'icd_cm_group_code' =>            'nullable|string|max:10',
            'icd_cm_group_name' =>            'nullable|string|max:500',
            'icd_cm_sub_group_code' =>        'nullable|string|max:10',
            'icd_cm_sub_group_name' =>        'nullable|string|max:500',
        ];
    }
    public function messages()
    {
        return [
            'icd_cm_code.required'    => config('keywords')['icd_cm']['icd_cm_code'].config('keywords')['error']['required'],
            'icd_cm_code.string'      => config('keywords')['icd_cm']['icd_cm_code'].config('keywords')['error']['string'],
            'icd_cm_code.max'         => config('keywords')['icd_cm']['icd_cm_code'].config('keywords')['error']['string_max'],
            'icd_cm_code.unique'      => config('keywords')['icd_cm']['icd_cm_code'].config('keywords')['error']['unique'],

            'icd_cm_name.required'    => config('keywords')['icd_cm']['icd_cm_name'].config('keywords')['error']['required'],
            'icd_cm_name.string'      => config('keywords')['icd_cm']['icd_cm_name'].config('keywords')['error']['string'],
            'icd_cm_name.max'         => config('keywords')['icd_cm']['icd_cm_name'].config('keywords')['error']['string_max'],

            'icd_cm_group_code.string'      => config('keywords')['icd_cm']['icd_cm_group_code'].config('keywords')['error']['string'],
            'icd_cm_group_code.max'         => config('keywords')['icd_cm']['icd_cm_group_code'].config('keywords')['error']['string_max'],

            'icd_cm_group_name.string'      => config('keywords')['icd_cm']['icd_cm_group_name'].config('keywords')['error']['string'],
            'icd_cm_group_name.max'         => config('keywords')['icd_cm']['icd_cm_group_name'].config('keywords')['error']['string_max'], 

            'icd_cm_sub_group_code.string'      => config('keywords')['icd_cm']['icd_cm_sub_group_code'].config('keywords')['error']['string'],
            'icd_cm_sub_group_code.max'         => config('keywords')['icd_cm']['icd_cm_sub_group_code'].config('keywords')['error']['string_max'],

            'icd_cm_sub_group_name.string'      => config('keywords')['icd_cm']['icd_cm_sub_group_name'].config('keywords')['error']['string'],
            'icd_cm_sub_group_name.max'         => config('keywords')['icd_cm']['icd_cm_sub_group_name'].config('keywords')['error']['string_max'],

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
