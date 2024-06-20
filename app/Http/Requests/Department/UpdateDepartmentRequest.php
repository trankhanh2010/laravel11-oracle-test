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
            'department_name' =>                'required|string|max:100',
            'g_code' =>                         'required|string|max:20|exists:App\Models\SDA\Group,g_code',
            'bhyt_code' =>                      'nullable|string|max:50',
            'default_instr_patient_type_id' =>  'nullable|integer|exists:App\Models\HIS\PatientType,id',
            'allow_treatment_type_ids' =>       'nullable|string|max:20',
            'theory_patient_count' =>           'nullable|integer|min:0',
            'reality_patient_count' =>          'nullable|integer|min:0',
            'req_surg_treatment_type_id' =>     'nullable|integer|exists:App\Models\HIS\TreatmentType,id',
            'phone' =>                          'nullable|string|max:50',
            'head_loginname' =>                 'nullable|string|max:50|exists:App\Models\HIS\Employee,loginname',
            'head_username' =>                  'nullable|string|max:100|exists:App\Models\HIS\Employee,tdl_username',
            'accepted_icd_codes' =>             'nullable|string|max:4000',
            'is_exam' =>                        'nullable|integer|in:0,1',
            'is_clinical' =>                    'nullable|integer|in:0,1',
            'allow_assign_package_price' =>     'nullable|integer|in:0,1',
            'auto_bed_assign_option' =>         'nullable|integer|in:0,1',
            'is_emergency' =>                   'nullable|integer|in:0,1',
            'is_auto_receive_patient' =>        'nullable|integer|in:0,1',
            'allow_assign_surgery_price' =>     'nullable|integer|in:0,1',
            'is_in_dep_stock_moba' =>           'nullable|integer|in:0,1',
            'warning_when_is_no_surg' =>        'nullable|integer|in:0,1',
        ];
    }
    public function messages()
    {
        return [

            'department_name.required'  => config('keywords')['department']['department_name'] . ' không được bỏ trống!',
            'department_name.string'    => config('keywords')['department']['department_name'] . ' phải là chuỗi string!',
            'department_name.max'       => config('keywords')['department']['department_name'] . ' tối đa 100 kí tự!',

            'g_code.required'   => config('keywords')['department']['g_code'] . ' không được bỏ trống!',
            'g_code.string'     => config('keywords')['department']['g_code'] . ' phải là chuỗi string!',
            'g_code.max'        => config('keywords')['department']['g_code'] . ' tối đa 20 kí tự!',
            'g_code.exists'     => config('keywords')['department']['g_code'] . ' = ' . $this->g_code . ' không tồn tại!',

            'bhyt_code.string'  => config('keywords')['department']['bhyt_code'] . ' phải là chuỗi string!',
            'bhyt_code.max'     => config('keywords')['department']['bhyt_code'] . ' tối đa 50 kí tự!',

            'default_instr_patient_type_id.integer'     => config('keywords')['department']['default_instr_patient_type_id'] . ' phải là số nguyên!',
            'default_instr_patient_type_id.exists'      => config('keywords')['department']['default_instr_patient_type_id'] . ' = ' . $this->default_instr_patient_type_id . ' không tồn tại!',

            'allow_treatment_type_ids.string'  => config('keywords')['department']['warning_when_is_no_surg'] . ' phải là chuỗi string!',
            
            'theory_patient_count.integer'  => config('keywords')['department']['theory_patient_count'] . ' phải là số nguyên!',
            'theory_patient_count.min'  => config('keywords')['department']['theory_patient_count'].' lớn hơn bằng 0!',

            'reality_patient_count.integer'     => config('keywords')['department']['reality_patient_count'] . ' phải là số nguyên!',
            'reality_patient_count.min'  => config('keywords')['department']['reality_patient_count'].' lớn hơn bằng 0!',

            'req_surg_treatment_type_id.integer'    => config('keywords')['department']['req_surg_treatment_type_id'] . ' phải là số nguyên!',
            'req_surg_treatment_type_id.exists'     => config('keywords')['department']['req_surg_treatment_type_id'] . ' = ' . $this->req_surg_treatment_type_id . ' không tồn tại!',

            'phone.string'  => config('keywords')['department']['phone'] . ' phải là chuỗi string!',
            'phone.max'     => config('keywords')['department']['phone'] . ' tối đa 50 kí tự!',

            'head_loginname.string'     => config('keywords')['department']['head_loginname'] . ' phải là chuỗi string!',
            'head_loginname.max'        => config('keywords')['department']['head_loginname'] . ' tối đa 50 kí tự!',
            'head_loginname.exists'     => config('keywords')['department']['head_loginname'].' = '.$this->head_loginname.' không tồn tại!',  

            'head_username.string'  => config('keywords')['department']['head_username'] . ' phải là chuỗi string!',
            'head_username.max'     => config('keywords')['department']['head_username'] . ' tối đa 100 kí tự!',
            'head_username.exists'  => config('keywords')['department']['head_username'].' = '.$this->head_username.' không tồn tại!',  

            'accepted_icd_codes.string' => config('keywords')['department']['accepted_icd_codes'] . ' phải là chuỗi string!',

            'is_exam.integer'   => config('keywords')['department']['is_exam'] . ' phải là số nguyên!',
            'is_exam.in'        => config('keywords')['department']['is_exam'] . ' phải là 0 hoặc 1!',

            'is_clinical.integer'   => config('keywords')['department']['is_clinical'] . ' phải là số nguyên!',
            'is_clinical.in'        => config('keywords')['department']['is_clinical'] . ' phải là 0 hoặc 1!',

            'allow_assign_package_price.integer'    => config('keywords')['department']['allow_assign_package_price'] . ' phải là số nguyên!',
            'allow_assign_package_price.in'         => config('keywords')['department']['allow_assign_package_price'] . ' phải là 0 hoặc 1!',

            'auto_bed_assign_option.integer'    => config('keywords')['department']['auto_bed_assign_option'] . ' phải là số nguyên!',
            'auto_bed_assign_option.in'         => config('keywords')['department']['auto_bed_assign_option'] . ' phải là 0 hoặc 1!',

            'is_emergency.integer'  => config('keywords')['department']['is_emergency'] . ' phải là số nguyên!',
            'is_emergency.in'       => config('keywords')['department']['is_emergency'] . ' phải là 0 hoặc 1!',

            'is_auto_receive_patient.integer'   => config('keywords')['department']['is_auto_receive_patient'] . ' phải là số nguyên!',
            'is_auto_receive_patient.in'        => config('keywords')['department']['is_auto_receive_patient'] . ' phải là 0 hoặc 1!',

            'allow_assign_surgery_price.integer'    => config('keywords')['department']['allow_assign_surgery_price'] . ' phải là số nguyên!',
            'allow_assign_surgery_price.in'         => config('keywords')['department']['allow_assign_surgery_price'] . ' phải là 0 hoặc 1!',

            'is_in_dep_stock_moba.integer'  => config('keywords')['department']['is_in_dep_stock_moba'] . ' phải là số nguyên!',
            'is_in_dep_stock_moba.in'       => config('keywords')['department']['is_in_dep_stock_moba'] . ' phải là 0 hoặc 1!',

            'warning_when_is_no_surg.integer'   => config('keywords')['department']['warning_when_is_no_surg'] . ' phải là số nguyên!',
            'warning_when_is_no_surg.in'        => config('keywords')['department']['warning_when_is_no_surg'] . ' phải là 0 hoặc 1!',

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
                $validator->errors()->add('accepted_icd_codes', config('keywords')['department']['accepted_icd_codes'] . ' tối đa 4000 kí tự!');
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
