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
        if(!is_numeric($this->id)){
            throw new HttpResponseException(return_id_error($this->id));
        }
        return [
            'national_code' => [
                'required',
                'string',
                'max:3',
                Rule::unique('App\Models\SDA\National')->ignore($this->id),
            ],
            'national_name' =>                  'required|string|max:100',
            'mps_national_code' =>              'nullable|string|max:3',
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
