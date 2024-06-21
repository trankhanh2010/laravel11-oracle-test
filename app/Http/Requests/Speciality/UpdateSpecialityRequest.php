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
            'speciality_code.required'    => config('keywords')['speciality']['speciality_code'].' không được bỏ trống!',
            'speciality_code.string'      => config('keywords')['speciality']['speciality_code'].' phải là chuỗi string!',
            'speciality_code.max'         => config('keywords')['speciality']['speciality_code'].' tối đa 50 kí tự!',
            'speciality_code.unique'      => config('keywords')['speciality']['speciality_code'].' = '. $this->speciality_code . ' đã tồn tại!',

            'speciality_name.required'    => config('keywords')['speciality']['speciality_name'].' không được bỏ trống!',
            'speciality_name.string'      => config('keywords')['speciality']['speciality_name'].' phải là chuỗi string!',
            'speciality_name.max'         => config('keywords')['speciality']['speciality_name'].' tối đa 200 kí tự!',

            'bhyt_limit.integer'     => config('keywords')['speciality']['speciality_name'].' phải là số nguyên!',
            'bhyt_limit.min'         => config('keywords')['speciality']['speciality_name'].' lớn hơn bằng 0!',
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