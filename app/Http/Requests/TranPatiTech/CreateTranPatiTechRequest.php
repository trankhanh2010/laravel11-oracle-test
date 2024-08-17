<?php

namespace App\Http\Requests\TranPatiTech;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateTranPatiTechRequest extends FormRequest
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
            'tran_pati_tech_code' =>      'required|string|max:2|unique:App\Models\HIS\TranPatiTech,tran_pati_tech_code',
            'tran_pati_tech_name' =>      'required|string|max:100',
        ];
    }
    public function messages()
    {
        return [
            'tran_pati_tech_code.required'    => config('keywords')['tran_pati_tech']['tran_pati_tech_code'].config('keywords')['error']['required'],
            'tran_pati_tech_code.string'      => config('keywords')['tran_pati_tech']['tran_pati_tech_code'].config('keywords')['error']['string'],
            'tran_pati_tech_code.max'         => config('keywords')['tran_pati_tech']['tran_pati_tech_code'].config('keywords')['error']['string_max'],
            'tran_pati_tech_code.unique'      => config('keywords')['tran_pati_tech']['tran_pati_tech_code'].config('keywords')['error']['unique'],

            'tran_pati_tech_name.required'    => config('keywords')['tran_pati_tech']['tran_pati_tech_name'].config('keywords')['error']['required'],
            'tran_pati_tech_name.string'      => config('keywords')['tran_pati_tech']['tran_pati_tech_name'].config('keywords')['error']['string'],
            'tran_pati_tech_name.max'         => config('keywords')['tran_pati_tech']['tran_pati_tech_name'].config('keywords')['error']['string_max'],

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
