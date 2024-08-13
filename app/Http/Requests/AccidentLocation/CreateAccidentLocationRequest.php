<?php

namespace App\Http\Requests\AccidentLocation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateAccidentLocationRequest extends FormRequest
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
            'accident_location_code' =>      'required|string|max:2|unique:App\Models\HIS\AccidentLocation,accident_location_code',
            'accident_location_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'accident_location_code.required'    => config('keywords')['accident_location']['accident_location_code'].config('keywords')['error']['required'],
            'accident_location_code.string'      => config('keywords')['accident_location']['accident_location_code'].config('keywords')['error']['string'],
            'accident_location_code.max'         => config('keywords')['accident_location']['accident_location_code'].config('keywords')['error']['string_max'],
            'accident_location_code.unique'      => config('keywords')['accident_location']['accident_location_code'].config('keywords')['error']['unique'],

            'accident_location_name.string'      => config('keywords')['accident_location']['accident_location_name'].config('keywords')['error']['string'],
            'accident_location_name.max'         => config('keywords')['accident_location']['accident_location_name'].config('keywords')['error']['string_max'],
            'accident_location_name.unique'      => config('keywords')['accident_location']['accident_location_name'].config('keywords')['error']['unique'],

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
