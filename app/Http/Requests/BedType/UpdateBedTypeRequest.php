<?php

namespace App\Http\Requests\BedType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateBedTypeRequest extends FormRequest
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
        if(!is_numeric($this->bed_type)){
            throw new HttpResponseException(returnIdError($this->bed_type));
        }
        return [
            'bed_type_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:10',
                                                    Rule::unique('App\Models\HIS\BedType')->ignore($this->bed_type),
                                                ],
            'bed_type_name' =>        'required|string|max:100',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'bed_type_code.required'    => config('keywords')['bed_type']['bed_type_code'].config('keywords')['error']['required'],
            'bed_type_code.string'      => config('keywords')['bed_type']['bed_type_code'].config('keywords')['error']['string'],
            'bed_type_code.max'         => config('keywords')['bed_type']['bed_type_code'].config('keywords')['error']['string_max'],
            'bed_type_code.unique'      => config('keywords')['bed_type']['bed_type_code'].config('keywords')['error']['unique'],

            'bed_type_name.string'      => config('keywords')['bed_type']['bed_type_name'].config('keywords')['error']['string'],
            'bed_type_name.max'         => config('keywords')['bed_type']['bed_type_name'].config('keywords')['error']['string_max'],
            'bed_type_name.unique'      => config('keywords')['bed_type']['bed_type_name'].config('keywords')['error']['unique'],

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