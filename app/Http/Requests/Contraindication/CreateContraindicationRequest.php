<?php

namespace App\Http\Requests\Contraindication;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateContraindicationRequest extends FormRequest
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
            'contraindication_code' =>      'required|string|max:10|unique:App\Models\HIS\Contraindication,contraindication_code',
            'contraindication_name' =>      'required|string|max:500',
            
        ];
    }
    public function messages()
    {
        return [
            'contraindication_code.required'    => config('keywords')['contraindication']['contraindication_code'].config('keywords')['error']['required'],
            'contraindication_code.string'      => config('keywords')['contraindication']['contraindication_code'].config('keywords')['error']['string'],
            'contraindication_code.max'         => config('keywords')['contraindication']['contraindication_code'].config('keywords')['error']['string_max'],
            'contraindication_code.unique'      => config('keywords')['contraindication']['contraindication_code'].config('keywords')['error']['unique'],

            'contraindication_name.required'    => config('keywords')['contraindication']['contraindication_name'].config('keywords')['error']['required'],
            'contraindication_name.string'      => config('keywords')['contraindication']['contraindication_name'].config('keywords')['error']['string'],
            'contraindication_name.max'         => config('keywords')['contraindication']['contraindication_name'].config('keywords')['error']['string_max'],

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
