<?php

namespace App\Http\Requests\RoomGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
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
            'group_code' =>             [
                                            'nullable',
                                            'string',
                                            'max:50',
                                            Rule::exists('App\Models\SDA\Group', 'group_code')
                                            ->where(function ($query) {
                                                $query = $query
                                                ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                            }),
                                        ],'|||exists:,',
            'room_group_code' =>        'required|string|max:10|unique:App\Models\HIS\RoomGroup,room_group_code',
            'room_group_name' =>        'required|string|max:200',
        ];
    }
    public function messages()
    {
        return [
            'group_code.string'     => config('keywords')['room_group']['group_code'].config('keywords')['error']['string'],
            'group_code.max'        => config('keywords')['room_group']['group_code'].config('keywords')['error']['string_max'],
            'group_code.exists'     => config('keywords')['room_group']['group_code'].config('keywords')['error']['exists'],

            'room_group_code.required' => config('keywords')['room_group']['room_group_code'].config('keywords')['error']['required'],
            'room_group_code.string'   => config('keywords')['room_group']['room_group_code'].config('keywords')['error']['string'],
            'room_group_code.max'      => config('keywords')['room_group']['room_group_code'].config('keywords')['error']['string_max'],
            'room_group_code.unique'   => config('keywords')['room_group']['room_group_code'].config('keywords')['error']['unique'],

            'room_group_name.required'  => config('keywords')['room_group']['room_group_name'].config('keywords')['error']['required'],
            'room_group_name.string'    => config('keywords')['room_group']['room_group_name'].config('keywords')['error']['string'],
            'room_group_name.max'       => config('keywords')['room_group']['room_group_name'].config('keywords')['error']['string_max'],

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
