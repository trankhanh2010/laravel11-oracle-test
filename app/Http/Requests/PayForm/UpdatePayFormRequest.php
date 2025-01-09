<?php

namespace App\Http\Requests\PayForm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdatePayFormRequest extends FormRequest
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
        if(!is_numeric($this->pay_form)){
            throw new HttpResponseException(returnIdError($this->pay_form));
        }
        return [
            'pay_form_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:2',
                                                    Rule::unique('App\Models\HIS\PayForm')->ignore($this->pay_form),
                                                ],
            'pay_form_name' =>        'required|string|max:100',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'pay_form_code.required'    => config('keywords')['pay_form']['pay_form_code'].config('keywords')['error']['required'],
            'pay_form_code.string'      => config('keywords')['pay_form']['pay_form_code'].config('keywords')['error']['string'],
            'pay_form_code.max'         => config('keywords')['pay_form']['pay_form_code'].config('keywords')['error']['string_max'],
            'pay_form_code.unique'      => config('keywords')['pay_form']['pay_form_code'].config('keywords')['error']['unique'],

            'pay_form_name.required'    => config('keywords')['pay_form']['pay_form_name'].config('keywords')['error']['required'],
            'pay_form_name.string'      => config('keywords')['pay_form']['pay_form_name'].config('keywords')['error']['string'],
            'pay_form_name.max'         => config('keywords')['pay_form']['pay_form_name'].config('keywords')['error']['string_max'],
            'pay_form_name.unique'      => config('keywords')['pay_form']['pay_form_name'].config('keywords')['error']['unique'],

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
