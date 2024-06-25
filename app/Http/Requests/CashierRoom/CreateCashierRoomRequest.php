<?php

namespace App\Http\Requests\CashierRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class CreateCashierRoomRequest extends FormRequest
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
            'cashier_room_code' =>              'required|string|max:10|unique:App\Models\HIS\CashierRoom,cashier_room_code',
            'cashier_room_name' =>              'required|string|max:100',
            'department_id' =>                  'required|integer|exists:App\Models\HIS\Department,id',
            'area_id' =>                        'nullable|integer|exists:App\Models\HIS\Area,id',
            'room_type_id'  =>                  'required|integer|exists:App\Models\HIS\RoomType,id',
            'einvoice_room_code' =>             'nullable|string|max:10',
            'einvoice_room_name' =>             'nullable|string|max:100',
        ];
    }
    public function messages()
    {
        return [
            'cashier_room_code.required'    => config('keywords')['cashier_room']['cashier_room_code'].config('keywords')['error']['required'],
            'cashier_room_code.string'      => config('keywords')['cashier_room']['cashier_room_code'].config('keywords')['error']['string'],
            'cashier_room_code.max'         => config('keywords')['cashier_room']['cashier_room_code'].config('keywords')['error']['string_max'],
            'cashier_room_code.unique'      => config('keywords')['cashier_room']['cashier_room_code'].config('keywords')['error']['unique'],

            'cashier_room_name.required'    => config('keywords')['cashier_room']['cashier_room_name'].config('keywords')['error']['required'],
            'cashier_room_name.string'      => config('keywords')['cashier_room']['cashier_room_name'].config('keywords')['error']['string'],
            'cashier_room_name.max'         => config('keywords')['cashier_room']['cashier_room_name'].config('keywords')['error']['string_max'],

            'department_id.required'    => config('keywords')['cashier_room']['department_id'].config('keywords')['error']['required'],            
            'department_id.integer'     => config('keywords')['cashier_room']['department_id'].config('keywords')['error']['integer'],
            'department_id.exists'      => config('keywords')['cashier_room']['department_id'].config('keywords')['error']['exists'],

            'area_id.integer'     => config('keywords')['cashier_room']['area_id'].config('keywords')['error']['integer'],
            'area_id.exists'      => config('keywords')['cashier_room']['area_id'].config('keywords')['error']['exists'], 

            'room_type_id.required'    => config('keywords')['cashier_room']['room_type_id'].config('keywords')['error']['required'],            
            'room_type_id.integer'     => config('keywords')['cashier_room']['room_type_id'].config('keywords')['error']['integer'],
            'room_type_id.exists'      => config('keywords')['cashier_room']['room_type_id'].config('keywords')['error']['exists'],  

            'einvoice_room_code.string'      => config('keywords')['cashier_room']['einvoice_room_code'].config('keywords')['error']['string'],
            'einvoice_room_code.max'         => config('keywords')['cashier_room']['einvoice_room_code'].config('keywords')['error']['string_max'],

            'einvoice_room_name.string'      => config('keywords')['cashier_room']['einvoice_room_name'].config('keywords')['error']['string'],
            'einvoice_room_name.max'         => config('keywords')['cashier_room']['einvoice_room_name'].config('keywords')['error']['string_max'],
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
