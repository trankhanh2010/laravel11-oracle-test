<?php

namespace App\Http\Requests\ServiceUnit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateServiceUnitRequest extends FormRequest
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
            'service_unit_code' =>      'required|string|max:3|unique:App\Models\HIS\ServiceUnit,service_unit_code',
            'service_unit_name' =>      'required|string|max:100',
            'service_unit_symbol' =>      'nullable|string|max:10',
            'medicine_num_order' =>      'nullable|integer',
            'material_num_order' =>      'nullable|integer',

            'convert_ratio' =>            'nullable|numeric|regex:/^\d{1,15}(\.\d{1,4})?$/|min:0', 
            'convert_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\ServiceUnit', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ], 
            'is_primary' =>      'nullable|integer|in:0,1',

        ];
    }
    public function messages()
    {
        return [
            'service_unit_code.required'    => config('keywords')['service_unit']['service_unit_code'].config('keywords')['error']['required'],
            'service_unit_code.string'      => config('keywords')['service_unit']['service_unit_code'].config('keywords')['error']['string'],
            'service_unit_code.max'         => config('keywords')['service_unit']['service_unit_code'].config('keywords')['error']['string_max'],
            'service_unit_code.unique'      => config('keywords')['service_unit']['service_unit_code'].config('keywords')['error']['unique'],

            'service_unit_name.required'    => config('keywords')['service_unit']['service_unit_name'].config('keywords')['error']['required'],
            'service_unit_name.string'      => config('keywords')['service_unit']['service_unit_name'].config('keywords')['error']['string'],
            'service_unit_name.max'         => config('keywords')['service_unit']['service_unit_name'].config('keywords')['error']['string_max'],

            'service_unit_symbol.string'      => config('keywords')['service_unit']['service_unit_symbol'].config('keywords')['error']['string'],
            'service_unit_symbol.max'         => config('keywords')['service_unit']['service_unit_symbol'].config('keywords')['error']['string_max'],

            'medicine_num_order.integer'      => config('keywords')['service_unit']['medicine_num_order'].config('keywords')['error']['integer'],

            'material_num_order.integer'      => config('keywords')['service_unit']['material_num_order'].config('keywords')['error']['integer'],

            'convert_ratio.numeric'     => config('keywords')['service_unit']['convert_ratio'].config('keywords')['error']['numeric'],
            'convert_ratio.regex'       => config('keywords')['service_unit']['convert_ratio'].config('keywords')['error']['regex_19_4'],
            'convert_ratio.min'         => config('keywords')['service_unit']['convert_ratio'].config('keywords')['error']['integer_min'],

            'convert_id.integer'     => config('keywords')['service_unit']['convert_id'].config('keywords')['error']['integer'],
            'convert_id.exists'      => config('keywords')['service_unit']['convert_id'].config('keywords')['error']['exists'],
            
            'is_primary.integer'     => config('keywords')['service_unit']['is_primary'].config('keywords')['error']['integer'],
            'is_primary.in'      => config('keywords')['service_unit']['is_primary'].config('keywords')['error']['in'],
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
