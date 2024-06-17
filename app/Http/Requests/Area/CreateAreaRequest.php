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
            'area_code' => 'required|string|max:2|unique:App\Models\HIS\Area,area_code',
            'area_name' => 'required|string|max:100',
            'department_id' => 'required|integer|max:22|exists:App\Models\HIS\Department,id',
        ];
    }
    public function messages()
    {
        return [
            'area_code.required' => 'Mã khu vực không được bỏ trống!',
            'area_code.string' => 'Mã khu vực phải là chuỗi string!',
            'area_code.max' => 'Mã khu vực tối đa 2 kí tự!',
            'area_code.unique' => 'Mã khu vực ' . $this->area_code . ' đã tồn tại!',

            'area_name.required' => 'Tên khu vực không được bỏ trống!',
            'area_name.string' => 'Tên khu vực phải là chuỗi string!',
            'area_name.max' => 'Tên khu vực tối đa 100 kí tự!',

            'department_id.required' => 'Id khoa không được bỏ trống!',
            'department_id.integer' => 'Id khoa phải là số nguyên!',
            'department_id.max' => 'Id khoa tối đa 22 kí tự!',
            'department_id.exists' => 'Id khoa không tồn tại!',

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
