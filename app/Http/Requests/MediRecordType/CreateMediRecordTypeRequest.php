<?php

namespace App\Http\Requests\MediRecordType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateMediRecordTypeRequest extends FormRequest
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
        return [
            'medi_record_type_code' =>      'required|string|max:10|unique:App\Models\HIS\MediRecordType,medi_record_type_code',
            'medi_record_type_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'medi_record_type_code.required'    => config('keywords')['medi_record_type']['medi_record_type_code'].config('keywords')['error']['required'],
            'medi_record_type_code.string'      => config('keywords')['medi_record_type']['medi_record_type_code'].config('keywords')['error']['string'],
            'medi_record_type_code.max'         => config('keywords')['medi_record_type']['medi_record_type_code'].config('keywords')['error']['string_max'],
            'medi_record_type_code.unique'      => config('keywords')['medi_record_type']['medi_record_type_code'].config('keywords')['error']['unique'],

            'medi_record_type_name.required'    => config('keywords')['medi_record_type']['medi_record_type_name'].config('keywords')['error']['required'],
            'medi_record_type_name.string'      => config('keywords')['medi_record_type']['medi_record_type_name'].config('keywords')['error']['string'],
            'medi_record_type_name.max'         => config('keywords')['medi_record_type']['medi_record_type_name'].config('keywords')['error']['string_max'],

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
