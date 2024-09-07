<?php

namespace App\Http\Requests\PtttCatastrophe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdatePtttCatastropheRequest extends FormRequest
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
        if(!is_numeric($this->pttt_catastrophe)){
            throw new HttpResponseException(returnIdError($this->pttt_catastrophe));
        }
        return [
            'pttt_catastrophe_code' => [
                                        'required',
                                        'string',
                                        'max:2',
                                        Rule::unique('App\Models\HIS\PtttCatastrophe')->ignore($this->pttt_catastrophe),
                                    ],
            'pttt_catastrophe_name' =>      'required|string|max:100',
            'is_active' =>               'required|integer|in:0,1'
        ];
    }
    public function messages()
    {
        return [
            'pttt_catastrophe_code.required'    => config('keywords')['pttt_catastrophe']['pttt_catastrophe_code'].config('keywords')['error']['required'],
            'pttt_catastrophe_code.string'      => config('keywords')['pttt_catastrophe']['pttt_catastrophe_code'].config('keywords')['error']['string'],
            'pttt_catastrophe_code.max'         => config('keywords')['pttt_catastrophe']['pttt_catastrophe_code'].config('keywords')['error']['string_max'],
            'pttt_catastrophe_code.unique'      => config('keywords')['pttt_catastrophe']['pttt_catastrophe_code'].config('keywords')['error']['unique'],

            'pttt_catastrophe_name.required'    => config('keywords')['pttt_catastrophe']['pttt_catastrophe_name'].config('keywords')['error']['required'],
            'pttt_catastrophe_name.string'      => config('keywords')['pttt_catastrophe']['pttt_catastrophe_name'].config('keywords')['error']['string'],
            'pttt_catastrophe_name.max'         => config('keywords')['pttt_catastrophe']['pttt_catastrophe_name'].config('keywords')['error']['string_max'],

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
