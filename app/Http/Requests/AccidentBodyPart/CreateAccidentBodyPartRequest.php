<?php

namespace App\Http\Requests\AccidentBodyPart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateAccidentBodyPartRequest extends FormRequest
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
            'accident_body_part_code' =>      'required|string|max:2|unique:App\Models\HIS\AccidentBodyPart,accident_body_part_code',
            'accident_body_part_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'accident_body_part_code.required'    => config('keywords')['accident_body_part']['accident_body_part_code'].config('keywords')['error']['required'],
            'accident_body_part_code.string'      => config('keywords')['accident_body_part']['accident_body_part_code'].config('keywords')['error']['string'],
            'accident_body_part_code.max'         => config('keywords')['accident_body_part']['accident_body_part_code'].config('keywords')['error']['string_max'],
            'accident_body_part_code.unique'      => config('keywords')['accident_body_part']['accident_body_part_code'].config('keywords')['error']['unique'],

            'accident_body_part_name.required'    => config('keywords')['accident_body_part']['accident_body_part_name'].config('keywords')['error']['required'],
            'accident_body_part_name.string'      => config('keywords')['accident_body_part']['accident_body_part_name'].config('keywords')['error']['string'],
            'accident_body_part_name.max'         => config('keywords')['accident_body_part']['accident_body_part_name'].config('keywords')['error']['string_max'],
            'accident_body_part_name.unique'      => config('keywords')['accident_body_part']['accident_body_part_name'].config('keywords')['error']['unique'],

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
