<?php

namespace App\Http\Requests\Province;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UpdateProvinceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(!is_numeric($this->province)){
            throw new HttpResponseException(returnIdError($this->province));
        }
        return [
            'province_code' => [
                'required',
                'string',
                'max:3',
                Rule::unique('App\Models\SDA\Province')->ignore($this->province),
            ],
            'province_name' =>                  'required|string|max:100',
            'search_code' =>                    'nullable|string|max:10',
            'national_id' =>                    [
                                                    'required',
                                                    'integer',
                                                    Rule::exists('App\Models\SDA\National', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ],
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'province_code.required'    => config('keywords')['province']['province_code'].config('keywords')['error']['required'],
            'province_code.string'      => config('keywords')['province']['province_code'].config('keywords')['error']['string'],
            'province_code.max'         => config('keywords')['province']['province_code'].config('keywords')['error']['string_max'],
            'province_code.unique'      => config('keywords')['province']['province_code'].config('keywords')['error']['unique'],

            'province_name.required'    => config('keywords')['province']['province_name'].config('keywords')['error']['required'],
            'province_name.string'      => config('keywords')['province']['province_name'].config('keywords')['error']['string'],
            'province_name.max'         => config('keywords')['province']['province_name'].config('keywords')['error']['string_max'],

            'search_code.string'      => config('keywords')['province']['search_code'].config('keywords')['error']['string'],
            'search_code.max'         => config('keywords')['province']['search_code'].config('keywords')['error']['string_max'],

            'national_id.required'   => config('keywords')['province']['national_id'].config('keywords')['error']['required'],            
            'national_id.integer'    => config('keywords')['province']['national_id'].config('keywords')['error']['integer'],
            'national_id.exists'     => config('keywords')['province']['national_id'].config('keywords')['error']['exists'], 

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
