<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateRoleRequest extends FormRequest
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
        if(!is_numeric($this->id)){
            throw new HttpResponseException(return_id_error($this->id));
        }
        return [
            'role_code' => [
                'required',
                'string',
                'max:8',
                Rule::unique('App\Models\ACS\Role')->ignore($this->id),
            ],
            'role_name' =>      'required|string|max:100',
            'is_full' =>        'nullable|integer|in:0,1',
            'is_active' =>             'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'role_code.required'    => config('keywords')['role']['role_code'].config('keywords')['error']['required'],
            'role_code.string'      => config('keywords')['role']['role_code'].config('keywords')['error']['string'],
            'role_code.max'         => config('keywords')['role']['role_code'].config('keywords')['error']['string_max'],
            'role_code.unique'      => config('keywords')['role']['role_code'].config('keywords')['error']['unique'],

            'role_name.required'    => config('keywords')['role']['role_name'].config('keywords')['error']['required'],
            'role_name.string'      => config('keywords')['role']['role_name'].config('keywords')['error']['string'],
            'role_name.max'         => config('keywords')['role']['role_name'].config('keywords')['error']['string_max'],

            'is_full.integer'      => config('keywords')['role']['is_full'].config('keywords')['error']['integer'],
            'is_full.in'         => config('keywords')['role']['is_full'].config('keywords')['error']['in'],

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
