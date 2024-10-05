<?php

namespace App\Http\Requests\Atc;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateAtcRequest extends FormRequest
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
        if(!is_numeric($this->atc)){
            throw new HttpResponseException(returnIdError($this->atc));
        }
        return [
            'atc_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:10',
                                                    Rule::unique('App\Models\HIS\Atc')->ignore($this->atc),
                                                ],
            'atc_name' =>        'required|string|max:100',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'atc_code.required'    => config('keywords')['atc']['atc_code'].config('keywords')['error']['required'],
            'atc_code.string'      => config('keywords')['atc']['atc_code'].config('keywords')['error']['string'],
            'atc_code.max'         => config('keywords')['atc']['atc_code'].config('keywords')['error']['string_max'],
            'atc_code.unique'      => config('keywords')['atc']['atc_code'].config('keywords')['error']['unique'],

            'atc_name.required'    => config('keywords')['atc']['atc_name'].config('keywords')['error']['required'],
            'atc_name.string'      => config('keywords')['atc']['atc_name'].config('keywords')['error']['string'],
            'atc_name.max'         => config('keywords')['atc']['atc_name'].config('keywords')['error']['string_max'],
            'atc_name.unique'      => config('keywords')['atc']['atc_name'].config('keywords')['error']['unique'],

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
