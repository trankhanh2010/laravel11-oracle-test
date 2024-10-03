<?php

namespace App\Http\Requests\MaterialTypeMap;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateMaterialTypeMapRequest extends FormRequest
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
            'material_type_map_code' =>      'required|string|max:25|unique:App\Models\HIS\MaterialTypeMap,material_type_map_code',
            'material_type_map_name' =>      'required|string|max:500',
            
        ];
    }
    public function messages()
    {
        return [
            'material_type_map_code.required'    => config('keywords')['material_type_map']['material_type_map_code'].config('keywords')['error']['required'],
            'material_type_map_code.string'      => config('keywords')['material_type_map']['material_type_map_code'].config('keywords')['error']['string'],
            'material_type_map_code.max'         => config('keywords')['material_type_map']['material_type_map_code'].config('keywords')['error']['string_max'],
            'material_type_map_code.unique'      => config('keywords')['material_type_map']['material_type_map_code'].config('keywords')['error']['unique'],

            'material_type_map_name.required'    => config('keywords')['material_type_map']['material_type_map_name'].config('keywords')['error']['required'],
            'material_type_map_name.string'      => config('keywords')['material_type_map']['material_type_map_name'].config('keywords')['error']['string'],
            'material_type_map_name.max'         => config('keywords')['material_type_map']['material_type_map_name'].config('keywords')['error']['string_max'],
            'material_type_map_name.unique'      => config('keywords')['material_type_map']['material_type_map_name'].config('keywords')['error']['unique'],

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
