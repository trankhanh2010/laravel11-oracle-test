<?php

namespace App\Http\Requests\ExpMestReason;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateExpMestReasonRequest extends FormRequest
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
        return [
            'exp_mest_reason_code' =>      'required|string|max:2|unique:App\Models\HIS\ExpMestReason,exp_mest_reason_code',
            'exp_mest_reason_name' =>      'required|string|max:100',
            'is_depa' =>                'nullable|integer|in:0,1',
            'is_odd' =>                 'nullable|integer|in:0,1',
        ];
    }
    public function messages()
    {
        return [
            'exp_mest_reason_code.required'    => config('keywords')['exp_mest_reason']['exp_mest_reason_code'].config('keywords')['error']['required'],
            'exp_mest_reason_code.string'      => config('keywords')['exp_mest_reason']['exp_mest_reason_code'].config('keywords')['error']['string'],
            'exp_mest_reason_code.max'         => config('keywords')['exp_mest_reason']['exp_mest_reason_code'].config('keywords')['error']['string_max'],
            'exp_mest_reason_code.unique'      => config('keywords')['exp_mest_reason']['exp_mest_reason_code'].config('keywords')['error']['unique'],

            'exp_mest_reason_name.required'    => config('keywords')['exp_mest_reason']['exp_mest_reason_name'].config('keywords')['error']['required'],
            'exp_mest_reason_name.string'      => config('keywords')['exp_mest_reason']['exp_mest_reason_name'].config('keywords')['error']['string'],
            'exp_mest_reason_name.max'         => config('keywords')['exp_mest_reason']['exp_mest_reason_name'].config('keywords')['error']['string_max'],
            'exp_mest_reason_name.unique'      => config('keywords')['exp_mest_reason']['exp_mest_reason_name'].config('keywords')['error']['unique'],

            'is_depa.integer'     => config('keywords')['exp_mest_reason']['is_depa'].config('keywords')['error']['integer'], 
            'is_depa.in'          => config('keywords')['exp_mest_reason']['is_depa'].config('keywords')['error']['in'], 

            'is_odd.integer'     => config('keywords')['exp_mest_reason']['is_odd'].config('keywords')['error']['integer'], 
            'is_odd.in'          => config('keywords')['exp_mest_reason']['is_odd'].config('keywords')['error']['in'], 
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
