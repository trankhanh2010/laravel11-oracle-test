<?php

namespace App\Http\Requests\SuimIndex;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdateSuimIndexRequest extends FormRequest
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
        if(!is_numeric($this->suim_index)){
            throw new HttpResponseException(returnIdError($this->suim_index));
        }
        return [
            'suim_index_code' =>        [
                                                    'required',
                                                    'string',
                                                    'max:20',
                                                    Rule::unique('App\Models\HIS\SuimIndex')->ignore($this->suim_index),
                                                ],
            'suim_index_name' =>        'required|string|max:1000',
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
            'is_active' =>                      'required|integer|in:0,1'

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
