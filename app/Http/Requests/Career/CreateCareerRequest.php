<?php

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateCareerRequest extends FormRequest
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
            'career_code' =>      'required|string|max:10|unique:App\Models\HIS\Career,career_code',
            'career_name' =>      'required|string|max:1000',
        ];
    }
    public function messages()
    {
        return [
            'career_code.required'    => config('keywords')['career']['career_code'].config('keywords')['error']['required'],
            'career_code.string'      => config('keywords')['career']['career_code'].config('keywords')['error']['string'],
            'career_code.max'         => config('keywords')['career']['career_code'].config('keywords')['error']['string_max'],
            'career_code.unique'      => config('keywords')['career']['career_code'].config('keywords')['error']['unique'],

            'career_name.required'    => config('keywords')['career']['career_name'].config('keywords')['error']['required'],
            'career_name.string'      => config('keywords')['career']['career_name'].config('keywords')['error']['string'],
            'career_name.max'         => config('keywords')['career']['career_name'].config('keywords')['error']['string_max'],

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