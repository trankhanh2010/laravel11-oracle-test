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
            'bed_room_code' => 'required|string|max:20|unique:App\Models\HIS\BedRoom,bed_room_code',
            'bed_room_name' => 'required|string|max:100',
            'department_id' => 'required|integer|max:22|exists:App\Models\HIS\Department,id',
            'area_id' => 'nullable|integer|max:22|exists:App\Models\HIS\Area,id',
            'speciality_id' => 'nullable|integer|max:22|exists:App\Models\HIS\Speciality,id',
            'default_cashier_room_id' => 'nullable|integer|max:22|exists:App\Models\HIS\CashierRoom,id',
            'default_instr_patient_type_id'  => 'nullable|integer|max:22|exists:App\Models\HIS\PatientType,id',
            'is_surgery'  => 'nullable|integer|max:22|in:0,1',
            'is_restrict_req_service' => 'nullable|integer|max:22|in:0,1',
            'is_pause' => 'nullable|integer|max:22|in:0,1',
            'is_restrict_execute_room' =>'nullable|integer|max:22|in:0,1',
        ];
    }
    public function messages()
    {
        return [
            'bed_room_code.required' => 'Mã buồng bệnh không được bỏ trống!',
            'bed_room_code.string' => 'Mã buồng bệnh phải là chuỗi string!',
            'bed_room_code.max' => 'Mã buồng bệnh tối đa 20 kí tự!',            
            'bed_room_code.unique' => 'Mã buồng bệnh '.$this->bed_room.' đã tồn tại!',

            'bed_room_name.required' => 'Tên buồng bệnh không được bỏ trống!',
            'bed_room_name.string' => 'Tên buồng bệnh phải là chuỗi string!',
            'bed_room_name.max' => 'Tên buồng bệnh tối đa 100 kí tự!',   
            
            'department_id.required' => config('keywords')['department_id'].' không được bỏ trống!',            
            'department_id.integer' => config('keywords')['department_id'].' phải là số nguyên!',
            'department_id.max' => config('keywords')['department_id'].' tối đa 22 kí tự!',            
            'department_id.exists' => config('keywords')['department_id'].' = '.$this->department_id.' không tồn tại!',  
            
            'area_id.integer' => 'Id khu vực phải là số nguyên!',
            'area_id.max' => 'Id khu vực tối đa 22 kí tự!',            
            'area_id.exists' => 'Id khu vực '.$this->department_id.' không tồn tại!',  

            'speciality_id.integer' => 'Id chuyên khoa phải là số nguyên!',
            'speciality_id.max' => 'Id chuyên khoa tối đa 22 kí tự!',            
            'speciality_id.exists' => 'Id chuyên khoa '.$this->department_id.' không tồn tại!',  

            'default_cashier_room_id.integer' => 'Id phòng thu ngân phải là số nguyên!',
            'default_cashier_room_id.max' => 'Id phòng thu ngân tối đa 22 kí tự!',            
            'default_cashier_room_id.exists' => 'Id phòng thu ngân không tồn tại!', 

            'default_instr_patient_type_id.integer' => 'Id đối tượng  thanh toán mặc định khi chỉ định dịch vụ CLS phải là số nguyên!',
            'default_instr_patient_type_id.max' => 'Id đối tượng  thanh toán mặc định khi chỉ định dịch vụ CLS tối đa 22 kí tự!',            
            'default_instr_patient_type_id.exists' => 'Id đối tượng  thanh toán mặc định khi chỉ định dịch vụ CLS không tồn tại!', 

            'is_surgery.integer' => 'Trường là buồng phẫu thuật phải là số nguyên!',
            'is_surgery.max' => 'Trường là buồng phẫu thuật tối đa 22 kí tự!', 
            'is_surgery.in' => 'Trường là buồng phẫu thuật phải là 0 hoặc 1!', 

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
                $validator->errors()->add('treatment_type_ids', 'Danh sách id diện điều trị tối đa 20 kí tự!');
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
