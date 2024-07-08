<?php

namespace App\Http\Requests\ExecuteRole;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateExecuteRoleRequest extends FormRequest
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
            'execute_role_code' =>      [
                'required',
                'string',
                'max:10',
                Rule::unique('App\Models\HIS\ExecuteRole')->ignore($this->id),
            ],
            'execute_role_name' =>      'required|string|max:200',
            'is_title' =>               'nullable|integer|in:0,1',
            'is_surgry' =>              'nullable|integer|in:0,1',
            'is_stock' =>               'nullable|integer|in:0,1',
            'is_position' =>            'nullable|integer|in:0,1',
            'is_surg_main' =>           'nullable|integer|in:0,1',
            'is_subclinical' =>         'nullable|integer|in:0,1',
            'is_subclinical_result' =>  'nullable|integer|in:0,1',
            'allow_simultaneity' =>     'nullable|integer|in:0,1',
            'is_single_in_ekip' =>      'nullable|integer|in:0,1',
            'is_disable_in_ekip' =>     'nullable|integer|in:0,1',
            'is_active' =>              'required|integer|in:0,1'


        ];
    }
    public function messages()
    {
        return [
            'execute_role_code.required'    => config('keywords')['execute_role']['execute_role_code'].config('keywords')['error']['required'],
            'execute_role_code.string'      => config('keywords')['execute_role']['execute_role_code'].config('keywords')['error']['string'],
            'execute_role_code.max'         => config('keywords')['execute_role']['execute_role_code'].config('keywords')['error']['string_max'],
            'execute_role_code.unique'      => config('keywords')['execute_role']['execute_role_code'].config('keywords')['error']['unique'],

            'execute_role_name.required'    => config('keywords')['execute_role']['execute_role_name'].config('keywords')['error']['required'],
            'execute_role_name.string'      => config('keywords')['execute_role']['execute_role_name'].config('keywords')['error']['string'],
            'execute_role_name.max'         => config('keywords')['execute_role']['execute_role_name'].config('keywords')['error']['string_max'],

            'is_title.integer'      => config('keywords')['execute_role']['is_title'].config('keywords')['error']['integer'],
            'is_title.in'           => config('keywords')['execute_role']['is_title'].config('keywords')['error']['in'],

            'is_surgry.integer'     => config('keywords')['execute_role']['is_surgry'].config('keywords')['error']['integer'],
            'is_surgry.in'          => config('keywords')['execute_role']['is_surgry'].config('keywords')['error']['in'],

            'is_stock.integer'      => config('keywords')['execute_role']['is_stock'].config('keywords')['error']['integer'],
            'is_stock.in'           => config('keywords')['execute_role']['is_stock'].config('keywords')['error']['in'],

            'is_position.integer'   => config('keywords')['execute_role']['is_position'].config('keywords')['error']['integer'],
            'is_position.in'        => config('keywords')['execute_role']['is_position'].config('keywords')['error']['in'],

            'is_surg_main.integer'  => config('keywords')['execute_role']['is_surg_main'].config('keywords')['error']['integer'],
            'is_surg_main.in'       => config('keywords')['execute_role']['is_surg_main'].config('keywords')['error']['in'],

            'is_subclinical.integer'    => config('keywords')['execute_role']['is_subclinical'].config('keywords')['error']['integer'],
            'is_subclinical.in'         => config('keywords')['execute_role']['is_subclinical'].config('keywords')['error']['in'],

            'is_subclinical_result.integer'     => config('keywords')['execute_role']['is_subclinical_result'].config('keywords')['error']['integer'],
            'is_subclinical_result.in'          => config('keywords')['execute_role']['is_subclinical_result'].config('keywords')['error']['in'],

            'allow_simultaneity.integer'    => config('keywords')['execute_role']['allow_simultaneity'].config('keywords')['error']['integer'],
            'allow_simultaneity.in'         => config('keywords')['execute_role']['allow_simultaneity'].config('keywords')['error']['in'],

            'is_single_in_ekip.integer'     => config('keywords')['execute_role']['is_single_in_ekip'].config('keywords')['error']['integer'],
            'is_single_in_ekip.in'          => config('keywords')['execute_role']['is_single_in_ekip'].config('keywords')['error']['in'],

            'is_disable_in_ekip.integer'    => config('keywords')['execute_role']['is_disable_in_ekip'].config('keywords')['error']['integer'],
            'is_disable_in_ekip.in'         => config('keywords')['execute_role']['is_disable_in_ekip'].config('keywords')['error']['in'],

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
