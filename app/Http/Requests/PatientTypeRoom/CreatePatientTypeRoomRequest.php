<?php

namespace App\Http\Requests\PatientTypeRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreatePatientTypeRoomRequest extends FormRequest
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
            'patient_type_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\PatientType', 'id')
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
            'patient_type_ids' => 'nullable|string|max:4000',

        ];
    }
    public function messages()
    {
        return [
            'patient_type_id.integer'     => config('keywords')['patient_type_room']['patient_type_id'].config('keywords')['error']['integer'],
            'patient_type_id.exists'      => config('keywords')['patient_type_room']['patient_type_id'].config('keywords')['error']['exists'],

            'room_ids.string'     => config('keywords')['patient_type_room']['room_ids'].config('keywords')['error']['string'],
            'room_ids.max'      => config('keywords')['patient_type_room']['room_ids'].config('keywords')['error']['string_max'],

            'room_id.integer'     => config('keywords')['patient_type_room']['room_id'].config('keywords')['error']['integer'],
            'room_id.exists'      => config('keywords')['patient_type_room']['room_id'].config('keywords')['error']['exists'],

            'patient_type_ids.string'     => config('keywords')['patient_type_room']['patient_type_ids'].config('keywords')['error']['string'],
            'patient_type_ids.max'      => config('keywords')['patient_type_room']['patient_type_ids'].config('keywords')['error']['string_max'],

        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('room_ids')) {
            $this->merge([
                'room_ids_list' => explode(',', $this->room_ids),
            ]);
        }
        if ($this->has('patient_type_ids')) {
            $this->merge([
                'patient_type_ids_list' => explode(',', $this->patient_type_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (($this->room_id === null) && ($this->patient_type_id === null)) {
                $validator->errors()->add('room_id',config('keywords')['patient_type_room']['patient_type_id'].' và '.config('keywords')['patient_type_room']['room_id'].' không thể cùng trống!');
                $validator->errors()->add('patient_type_id',config('keywords')['patient_type_room']['patient_type_id'].' và '.config('keywords')['patient_type_room']['room_id'].' không thể cùng trống!');
            }
            if (($this->room_id !== null) && ($this->patient_type_id !== null)) {
                $validator->errors()->add('room_id',config('keywords')['patient_type_room']['patient_type_id'].' và '.config('keywords')['patient_type_room']['room_id'].' không thể cùng có giá trị!');
                $validator->errors()->add('patient_type_id',config('keywords')['patient_type_room']['patient_type_id'].' và '.config('keywords')['patient_type_room']['room_id'].' không thể cùng có giá trị!');
            }
            if ($this->has('room_ids_list') && ($this->room_ids_list[0] != null)) {
                foreach ($this->room_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Room::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('room_ids', 'Phòng với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('patient_type_ids_list') && ($this->patient_type_ids_list[0] != null)) {
                foreach ($this->patient_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\PatientType::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('patient_type_ids', 'Đối tượng với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
