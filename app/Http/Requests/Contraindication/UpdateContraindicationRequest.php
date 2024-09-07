<?php

namespace App\Http\Requests\Contraindication;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateContraindicationRequest extends FormRequest
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
        if(!is_numeric($this->contraindication)){
            throw new HttpResponseException(returnIdError($this->contraindication));
        }
        return [
            'contraindication_code' =>        [
                                            'required',
                                            'string',
                                            'max:10',
                                            Rule::unique('App\Models\HIS\Contraindication')->ignore($this->contraindication),
                                        ],
            'contraindication_name' =>      'required|string|max:500',
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'contraindication_code.required'    => config('keywords')['contraindication']['contraindication_code'].config('keywords')['error']['required'],
            'contraindication_code.string'      => config('keywords')['contraindication']['contraindication_code'].config('keywords')['error']['string'],
            'contraindication_code.max'         => config('keywords')['contraindication']['contraindication_code'].config('keywords')['error']['string_max'],
            'contraindication_code.unique'      => config('keywords')['contraindication']['contraindication_code'].config('keywords')['error']['unique'],

            'contraindication_name.required'    => config('keywords')['contraindication']['contraindication_name'].config('keywords')['error']['required'],
            'contraindication_name.string'      => config('keywords')['contraindication']['contraindication_name'].config('keywords')['error']['string'],
            'contraindication_name.max'         => config('keywords')['contraindication']['contraindication_name'].config('keywords')['error']['string_max'],

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
