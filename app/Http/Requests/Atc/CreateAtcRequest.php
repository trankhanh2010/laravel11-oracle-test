<?php

namespace App\Http\Requests\Atc;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateAtcRequest extends FormRequest
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
            'atc_code' =>      'required|string|max:10|unique:App\Models\HIS\Atc,atc_code',
            'atc_name' =>      'required|string|max:100',
            
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