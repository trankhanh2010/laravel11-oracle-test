<?php

namespace App\Http\Requests\DosageForm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateDosageFormRequest extends FormRequest
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
             'dosage_form_name' =>      'required|string|max:2000',
             'is_active' =>               'required|integer|in:0,1'

         ];
     }
     public function messages()
     {
         return [
             'dosage_form_name.required'    => config('keywords')['dosage_form']['dosage_form_name'].config('keywords')['error']['required'],
             'dosage_form_name.string'      => config('keywords')['dosage_form']['dosage_form_name'].config('keywords')['error']['string'],
             'dosage_form_name.max'         => config('keywords')['dosage_form']['dosage_form_name'].config('keywords')['error']['string_max'],
 
             'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
             'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
             'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 
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
