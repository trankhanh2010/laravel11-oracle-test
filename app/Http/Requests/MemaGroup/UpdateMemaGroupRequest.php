<?php

namespace App\Http\Requests\MemaGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateMemaGroupRequest extends FormRequest
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
        if(!is_numeric($this->mema_group)){
            throw new HttpResponseException(returnIdError($this->mema_group));
        }
        return [
            'mema_group_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:6',
                                                    Rule::unique('App\Models\HIS\MemaGroup')->ignore($this->mema_group),
                                                ],
            'mema_group_name' =>        'required|string|max:100',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'mema_group_code.required'    => config('keywords')['mema_group']['mema_group_code'].config('keywords')['error']['required'],
            'mema_group_code.string'      => config('keywords')['mema_group']['mema_group_code'].config('keywords')['error']['string'],
            'mema_group_code.max'         => config('keywords')['mema_group']['mema_group_code'].config('keywords')['error']['string_max'],
            'mema_group_code.unique'      => config('keywords')['mema_group']['mema_group_code'].config('keywords')['error']['unique'],

            'mema_group_name.required'    => config('keywords')['mema_group']['mema_group_name'].config('keywords')['error']['required'],
            'mema_group_name.string'      => config('keywords')['mema_group']['mema_group_name'].config('keywords')['error']['string'],
            'mema_group_name.max'         => config('keywords')['mema_group']['mema_group_name'].config('keywords')['error']['string_max'],
            'mema_group_name.unique'      => config('keywords')['mema_group']['mema_group_name'].config('keywords')['error']['unique'],

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
