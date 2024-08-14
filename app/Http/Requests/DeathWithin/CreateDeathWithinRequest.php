<?php

namespace App\Http\Requests\DeathWithin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateDeathWithinRequest extends FormRequest
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
            'death_within_code' =>      'required|string|max:10|unique:App\Models\HIS\DeathWithin,death_within_code',
            'death_within_name' =>      'required|string|max:500',
            
        ];
    }
    public function messages()
    {
        return [
            'death_within_code.required'    => config('keywords')['death_within']['death_within_code'].config('keywords')['error']['required'],
            'death_within_code.string'      => config('keywords')['death_within']['death_within_code'].config('keywords')['error']['string'],
            'death_within_code.max'         => config('keywords')['death_within']['death_within_code'].config('keywords')['error']['string_max'],
            'death_within_code.unique'      => config('keywords')['death_within']['death_within_code'].config('keywords')['error']['unique'],

            'death_within_name.required'    => config('keywords')['death_within']['death_within_name'].config('keywords')['error']['required'],
            'death_within_name.string'      => config('keywords')['death_within']['death_within_name'].config('keywords')['error']['string'],
            'death_within_name.max'         => config('keywords')['death_within']['death_within_name'].config('keywords')['error']['string_max'],

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
