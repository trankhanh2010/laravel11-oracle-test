<?php

namespace App\Http\Requests\LicenseClass;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateLicenseClassRequest extends FormRequest
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
            'license_class_code' => [
                                            'required',
                                            'string',
                                            'max:5',
                                            Rule::unique('App\Models\HIS\LicenseClass')->ignore($this->id),
                                        ],
            'license_class_name' =>      'required|string|max:100',
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'license_class_code.required'    => config('keywords')['license_class']['license_class_code'].config('keywords')['error']['required'],
            'license_class_code.string'      => config('keywords')['license_class']['license_class_code'].config('keywords')['error']['string'],
            'license_class_code.max'         => config('keywords')['license_class']['license_class_code'].config('keywords')['error']['string_max'],
            'license_class_code.unique'      => config('keywords')['license_class']['license_class_code'].config('keywords')['error']['unique'],

            'license_class_name.required'    => config('keywords')['license_class']['license_class_name'].config('keywords')['error']['required'],
            'license_class_name.string'      => config('keywords')['license_class']['license_class_name'].config('keywords')['error']['string'],
            'license_class_name.max'         => config('keywords')['license_class']['license_class_name'].config('keywords')['error']['string_max'],

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
