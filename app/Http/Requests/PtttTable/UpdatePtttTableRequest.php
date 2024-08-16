<?php

namespace App\Http\Requests\PtttTable;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class UpdatePtttTableRequest extends FormRequest
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
            'pttt_table_name' =>      'required|string|max:100',
            'execute_room_id' =>  [
                                    'required',
                                    'integer',
                                    Rule::exists('App\Models\HIS\ExecuteRoom', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
            'is_active' =>               'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [
            'pttt_table_name.required'    => config('keywords')['pttt_table']['pttt_table_name'].config('keywords')['error']['required'],
            'pttt_table_name.string'      => config('keywords')['pttt_table']['pttt_table_name'].config('keywords')['error']['string'],
            'pttt_table_name.max'         => config('keywords')['pttt_table']['pttt_table_name'].config('keywords')['error']['string_max'],

            'execute_room_id.required'    => config('keywords')['pttt_table']['execute_room_id'].config('keywords')['error']['required'],
            'execute_room_id.integer'     => config('keywords')['pttt_table']['execute_room_id'].config('keywords')['error']['integer'],
            'execute_room_id.exists'      => config('keywords')['pttt_table']['execute_room_id'].config('keywords')['error']['exists'],

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
