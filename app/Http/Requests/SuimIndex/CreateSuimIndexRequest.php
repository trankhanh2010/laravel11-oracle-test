<?php

namespace App\Http\Requests\SuimIndex;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateSuimIndexRequest extends FormRequest
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
            'suim_index_code' =>      'required|string|max:20|unique:App\Models\HIS\SuimIndex,suim_index_code',
            'suim_index_name' =>      'required|string|max:1000',
            'suim_index_unit_id'  =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\SuimIndexUnit', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],         
            'num_order'  => 'nullable|integer',    
        ];
    }
    public function messages()
    {
        return [
            'suim_index_code.required'    => config('keywords')['suim_index']['suim_index_code'].config('keywords')['error']['required'],
            'suim_index_code.string'      => config('keywords')['suim_index']['suim_index_code'].config('keywords')['error']['string'],
            'suim_index_code.max'         => config('keywords')['suim_index']['suim_index_code'].config('keywords')['error']['string_max'],
            'suim_index_code.unique'      => config('keywords')['suim_index']['suim_index_code'].config('keywords')['error']['unique'],

            'suim_index_name.required'    => config('keywords')['suim_index']['suim_index_name'].config('keywords')['error']['required'],
            'suim_index_name.string'      => config('keywords')['suim_index']['suim_index_name'].config('keywords')['error']['string'],
            'suim_index_name.max'         => config('keywords')['suim_index']['suim_index_name'].config('keywords')['error']['string_max'],
            'suim_index_name.unique'      => config('keywords')['suim_index']['suim_index_name'].config('keywords')['error']['unique'],

            'suim_index_unit_id.integer'     => config('keywords')['suim_index']['suim_index_unit_id'].config('keywords')['error']['integer'],
            'suim_index_unit_id.exists'      => config('keywords')['suim_index']['suim_index_unit_id'].config('keywords')['error']['exists'],

            'num_order.integer'     => config('keywords')['suim_index']['num_order'].config('keywords')['error']['integer'],
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
