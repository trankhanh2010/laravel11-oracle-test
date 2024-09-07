<?php

namespace App\Http\Requests\BloodGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateBloodGroupRequest extends FormRequest
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
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->blood_group)){
            throw new HttpResponseException(returnIdError($this->blood_group));
        }
        return [
            'blood_group_code' =>        [
                                            'required',
                                            'string',
                                            'max:2',
                                            Rule::unique('App\Models\HIS\BloodGroup')->ignore($this->blood_group),
                                        ],
            'blood_group_name' =>       'required|string|max:100',
            'blood_erythrocyte' =>      [
                                            'nullable',
                                            'integer',
                                            'in:0,1',
                                            function ($attribute, $value, $fail)  {
                                                if (($this->blood_plasma == 1) && ($value == 1)) {
                                                    $fail(config('keywords')['blood_group']['blood_erythrocyte'].' chỉ được chọn khi '.config('keywords')['blood_group']['blood_plasma'].' trống!');
                                                }
                                            },
                                        ],
            'blood_plasma' =>           [
                                            'nullable',
                                            'integer',
                                            'in:0,1',
                                            function ($attribute, $value, $fail)  {
                                                if (($this->blood_erythrocyte == 1) && ($value == 1)) {
                                                    $fail(config('keywords')['blood_group']['blood_plasma'].' chỉ được chọn khi '.config('keywords')['blood_group']['blood_erythrocyte'].' trống!');
                                                }
                                            },
                                        ],
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'blood_group_code.required'    => config('keywords')['blood_group']['blood_group_code'].config('keywords')['error']['required'],
            'blood_group_code.string'      => config('keywords')['blood_group']['blood_group_code'].config('keywords')['error']['string'],
            'blood_group_code.max'         => config('keywords')['blood_group']['blood_group_code'].config('keywords')['error']['string_max'],
            'blood_group_code.unique'      => config('keywords')['blood_group']['blood_group_code'].config('keywords')['error']['unique'],

            'blood_group_name.string'      => config('keywords')['blood_group']['blood_group_name'].config('keywords')['error']['string'],
            'blood_group_name.max'         => config('keywords')['blood_group']['blood_group_name'].config('keywords')['error']['string_max'],
            'blood_group_name.unique'      => config('keywords')['blood_group']['blood_group_name'].config('keywords')['error']['unique'],

            'blood_erythrocyte.integer'      => config('keywords')['blood_group']['blood_erythrocyte'].config('keywords')['error']['integer'],
            'blood_erythrocyte.in'         => config('keywords')['blood_group']['blood_erythrocyte'].config('keywords')['error']['in'],

            'blood_plasma.integer'      => config('keywords')['blood_group']['blood_plasma'].config('keywords')['error']['integer'],
            'blood_plasma.in'         => config('keywords')['blood_group']['blood_plasma'].config('keywords')['error']['in'],

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
