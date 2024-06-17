<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class UpdateDepartmentRequest extends FormRequest
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
            'department_name' => 'required|string|max:100',
            'g_code' => 'required|string|max:20|exists:App\Models\SDA\Group,g_code',
            'bhyt_code' => 'string|max:50',
            'default_instr_patient_type_id' => 'integer|max:22|exists:App\Models\HIS\PatientType,id',
            'allow_treatment_type_ids' => 'string|max:20',
            'theory_patient_count' => 'integer|max:22',
            'reality_patient_count' => 'integer|max:22',
            'req_surg_treatment_type_id' => 'integer|max:22|exists:App\Models\HIS\TreatmentType,id',
            'phone' => 'string|max:50',
            'head_loginname' => 'string|max:50',
            'head_username' => 'string|max:100',
            'accepted_icd_codes' => 'string|max:4000',
            'is_exam' => 'integer|max:22|in:0,1',
            'is_clinical' => 'integer|max:22|in:0,1',
            'allow_assign_package_price' => 'integer|max:22|in:0,1',
            'auto_bed_assign_option' => 'integer|max:22|in:0,1',
            'is_emergency' => 'integer|max:22|in:0,1',
            'is_auto_receive_patient' => 'integer|max:22|in:0,1',
            'allow_assign_surgery_price' => 'integer|max:22|in:0,1',
            'is_in_dep_stock_moba' => 'integer|max:22|in:0,1',
            'warning_when_is_no_surg' => 'integer|max:22|in:0,1',
        ];
    }
    public function messages()
    {
        return [

            'department_name.required' => 'Tên khoa không được bỏ trống!',
            'department_name.string' => 'Tên khoa phải là chuỗi string!',
            'department_name.max' => 'Tên khoa tối đa 100 kí tự!',   
            
            'g_code.required' => 'Mã đơn vị không được bỏ trống!',            
            'g_code.string' => 'Mã đơn vị phải là chuỗi string!',
            'g_code.max' => 'Mã đơn vị tối đa 20 kí tự!',            
            'g_code.exists' => 'Mã đơn vị '.$this->g_code.' không tồn tại!',            

            'bhyt_code.string' => 'Mã BHYT phải là chuỗi string!',
            'bhyt_code.max' => 'Mã BHYT tối đa 50 kí tự!',      

            'default_instr_patient_type_id.integer' => 'Id đối tượng  thanh toán mặc định khi chỉ định dịch vụ CLS phải là số nguyên!',
            'default_instr_patient_type_id.max' => 'Id đối tượng thanh toán mặc định khi chỉ định dịch vụ CLS tối đa 22 kí tự!',            
            'default_instr_patient_type_id.exists' => 'Id đối tượng thanh toán mặc định khi chỉ định dịch vụ CLS không tồn tại!', 

            'theory_patient_count.integer' => 'Số giường kế hoạch phải là số nguyên!',
            'theory_patient_count.max' => 'Số giường kế hoạch tối đa 22 kí tự!',   

            'reality_patient_count.integer' => 'Số giường thực tế phải là số nguyên!',
            'reality_patient_count.max' => 'Số giường thực tế tối đa 22 kí tự!',   

            'req_surg_treatment_type_id.integer' => 'Id diện điều trị được dùng khi tính công phẫu thuật thủ thuật đối với khoa chỉ định dịch vụ phải là số nguyên!',
            'req_surg_treatment_type_id.max' => 'Id diện điều trị được dùng khi tính công phẫu thuật thủ thuật đối với khoa chỉ định dịch vụ tối đa 22 kí tự!',            
            'req_surg_treatment_type_id.exists' => 'Id diện điều trị được dùng khi tính công phẫu thuật thủ thuật đối với khoa chỉ định dịch vụ không tồn tại!', 

            'phone.string' => 'Số điện thoại phải là chuỗi string!',
            'phone.max' => 'Số điện thoại tối đa 50 kí tự!',   

            'head_loginname.string' => 'Loginname của trưởng khoa phải là chuỗi string!',
            'head_loginname.max' => 'Loginname của trưởng khoa tối đa 50 kí tự!', 

            'head_username.string' => 'Username của trưởng khoa phải là chuỗi string!',
            'head_username.max' => 'Username của trưởng khoa tối đa 100 kí tự!', 

            'is_exam.integer' => 'Trường là khoa khám bệnh phải là số nguyên!',
            'is_exam.max' => 'Trường là khoa khám bệnh tối đa 22 kí tự!',  
            'is_exam.in' => 'Trường là khoa khám bệnh phải là 0 hoặc 1!',  

            'is_clinical.integer' => 'Trường là khoa lâm sàng phải là số nguyên!',
            'is_clinical.max' => 'Trường là khoa lâm sàng tối đa 22 kí tự!', 
            'is_clinical.in' => 'Trường là khoa lâm sàng phải là 0 hoặc 1!', 

            'allow_assign_package_price.integer' => 'Trường cho phép nhập giá gói lúc chỉ định gói phải là số nguyên!',
            'allow_assign_package_price.max' => 'Trường cho phép nhập giá gói lúc chỉ định gói tối đa 22 kí tự!', 
            'allow_assign_package_price.in' => 'Trường cho phép nhập giá gói lúc chỉ định gói phải là 0 hoặc 1!', 

            'auto_bed_assign_option.integer' => 'Trường tự động cảnh báo và cho phép chỉ định giường, dịch vụ giường khi chuyển khoa, kết thúc điều trị phải là số nguyên!',
            'auto_bed_assign_option.max' => 'Trường tự động cảnh báo và cho phép chỉ định giường, dịch vụ giường khi chuyển khoa, kết thúc điều trị tối đa 22 kí tự!', 
            'auto_bed_assign_option.in' => 'Trường tự động cảnh báo và cho phép chỉ định giường, dịch vụ giường khi chuyển khoa, kết thúc điều trị phải là 0 hoặc 1!', 
            
            'is_emergency.integer' => 'Trường là khoa cấp cứu phải là số nguyên!',
            'is_emergency.max' => 'Trường là khoa cấp cứu tối đa 22 kí tự!', 
            'is_emergency.in' => 'Trường là khoa cấp cứu phải là 0 hoặc 1!', 

            'is_auto_receive_patient.integer' => 'Trường tự động tiếp nhận bệnh nhân vào khoa phải là số nguyên!',
            'is_auto_receive_patient.max' => 'Trường tự động tiếp nhận bệnh nhân vào khoa tối đa 22 kí tự!', 
            'is_auto_receive_patient.in' => 'Trường tự động tiếp nhận bệnh nhân vào khoa phải là 0 hoặc 1!', 

            'allow_assign_surgery_price.integer' => 'Trường cho phép nhập giá lúc chỉ định phẫu thuật phải là số nguyên!',
            'allow_assign_surgery_price.max' => 'Trường cho phép nhập giá lúc chỉ định phẫu thuật tối đa 22 kí tự!', 
            'allow_assign_surgery_price.in' => 'Trường cho phép nhập giá lúc chỉ định phẫu thuật phải là 0 hoặc 1!', 

            'is_in_dep_stock_moba.integer' => 'Trường mặc định chọn kho thu hồi là kho thuộc khoa phải là số nguyên!',
            'is_in_dep_stock_moba.max' => 'Trường mặc định chọn kho thu hồi là kho thuộc khoa tối đa 22 kí tự!',
            'is_in_dep_stock_moba.in' => 'Trường mặc định chọn kho thu hồi là kho thuộc khoa phải là 0 hoặc 1!',

            'warning_when_is_no_surg.integer' => 'Trường cảnh báo khi chưa chỉ định dịch vụ phẫu thuật phải là số nguyên!',
            'warning_when_is_no_surg.max' => 'Trường cảnh báo khi chưa chỉ định dịch vụ phẫu thuật cứu tối đa 22 kí tự!', 
            'warning_when_is_no_surg.in' => 'Trường cảnh báo khi chưa chỉ định dịch vụ phẫu thuật cứu phải là 0 hoặc 1!', 

        ];
    }
    protected function prepareForValidation()
    {
        if ($this->has('allow_treatment_type_ids')) {
            $this->merge([
                'allow_treatment_type_ids_list' => explode(',', $this->allow_treatment_type_ids),
            ]);
        }
        if ($this->has('accepted_icd_codes')) {
            $this->merge([
                'accepted_icd_codes_list' => explode(',', $this->accepted_icd_codes),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('allow_treatment_type_ids') && (strlen($this->allow_treatment_type_ids) >= 20)) {
                $validator->errors()->add('allow_treatment_type_ids', 'Danh sách id diện điều trị tối đa 20 kí tự!');
            }
            if ($this->has('allow_treatment_type_ids_list') && ($this->allow_treatment_type_ids_list[0] != null)) {
                foreach ($this->allow_treatment_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\TreatmentType::find($id)) {
                        $validator->errors()->add('allow_treatment_type_ids', 'Diện điều trị với id = ' . $id . ' trong danh sách diện điều trị không tồn tại!');
                    }
                }
            }
            ///////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($this->has('accepted_icd_codes') && (strlen($this->accepted_icd_codes) >= 4000)) {
                $validator->errors()->add('accepted_icd_codes', 'Danh sách icd code diện điều trị tối đa 4000 kí tự!');
            }
            if ($this->has('accepted_icd_codes_list') && ($this->accepted_icd_codes_list[0] != null)) {
                foreach ($this->accepted_icd_codes_list as $icd_code) {
                    if (!\App\Models\HIS\Icd::where('icd_code', $icd_code)->exists()) {
                        $validator->errors()->add('accepted_icd_codes', 'Chẩn đoán nhập viện với icd code = ' . $icd_code . ' trong danh sách chẩn đoán nhập viện không tồn tại!');
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
