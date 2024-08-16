<?php

namespace App\Http\Requests\Position;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreatePositionRequest extends FormRequest
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
            'position_code' =>      'required|string|max:15|unique:App\Models\HIS\Position,position_code',
            'position_name' =>      'required|string|max:100',
            'description' =>        'nullable|string|max:500',
        ];
    }
    public function messages()
    {
        return [
            'position_code.required'    => config('keywords')['position']['position_code'].config('keywords')['error']['required'],
            'position_code.string'      => config('keywords')['position']['position_code'].config('keywords')['error']['string'],
            'position_code.max'         => config('keywords')['position']['position_code'].config('keywords')['error']['string_max'],
            'position_code.unique'      => config('keywords')['position']['position_code'].config('keywords')['error']['unique'],

            'position_name.required'    => config('keywords')['position']['position_name'].config('keywords')['error']['required'],
            'position_name.string'      => config('keywords')['position']['position_name'].config('keywords')['error']['string'],
            'position_name.max'         => config('keywords')['position']['position_name'].config('keywords')['error']['string_max'],

            'description.string'      => config('keywords')['position']['description'].config('keywords')['error']['string'],
            'description.max'         => config('keywords')['position']['description'].config('keywords')['error']['string_max'],

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
