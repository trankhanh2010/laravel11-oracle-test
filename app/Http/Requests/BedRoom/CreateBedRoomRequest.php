<?php

namespace App\Http\Requests\BedRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Contracts\Validation\Validator;
class CreateBedRoomRequest extends FormRequest
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
            'bed_room_code' =>                  'required|string|max:20|unique:App\Models\HIS\BedRoom,bed_room_code',
            'bed_room_name' =>                  'required|string|max:100',
            'department_id' =>                  'required|integer|exists:App\Models\HIS\Department,id',
            'area_id' =>                        'nullable|integer|exists:App\Models\HIS\Area,id',
            'speciality_id' =>                  'nullable|integer|exists:App\Models\HIS\Speciality,id',
            'treatment_type_ids' =>             'nullable|string|max:200',
            'default_cashier_room_id' =>        'nullable|integer|exists:App\Models\HIS\CashierRoom,id',
            'default_instr_patient_type_id'  => 'nullable|integer|exists:App\Models\HIS\PatientType,id',
            'is_surgery'  =>                    'nullable|integer|in:0,1',
            'is_restrict_req_service' =>        'nullable|integer|in:0,1',
            'is_pause' =>                       'nullable|integer|in:0,1',
            'is_restrict_execute_room' =>       'nullable|integer|in:0,1',
            'room_type_id' =>                   'required|integer|exists:App\Models\HIS\RoomType,id',
        ];
    }
    public function messages()
    {
        return [
            'bed_room_code.required'    => config('keywords')['bed_room']['bed_room_code'].' không được bỏ trống!',
            'bed_room_code.string'      => config('keywords')['bed_room']['bed_room_code'].' phải là chuỗi string!',
            'bed_room_code.max'         => config('keywords')['bed_room']['bed_room_code'].' tối đa 20 kí tự!',            
            'bed_room_code.unique'      => config('keywords')['bed_room']['bed_room_code'].' = '.$this->bed_room_code.' đã tồn tại!',

            'bed_room_name.required'    => config('keywords')['bed_room']['bed_room_name'].' không được bỏ trống!',
            'bed_room_name.string'      => config('keywords')['bed_room']['bed_room_name'].' phải là chuỗi string!',
            'bed_room_name.max'         => config('keywords')['bed_room']['bed_room_name'].' tối đa 100 kí tự!',   
            
            'department_id.required'    => config('keywords')['bed_room']['department_id'].' không được bỏ trống!',            
            'department_id.integer'     => config('keywords')['bed_room']['department_id'].' phải là số nguyên!',
            'department_id.exists'      => config('keywords')['bed_room']['department_id'].' = '.$this->department_id.' không tồn tại!',  
            
            'area_id.integer'   => config('keywords')['bed_room']['area_id'].' phải là số nguyên!',
            'area_id.exists'    => config('keywords')['bed_room']['area_id'].' = '.$this->department_id.' không tồn tại!',  

            'speciality_id.integer'     => config('keywords')['bed_room']['speciality_id'].' phải là số nguyên!',
            'speciality_id.exists'      => config('keywords')['bed_room']['speciality_id'].' = '.$this->department_id.' không tồn tại!',  

            'treatment_type_ids.string' => config('keywords')['bed_room']['treatment_type_ids'].' phải là chuỗi string!',

            'default_cashier_room_id.integer'   => config('keywords')['bed_room']['default_cashier_room_id'].' phải là số nguyên!',
            'default_cashier_room_id.exists'    => config('keywords')['bed_room']['default_cashier_room_id'].' không tồn tại!', 

            'default_instr_patient_type_id.integer'     => config('keywords')['bed_room']['default_instr_patient_type_id'].' phải là số nguyên!',
            'default_instr_patient_type_id.exists'      => config('keywords')['bed_room']['default_instr_patient_type_id'].' không tồn tại!', 

            'is_surgery.integer'    => config('keywords')['bed_room']['is_surgery'].' phải là số nguyên!',
            'is_surgery.in'         => config('keywords')['bed_room']['is_surgery'].' phải là 0 hoặc 1!', 

            'is_restrict_req_service.integer'    => config('keywords')['bed_room']['is_restrict_req_service'].' phải là số nguyên!',
            'is_restrict_req_service.in'         => config('keywords')['bed_room']['is_restrict_req_service'].' phải là 0 hoặc 1!', 

            'is_pause.integer'    => config('keywords')['bed_room']['is_pause'].' phải là số nguyên!',
            'is_pause.in'         => config('keywords')['bed_room']['is_pause'].' phải là 0 hoặc 1!', 

            'is_restrict_execute_room.integer'    => config('keywords')['bed_room']['is_restrict_execute_room'].' phải là số nguyên!',
            'is_restrict_execute_room.in'         => config('keywords')['bed_room']['is_restrict_execute_room'].' phải là 0 hoặc 1!', 

            'room_type_id.required'    => config('keywords')['bed_room']['room_type_id'].' không được bỏ trống!',            
            'room_type_id.integer'     => config('keywords')['bed_room']['room_type_id'].' phải là số nguyên!',
            'room_type_id.exists'      => config('keywords')['bed_room']['room_type_id'].' = '.$this->room_type_id.' không tồn tại!',  

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
            if ($this->has('treatment_type_ids') && (strlen($this->treatment_type_ids) >= 20)) {
                $validator->errors()->add('treatment_type_ids', config('keywords')['bed_room']['treatment_type_ids'].' tối đa 20 kí tự!');
            }
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
