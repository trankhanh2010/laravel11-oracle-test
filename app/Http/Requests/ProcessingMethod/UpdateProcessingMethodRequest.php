<?php

namespace App\Http\Requests\ProcessingMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateProcessingMethodRequest extends FormRequest
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
        if(!is_numeric($this->processing_method)){
            throw new HttpResponseException(returnIdError($this->processing_method));
        }
        return [
            'processing_method_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:20',
                                                    Rule::unique('App\Models\HIS\ProcessingMethod')->ignore($this->processing_method),
                                                ],
            'processing_method_name' =>        'required|string|max:500',
            'processing_method_type' =>      'nullable|integer|in:1,2',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'processing_method_code.required'    => config('keywords')['processing_method']['processing_method_code'].config('keywords')['error']['required'],
            'processing_method_code.string'      => config('keywords')['processing_method']['processing_method_code'].config('keywords')['error']['string'],
            'processing_method_code.max'         => config('keywords')['processing_method']['processing_method_code'].config('keywords')['error']['string_max'],
            'processing_method_code.unique'      => config('keywords')['processing_method']['processing_method_code'].config('keywords')['error']['unique'],

            'processing_method_name.required'    => config('keywords')['processing_method']['processing_method_name'].config('keywords')['error']['required'],
            'processing_method_name.string'      => config('keywords')['processing_method']['processing_method_name'].config('keywords')['error']['string'],
            'processing_method_name.max'         => config('keywords')['processing_method']['processing_method_name'].config('keywords')['error']['string_max'],
            'processing_method_name.unique'      => config('keywords')['processing_method']['processing_method_name'].config('keywords')['error']['unique'],

            'processing_method_type.integer'      => config('keywords')['processing_method']['processing_method_type'].config('keywords')['error']['integer'],
            'processing_method_type.in'      => config('keywords')['processing_method']['processing_method_type'].config('keywords')['error']['in'],
            
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
