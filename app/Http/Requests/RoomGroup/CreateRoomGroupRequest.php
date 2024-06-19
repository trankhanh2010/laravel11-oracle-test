<?php

namespace App\Http\Requests\RoomGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Contracts\Validation\Validator;
class CreateRoomGroupRequest extends FormRequest
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
            'group_code' =>             'nullable|string|max:50|exists:App\Models\SDA\Group,group_code',
            'room_group_code' =>        'required|string|max:10|unique:App\Models\HIS\RoomGroup,room_group_code',
            'room_group_name' =>        'required|string|max:200',
        ];
    }
    public function messages()
    {
        return [
            'group_code.string'     => config('keywords')['room_group']['group_code'].' phải là chuỗi string!',
            'group_code.max'        => config('keywords')['room_group']['group_code'].' tối đa 50 kí tự!',
            'group_code.exists'     => config('keywords')['room_group']['group_code'].' = '.$this->group_code.' không tồn tại!',

            'room_group_code.required' => config('keywords')['room_group']['room_group_code'].' không được bỏ trống!',
            'room_group_code.string'   => config('keywords')['room_group']['room_group_code'].' phải là chuỗi string!',
            'room_group_code.max'      => config('keywords')['room_group']['room_group_code'].' tối đa 10 kí tự!',
            'room_group_code.unique'   => config('keywords')['room_group']['room_group_code'].' = '. $this->room_group_code . ' đã tồn tại!',

            'room_group_name.required'  => config('keywords')['room_group']['room_group_name'].' không được bỏ trống!',
            'room_group_name.string'    => config('keywords')['room_group']['room_group_name'].' phải là chuỗi string!',
            'room_group_name.max'       => config('keywords')['room_group']['room_group_name'].' tối đa 200 kí tự!',

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
