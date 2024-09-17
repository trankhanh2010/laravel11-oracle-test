<?php

namespace App\Http\Requests\ExroRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateExroRoomRequest extends FormRequest
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
            'execute_room_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\ExecuteRoom', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'room_ids' => 'nullable|string|max:4000',

            'room_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Room', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'execute_room_ids' => 'nullable|string|max:4000',
            'is_hold_order' => 'nullable|integer|in:1',
            'is_allow_request' => 'nullable|integer|in:1',
            'is_priority_require' => 'nullable|integer|in:1',
        ];
    }
    public function messages()
    {
        return [
            'execute_room_id.integer'     => config('keywords')['exro_room']['execute_room_id'].config('keywords')['error']['integer'],
            'execute_room_id.exists'      => config('keywords')['exro_room']['execute_room_id'].config('keywords')['error']['exists'],

            'room_ids.string'     => config('keywords')['exro_room']['room_ids'].config('keywords')['error']['string'],
            'room_ids.max'      => config('keywords')['exro_room']['room_ids'].config('keywords')['error']['string_max'],

            'room_id.integer'     => config('keywords')['exro_room']['room_id'].config('keywords')['error']['integer'],
            'room_id.exists'      => config('keywords')['exro_room']['room_id'].config('keywords')['error']['exists'],

            'execute_room_ids.string'     => config('keywords')['exro_room']['execute_room_ids'].config('keywords')['error']['string'],
            'execute_room_ids.max'      => config('keywords')['exro_room']['execute_room_ids'].config('keywords')['error']['string_max'],

            'is_hold_order.integer'    => config('keywords')['exro_room']['is_hold_order'].config('keywords')['error']['integer'],
            'is_hold_order.in'         => config('keywords')['exro_room']['is_hold_order'].config('keywords')['error']['in'],

            'is_allow_request.integer'    => config('keywords')['exro_room']['is_allow_request'].config('keywords')['error']['integer'],
            'is_allow_request.in'         => config('keywords')['exro_room']['is_allow_request'].config('keywords')['error']['in'],

            'is_priority_require.integer'    => config('keywords')['exro_room']['is_priority_require'].config('keywords')['error']['integer'],
            'is_priority_require.in'         => config('keywords')['exro_room']['is_priority_require'].config('keywords')['error']['in'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('room_ids')) {
            $this->merge([
                'room_ids_list' => explode(',', $this->room_ids),
            ]);
        }
        if ($this->has('execute_room_ids')) {
            $this->merge([
                'execute_room_ids_list' => explode(',', $this->execute_room_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (($this->room_id === null) && ($this->execute_room_id === null)) {
                $validator->errors()->add('room_id',config('keywords')['exro_room']['execute_room_id'].' và '.config('keywords')['exro_room']['room_id'].' không thể cùng trống!');
                $validator->errors()->add('execute_room_id',config('keywords')['exro_room']['execute_room_id'].' và '.config('keywords')['exro_room']['room_id'].' không thể cùng trống!');
            }
            if (($this->room_id !== null) && ($this->execute_room_id !== null)) {
                $validator->errors()->add('room_id',config('keywords')['exro_room']['execute_room_id'].' và '.config('keywords')['exro_room']['room_id'].' không thể cùng có giá trị!');
                $validator->errors()->add('execute_room_id',config('keywords')['exro_room']['execute_room_id'].' và '.config('keywords')['exro_room']['room_id'].' không thể cùng có giá trị!');
            }
            if ($this->has('room_ids_list') && ($this->room_ids_list[0] != null)) {
                foreach ($this->room_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Room::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('room_ids', 'Phòng chỉ định với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('execute_room_ids_list') && ($this->execute_room_ids_list[0] != null)) {
                foreach ($this->execute_room_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\ExecuteRoom::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('execute_room_ids', 'Phòng thực hiện với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
        });
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
