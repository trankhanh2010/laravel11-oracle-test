<?php

namespace App\Http\Requests\RoomType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CreateRoomTypeRequest extends FormRequest
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
            'room_type_code' =>      'required|string|max:2|unique:App\Models\HIS\RoomType,room_type_code',
            'room_type_name' =>      'required|string|max:100',
            
        ];
    }
    public function messages()
    {
        return [
            'room_type_code.required'    => config('keywords')['room_type']['room_type_code'].config('keywords')['error']['required'],
            'room_type_code.string'      => config('keywords')['room_type']['room_type_code'].config('keywords')['error']['string'],
            'room_type_code.max'         => config('keywords')['room_type']['room_type_code'].config('keywords')['error']['string_max'],
            'room_type_code.unique'      => config('keywords')['room_type']['room_type_code'].config('keywords')['error']['unique'],

            'room_type_name.required'    => config('keywords')['room_type']['room_type_name'].config('keywords')['error']['required'],
            'room_type_name.string'      => config('keywords')['room_type']['room_type_name'].config('keywords')['error']['string'],
            'room_type_name.max'         => config('keywords')['room_type']['room_type_name'].config('keywords')['error']['string_max'],
            'room_type_name.unique'      => config('keywords')['room_type']['room_type_name'].config('keywords')['error']['unique'],

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
