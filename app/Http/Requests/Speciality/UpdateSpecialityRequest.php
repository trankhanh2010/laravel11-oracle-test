<?php

namespace App\Http\Requests\Speciality;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateSpecialityRequest extends FormRequest
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
            'speciality_code' =>              [
                'required',
                'string',
                'max:50',
                Rule::unique('App\Models\HIS\Speciality')->ignore($this->id),
            ],
            'speciality_name' =>              'required|string|max:200',
            'bhyt_limit' =>                   'nullable|integer|min:0',
        ];
    }
    public function messages()
    {
        return [
            'speciality_code.required'    => config('keywords')['speciality']['speciality_code'].config('keywords')['error']['required'],
            'speciality_code.string'      => config('keywords')['speciality']['speciality_code'].config('keywords')['error']['string'],
            'speciality_code.max'         => config('keywords')['speciality']['speciality_code'].config('keywords')['error']['string_max'],
            'speciality_code.unique'      => config('keywords')['speciality']['speciality_code'].config('keywords')['error']['unique'],

            'speciality_name.required'    => config('keywords')['speciality']['speciality_name'].config('keywords')['error']['required'],
            'speciality_name.string'      => config('keywords')['speciality']['speciality_name'].config('keywords')['error']['string'],
            'speciality_name.max'         => config('keywords')['speciality']['speciality_name'].config('keywords')['error']['string_max'],

            'bhyt_limit.integer'     => config('keywords')['speciality']['speciality_name'].config('keywords')['error']['integer'],
            'bhyt_limit.min'         => config('keywords')['speciality']['speciality_name'].config('keywords')['error']['integer_min'],
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
