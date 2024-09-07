<?php

namespace App\Http\Requests\AccidentBodyPart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateAccidentBodyPartRequest extends FormRequest
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
        if(!is_numeric($this->accident_body_part)){
            throw new HttpResponseException(returnIdError($this->accident_body_part));
        }
        return [
            'accident_body_part_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:2',
                                                    Rule::unique('App\Models\HIS\AccidentBodyPart')->ignore($this->accident_body_part),
                                                ],
            'accident_body_part_name' =>        'required|string|max:100',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'accident_body_part_code.required'    => config('keywords')['accident_body_part']['accident_body_part_code'].config('keywords')['error']['required'],
            'accident_body_part_code.string'      => config('keywords')['accident_body_part']['accident_body_part_code'].config('keywords')['error']['string'],
            'accident_body_part_code.max'         => config('keywords')['accident_body_part']['accident_body_part_code'].config('keywords')['error']['string_max'],
            'accident_body_part_code.unique'      => config('keywords')['accident_body_part']['accident_body_part_code'].config('keywords')['error']['unique'],

            'accident_body_part_name.string'      => config('keywords')['accident_body_part']['accident_body_part_name'].config('keywords')['error']['string'],
            'accident_body_part_name.max'         => config('keywords')['accident_body_part']['accident_body_part_name'].config('keywords')['error']['string_max'],
            'accident_body_part_name.unique'      => config('keywords')['accident_body_part']['accident_body_part_name'].config('keywords')['error']['unique'],

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
