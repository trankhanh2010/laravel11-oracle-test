<?php

namespace App\Http\Requests\Htu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateHtuRequest extends FormRequest
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
        if(!is_numeric($this->htu)){
            throw new HttpResponseException(returnIdError($this->htu));
        }
        return [
            'htu_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:10',
                                                    Rule::unique('App\Models\HIS\Htu')->ignore($this->htu),
                                                ],
            'htu_name' =>        'required|string|max:100',
            'num_order' =>      'nullable|integer',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'htu_code.required'    => config('keywords')['htu']['htu_code'].config('keywords')['error']['required'],
            'htu_code.string'      => config('keywords')['htu']['htu_code'].config('keywords')['error']['string'],
            'htu_code.max'         => config('keywords')['htu']['htu_code'].config('keywords')['error']['string_max'],
            'htu_code.unique'      => config('keywords')['htu']['htu_code'].config('keywords')['error']['unique'],

            'htu_name.required'    => config('keywords')['htu']['htu_name'].config('keywords')['error']['required'],
            'htu_name.string'      => config('keywords')['htu']['htu_name'].config('keywords')['error']['string'],
            'htu_name.max'         => config('keywords')['htu']['htu_name'].config('keywords')['error']['string_max'],
            'htu_name.unique'      => config('keywords')['htu']['htu_name'].config('keywords')['error']['unique'],

            'num_order.integer'      => config('keywords')['htu']['num_order'].config('keywords')['error']['integer'],

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
