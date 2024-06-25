<?php

namespace App\Http\Requests\Refectory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class CreateRefectoryRequest extends FormRequest
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
            'refectory_code' =>              'required|string|max:10|unique:App\Models\HIS\Refectory,refectory_code',
            'refectory_name' =>              'required|string|max:100',
            'department_id' =>               'required|integer|exists:App\Models\HIS\Department,id',
            'room_type_id'  =>               'required|integer|exists:App\Models\HIS\RoomType,id',
        ];
    }
    public function messages()
    {
        return [
            'refectory_code.required'    => config('keywords')['refectory']['refectory_code'].config('keywords')['error']['required'],
            'refectory_code.string'      => config('keywords')['refectory']['refectory_code'].config('keywords')['error']['string'],
            'refectory_code.max'         => config('keywords')['refectory']['refectory_code'].config('keywords')['error']['string_max'],
            'refectory_code.unique'      => config('keywords')['refectory']['refectory_code'].config('keywords')['error']['unique'],

            'refectory_name.required'    => config('keywords')['refectory']['refectory_name'].config('keywords')['error']['required'],
            'refectory_name.string'      => config('keywords')['refectory']['refectory_name'].config('keywords')['error']['string'],
            'refectory_name.max'         => config('keywords')['refectory']['refectory_name'].config('keywords')['error']['string_max'],

            'department_id.required'    => config('keywords')['refectory']['department_id'].config('keywords')['error']['required'],            
            'department_id.integer'     => config('keywords')['refectory']['department_id'].config('keywords')['error']['integer'],
            'department_id.exists'      => config('keywords')['refectory']['department_id'].config('keywords')['error']['exists'],

            'room_type_id.required'    => config('keywords')['refectory']['room_type_id'].config('keywords')['error']['required'],            
            'room_type_id.integer'     => config('keywords')['refectory']['room_type_id'].config('keywords')['error']['integer'],
            'room_type_id.exists'      => config('keywords')['refectory']['room_type_id'].config('keywords')['error']['exists'],  
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
