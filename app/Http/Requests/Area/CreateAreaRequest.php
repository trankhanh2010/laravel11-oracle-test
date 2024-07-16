<?php

namespace App\Http\Requests\Area;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateAreaRequest extends FormRequest
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
        return [
            'area_code' =>      'required|string|max:2|unique:App\Models\HIS\Area,area_code',
            'area_name' =>      'required|string|max:100',
            'department_id' =>  [
                                    'required',
                                    'integer',
                                    Rule::exists('App\Models\HIS\Department', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
        ];
    }
    public function messages()
    {
        return [
            'area_code.required'    => config('keywords')['area']['area_code'].config('keywords')['error']['required'],
            'area_code.string'      => config('keywords')['area']['area_code'].config('keywords')['error']['string'],
            'area_code.max'         => config('keywords')['area']['area_code'].config('keywords')['error']['string_max'],
            'area_code.unique'      => config('keywords')['area']['area_code'].config('keywords')['error']['unique'],

            'area_name.required'    => config('keywords')['area']['area_name'].config('keywords')['error']['required'],
            'area_name.string'      => config('keywords')['area']['area_name'].config('keywords')['error']['string'],
            'area_name.max'         => config('keywords')['area']['area_name'].config('keywords')['error']['string_max'],

            'department_id.required'    => config('keywords')['area']['department_id'].config('keywords')['error']['required'],
            'department_id.integer'     => config('keywords')['area']['department_id'].config('keywords')['error']['integer'],
            'department_id.exists'      => config('keywords')['area']['department_id'].config('keywords')['error']['exists'],

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
