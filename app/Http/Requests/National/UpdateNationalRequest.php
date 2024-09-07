<?php

namespace App\Http\Requests\National;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateNationalRequest extends FormRequest
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
        if(!is_numeric($this->national)){
            throw new HttpResponseException(returnIdError($this->national));
        }
        return [
            'national_code' => [
                'required',
                'string',
                'max:3',
                Rule::unique('App\Models\SDA\National')->ignore($this->national),
            ],
            'national_name' =>                  'required|string|max:100',
            'mps_national_code' =>              'nullable|string|max:3',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'national_code.required'    => config('keywords')['national']['nationnal_code'].config('keywords')['error']['required'],
            'national_code.string'      => config('keywords')['national']['nationnal_code'].config('keywords')['error']['string'],
            'national_code.max'         => config('keywords')['national']['nationnal_code'].config('keywords')['error']['string_max'],
            'national_code.unique'      => config('keywords')['national']['nationnal_code'].config('keywords')['error']['unique'],

            'national_name.required'    => config('keywords')['national']['national_name'].config('keywords')['error']['required'],
            'national_name.string'      => config('keywords')['national']['national_name'].config('keywords')['error']['string'],
            'national_name.max'         => config('keywords')['national']['national_name'].config('keywords')['error']['string_max'],

            'mps_national_code.string'      => config('keywords')['national']['mps_national_code'].config('keywords')['error']['string'],
            'mps_national_code.max'         => config('keywords')['national']['mps_national_code'].config('keywords')['error']['string_max'],

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
