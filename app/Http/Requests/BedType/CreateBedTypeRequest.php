<?php

namespace App\Http\Requests\BedType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateBedTypeRequest extends FormRequest
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
            'bed_type_code' =>      'required|string|max:10|unique:App\Models\HIS\BedType,bed_type_code',
            'bed_type_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'bed_type_code.required'    => config('keywords')['bed_type']['bed_type_code'].config('keywords')['error']['required'],
            'bed_type_code.string'      => config('keywords')['bed_type']['bed_type_code'].config('keywords')['error']['string'],
            'bed_type_code.max'         => config('keywords')['bed_type']['bed_type_code'].config('keywords')['error']['string_max'],
            'bed_type_code.unique'      => config('keywords')['bed_type']['bed_type_code'].config('keywords')['error']['unique'],

            'bed_type_name.required'    => config('keywords')['bed_type']['bed_type_name'].config('keywords')['error']['required'],
            'bed_type_name.string'      => config('keywords')['bed_type']['bed_type_name'].config('keywords')['error']['string'],
            'bed_type_name.max'         => config('keywords')['bed_type']['bed_type_name'].config('keywords')['error']['string_max'],
            'bed_type_name.unique'      => config('keywords')['bed_type']['bed_type_name'].config('keywords')['error']['unique'],

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
