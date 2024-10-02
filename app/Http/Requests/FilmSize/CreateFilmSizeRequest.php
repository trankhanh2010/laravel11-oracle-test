<?php

namespace App\Http\Requests\FilmSize;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateFilmSizeRequest extends FormRequest
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
            'film_size_code' =>      'required|string|max:20|unique:App\Models\HIS\FilmSize,film_size_code',
            'film_size_name' =>      'required|string|max:50',
            
        ];
    }
    public function messages()
    {
        return [
            'film_size_code.required'    => config('keywords')['film_size']['film_size_code'].config('keywords')['error']['required'],
            'film_size_code.string'      => config('keywords')['film_size']['film_size_code'].config('keywords')['error']['string'],
            'film_size_code.max'         => config('keywords')['film_size']['film_size_code'].config('keywords')['error']['string_max'],
            'film_size_code.unique'      => config('keywords')['film_size']['film_size_code'].config('keywords')['error']['unique'],

            'film_size_name.required'    => config('keywords')['film_size']['film_size_name'].config('keywords')['error']['required'],
            'film_size_name.string'      => config('keywords')['film_size']['film_size_name'].config('keywords')['error']['string'],
            'film_size_name.max'         => config('keywords')['film_size']['film_size_name'].config('keywords')['error']['string_max'],
            'film_size_name.unique'      => config('keywords')['film_size']['film_size_name'].config('keywords')['error']['unique'],

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
