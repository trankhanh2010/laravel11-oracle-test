<?php

namespace App\Http\Requests\BedRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
class UpdateBedRoomRequest extends FormRequest
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
            'bed_room_name' =>                  'required|string|max:100',
            'area_id' =>                        [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\Area', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ],
            'speciality_id' =>                  [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\Speciality', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ],
            'treatment_type_ids' =>             'nullable|string|max:200',
            'default_cashier_room_id' =>        [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\CashierRoom', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ],
            'default_instr_patient_type_id'  => [
                                                    'nullable',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\PatientType', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ],
            'is_surgery'  =>                    'nullable|integer|in:0,1',
            'is_restrict_req_service' =>        'nullable|integer|in:0,1',
            'is_pause' =>                       'nullable|integer|in:0,1',
            'is_restrict_execute_room' =>       'nullable|integer|in:0,1',
            'room_type_id' =>                   [
                                                    'required',
                                                    'integer',
                                                    Rule::exists('App\Models\HIS\RoomType', 'id')
                                                    ->where(function ($query) {
                                                        $query = $query
                                                        ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                                                    }),
                                                ],
            'is_active' =>                      'required|integer|in:0,1'

        ];
    }
    public function messages()
    {
        return [

            'bed_room_name.required'    => config('keywords')['bed_room']['bed_room_name'].config('keywords')['error']['required'],
            'bed_room_name.string'      => config('keywords')['bed_room']['bed_room_name'].config('keywords')['error']['string'],
            'bed_room_name.max'         => config('keywords')['bed_room']['bed_room_name'].config('keywords')['error']['string_max'],   
            
            'area_id.integer'   => config('keywords')['bed_room']['area_id'].config('keywords')['error']['integer'],
            'area_id.exists'    => config('keywords')['bed_room']['area_id'].config('keywords')['error']['exists'],  

            'speciality_id.integer'     => config('keywords')['bed_room']['speciality_id'].config('keywords')['error']['integer'],
            'speciality_id.exists'      => config('keywords')['bed_room']['speciality_id'].config('keywords')['error']['exists'],  

            'treatment_type_ids.string' => config('keywords')['bed_room']['treatment_type_ids'].config('keywords')['error']['string'],
            'treatment_type_ids.max'    => config('keywords')['bed_room']['treatment_type_ids'].config('keywords')['error']['string_max'],

            'default_cashier_room_id.integer'   => config('keywords')['bed_room']['default_cashier_room_id'].config('keywords')['error']['integer'],
            'default_cashier_room_id.exists'    => config('keywords')['bed_room']['default_cashier_room_id'].config('keywords')['error']['exists'], 

            'default_instr_patient_type_id.integer'     => config('keywords')['bed_room']['default_instr_patient_type_id'].config('keywords')['error']['integer'],
            'default_instr_patient_type_id.exists'      => config('keywords')['bed_room']['default_instr_patient_type_id'].config('keywords')['error']['exists'], 

            'is_surgery.integer'    => config('keywords')['bed_room']['is_surgery'].config('keywords')['error']['integer'],
            'is_surgery.in'         => config('keywords')['bed_room']['is_surgery'].config('keywords')['error']['in'], 

            'is_restrict_req_service.integer'    => config('keywords')['bed_room']['is_restrict_req_service'].config('keywords')['error']['integer'],
            'is_restrict_req_service.in'         => config('keywords')['bed_room']['is_restrict_req_service'].config('keywords')['error']['in'], 

            'is_pause.integer'    => config('keywords')['bed_room']['is_pause'].config('keywords')['error']['integer'],
            'is_pause.in'         => config('keywords')['bed_room']['is_pause'].config('keywords')['error']['in'], 

            'is_restrict_execute_room.integer'    => config('keywords')['bed_room']['is_restrict_execute_room'].config('keywords')['error']['integer'],
            'is_restrict_execute_room.in'         => config('keywords')['bed_room']['is_restrict_execute_room'].config('keywords')['error']['in'], 

            'room_type_id.required'    => config('keywords')['bed_room']['room_type_id'].config('keywords')['error']['required'],            
            'room_type_id.integer'     => config('keywords')['bed_room']['room_type_id'].config('keywords')['error']['integer'],
            'room_type_id.exists'      => config('keywords')['bed_room']['room_type_id'].config('keywords')['error']['exists'],  

            'is_active.required'    => config('keywords')['all']['is_active'].config('keywords')['error']['required'],            
            'is_active.integer'     => config('keywords')['all']['is_active'].config('keywords')['error']['integer'], 
            'is_active.in'          => config('keywords')['all']['is_active'].config('keywords')['error']['in'], 

        ];
    }
    protected function prepareForValidation()
    {
        if ($this->has('treatment_type_ids')) {
            $this->merge([
                'treatment_type_ids_list' => explode(',', $this->treatment_type_ids),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('treatment_type_ids_list') && ($this->treatment_type_ids_list[0] != null)) {
                foreach ($this->treatment_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\TreatmentType::find($id)) {
                        $validator->errors()->add('treatment_type_ids', 'Diện điều trị với id = ' . $id . ' trong danh sách diện điều trị không tồn tại!');
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
