<?php

namespace App\Http\Requests\TreatmentEndType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateTreatmentEndTypeRequest extends FormRequest
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
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->id)){
            throw new HttpResponseException(return_id_error($this->id));
        }
        return [
            'treatment_end_type_code' => [
                'nullable',
                'string',
                'max:3',
                Rule::unique('App\Models\HIS\TreatmentEndType')->ignore($this->id),
            ],
            'treatment_end_type_name' =>        'nullable|string|max:100',
            'end_code_prefix' =>                'nullable|string|max:5',
            'is_for_out_patient' =>             'nullable|integer|in:0,1',
            'is_for_in_patient' =>              'nullable|integer|in:0,1',
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'treatment_end_type_code.string'      => config('keywords')['treatment_end_type']['treatment_end_type_code'].config('keywords')['error']['string'],
            'treatment_end_type_code.max'         => config('keywords')['treatment_end_type']['treatment_end_type_code'].config('keywords')['error']['string_max'],
            'treatment_end_type_code.unique'      => config('keywords')['treatment_end_type']['treatment_end_type_code'].config('keywords')['error']['unique'],

            'treatment_end_type_name.string'      => config('keywords')['treatment_end_type']['treatment_end_type_name'].config('keywords')['error']['string'],
            'treatment_end_type_name.max'         => config('keywords')['treatment_end_type']['treatment_end_type_name'].config('keywords')['error']['string_max'],

            'end_code_prefix.string'      => config('keywords')['treatment_end_type']['end_code_prefix'].config('keywords')['error']['string'],
            'end_code_prefix.max'         => config('keywords')['treatment_end_type']['end_code_prefix'].config('keywords')['error']['string_max'],

            'is_for_out_patient.integer'      => config('keywords')['treatment_end_type']['is_for_out_patient'].config('keywords')['error']['integer'],
            'is_for_out_patient.in'         => config('keywords')['treatment_end_type']['is_for_out_patient'].config('keywords')['error']['in'],

            'is_for_in_patient.integer'      => config('keywords')['treatment_end_type']['is_for_in_patient'].config('keywords')['error']['integer'],
            'is_for_in_patient.in'         => config('keywords')['treatment_end_type']['is_for_in_patient'].config('keywords')['error']['in'],

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
