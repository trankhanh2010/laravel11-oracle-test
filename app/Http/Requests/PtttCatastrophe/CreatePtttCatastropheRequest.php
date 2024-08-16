<?php

namespace App\Http\Requests\PtttCatastrophe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreatePtttCatastropheRequest extends FormRequest
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
            'pttt_catastrophe_code' =>      'required|string|max:2|unique:App\Models\HIS\PtttCatastrophe,pttt_catastrophe_code',
            'pttt_catastrophe_name' =>      'required|string|max:100',
            
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
