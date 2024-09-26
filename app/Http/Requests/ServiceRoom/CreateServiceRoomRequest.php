<?php

namespace App\Http\Requests\ServiceRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateServiceRoomRequest extends FormRequest
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
            'service_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\Service', 'id')
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
            'service_ids' => 'nullable|string|max:4000',
            'is_priority' => 'nullable|integer|in:0,1',
        ];
    }
    public function messages()
    {
        return [
            'service_id.integer'     => config('keywords')['service_room']['service_id'].config('keywords')['error']['integer'],
            'service_id.exists'      => config('keywords')['service_room']['service_id'].config('keywords')['error']['exists'],

            'room_ids.string'     => config('keywords')['service_room']['room_ids'].config('keywords')['error']['string'],
            'room_ids.max'      => config('keywords')['service_room']['room_ids'].config('keywords')['error']['string_max'],

            'room_id.integer'     => config('keywords')['service_room']['room_id'].config('keywords')['error']['integer'],
            'room_id.exists'      => config('keywords')['service_room']['room_id'].config('keywords')['error']['exists'],

            'service_ids.string'     => config('keywords')['service_room']['service_ids'].config('keywords')['error']['string'],
            'service_ids.max'      => config('keywords')['service_room']['service_ids'].config('keywords')['error']['string_max'],

            'is_priority.integer'    => config('keywords')['service_room']['is_priority'].config('keywords')['error']['integer'],
            'is_priority.in'         => config('keywords')['service_room']['is_priority'].config('keywords')['error']['in'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('room_ids')) {
            $this->merge([
                'room_ids_list' => explode(',', $this->room_ids),
            ]);
        }
        if ($this->has('service_ids')) {
            $this->merge([
                'service_ids_list' => explode(',', $this->service_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (($this->room_id === null) && ($this->service_id === null)) {
                $validator->errors()->add('room_id',config('keywords')['service_room']['service_id'].' và '.config('keywords')['service_room']['room_id'].' không thể cùng trống!');
                $validator->errors()->add('service_id',config('keywords')['service_room']['service_id'].' và '.config('keywords')['service_room']['room_id'].' không thể cùng trống!');
            }
            if (($this->room_id !== null) && ($this->service_id !== null)) {
                $validator->errors()->add('room_id',config('keywords')['service_room']['service_id'].' và '.config('keywords')['service_room']['room_id'].' không thể cùng có giá trị!');
                $validator->errors()->add('service_id',config('keywords')['service_room']['service_id'].' và '.config('keywords')['service_room']['room_id'].' không thể cùng có giá trị!');
            }
            if ($this->has('room_ids_list') && ($this->room_ids_list[0] != null)) {
                foreach ($this->room_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Room::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('room_ids', 'Phòng với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('service_ids_list') && ($this->service_ids_list[0] != null)) {
                foreach ($this->service_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Service::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('service_ids', 'Dịch vụ với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
