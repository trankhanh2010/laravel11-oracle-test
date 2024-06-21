<?php

namespace App\Http\Requests\District;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use App\Models\SDA\Province;
class UpdateDistrictRequest extends FormRequest
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
                                    'district_code' =>      [
                                        'required',
                                        'string',
                                        'max:4',
                                        Rule::unique('App\Models\SDA\District')->ignore($this->id),
                                    ],
            'district_name' =>      'required|string|max:100',
            'initial_name' =>       'nullable|string|max:20|in:Huyện,Quận,Thị Xã,Thành Phố',
            'search_code' =>        'nullable|string|max:10',
            'province_id' =>        'required|integer|exists:App\Models\SDA\Province,id',
        ];
    }
    public function messages()
    {
        return [
            'district_code.required'    => config('keywords')['district']['district_code'].' không được bỏ trống!',
            'district_code.string'      => config('keywords')['district']['district_code'].' phải là chuỗi string!',
            'district_code.max'         => config('keywords')['district']['district_code'].' tối đa 4 kí tự!',
            'district_code.unique'      => config('keywords')['district']['district_code'].' = '. $this->district_code . ' đã tồn tại!',

            'district_name.required'    => config('keywords')['district']['district_name'].' không được bỏ trống!',
            'district_name.string'      => config('keywords')['district']['district_name'].' phải là chuỗi string!',
            'district_name.max'         => config('keywords')['district']['district_name'].' tối đa 100 kí tự!',

            'initial_name.string'       => config('keywords')['district']['initial_name'].' phải là chuỗi string!',
            'initial_name.max'          => config('keywords')['district']['initial_name'].' tối đa 20 kí tự!',
            'initial_name.in'           => config('keywords')['district']['initial_name'].' phải là Huyện, Quận, Thị Xã hoặc Thành Phố!',

            'search_code.string'        => config('keywords')['district']['search_code'].' phải là chuỗi string!',
            'search_code.max'           => config('keywords')['district']['search_code'].' tối đa 10 kí tự!',

            'province_id.required'      => config('keywords')['district']['province_id'].' không được bỏ trống!',
            'province_id.integer'       => config('keywords')['district']['province_id'].' phải là số nguyên!',
            'province_id.exists'        => config('keywords')['district']['province_id'].' = '.$this->province_id.' không tồn tại!',

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
