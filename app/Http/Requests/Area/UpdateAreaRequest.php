<?php

namespace App\Http\Requests\Area;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateAreaRequest extends FormRequest
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
            'area_code' => [
                'required',
                'string',
                'max:2',
                Rule::unique('App\Models\HIS\Area')->ignore($this->id),
            ],
            'area_name' =>      'required|string|max:100',
            'department_id' =>  'required|integer|exists:App\Models\HIS\Department,id',
        ];
    }
    public function messages()
    {
        return [
            'area_code.required'    => config('keywords')['area']['area_code'].config('keywords')['error']['required'],
            'area_code.string'      => config('keywords')['area']['area_code'].config('keywords')['error']['string'],
            'area_code.max'         => config('keywords')['area']['area_code'].config('keywords')['error']['string_max'],
            'area_code.unique'      => config('keywords')['area']['area_code'].config('keywords')['error']['unique'],

            'area_name.required'    => config('keywords')['area']['area_name'].config('keywords')['error']['required'],
            'area_name.string'      => config('keywords')['area']['area_name'].config('keywords')['error']['string'],
            'area_name.max'         => config('keywords')['area']['area_name'].config('keywords')['error']['string_max'],

            'department_id.required'    => config('keywords')['area']['department_id'].config('keywords')['error']['required'],
            'department_id.integer'     => config('keywords')['area']['department_id'].config('keywords')['error']['integer'],
            'department_id.exists'      => config('keywords')['area']['department_id'].config('keywords')['error']['exists'],

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
