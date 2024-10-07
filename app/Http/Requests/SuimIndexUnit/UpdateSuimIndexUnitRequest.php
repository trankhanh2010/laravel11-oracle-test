<?php

namespace App\Http\Requests\SuimIndexUnit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateSuimIndexUnitRequest extends FormRequest
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
        if(!is_numeric($this->suim_index_unit)){
            throw new HttpResponseException(returnIdError($this->suim_index_unit));
        }
        return [
            'suim_index_unit_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:2',
                                                    Rule::unique('App\Models\HIS\SuimIndexUnit')->ignore($this->suim_index_unit),
                                                ],
            'suim_index_unit_name' =>        'required|string|max:100',
            'suim_index_unit_symbol' =>      'nullable|string|max:10',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'suim_index_unit_code.required'    => config('keywords')['suim_index_unit']['suim_index_unit_code'].config('keywords')['error']['required'],
            'suim_index_unit_code.string'      => config('keywords')['suim_index_unit']['suim_index_unit_code'].config('keywords')['error']['string'],
            'suim_index_unit_code.max'         => config('keywords')['suim_index_unit']['suim_index_unit_code'].config('keywords')['error']['string_max'],
            'suim_index_unit_code.unique'      => config('keywords')['suim_index_unit']['suim_index_unit_code'].config('keywords')['error']['unique'],

            'suim_index_unit_name.required'    => config('keywords')['suim_index_unit']['suim_index_unit_name'].config('keywords')['error']['required'],
            'suim_index_unit_name.string'      => config('keywords')['suim_index_unit']['suim_index_unit_name'].config('keywords')['error']['string'],
            'suim_index_unit_name.max'         => config('keywords')['suim_index_unit']['suim_index_unit_name'].config('keywords')['error']['string_max'],
            'suim_index_unit_name.unique'      => config('keywords')['suim_index_unit']['suim_index_unit_name'].config('keywords')['error']['unique'],

            'suim_index_unit_symbol.string'      => config('keywords')['suim_index_unit']['suim_index_unit_symbol'].config('keywords')['error']['string'],
            'suim_index_unit_symbol.max'         => config('keywords')['suim_index_unit']['suim_index_unit_symbol'].config('keywords')['error']['string_max'],

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
