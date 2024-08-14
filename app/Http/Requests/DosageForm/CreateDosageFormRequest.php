<?php

namespace App\Http\Requests\DosageForm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateDosageFormRequest extends FormRequest
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
            'dosage_form_code' =>      'required|string|max:5|unique:App\Models\HIS\DosageForm,dosage_form_code',
            'dosage_form_name' =>      'required|string|max:2000',
            
        ];
    }
    public function messages()
    {
        return [
            'dosage_form_code.required'    => config('keywords')['dosage_form']['dosage_form_code'].config('keywords')['error']['required'],
            'dosage_form_code.string'      => config('keywords')['dosage_form']['dosage_form_code'].config('keywords')['error']['string'],
            'dosage_form_code.max'         => config('keywords')['dosage_form']['dosage_form_code'].config('keywords')['error']['string_max'],
            'dosage_form_code.unique'      => config('keywords')['dosage_form']['dosage_form_code'].config('keywords')['error']['unique'],

            'dosage_form_name.required'    => config('keywords')['dosage_form']['dosage_form_name'].config('keywords')['error']['required'],
            'dosage_form_name.string'      => config('keywords')['dosage_form']['dosage_form_name'].config('keywords')['error']['string'],
            'dosage_form_name.max'         => config('keywords')['dosage_form']['dosage_form_name'].config('keywords')['error']['string_max'],

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
