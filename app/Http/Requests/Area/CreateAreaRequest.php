<?php

namespace App\Http\Requests\Area;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class CreateAreaRequest extends FormRequest
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
        return [
            'area_code' =>      'required|string|max:2|unique:App\Models\HIS\Area,area_code',
            'area_name' =>      'required|string|max:100',
            'department_id' =>  'required|integer|exists:App\Models\HIS\Department,id',
        ];
    }
    public function messages()
    {
        return [
            'area_code.required' => config('keywords')['area']['area_code'].' không được bỏ trống!',
            'area_code.string' => config('keywords')['area']['area_code'].' phải là chuỗi string!',
            'area_code.max' => config('keywords')['area']['area_code'].' tối đa 2 kí tự!',
            'area_code.unique' => config('keywords')['area']['area_code'].' = '. $this->area_code . ' đã tồn tại!',

            'area_name.required' => config('keywords')['area']['area_name'].' không được bỏ trống!',
            'area_name.string' => config('keywords')['area']['area_name'].' phải là chuỗi string!',
            'area_name.max' => config('keywords')['area']['area_name'].' tối đa 100 kí tự!',

            'department_id.required' => config('keywords')['area']['department_id'].' không được bỏ trống!',
            'department_id.integer' => config('keywords')['area']['department_id'].' phải là số nguyên!',
            'department_id.exists' => config('keywords')['area']['department_id'].' = '.$this->department_id.' không tồn tại!',

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
