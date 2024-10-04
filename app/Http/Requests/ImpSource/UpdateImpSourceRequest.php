<?php

namespace App\Http\Requests\ImpSource;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateImpSourceRequest extends FormRequest
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
        if(!is_numeric($this->imp_source)){
            throw new HttpResponseException(returnIdError($this->imp_source));
        }
        return [
            'imp_source_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:2',
                                                    Rule::unique('App\Models\HIS\ImpSource')->ignore($this->imp_source),
                                                ],
            'imp_source_name' =>        'required|string|max:100',
            'is_default' =>                      'nullable|integer|in:0,1',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'imp_source_code.required'    => config('keywords')['imp_source']['imp_source_code'].config('keywords')['error']['required'],
            'imp_source_code.string'      => config('keywords')['imp_source']['imp_source_code'].config('keywords')['error']['string'],
            'imp_source_code.max'         => config('keywords')['imp_source']['imp_source_code'].config('keywords')['error']['string_max'],
            'imp_source_code.unique'      => config('keywords')['imp_source']['imp_source_code'].config('keywords')['error']['unique'],

            'imp_source_name.string'      => config('keywords')['imp_source']['imp_source_name'].config('keywords')['error']['string'],
            'imp_source_name.max'         => config('keywords')['imp_source']['imp_source_name'].config('keywords')['error']['string_max'],
            'imp_source_name.unique'      => config('keywords')['imp_source']['imp_source_name'].config('keywords')['error']['unique'],

            'is_default.integer'     => config('keywords')['imp_source']['is_default'].config('keywords')['error']['integer'], 
            'is_default.in'          => config('keywords')['imp_source']['is_default'].config('keywords')['error']['in'], 

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
