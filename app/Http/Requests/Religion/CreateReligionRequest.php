<?php

namespace App\Http\Requests\Religion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateReligionRequest extends FormRequest
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
            'religion_code' =>      'required|string|max:2|unique:App\Models\SDA\Religion,religion_code',
            'religion_name' =>      'required|string|max:100',
        ];
    }
    public function messages()
    {
        return [
            'religion_code.required'    => config('keywords')['religion']['religion_code'].config('keywords')['error']['required'],
            'religion_code.string'      => config('keywords')['religion']['religion_code'].config('keywords')['error']['string'],
            'religion_code.max'         => config('keywords')['religion']['religion_code'].config('keywords')['error']['string_max'],
            'religion_code.unique'      => config('keywords')['religion']['religion_code'].config('keywords')['error']['unique'],

            'religion_name.required'    => config('keywords')['religion']['religion_name'].config('keywords')['error']['required'],
            'religion_name.string'      => config('keywords')['religion']['religion_name'].config('keywords')['error']['string'],
            'religion_name.max'         => config('keywords')['religion']['religion_name'].config('keywords')['error']['string_max'],

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
