<?php

namespace App\Http\Requests\Machine;

use App\Models\HIS\RoomType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
class CreateMachineRequest extends FormRequest
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
            'machine_code' =>       'required|string|max:100|unique:App\Models\HIS\Machine,machine_code',
            'machine_name' =>       'required|string|max:200',
            'serial_number' =>      'nullable|string|max:200',
            'source_code' =>        'nullable|string|max:2|in:1,2,3',
            'machine_group_code' => 'nullable|string|max:10',
            'symbol' =>             'nullable|string|max:500',

            'manufacturer_name' =>  'nullable|string|max:500',
            'national_name' =>      'nullable|string|max:500',
            'manufactured_year' =>  'nullable|integer|regex:/^\d{4}$/',
            'used_year' =>          'nullable|integer|regex:/^\d{4}$/',
            'circulation_number' => 'nullable|string|max:22',
            'integrate_address' =>  'nullable|string|max:500',

            'max_service_per_day' =>    'nullable|integer|min:0',
            'department_id' =>  [
                                    'nullable',
                                    'integer',
                                    Rule::exists('App\Models\HIS\Department', 'id')
                                    ->where(function ($query) {
                                        $query = $query
                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                    }),
                                ], 
            'room_ids' =>       'nullable|string|max:2000',
            'is_kidney' =>      'nullable|integer|in:0,1',
        ];
    }
    public function messages()
    {
        return [
            'machine_code.required'    => config('keywords')['machine']['machine_code'].config('keywords')['error']['required'],
            'machine_code.string'      => config('keywords')['machine']['machine_code'].config('keywords')['error']['string'],
            'machine_code.max'         => config('keywords')['machine']['machine_code'].config('keywords')['error']['string_max'],
            'machine_code.unique'      => config('keywords')['machine']['machine_code'].config('keywords')['error']['unique'],

            'machine_name.required'    => config('keywords')['machine']['machine_name'].config('keywords')['error']['required'],
            'machine_name.string'      => config('keywords')['machine']['machine_name'].config('keywords')['error']['string'],
            'machine_name.max'         => config('keywords')['machine']['machine_name'].config('keywords')['error']['string_max'],

            'serial_number.string'      => config('keywords')['machine']['serial_number'].config('keywords')['error']['string'],
            'serial_number.max'         => config('keywords')['machine']['serial_number'].config('keywords')['error']['string_max'],

            'source_code.string'      => config('keywords')['machine']['source_code'].config('keywords')['error']['string'],
            'source_code.max'         => config('keywords')['machine']['source_code'].config('keywords')['error']['string_max'],
            'source_code.in'          => config('keywords')['machine']['source_code'].config('keywords')['error']['in'],

            'machine_group_code.string'      => config('keywords')['machine']['machine_group_code'].config('keywords')['error']['string'],
            'machine_group_code.max'         => config('keywords')['machine']['machine_group_code'].config('keywords')['error']['string_max'],

            'symbol.string'      => config('keywords')['machine']['symbol'].config('keywords')['error']['string'],
            'symbol.max'         => config('keywords')['machine']['symbol'].config('keywords')['error']['string_max'],


            'manufacturer_name.string'      => config('keywords')['machine']['manufacturer_name'].config('keywords')['error']['string'],
            'manufacturer_name.max'         => config('keywords')['machine']['manufacturer_name'].config('keywords')['error']['string_max'],

            'national_name.string'      => config('keywords')['machine']['national_name'].config('keywords')['error']['string'],
            'national_name.max'         => config('keywords')['machine']['national_name'].config('keywords')['error']['string_max'],

            'manufactured_year.integer'      => config('keywords')['machine']['manufactured_year'].config('keywords')['error']['integer'],
            'manufactured_year.regex'         => config('keywords')['machine']['manufactured_year'].config('keywords')['error']['regex_year'],

            'used_year.integer'      => config('keywords')['machine']['used_year'].config('keywords')['error']['integer'],
            'used_year.regex'         => config('keywords')['machine']['used_year'].config('keywords')['error']['regex_year'],

            'circulation_number.string'      => config('keywords')['machine']['circulation_number'].config('keywords')['error']['string'],
            'circulation_number.max'         => config('keywords')['machine']['circulation_number'].config('keywords')['error']['string_max'],

            'integrate_address.string'      => config('keywords')['machine']['integrate_address'].config('keywords')['error']['string'],
            'integrate_address.max'         => config('keywords')['machine']['integrate_address'].config('keywords')['error']['string_max'],

            
            'max_service_per_day.integer'      => config('keywords')['machine']['max_service_per_day'].config('keywords')['error']['integer'],
            'max_service_per_day.min'         => config('keywords')['machine']['max_service_per_day'].config('keywords')['error']['integer_min'],

            'department_id.integer'      => config('keywords')['machine']['department_id'].config('keywords')['error']['integer'],
            'department_id.exists'         => config('keywords')['machine']['department_id'].config('keywords')['error']['exists'],

            'room_ids.string'      => config('keywords')['machine']['room_ids'].config('keywords')['error']['string'],
            'room_ids.max'         => config('keywords')['machine']['room_ids'].config('keywords')['error']['string_max'],

            'is_kidney.integer'      => config('keywords')['machine']['is_kidney'].config('keywords')['error']['integer'],
            'is_kidney.in'         => config('keywords')['machine']['is_kidney'].config('keywords')['error']['in'],

        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('room_ids')) {
            $this->merge([
                'room_ids_list' => explode(',', $this->room_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('room_ids_list') && ($this->room_ids_list[0] != null)) {
                $room_type_id_XL = RoomType::where('room_type_code', 'XL')->value('id');
                foreach ($this->room_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\Room::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('room_ids', 'Phòng với Id = ' . $id . config('keywords')['error']['not_find_or_not_active_in_list']);
                    }
                    if (!\App\Models\HIS\Room::where('id', $id)->where('department_id', intval($this->department_id))->first()) {
                        $validator->errors()->add('room_ids', 'Phòng với Id = ' . $id . config('keywords')['error']['not_in_department_id']);
                    }
                    if (!\App\Models\HIS\Room::where('id', $id)->where('room_type_id', intval($room_type_id_XL))->first()) {
                        $validator->errors()->add('room_ids', 'Phòng với Id = ' . $id . config('keywords')['error']['not_in_room_type_XL']);
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
