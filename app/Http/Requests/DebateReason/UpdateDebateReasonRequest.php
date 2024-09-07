<?php

namespace App\Http\Requests\DebateReason;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateDebateReasonRequest extends FormRequest
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
        if(!is_numeric($this->debate_reason)){
            throw new HttpResponseException(returnIdError($this->debate_reason));
        }        
        return [
            'debate_reason_code' =>        [
                                            'required',
                                            'string',
                                            'max:10',
                                            Rule::unique('App\Models\HIS\DebateReason')->ignore($this->debate_reason),
                                        ],
            'debate_reason_name' =>      'required|string|max:100',
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'debate_reason_code.required'    => config('keywords')['debate_reason']['debate_reason_code'].config('keywords')['error']['required'],
            'debate_reason_code.string'      => config('keywords')['debate_reason']['debate_reason_code'].config('keywords')['error']['string'],
            'debate_reason_code.max'         => config('keywords')['debate_reason']['debate_reason_code'].config('keywords')['error']['string_max'],
            'debate_reason_code.unique'      => config('keywords')['debate_reason']['debate_reason_code'].config('keywords')['error']['unique'],

            'debate_reason_name.required'    => config('keywords')['debate_reason']['debate_reason_name'].config('keywords')['error']['required'],
            'debate_reason_name.string'      => config('keywords')['debate_reason']['debate_reason_name'].config('keywords')['error']['string'],
            'debate_reason_name.max'         => config('keywords')['debate_reason']['debate_reason_name'].config('keywords')['error']['string_max'],

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
