<?php

namespace App\Http\Requests\MedicineUseForm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateMedicineUseFormRequest extends FormRequest
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
            'medicine_use_form_code' =>      'required|string|max:6|unique:App\Models\HIS\MedicineUseForm,medicine_use_form_code',
            'medicine_use_form_name' =>      'required|string|max:100',
            'num_order' =>    'nullable|integer',

        ];
    }
    public function messages()
    {
        return [
            'medicine_use_form_code.required'    => config('keywords')['medicine_use_form']['medicine_use_form_code'].config('keywords')['error']['required'],
            'medicine_use_form_code.string'      => config('keywords')['medicine_use_form']['medicine_use_form_code'].config('keywords')['error']['string'],
            'medicine_use_form_code.max'         => config('keywords')['medicine_use_form']['medicine_use_form_code'].config('keywords')['error']['string_max'],
            'medicine_use_form_code.unique'      => config('keywords')['medicine_use_form']['medicine_use_form_code'].config('keywords')['error']['unique'],

            'medicine_use_form_name.required'    => config('keywords')['medicine_use_form']['medicine_use_form_name'].config('keywords')['error']['required'],
            'medicine_use_form_name.string'      => config('keywords')['medicine_use_form']['medicine_use_form_name'].config('keywords')['error']['string'],
            'medicine_use_form_name.max'         => config('keywords')['medicine_use_form']['medicine_use_form_name'].config('keywords')['error']['string_max'],

            'num_order.integer'      => config('keywords')['medicine_use_form']['num_order'].config('keywords')['error']['integer'],

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
