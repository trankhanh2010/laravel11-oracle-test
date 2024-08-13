<?php

namespace App\Http\Requests\Awareness;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateAwarenessRequest extends FormRequest
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
            'awareness_code' =>      'required|string|max:2|unique:App\Models\HIS\Awareness,awareness_code',
            'awareness_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'awareness_code.required'    => config('keywords')['awareness']['awareness_code'].config('keywords')['error']['required'],
            'awareness_code.string'      => config('keywords')['awareness']['awareness_code'].config('keywords')['error']['string'],
            'awareness_code.max'         => config('keywords')['awareness']['awareness_code'].config('keywords')['error']['string_max'],
            'awareness_code.unique'      => config('keywords')['awareness']['awareness_code'].config('keywords')['error']['unique'],

            'awareness_name.string'      => config('keywords')['awareness']['awareness_name'].config('keywords')['error']['string'],
            'awareness_name.max'         => config('keywords')['awareness']['awareness_name'].config('keywords')['error']['string_max'],
            'awareness_name.unique'      => config('keywords')['awareness']['awareness_name'].config('keywords')['error']['unique'],

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
