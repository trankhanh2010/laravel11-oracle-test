<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
class CreateDepartmentRequest extends FormRequest
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
        $head_loginname = $this->head_loginname ?? "";
        return [
            'department_code' =>                'required|string|max:20|unique:App\Models\HIS\Department,department_code',
            'department_name' =>                'required|string|max:100',
            'g_code' =>                         [
                'required',
                'string',
                'max:20',
                Rule::exists('App\Models\SDA\Group', 'g_code')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'bhyt_code' =>                      'nullable|string|max:50',
            'branch_id' =>                      [
                'required',
                'integer',
                Rule::exists('App\Models\HIS\Branch', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'default_instr_patient_type_id' =>  [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\PatientType', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'num_order' =>                      'nullable|integer',
            'allow_treatment_type_ids' =>       'nullable|string|max:20',
            'theory_patient_count' =>           'nullable|integer|min:0',
            'reality_patient_count' =>          'nullable|integer|min:0',
            'req_surg_treatment_type_id' =>     [
                'nullable',
                'integer',
                Rule::exists('App\Models\HIS\TreatmentType', 'id')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'phone' =>                          'nullable|string|max:50',
            'head_loginname' =>                 [
                'nullable',
                'string',
                'max:50',
                Rule::exists('App\Models\HIS\Employee', 'loginname')
                ->where(function ($query) {
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1);
                }),
            ],
            'head_username' =>                  [
                'nullable',
                'string',
                'max:100',
                Rule::exists('App\Models\HIS\Employee', 'tdl_username')
                ->where(function ($query) use ($head_loginname){
                    $query = $query
                    ->where(DB::connection('oracle_his')->raw("is_active"), 1)
                    ->where(DB::connection('oracle_his')->raw("loginname"), $head_loginname);
                }),
            ],
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
            'department_code.required'  => config('keywords')['department']['department_code'].config('keywords')['error']['required'],
            'department_code.string'    => config('keywords')['department']['department_code'].config('keywords')['error']['string'],
            'department_code.max'       => config('keywords')['department']['department_code'].config('keywords')['error']['string_max'],            
            'department_code.unique'    => config('keywords')['department']['department_code'].config('keywords')['error']['unique'],

            'department_name.required'  => config('keywords')['department']['department_name'].config('keywords')['error']['required'],
            'department_name.string'    => config('keywords')['department']['department_name'].config('keywords')['error']['string'],
            'department_name.max'       => config('keywords')['department']['department_name'].config('keywords')['error']['string_max'],   
            
            'g_code.required'   => config('keywords')['department']['g_code'].config('keywords')['error']['required'],            
            'g_code.string'     => config('keywords')['department']['g_code'].config('keywords')['error']['string'],
            'g_code.max'        => config('keywords')['department']['g_code'].config('keywords')['error']['string_max'],            
            'g_code.exists'     => config('keywords')['department']['g_code'].config('keywords')['error']['exists'],            

            'bhyt_code.string'  => config('keywords')['department']['bhyt_code'].config('keywords')['error']['string'],
            'bhyt_code.max'     => config('keywords')['department']['bhyt_code'].config('keywords')['error']['string_max'],      

            'branch_id.required'    => config('keywords')['department']['branch_id'].config('keywords')['error']['required'],            
            'branch_id.integer'     => config('keywords')['department']['branch_id'].config('keywords')['error']['integer'],
            'branch_id.exists'      => config('keywords')['department']['branch_id'].config('keywords')['error']['exists'], 

            'default_instr_patient_type_id.integer'     => config('keywords')['department']['default_instr_patient_type_id'].config('keywords')['error']['integer'],
            'default_instr_patient_type_id.exists'      => config('keywords')['department']['default_instr_patient_type_id'].config('keywords')['error']['exists'], 

            'num_order.integer'     => config('keywords')['department']['num_order'].config('keywords')['error']['integer'],

            'allow_treatment_type_ids.string'   => config('keywords')['department']['allow_treatment_type_ids'].config('keywords')['error']['string'],
            'allow_treatment_type_ids.max'      => config('keywords')['department']['allow_treatment_type_ids'].config('keywords')['error']['string_max'],

            'theory_patient_count.integer'  => config('keywords')['department']['theory_patient_count'].config('keywords')['error']['integer'],
            'theory_patient_count.min'      => config('keywords')['department']['theory_patient_count'].config('keywords')['error']['integer_min'],

            'reality_patient_count.integer'     => config('keywords')['department']['reality_patient_count'].config('keywords')['error']['integer'],
            'reality_patient_count.min'         => config('keywords')['department']['reality_patient_count'].config('keywords')['error']['integer_min'],

            'req_surg_treatment_type_id.integer'    => config('keywords')['department']['req_surg_treatment_type_id'].config('keywords')['error']['integer'],
            'req_surg_treatment_type_id.exists'     => config('keywords')['department']['req_surg_treatment_type_id'].config('keywords')['error']['exists'], 

            'phone.string'  => config('keywords')['department']['phone'].config('keywords')['error']['string'],
            'phone.max'     => config('keywords')['department']['phone'].config('keywords')['error']['string_max'],   

            'head_loginname.string'     => config('keywords')['department']['head_loginname'].config('keywords')['error']['string'],
            'head_loginname.max'        => config('keywords')['department']['head_loginname'].config('keywords')['error']['string_max'], 
            'head_loginname.exists'     => config('keywords')['department']['head_loginname'].config('keywords')['error']['exists'],  

            'head_username.string'  => config('keywords')['department']['head_username'].config('keywords')['error']['string'],
            'head_username.max'     => config('keywords')['department']['head_username'].config('keywords')['error']['string_max'], 
            'head_username.exists'  => config('keywords')['department']['head_username'].config('keywords')['error']['exists'].config('keywords')['error']['not_in_loginname'],  

            'accepted_icd_codes.string' => config('keywords')['department']['accepted_icd_codes'].config('keywords')['error']['string'],
            'accepted_icd_codes.max'    => config('keywords')['department']['accepted_icd_codes'].config('keywords')['error']['string_max'],

            'is_exam.integer'   => config('keywords')['department']['is_exam'].config('keywords')['error']['integer'],
            'is_exam.in'        => config('keywords')['department']['is_exam'].config('keywords')['error']['in'],  

            'is_clinical.integer'   => config('keywords')['department']['is_clinical'].config('keywords')['error']['integer'],
            'is_clinical.in'        => config('keywords')['department']['is_clinical'].config('keywords')['error']['in'], 

            'allow_assign_package_price.integer'    => config('keywords')['department']['allow_assign_package_price'].config('keywords')['error']['integer'],
            'allow_assign_package_price.in'         => config('keywords')['department']['allow_assign_package_price'].config('keywords')['error']['in'], 

            'auto_bed_assign_option.integer'    => config('keywords')['department']['auto_bed_assign_option'].config('keywords')['error']['integer'],
            'auto_bed_assign_option.in'         => config('keywords')['department']['auto_bed_assign_option'].config('keywords')['error']['in'], 
            
            'is_emergency.integer'  => config('keywords')['department']['is_emergency'].config('keywords')['error']['integer'],
            'is_emergency.in'       => config('keywords')['department']['is_emergency'].config('keywords')['error']['in'], 

            'is_auto_receive_patient.integer'   => config('keywords')['department']['is_auto_receive_patient'].config('keywords')['error']['integer'],
            'is_auto_receive_patient.in'        => config('keywords')['department']['is_auto_receive_patient'].config('keywords')['error']['in'], 

            'allow_assign_surgery_price.integer'    => config('keywords')['department']['allow_assign_surgery_price'].config('keywords')['error']['integer'],
            'allow_assign_surgery_price.in'         => config('keywords')['department']['allow_assign_surgery_price'].config('keywords')['error']['in'], 

            'is_in_dep_stock_moba.integer'  => config('keywords')['department']['is_in_dep_stock_moba'].config('keywords')['error']['integer'],
            'is_in_dep_stock_moba.in'       => config('keywords')['department']['is_in_dep_stock_moba'].config('keywords')['error']['in'],

            'warning_when_is_no_surg.integer'   => config('keywords')['department']['warning_when_is_no_surg'].config('keywords')['error']['integer'],
            'warning_when_is_no_surg.in'        => config('keywords')['department']['warning_when_is_no_surg'].config('keywords')['error']['in'], 

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
            if ($this->has('allow_treatment_type_ids_list') && ($this->allow_treatment_type_ids_list[0] != null)) {
                foreach ($this->allow_treatment_type_ids_list as $id) {
                    if (!is_numeric($id) || !\App\Models\HIS\TreatmentType::where('id', $id)->where('is_active', 1)->first()) {
                        $validator->errors()->add('allow_treatment_type_ids', 'Diện điều trị với id = ' . $id . ' trong danh sách diện điều trị không tồn tại!');
                    }
                }
            }
            ///////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($this->has('accepted_icd_codes_list') && ($this->accepted_icd_codes_list[0] != null)) {
                foreach ($this->accepted_icd_codes_list as $icd_code) {
                    if (!\App\Models\HIS\Icd::where('icd_code', $icd_code)->where('is_active', 1)->exists()) {
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
