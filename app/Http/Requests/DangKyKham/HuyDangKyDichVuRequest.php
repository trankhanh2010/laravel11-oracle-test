<?php

namespace App\Http\Requests\DangKyKham;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class HuyDangKyDichVuRequest extends FormRequest
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
            'deleteSereServId' =>    'required|integer',
        ];
    }
    public function messages()
    {
        return [
            'deleteSereServId.required'    => 'Id dịch vụ đăng ký' . config('keywords')['error']['required'],
            'deleteSereServId.integer'     => 'Id dịch vụ đăng ký' . config('keywords')['error']['integer'],
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
