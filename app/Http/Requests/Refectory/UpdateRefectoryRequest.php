<?php

namespace App\Http\Requests\Refectory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
class UpdateRefectoryRequest extends FormRequest
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
            'refectory_name' =>                 'required|string|max:100',
                'room_type_id'  =>               [
                                                    'required',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\RoomType', 'id')
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
            'refectory_name.required'    => config('keywords')['refectory']['refectory_name'].config('keywords')['error']['required'],
            'refectory_name.string'      => config('keywords')['refectory']['refectory_name'].config('keywords')['error']['string'],
            'refectory_name.max'         => config('keywords')['refectory']['refectory_name'].config('keywords')['error']['string_max'],

            'room_type_id.required'    => config('keywords')['refectory']['room_type_id'].config('keywords')['error']['required'],            
            'room_type_id.integer'     => config('keywords')['refectory']['room_type_id'].config('keywords')['error']['integer'],
            'room_type_id.exists'      => config('keywords')['refectory']['room_type_id'].config('keywords')['error']['exists'],  

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
