<?php

namespace App\Http\Requests\District;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateDistrictRequest extends FormRequest
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
            'district_code' =>      'required|string|max:4|unique:App\Models\SDA\District,district_code',
            'district_name' =>      'required|string|max:100',
            'initial_name' =>       'nullable|string|max:20|in:Huyện,Quận,Thị Xã,Thành Phố',
            'search_code' =>        'nullable|string|max:10',
            'province_id' =>        'required|integer|exists:App\Models\SDA\Province,id',
        ];
    }
    public function messages()
    {
        return [
            'district_code.required'    => config('keywords')['district']['district_code'].config('keywords')['error']['required'],
            'district_code.string'      => config('keywords')['district']['district_code'].config('keywords')['error']['string'],
            'district_code.max'         => config('keywords')['district']['district_code'].config('keywords')['error']['string_max'],
            'district_code.unique'      => config('keywords')['district']['district_code'].config('keywords')['error']['unique'],

            'district_name.required'    => config('keywords')['district']['district_name'].config('keywords')['error']['required'],
            'district_name.string'      => config('keywords')['district']['district_name'].config('keywords')['error']['string'],
            'district_name.max'         => config('keywords')['district']['district_name'].config('keywords')['error']['string_max'],

            'initial_name.string'       => config('keywords')['district']['initial_name'].config('keywords')['error']['string'],
            'initial_name.max'          => config('keywords')['district']['initial_name'].config('keywords')['error']['string_max'],
            'initial_name.in'           => config('keywords')['district']['initial_name'].config('keywords')['error']['in'],

            'search_code.string'        => config('keywords')['district']['search_code'].config('keywords')['error']['string'],
            'search_code.max'           => config('keywords')['district']['search_code'].config('keywords')['error']['string_max'],

            'province_id.required'      => config('keywords')['district']['province_id'].config('keywords')['error']['required'],
            'province_id.integer'       => config('keywords')['district']['province_id'].config('keywords')['error']['integer'],
            'province_id.exists'        => config('keywords')['district']['province_id'].config('keywords')['error']['exists'],

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
