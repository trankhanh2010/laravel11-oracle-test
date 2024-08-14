<?php

namespace App\Http\Requests\CareerTitle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateCareerTitleRequest extends FormRequest
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
            'career_title_code' =>      'required|string|max:2|unique:App\Models\HIS\CareerTitle,career_title_code',
            'career_title_name' =>      'required|string|max:200',
            
        ];
    }
    public function messages()
    {
        return [
            'career_title_code.required'    => config('keywords')['career_title']['career_title_code'].config('keywords')['error']['required'],
            'career_title_code.string'      => config('keywords')['career_title']['career_title_code'].config('keywords')['error']['string'],
            'career_title_code.max'         => config('keywords')['career_title']['career_title_code'].config('keywords')['error']['string_max'],
            'career_title_code.unique'      => config('keywords')['career_title']['career_title_code'].config('keywords')['error']['unique'],

            'career_title_name.required'    => config('keywords')['career_title']['career_title_name'].config('keywords')['error']['required'],
            'career_title_name.string'      => config('keywords')['career_title']['career_title_name'].config('keywords')['error']['string'],
            'career_title_name.max'         => config('keywords')['career_title']['career_title_name'].config('keywords')['error']['string_max'],

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
