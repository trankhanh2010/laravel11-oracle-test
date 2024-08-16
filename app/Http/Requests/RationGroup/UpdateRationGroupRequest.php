<?php

namespace App\Http\Requests\RationGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateRationGroupRequest extends FormRequest
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
            'ration_group_code' => [
                'required',
                'string',
                'max:2',
                Rule::unique('App\Models\HIS\RationGroup')->ignore($this->id),
            ],
            'ration_group_name' =>      'required|string|max:100',
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'ration_group_code.required'    => config('keywords')['ration_group']['ration_group_code'].config('keywords')['error']['required'],
            'ration_group_code.string'      => config('keywords')['ration_group']['ration_group_code'].config('keywords')['error']['string'],
            'ration_group_code.max'         => config('keywords')['ration_group']['ration_group_code'].config('keywords')['error']['string_max'],
            'ration_group_code.unique'      => config('keywords')['ration_group']['ration_group_code'].config('keywords')['error']['unique'],

            'ration_group_name.required'    => config('keywords')['ration_group']['ration_group_name'].config('keywords')['error']['required'],
            'ration_group_name.string'      => config('keywords')['ration_group']['ration_group_name'].config('keywords')['error']['string'],
            'ration_group_name.max'         => config('keywords')['ration_group']['ration_group_name'].config('keywords')['error']['string_max'],

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
