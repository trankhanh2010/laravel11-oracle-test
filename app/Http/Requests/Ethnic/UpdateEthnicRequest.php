<?php

namespace App\Http\Requests\Ethnic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateEthnicRequest extends FormRequest
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
            'ethnic_code' =>        [
                                        'required',
                                        'string',
                                        'max:3',
                                        Rule::unique('App\Models\SDA\Ethnic')->ignore($this->id),
                                    ],
            'ethnic_name' =>      'required|string|max:100',
            'other_name' =>       'nullable|string|max:500',
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'ethnic_code.required'    => config('keywords')['ethnic']['ethnic_code'].config('keywords')['error']['required'],
            'ethnic_code.string'      => config('keywords')['ethnic']['ethnic_code'].config('keywords')['error']['string'],
            'ethnic_code.max'         => config('keywords')['ethnic']['ethnic_code'].config('keywords')['error']['string_max'],
            'ethnic_code.unique'      => config('keywords')['ethnic']['ethnic_code'].config('keywords')['error']['unique'],

            'ethnic_name.required'    => config('keywords')['ethnic']['ethnic_name'].config('keywords')['error']['required'],
            'ethnic_name.string'      => config('keywords')['ethnic']['ethnic_name'].config('keywords')['error']['string'],
            'ethnic_name.max'         => config('keywords')['ethnic']['ethnic_name'].config('keywords')['error']['string_max'],

            'other_name.string'      => config('keywords')['ethnic']['other_name'].config('keywords')['error']['string'],
            'other_name.max'         => config('keywords')['ethnic']['other_name'].config('keywords')['error']['string_max'],

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
