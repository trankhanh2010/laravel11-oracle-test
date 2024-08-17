<?php

namespace App\Http\Requests\MestRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateMestRoomRequest extends FormRequest
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
            'medi_stock_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\MediStock', 'id')
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
            'medi_stock_ids' => 'nullable|string|max:4000',

        ];
    }
    public function messages()
    {
        return [
            'medi_stock_id.integer'     => config('keywords')['mest_room']['medi_stock_id'].config('keywords')['error']['integer'],
            'medi_stock_id.exists'      => config('keywords')['mest_room']['medi_stock_id'].config('keywords')['error']['exists'],

            'room_ids.string'     => config('keywords')['mest_room']['room_ids'].config('keywords')['error']['string'],
            'room_ids.max'      => config('keywords')['mest_room']['room_ids'].config('keywords')['error']['string_max'],

            'room_id.integer'     => config('keywords')['mest_room']['room_id'].config('keywords')['error']['integer'],
            'room_id.exists'      => config('keywords')['mest_room']['room_id'].config('keywords')['error']['exists'],

            'medi_stock_ids.string'     => config('keywords')['mest_room']['medi_stock_ids'].config('keywords')['error']['string'],
            'medi_stock_ids.max'      => config('keywords')['mest_room']['medi_stock_ids'].config('keywords')['error']['string_max'],

        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('room_ids')) {
            $this->merge([
                'room_ids_list' => explode(',', $this->room_ids),
            ]);
        }
        if ($this->has('medi_stock_ids')) {
            $this->merge([
                'medi_stock_ids_list' => explode(',', $this->medi_stock_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (($this->room_id === null) && ($this->medi_stock_id === null)) {
                $validator->errors()->add('room_id',config('keywords')['mest_room']['medi_stock_id'].' và '.config('keywords')['mest_room']['room_id'].' không thể cùng trống!');
                $validator->errors()->add('medi_stock_id',config('keywords')['mest_room']['medi_stock_id'].' và '.config('keywords')['mest_room']['room_id'].' không thể cùng trống!');
            }
            if (($this->room_id !== null) && ($this->medi_stock_id !== null)) {
                $validator->errors()->add('room_id',config('keywords')['mest_room']['medi_stock_id'].' và '.config('keywords')['mest_room']['room_id'].' không thể cùng có giá trị!');
                $validator->errors()->add('medi_stock_id',config('keywords')['mest_room']['medi_stock_id'].' và '.config('keywords')['mest_room']['room_id'].' không thể cùng có giá trị!');
            }
            if ($this->has('room_ids_list') && ($this->room_ids_list[0] != null)) {
                foreach ($this->room_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Room::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('room_ids', 'Phòng với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                }
            }
            if ($this->has('medi_stock_ids_list') && ($this->medi_stock_ids_list[0] != null)) {
                foreach ($this->medi_stock_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\MediStock::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('medi_stock_ids', 'Kho với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
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
