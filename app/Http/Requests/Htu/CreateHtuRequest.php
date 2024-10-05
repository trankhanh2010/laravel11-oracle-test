<?php

namespace App\Http\Requests\Htu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateHtuRequest extends FormRequest
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
            'htu_code' =>      'required|string|max:10|unique:App\Models\HIS\Htu,htu_code',
            'htu_name' =>      'required|string|max:100',
            'num_order' =>      'nullable|integer',
        ];
    }
    public function messages()
    {
        return [
            'htu_code.required'    => config('keywords')['htu']['htu_code'].config('keywords')['error']['required'],
            'htu_code.string'      => config('keywords')['htu']['htu_code'].config('keywords')['error']['string'],
            'htu_code.max'         => config('keywords')['htu']['htu_code'].config('keywords')['error']['string_max'],
            'htu_code.unique'      => config('keywords')['htu']['htu_code'].config('keywords')['error']['unique'],

            'htu_name.required'    => config('keywords')['htu']['htu_name'].config('keywords')['error']['required'],
            'htu_name.string'      => config('keywords')['htu']['htu_name'].config('keywords')['error']['string'],
            'htu_name.max'         => config('keywords')['htu']['htu_name'].config('keywords')['error']['string_max'],
            'htu_name.unique'      => config('keywords')['htu']['htu_name'].config('keywords')['error']['unique'],

            'num_order.integer'      => config('keywords')['htu']['num_order'].config('keywords')['error']['integer'],
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
