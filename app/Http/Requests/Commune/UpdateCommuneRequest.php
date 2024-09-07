<?php

namespace App\Http\Requests\Commune;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UpdateCommuneRequest extends FormRequest
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
        // Kiểm tra Id nhập vào của người dùng trước khi dùng Rule
        if(!is_numeric($this->commune)){
            throw new HttpResponseException(returnIdError($this->commune));
        }
        return [
            'commune_code' =>                  [
                'required',
                'string',
                'max:6',
                Rule::unique('App\Models\SDA\Commune')->ignore($this->commune),
            ],
            'commune_name' =>                   'required|string|max:100',
            'search_code' =>                    'nullable|string|max:10',
            'initial_name' =>                   'nullable|string|max:20',
            'district_id' =>                    [
                                                    'required',
                                                    'integer',
                                                    Rule::exists('App\Models\SDA\District', 'id')
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
            'commune_code.required'    => config('keywords')['commune']['commune_code'].config('keywords')['error']['required'],
            'commune_code.string'      => config('keywords')['commune']['commune_code'].config('keywords')['error']['string'],
            'commune_code.max'         => config('keywords')['commune']['commune_code'].config('keywords')['error']['string_max'],
            'commune_code.unique'      => config('keywords')['commune']['commune_code'].config('keywords')['error']['unique'],

            'commune_name.required'    => config('keywords')['commune']['commune_name'].config('keywords')['error']['required'],
            'commune_name.string'      => config('keywords')['commune']['commune_name'].config('keywords')['error']['string'],
            'commune_name.max'         => config('keywords')['commune']['commune_name'].config('keywords')['error']['string_max'],

            'search_code.string'      => config('keywords')['commune']['search_code'].config('keywords')['error']['string'],
            'search_code.max'         => config('keywords')['commune']['search_code'].config('keywords')['error']['string_max'],

            'initial_name.string'      => config('keywords')['commune']['initial_name'].config('keywords')['error']['string'],
            'initial_name.max'         => config('keywords')['commune']['initial_name'].config('keywords')['error']['string_max'], 

            'district_id.required'   => config('keywords')['commune']['district_id'].config('keywords')['error']['required'],            
            'district_id.integer'    => config('keywords')['commune']['district_id'].config('keywords')['error']['integer'],
            'district_id.exists'     => config('keywords')['commune']['district_id'].config('keywords')['error']['exists'], 

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
