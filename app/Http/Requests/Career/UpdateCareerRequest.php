<?php

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateCareerRequest extends FormRequest
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
        if(!is_numeric($this->career)){
            throw new HttpResponseException(returnIdError($this->career));
        }
        return [
            'career_code' =>        [
                                        'required',
                                        'string',
                                        'max:10',
                                        Rule::unique('App\Models\HIS\Career')->ignore($this->career),
                                    ],
            'career_name' =>      'required|string|max:1000',
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'career_code.required'    => config('keywords')['career']['career_code'].config('keywords')['error']['required'],
            'career_code.string'      => config('keywords')['career']['career_code'].config('keywords')['error']['string'],
            'career_code.max'         => config('keywords')['career']['career_code'].config('keywords')['error']['string_max'],
            'career_code.unique'      => config('keywords')['career']['career_code'].config('keywords')['error']['unique'],

            'career_name.required'    => config('keywords')['career']['career_name'].config('keywords')['error']['required'],
            'career_name.string'      => config('keywords')['career']['career_name'].config('keywords')['error']['string'],
            'career_name.max'         => config('keywords')['career']['career_name'].config('keywords')['error']['string_max'],

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
