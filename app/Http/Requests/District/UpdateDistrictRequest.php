<?php

namespace App\Http\Requests\District;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use App\Models\SDA\Province;
use Illuminate\Support\Facades\DB;

class UpdateDistrictRequest extends FormRequest
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
        if(!is_numeric($this->id)){
            throw new HttpResponseException(return_id_error($this->id));
        }
        return [
                                    'district_code' =>      [
                                        'required',
                                        'string',
                                        'max:4',
                                        Rule::unique('App\Models\SDA\District')->ignore($this->id),
                                    ],
            'district_name' =>      'required|string|max:100',
            'initial_name' =>       'nullable|string|max:20|in:Huyện,Quận,Thị Xã,Thành Phố',
            'search_code' =>        'nullable|string|max:10',
            'province_id' =>        [
                                        'required',
                                        'integer',
                                        Rule::exists('App\Models\SDA\Province', 'id')
                                        ->where(function ($query) {
                                            $query = $query
                                            ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                        }),
                                    ],
            'is_active' =>          'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'district_code.required'    => config('keywords')['district']['district_code'].config('keywords')['error']['required'],
            'district_code.string'      => config('keywords')['district']['district_code'].config('keywords')['error']['string'],
            'district_code.max'         => config('keywords')['district']['district_code'].config('keywords')['error']['string_max'],
            'district_code.unique'      => config('keywords')['district']['district_code'].config('keywords')['error']['unique'],

            'district_name.required'    => config('keywords')['district']['district_name'].config('keywords')['error']['required'],
            'district_name.string'      => config('keywords')['district']['district_name'].config('keywords')['error']['string'],
            'district_name.max'         => config('keywords')['district']['district_name'].config('keywords')['error']['string_max'],

            'initial_name.string'       => config('keywords')['district']['initial_name'].config('keywords')['error']['string'],
            'initial_name.max'          => config('keywords')['district']['initial_name'].config('keywords')['error']['string_max'],
            'initial_name.in'           => config('keywords')['district']['initial_name'].config('keywords')['error']['in'],

            'search_code.string'        => config('keywords')['district']['search_code'].config('keywords')['error']['string'],
            'search_code.max'           => config('keywords')['district']['search_code'].config('keywords')['error']['string_max'],

            'province_id.required'      => config('keywords')['district']['province_id'].config('keywords')['error']['required'],
            'province_id.integer'       => config('keywords')['district']['province_id'].config('keywords')['error']['integer'],
            'province_id.exists'        => config('keywords')['district']['province_id'].config('keywords')['error']['exists'],

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
