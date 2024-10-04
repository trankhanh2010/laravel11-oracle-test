<?php

namespace App\Http\Requests\MedicineGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateMedicineGroupRequest extends FormRequest
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
            'medicine_group_code' =>      'required|string|max:4|unique:App\Models\HIS\MedicineGroup,medicine_group_code',
            'medicine_group_name' =>      'required|string|max:100',
            'num_order' => 'nullable|integer',    
            'is_separate_printing'  =>  'nullable|integer|in:0,1',
            'is_numbered_tracking'  =>  'nullable|integer|in:0,1',
            'is_warning' =>  'nullable|integer|in:0,1',
            'number_day'  => 'nullable|integer|min:0', 
            'is_auto_treatment_day_count' =>  'nullable|integer|in:0,1',
        ];
    }
    public function messages()
    {
        return [
            'medicine_group_code.required'    => config('keywords')['medicine_group']['medicine_group_code'].config('keywords')['error']['required'],
            'medicine_group_code.string'      => config('keywords')['medicine_group']['medicine_group_code'].config('keywords')['error']['string'],
            'medicine_group_code.max'         => config('keywords')['medicine_group']['medicine_group_code'].config('keywords')['error']['string_max'],
            'medicine_group_code.unique'      => config('keywords')['medicine_group']['medicine_group_code'].config('keywords')['error']['unique'],

            'medicine_group_name.required'    => config('keywords')['medicine_group']['medicine_group_name'].config('keywords')['error']['required'],
            'medicine_group_name.string'      => config('keywords')['medicine_group']['medicine_group_name'].config('keywords')['error']['string'],
            'medicine_group_name.max'         => config('keywords')['medicine_group']['medicine_group_name'].config('keywords')['error']['string_max'],
            'medicine_group_name.unique'      => config('keywords')['medicine_group']['medicine_group_name'].config('keywords')['error']['unique'],

            'num_order.integer'     => config('keywords')['medicine_group']['num_order'].config('keywords')['error']['integer'], 

            'is_separate_printing.integer'     => config('keywords')['medicine_group']['is_separate_printing'].config('keywords')['error']['integer'], 
            'is_separate_printing.in'          => config('keywords')['medicine_group']['is_separate_printing'].config('keywords')['error']['in'], 

            'is_numbered_tracking.integer'     => config('keywords')['medicine_group']['is_numbered_tracking'].config('keywords')['error']['integer'], 
            'is_numbered_tracking.in'          => config('keywords')['medicine_group']['is_numbered_tracking'].config('keywords')['error']['in'], 

            'is_warning.integer'     => config('keywords')['medicine_group']['is_warning'].config('keywords')['error']['integer'], 
            'is_warning.in'          => config('keywords')['medicine_group']['is_warning'].config('keywords')['error']['in'], 

            'number_day.integer'      => config('keywords')['medicine_group']['number_day'].config('keywords')['error']['integer'], 
            'number_day.min'          => config('keywords')['medicine_group']['number_day'].config('keywords')['error']['integer_min'], 
            
            'is_auto_treatment_day_count.integer'     => config('keywords')['medicine_group']['is_auto_treatment_day_count'].config('keywords')['error']['integer'], 
            'is_auto_treatment_day_count.in'          => config('keywords')['medicine_group']['is_auto_treatment_day_count'].config('keywords')['error']['in'], 
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
