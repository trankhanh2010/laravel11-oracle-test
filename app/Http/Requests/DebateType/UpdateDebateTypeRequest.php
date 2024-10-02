<?php

namespace App\Http\Requests\DebateType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateDebateTypeRequest extends FormRequest
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
        if(!is_numeric($this->debate_type)){
            throw new HttpResponseException(returnIdError($this->debate_type));
        }
        return [
            'debate_type_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:10',
                                                    Rule::unique('App\Models\HIS\DebateType')->ignore($this->debate_type),
                                                ],
            'debate_type_name' =>        'required|string|max:100',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'debate_type_code.required'    => config('keywords')['debate_type']['debate_type_code'].config('keywords')['error']['required'],
            'debate_type_code.string'      => config('keywords')['debate_type']['debate_type_code'].config('keywords')['error']['string'],
            'debate_type_code.max'         => config('keywords')['debate_type']['debate_type_code'].config('keywords')['error']['string_max'],
            'debate_type_code.unique'      => config('keywords')['debate_type']['debate_type_code'].config('keywords')['error']['unique'],

            'debate_type_name.string'      => config('keywords')['debate_type']['debate_type_name'].config('keywords')['error']['string'],
            'debate_type_name.max'         => config('keywords')['debate_type']['debate_type_name'].config('keywords')['error']['string_max'],
            'debate_type_name.unique'      => config('keywords')['debate_type']['debate_type_name'].config('keywords')['error']['unique'],

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
